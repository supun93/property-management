<?php

namespace App\Http\Controllers;

use App\Models\UnitBillingTypes;
use App\Helpers\IndexRepositoryHelper;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class UnitBillingTypesController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new UnitBillingTypes());
    }

    public $statuses = [
        'active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'inactive' => ['id' => 0, 'label' => 'Inactive', 'class' => 'danger']
    ];

    public function index($id, Request $request)
    {
        $unit = Unit::find($id);
        if($unit == null){
            abort(403, "Invalid Unit");

        }
        if ($this->trash) {
            $this->repository->setPageTitle("Unit Billing Types - Trashed | " . $unit->unit_name);
        } else {
            $this->repository->setPageTitle("Unit Billing Types | " . $unit->unit_name);
        }

        $this->repository
            ->setColumns("id", "unit.unit_name", "billingType.name", "status", "created_at")
            ->setColumnLabel("unit.unit_name", "Unit Name")
            ->setColumnLabel("billingType.name", "Billing Type Name")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnSearchability("created_at", false)->setRefferanceId($id);

       $query = UnitBillingTypes::where("unit_id", $id);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Unit Billing Types - Trashed | " . $unit->unit_name)
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Unit Billing Types | " . $unit->unit_name)
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

    public function create($id)
    {
        return view('unit-billing-types.create', compact('id'));
    }
    public function edit($id)
    {
        $record = UnitBillingTypes::findOrFail($id);
        return view('unit-billing-types.edit', compact('record'));
    }

    public function save($id, Request $request)
    {
        $data = request()->validate([
            'unit_id' => 'required|exists:units,id',
            'billing_type_id' => 'required|exists:billing_types,id',
        ]);

        $record = new UnitBillingTypes();
        $record->unit_id = $id;
        $record->billing_type_id = $request->billing_type_id;
        $record->save();

        return response()->json("success");
    }

    public function update($id, Request $request)
    {
        $record = UnitBillingTypes::findOrFail($id);
        $data = request()->validate([
            'unit_id' => 'required|exists:units,id',
            'billing_type_id' => 'required|exists:billing_types,id',
        ]);

        $record->unit_id = $request->unit_id;
        $record->billing_type_id = $request->billing_type_id;
        $record->status = $request->status;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = UnitBillingTypes::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = UnitBillingTypes::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData($id)
    {
        $search = request()->get('query');
        $query = UnitBillingTypes::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
