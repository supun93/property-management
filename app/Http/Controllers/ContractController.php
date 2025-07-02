<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payments;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Contract());
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
            $this->repository->setPageTitle("Contracts - Trashed");
        } else {
            $this->repository->setPageTitle("Contracts");
        }

        $this->repository
            ->setColumns(
                "id",
                "tenant.name",
                "unit.unit_number",
                "agreement_start_date",
                "agreement_end_date",
                "rent_amount",
                "next_rent_due_date",
                "status",
                "approval_status",
                "created_at"
            )
            ->setColumnLabel("tenant.name", "Tenant Name")
            ->setColumnLabel("unit.unit_number", "Unit Number")
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


        $query = Contract::with(['tenant', 'unit']);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Contracts - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Contracts")
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
        $record = Contract::with(['tenant', 'unit'])->findOrFail($id);
        return view('contracts.edit', compact('record'));
    }

    public function generateRentalPayments($model)
    {
        $contractId = $model->id ?? null;
        $rentType = $model->rent_payment_type; // null:none, 1:monthly, 2:6months, 3:yearly
        $fullAmount = $model->full_amount;
        $rentAmount = $model->rent_amount;
        $nextDueDate = null;
        $totalInstallment = null;

        // Calculate only if fullAmount is set and greater than 0
        if ($fullAmount > 0) {
            switch ($rentType) {
                case 1: // Monthly
                    $totalInstallment = ceil($fullAmount / $rentAmount);
                    $nextDueDate = now()->addMonth();
                    break;
                case 2: // 6 Months
                    $totalInstallment = ceil($fullAmount / $rentAmount);
                    $nextDueDate = now()->addMonths(6);
                    break;
                case 3: // Yearly
                    $totalInstallment = ceil($fullAmount / $rentAmount);
                    $nextDueDate = now()->addYear();
                    break;
                default:
                    $totalInstallment = 1;
                    $nextDueDate = null;
            }
        }

        if ($fullAmount > 0 && $totalInstallment > 0 && $rentAmount > 0) {
            $dueDate = now();
            for ($i = 1; $i <= $totalInstallment; $i++) {

                $payment = new Payments();
                $payment->contract_id = $contractId;
                $payment->installment_number = $i;
                $payment->amount = $rentAmount;
                $payment->payment_date = match ($rentType) {
                    1 => $dueDate->copy()->addMonths($i - 1),
                    2 => $dueDate->copy()->addMonths(6 * ($i - 1)),
                    3 => $dueDate->copy()->addYears($i - 1),
                    default => $dueDate
                };
                $payment->status = 0;
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

        $record = new Contract();
        $record->tenant_id = $request->tenant_id;
        $record->unit_id = $request->unit_id;
        $record->agreement_start_date = $request->agreement_start_date;
        $record->agreement_end_date = $request->agreement_end_date;
        $record->rent_amount = $request->rent_amount;
        $record->deposit_amount = $request->deposit_amount;
        $record->rent_payment_type = 1;
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
        $record = Contract::findOrFail($id);
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
        $record = Contract::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = Contract::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = Contract::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $records = $query->limit(10)->get(['id', 'name']);

        return response()->json($records);
    }
}
