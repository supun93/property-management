<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Models\Property;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Property());
    }

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Properties - Trashed");
        } else {
            $this->repository->setPageTitle("Properties");
        }

        $this->repository
            ->setColumns("id", "name", "address", "category.name", "owner.name", "created_at")
            ->setColumnLabel("category.name", "Category Name")
            ->setColumnLabel("owner.name", "Owner Name")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);


        $query = Property::with(['category', 'owner']);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Properties - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Properties")
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
        return view('property.create');
    }
    public function edit($id)
    {
        $record = Property::with(['category', 'owner'])->findOrFail($id);
        // create a new property category
        return view('property.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'category_id' => 'required|exists:property_categories,id',
            'owner_id' => 'required|exists:users,id',
        ]);
        $record = new Property();
        $record->name = $request->name;
        $record->address = $request->address;
        $record->category_id = $request->category_id;
        $record->owner_id = $request->owner_id;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = Property::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'category_id' => 'required|exists:property_categories,id',
            'owner_id' => 'required|exists:users,id',
        ]);

        $record->name = $request->name;
        $record->address = $request->address;
        $record->category_id = $request->category_id;
        $record->owner_id = $request->owner_id;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = Property::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = Property::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

     public function searchData ()
    {
        $search = request()->get('query');
        $query = Property::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
