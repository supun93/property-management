<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Unit());
    }

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Units - Trashed");
        } else {
            $this->repository->setPageTitle("Units");
        }

        $this->repository
            ->setColumns("id", "unit_number", "property.name", "rent", "is_occupied", "created_at")
            ->setColumnLabel("property.name", "Property Name")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);


        $query = Unit::with(['property']);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Units - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Units")
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
        return view('unit.create');
    }
    public function edit($id)
    {
        $record = Unit::with(['property'])->findOrFail($id);
        return view('unit.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'unit_number' => 'required|string|max:255',
            'rent' => 'required|numeric|min:0',
            'is_occupied' => 'required|boolean',
            'property_id' => 'required|exists:properties,id',
        ]);
        $record = new Unit();
        $record->unit_number = $request->unit_number;
        $record->rent = $request->rent;
        $record->is_occupied = $request->is_occupied;
        $record->property_id = $request->property_id;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = Unit::findOrFail($id);
        $data = request()->validate([
            'unit_number' => 'required|string|max:255',
            'rent' => 'required|numeric|min:0',
            'is_occupied' => 'required|boolean',
            'property_id' => 'required|exists:properties,id',
        ]);

        $record->unit_number = $request->unit_number;
        $record->rent = $request->rent;
        $record->is_occupied = $request->is_occupied;
        $record->property_id = $request->property_id;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = Unit::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = Unit::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = Unit::query();

        if ($search) {
            $query->where('unit_number', 'like', '%' . $search . '%');
        }

        $records = $query->limit(10)->get(['id', 'unit_number']);
        $data = [];
        foreach ($records as $rec) {
            $row = [];
            $row["id"] = $rec->id;
            $row["name"] = $rec->unit_number;
            $data[] = $row;
        }
        return response()->json($data);
    }
}
