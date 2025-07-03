<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payments;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new UnitContracts());
    }

    public $statuses = [
        'Active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'Pending' => ['id' => 0, 'label' => 'Pending', 'class' => 'warning'],
        'Terminated' => ['id' => 2, 'label' => 'Terminated', 'class' => 'danger'],
    ];

    public $approvalStatuses = [
        'Not sent for approval' => ['id' => null, 'label' => 'Not sent for approval', 'class' => 'info'],
        'Pending Approval' => ['id' => 0, 'label' => 'Pending Approval', 'class' => 'warning'],
        'Approved' => ['id' => 1, 'label' => 'Approved', 'class' => 'success'],
        'Declined' => ['id' => 2, 'label' => 'Declined', 'class' => 'danger']
    ];

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Unit Contracts - Trashed");
        } else {
            $this->repository->setPageTitle("Unit Contracts");
        }

        $this->repository
            ->setColumns(
                "id",
                "tenant.name",
                "unit.unit_name",
                "agreement_start_date",
                "agreement_end_date",
                "rent_amount",
                "next_rent_due_date",
                "status",
                "approval_status",
                "created_at"
            )
            ->setColumnLabel("tenant.name", "Tenant Name")
            ->setColumnLabel("unit.unit_name", "Unit Name")
            ->setColumnLabel("next_rent_due_date", "Next Due Date")
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnDisplay(
                'approval_status',
                [$this->repository, 'displayStatusAs'],
                [$this->approvalStatuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);


        $query = UnitContracts::with(['tenant', 'unit']);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Unit Contracts - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Unit Contracts")
                ->disableViewData("view")
                ->enableViewData("export", "trash", "edit", "add", "trashList");
        }

        return $this->repository->render("layouts.master")->index($query);
    }

    public function trash(Request $request)
    {
        $this->trash = true;
        return $this->index($request);
    }

    public function create()
    {
        return view('contracts.create');
    }
    public function edit($id)
    {
        $record = UnitContracts::with(['tenant', 'unit'])->findOrFail($id);
        return view('contracts.edit', compact('record'));
    }

    public function generateRentalPayments($model)
    {
        $billingTypes = $model->unit->billingTypes ?? [];
        $contractId = $model->id ?? null;
        $fullAmount = $model->full_amount;
        $rentAmount = $model->rent_amount;
        $nextDueDate = null;
        $totalInstallment = null;
        $rentPaymentType = $model->rent_payment_type;
        $durationInMonths = $model->duration_in_months;

        $totalInstallment = ceil($fullAmount / $rentAmount);
        $nextDueDate = now()->addMonth();

        if ($rentAmount > 0 && $rentPaymentType == 2) {
            $dueDate = now();
            for ($i = 1; $i <= $durationInMonths; $i++) {

                $payment = new UnitPaymentSchedules();
                $payment->unit_contract_id = $contractId;
                $payment->installment_number = $i;
                $payment->amount = $rentAmount;
                $payment->payment_date =  $dueDate->copy()->addMonths($i - 1);
                $payment->is_rent = 1;
                $payment->status = 0;
                $payment->note = "Rent of installment_number " . $i;
                $payment->save();
            }
        }

        foreach ($billingTypes as $bType) {
            $dueDate = now();
            $payment = UnitPaymentSchedules::where("unit_contract_id", $contractId)
                ->where("payment_date", $dueDate)
                ->where("unit_billing_type_id", $bType->id)->first();

            if (isset($bType->billingType->name)) {
                if ($payment == null) {
                    $payment = new UnitPaymentSchedules();
                }

                $payment->unit_contract_id = $contractId;
                $payment->unit_billing_type_id = $bType->id;
                $payment->amount = $rentAmount;
                $payment->payment_date =  $dueDate;
                $payment->status = 0;
                $payment->note = $bType->billingType->name;
                $payment->save();
            }
        }

        // Return or process as needed
        return [
            'total_installments' => $totalInstallment,
            'rent_amount' => $rentAmount,
            'next_due_date' => $nextDueDate,
        ];
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'unit_id' => 'required|exists:units,id',
            'agreement_start_date' => 'required|date',
            'agreement_end_date' => 'required|date|after_or_equal:agreement_start_date',
            // 'rent_payment_type' => 'nullable|integer',
            // 'full_amount' => 'nullable|numeric|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
        ]);

        $record = new UnitContracts();
        $record->tenant_id = $request->tenant_id;
        $record->unit_id = $request->unit_id;
        $record->agreement_start_date = $request->agreement_start_date;
        $record->agreement_end_date = $request->agreement_end_date;
        $record->rent_amount = $request->rent_amount;
        $record->deposit_amount = $request->deposit_amount;
        $record->rent_payment_type = 2;
        $record->duration_in_months = 12;
        $record->full_amount = 10000.0;
        $record->terms = $request->terms;
        $record->save();

        // Calculate rental payment fields
        $calc = $this->generateRentalPayments($record);

        $record->rent_amount = $calc['rent_amount'];
        $record->total_installments = $calc['total_installments'];
        $record->next_rent_due_date = $calc['next_due_date'];

        if ($calc['total_installments'] == 1) {
            $record->completed_installments = 1;
            $record->total_paid_amount = $record->full_amount;
        } else {
            $record->completed_installments = 0;
            $record->total_paid_amount = $record->rent_amount;
        }


        $record->save();

        return response()->json("success");
    }

    public function update($id, Request $request)
    {
        $record = UnitContracts::findOrFail($id);
        $data = request()->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'unit_id' => 'required|exists:units,id',
            'agreement_start_date' => 'required|date',
            'agreement_end_date' => 'required|date|after_or_equal:agreement_start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $record->tenant_id = $request->tenant_id;
        $record->unit_id = $request->unit_id;
        $record->agreement_start_date = $request->agreement_start_date;
        $record->agreement_end_date = $request->agreement_end_date;
        $record->rent_amount = $request->rent_amount;
        $record->deposit_amount = $request->deposit_amount;
        $record->terms = $request->terms;
        $record->status = $request->status;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = UnitContracts::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = UnitContracts::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = UnitContracts::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $records = $query->limit(10)->get(['id', 'name']);

        return response()->json($records);
    }
}
