<?php

namespace App\Http\Controllers;

use App\Models\BillingTypes;
use App\Helpers\IndexRepositoryHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class BillingTypesController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new BillingTypes());
    }

    public $loopStatuses = [
        'Enabled' => ['id' => 1, 'label' => 'Enabled', 'class' => 'success'],
        'Disable' => ['id' => 0, 'label' => 'Disabled', 'class' => 'danger']
    ];

    public $statuses = [
        'active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'inactive' => ['id' => 0, 'label' => 'Inactive', 'class' => 'danger']
    ];

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Billing Types - Trashed");
        } else {
            $this->repository->setPageTitle("Billing Types");
        }

        $this->repository
            ->setColumns("id", "name", "monthly_loop", "status", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnDisplay(
                'monthly_loop',
                [$this->repository, 'displayStatusAs'],
                [$this->loopStatuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            ) 
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnSearchability("created_at", false);


        $query = BillingTypes::query(); // remove createdBy

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Billing Types - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Billing Types")
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
        return view('billing-types.create');
    }
    public function edit($id)
    {
        $record = BillingTypes::findOrFail($id);
        return view('billing-types.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'monthly_loop' => 'nullable|integer',
        ]);

        $record = new BillingTypes();
        $record->name = $request->name;
        $record->monthly_loop = $request->monthly_loop;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = BillingTypes::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer',
            'monthly_loop' => 'nullable|integer',
        ]);

        $record->name = $request->name;
        $record->status = $request->status;
        $record->monthly_loop = $request->monthly_loop;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = BillingTypes::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = BillingTypes::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = BillingTypes::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
