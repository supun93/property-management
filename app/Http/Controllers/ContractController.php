<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Contract;
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
                "deposit_amount",
                "status",
                "approval_status",
                "created_at"
            )
            ->setColumnLabel("tenant.name", "Tenant Name")
            ->setColumnLabel("unit.unit_number", "Unit Number")
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

    public function save(Request $request)
    {
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
        $record = new Contract();
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
