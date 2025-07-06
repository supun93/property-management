<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLine;
use App\Helpers\IndexRepositoryHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class InvoiceLineController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new InvoiceLine());
    }

    public $statuses = [
        'active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'inactive' => ['id' => 0, 'label' => 'Inactive', 'class' => 'danger']
    ];

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Invoice Line - Trashed");
        } else {
            $this->repository->setPageTitle("Invoice Line");
        }

        $this->repository
            ->setColumns("id", "name", "status", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnSearchability("created_at", false);


        $query = InvoiceLine::query(); // remove createdBy

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Invoice Line - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Invoice Line")
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
        return view('invoice_lines.create');
    }
    public function edit($id)
    {
        $record = InvoiceLine::findOrFail($id);
        return view('invoice_lines.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $record = new InvoiceLine();
        $record->name = $request->name;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = InvoiceLine::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $record->name = $request->name;
        $record->status = $request->status;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = InvoiceLine::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = InvoiceLine::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = InvoiceLine::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
