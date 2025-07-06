<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\BillingTypes;
use App\Models\Unit;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UnitPaymentController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new UnitPaymentSchedules());
    }

    public $statuses = [
        'Active' => ['id' => 1, 'label' => 'Paid', 'class' => 'success'],
        'Pending' => ['id' => 0, 'label' => 'Pending', 'class' => 'warning'],
        'Terminated' => ['id' => 2, 'label' => 'Pending Verification', 'class' => 'info'],
    ];

    public function index($id, Request $request)
    {
        $contract = UnitContracts::find($id);
        if ($contract == null) {
            abort(403, "Invalid contract");
        }

        if ($this->trash) {
            $this->repository->setPageTitle("Unit Payments - Trashed | " . $contract->unit->unit_name);
        } else {
            $this->repository->setPageTitle("Unit Payments | " . $contract->unit->unit_name);
        }

        $this->repository->setColumns(
            "payment_date",
            "contract.unit.property.name",
            "contract.unit.unit_name",
            "contract.tenant.name",
            "note",
            "amount",
            "status",
            "paid_at"
        );

        $this->repository->setColumnDisplay("status", [$this->repository, 'displayStatusAs'], [true])
            ->setColumnLabel("contract.unit.property.name", "Property Name")
            ->setColumnLabel("contract.unit.unit_name", "Unit Name")
            ->setColumnLabel("contract.tenant.name", "Tenent Name")
            ->setColumnLabel("note", "Billing Type")
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnSearchability("created_at", false)
            ->addFilter('status', 'Status', 'select', [0 => 'Pending', 2 => 'Pending Verification', 1 => 'Paid'])
            ->addFilter('unit_billing_type_id', 'Billing Type', 'select', BillingTypes::pluck('name', 'id')->toArray())
            ->addFilter('payment_date_from', 'From Date', 'date') // ✅ added
            ->addFilter('payment_date_to', 'To Date', 'date')     // ✅ added
            ->setDefaultOrder('payment_date', 'asc')->setRefferanceId($id);


        $query = UnitPaymentSchedules::with([
            'contract',                 // 🟢 for unit_id
            'contract.unit',            // 🟢 for displaying unit info
            'contract.unit.property',
            'contract.tenant',
            'unitBillingType.billingType'
        ]);

        if ($id) {
            $query = $query->where("unit_contract_id", $id);
        }

        $query = $query->select('unit_payment_schedules.*');

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle($contract->unit->unit_name . " | Unit Payments - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle($contract->unit->unit_name . " | Unit Payments")
                ->disableViewData("view")
                ->enableViewData("export", "trash", "edit", "add", "trashList");
        }

        return $this->repository->render("layouts.master")->index($query);
    }

    public function trash($id, Request $request)
    {
        $this->trash = true;
        return $this->index($id, $request);
    }

    public function edit($id)
    {
        $record = UnitPaymentSchedules::findOrFail($id);
        return view('unit-payments.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $payment = UnitPaymentSchedules::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment record not found.'], 404);
        }

        // 🧪 Validate input
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'installment_number' => 'nullable|integer|min:1',
            'note' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        // 📝 Update values
        $payment->payment_date = $validated['payment_date'];
        $payment->amount = $validated['amount'];
        $payment->installment_number = $validated['installment_number'] ?? null;
        $payment->note = $validated['note'] ?? null;
        $payment->status = $validated['status'];
        if ($validated['status'] == 1) {
            $payment->paid_at = Carbon::now();
        } else {
            $payment->paid_at = null;
        }

        $payment->save();

        return response()->json([
            'message' => 'Unit payment updated successfully.',
            'redirect' => route('unit-payment-schedules.index', $payment->unit_contract_id)
        ]);
    }
}
