<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Helpers\IndexRepositoryHelper;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class InvoiceController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Invoice());
    }

    public $statuses = [
        'active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'inactive' => ['id' => 0, 'label' => 'Inactive', 'class' => 'danger']
    ];

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Invoice - Trashed");
        } else {
            $this->repository->setPageTitle("Invoice");
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


        $query = Invoice::query(); // remove createdBy

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Invoice - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Invoice")
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
        return view('invoices.create');
    }
    public function edit($id)
    {

        $invoice = Invoice::findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("Invoice-{$invoice->id}.pdf");


        $record = Invoice::findOrFail($id);
        return view('invoices.edit', compact('record'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $record = new Invoice();
        $record->name = $request->name;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = Invoice::findOrFail($id);
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
        $record = Invoice::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = Invoice::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = Invoice::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }
}
