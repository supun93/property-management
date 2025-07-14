<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Helpers\IndexRepositoryHelper;
use App\Models\InvoiceLine;
use App\Models\Property;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class InvoiceController extends BaseController
{
    protected $repository;
    protected $trash;
    protected $history = false;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Invoice());
    }

    public $statuses = [
        'pending' => ['id' => 0, 'label' => 'Pending', 'class' => 'warning'],
        'paid' => ['id' => 1, 'label' => 'Approved & Paid', 'class' => 'success'],
        'rejected' => ['id' => 2, 'label' => 'Rejected', 'class' => 'danger']
    ];

    public function index($id, Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Invoice - Trashed");
        } else {
            $this->repository->setPageTitle("Invoice");
        }

        $this->repository
            ->setColumns("id", "name", "payment_date", "status", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )->setRefferanceId($id)
            ->setExtraListButtonUrl(route("unit-payment-schedules.index", $id))
            ->setExtraListButtonLabel("VIEW PAYMENTS")
            ->addFilter('payment_date_from', 'From Date', 'date') // ✅ added
            ->addFilter('payment_date_to', 'To Date', 'date')     // ✅ added
            ->setColumnSearchability("created_at", false);


        $query = Invoice::where("contract_id", $id);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Invoice - Trashed")
                ->disableViewData("view", 'add', 'restore')
                ->enableViewData("export", "edit", "list");
        } else {
            $this->repository->setTableTitle("Invoice")
                ->disableViewData("view", 'trash', 'trashList')
                ->enableViewData("export", "edit", "add", 'download');
        }

        return $this->repository->render("layouts.master")->index($query);
    }

    public function trash($id, Request $request)
    {
        $this->trash = true;
        return $this->index($id, $request);
    }

    public function pendingIndex(Request $request)
    {
        if ($this->history) {
            $this->repository->setPageTitle("Invoices History");
        } else {
            $this->repository->setPageTitle("Pending Invoices");
        }

        $contracts = UnitContracts::where("status", 1)->get();

        $contractsData = [];
        foreach ($contracts ?? [] as $ct) {
            $contractsData[$ct->id] = $ct->unit->property->name . ' - ' . $ct->unit->unit_name;
        }

        $this->repository
            ->setColumns("id", "contract.unit.property.name", "contract.unit.unit_name", "name", "payment_date", "status", "created_at")
            ->setColumnLabel("contract.unit.property.name", "Property Name")
            ->setColumnLabel("contract.unit.unit_name", "Unit Name")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->addFilter('contract_id', 'Units', 'select', $contractsData)
            ->addFilter('payment_date_from', 'From Date', 'date') // ✅ added
            ->addFilter('payment_date_to', 'To Date', 'date')     // ✅ added
            ->setColumnSearchability("created_at", false);

        if ($this->history) {

            $this->repository->setExtraListButtonUrl(route("invoice.pending.index"))
                ->setExtraListButtonLabel("VIEW PENDING");

            $query = Invoice::where("status", "!=", 0);
        } else {

            $this->repository->setExtraListButtonUrl(route("invoice.payment.history"))
                ->setExtraListButtonLabel("VIEW HISTORY");

            $query = Invoice::where("status", 0);
        }

        if ($this->history) {
            $this->repository->setTableTitle("Invoices History")
                ->disableViewData("view", 'trash', 'trashList', 'add')
                ->enableViewData("export", 'edit', 'download');
        } else {
            $this->repository->setTableTitle("Pending Invoices")
                ->disableViewData("view", 'trash', 'trashList', 'add')
                ->enableViewData("export", 'edit', 'download');
        }


        return $this->repository->render("layouts.master")->index($query);
    }

    public function paymentHistory(Request $request)
    {
        $this->history = true;
        return $this->pendingIndex($request);
    }

    public function create($id)
    {
        $contract = UnitContracts::with('tenant', 'unit')->findOrFail($id);
        $nextMonth = now();

        $schedules = UnitPaymentSchedules::with('unitBillingType') // use correct relation name
            ->where('unit_contract_id', $id)
            ->whereMonth('payment_date', $nextMonth->month)
            ->whereYear('payment_date', $nextMonth->year)
            ->where('status', 0)
            ->whereNull('invoice_id')
            ->get();

        return view('invoices.create', compact('contract', 'schedules'));
    }

    public function download($id)
    {

        $invoice = Invoice::findOrFail($id);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("Invoice-{$invoice->id}.pdf");
    }

    public function save($id, Request $request)
    {
        $request->validate([
            'amounts' => 'required|array',
        ]);

        $contract = UnitContracts::with('unit')->findOrFail($id);
        $firstSchedule = $request->amounts ? UnitPaymentSchedules::find(array_key_first($request->amounts)) : null;
        $paymentDate = $firstSchedule ? \Carbon\Carbon::parse($firstSchedule->payment_date)->format('Y-m-d') : now()->format('Y-m-d');

        $invoice = Invoice::create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'payment_date' => $paymentDate,
            'status' => 0,
            'name' => "Monthly Billing Invoice - " . ($contract->unit->unit_name ?? 'Unknown Unit') . ' | ' . $paymentDate
        ]);

        $total = 0;
        foreach ($request->amounts as $scheduleId => $amount) {
            $schedule = UnitPaymentSchedules::find($scheduleId);
            if ($schedule && $schedule->status == 0 && $schedule->invoice_id == null) {
                $schedule->update([
                    'amount' => $amount,
                    'invoice_id' => $invoice->id
                ]);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'unit_payment_schedule_id' => $schedule->id,
                    'amount' => $amount,
                    'description' => $schedule->note,
                    'unit_billing_type_id' => $schedule->unit_billing_type_id
                ]);

                $total = $total + $amount;
            }
        }

        $invoice->update([
            'total_amount' => $total
        ]);

        return response()->json("success");
    }

    public function edit($id)
    {
        $record = Invoice::findOrFail($id);
        return view('invoices.edit', compact('record'));
    }

    public function update($id, Request $request)
    {
        $record = Invoice::findOrFail($id);
        $data = request()->validate([
            'status' => 'required|string|max:255',
        ]);

        $record->status = $request->status;
        $record->approval_remarks = $request->approval_remarks;
        $record->save();

        foreach ($record->schedules as $schedule) {
            $schedule->status = $request->status;
            $schedule->approval_status = $request->status;
            $schedule->approval_remarks = $request->approval_remarks;
            $schedule->paid_at = now();
            $schedule->save();
        }

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

    public function searchData($id)
    {
        $search = request()->get('query');
        $query = Invoice::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $categories = $query->limit(10)->get(['id', 'name']);

        return response()->json($categories);
    }

    public function uploadForm($id)
    {
        $record = Invoice::findOrFail($id);
        return view('unit-payments.view', compact('record'));
    }

    
}
