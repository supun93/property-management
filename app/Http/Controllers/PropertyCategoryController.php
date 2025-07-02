<?php

namespace App\Http\Controllers;

use App\Models\PropertyCategory;
use App\Helpers\IndexRepositoryHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class PropertyCategoryController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new PropertyCategory());
    }

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Property Categories - Trashed");
        } else {
            $this->repository->setPageTitle("Property Categories");
        }

        $this->repository
            ->setColumns("id", "name", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);


        $query = PropertyCategory::query();// remove createdBy

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Property Categories - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Property Categories")
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
        // create a new property category
        return view('property-category.create');
    }
    public function edit($id)
    {
        $record = PropertyCategory::findOrFail($id);
        // create a new property category
        return view('property-category.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $record = new PropertyCategory();
        $record->name = $request->name;
        $record->save();
        
        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = PropertyCategory::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $record->name = $request->name;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = PropertyCategory::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = PropertyCategory::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData ()
    {
        $search = request()->get('query');
        $query = PropertyCategory::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
