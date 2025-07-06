<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenants;
use App\Models\UnitContracts;
use App\Models\UnitPaymentSchedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    protected $repository;
    protected $trash;

    public $statuses = [
        'active' => ['id' => 1, 'label' => 'Active', 'class' => 'success'],
        'inactive' => ['id' => 0, 'label' => 'Inactive', 'class' => 'danger']
    ];

    public $approvalStatuses = [
        'Not sent for approval' => ['id' => null, 'label' => 'Not sent for approval', 'class' => 'info'],
        'Pending Approval' => ['id' => 0, 'label' => 'Pending Approval', 'class' => 'warning'],
        'Approved' => ['id' => 1, 'label' => 'Approved', 'class' => 'success'],
        'Declined' => ['id' => 2, 'label' => 'Declined', 'class' => 'danger']
    ];

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new Tenants());
    }

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Tenants - Trashed");
        } else {
            $this->repository->setPageTitle("Tenants");
        }

        $this->repository
            ->setColumns("id", "name", "nic_passport", "phone", "email", "address", "status", "approval_status", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
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
            ->setColumnSearchability("created_at", false);


        $query = Tenants::query();;

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Tenants - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Tenants")
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
        return view('tenant.create');
    }
    public function edit($id)
    {
        $tenant = Tenants::with(['user'])->findOrFail($id);
        return view('tenant.edit', compact('tenant'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'nic_passport' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|integer',
            'approval_status' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',
        ]);
        $record = new Tenants();
        $record->user_id = $request->user_id;
        $record->name = $request->name;
        $record->nic_passport = $request->nic_passport;
        $record->phone = $request->phone;
        $record->email = $request->email;
        $record->address = $request->address;
        $record->status = $request->status;
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = Tenants::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'nic_passport' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|integer',
            'approval_status' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',
        ]);

        $record->user_id = $request->user_id;
        $record->name = $request->name;
        $record->nic_passport = $request->nic_passport;
        $record->phone = $request->phone;
        $record->email = $request->email;
        $record->address = $request->address;
        $record->status = $request->status;
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = Tenants::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = Tenants::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }

    public function searchData()
    {
        $search = request()->get('query');
        $query = Tenants::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $records = $query->limit(10)->get(['id', 'name']);

        return response()->json($records);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $tenant = Tenants::where('user_id', $user->id)->first();

        if (!$tenant) {
            abort(403, "No tenant profile found.");
        }

        $contract = UnitContracts::with(['unit', 'unit.property'])
            ->where('tenant_id', $tenant->id)
            ->latest('id')
            ->first();

        $invoices = collect();
        $pendingRentPayments = collect();
        $paidRentPayments = collect();
        $pendingUtilityPayments = collect();
        $paidUtilityPayments = collect();

        if ($contract) {
            // 🔶 Pending Rent Payments
            $pendingRentPayments = UnitPaymentSchedules::where('unit_contract_id', $contract->id)
                ->where('is_rent', 1)
                ->whereIn('status', [0, 2]) // PENDING
                ->orderBy('payment_date', 'asc')->limit(5)
                ->get();

            // ✅ Paid Rent Payments
            $paidRentPayments = UnitPaymentSchedules::where('unit_contract_id', $contract->id)
                ->where('is_rent', 1)
                ->where('status', 1) // PAID
                ->orderBy('payment_date', 'desc')
                ->get();

            // 🔶 Pending Utilities
            $pendingUtilityPayments = UnitPaymentSchedules::with('unitBillingType.billingType')
                ->where('unit_contract_id', $contract->id)
                ->where('is_rent', 0)
                ->whereIn('status', [0, 2]) // PENDING
                ->orderBy('payment_date', 'asc')
                ->get();

            // ✅ Paid Utilities
            $paidUtilityPayments = UnitPaymentSchedules::with('unitBillingType.billingType')
                ->where('unit_contract_id', $contract->id)
                ->where('is_rent', 0)
                ->where('status', 1) // PAID
                ->orderBy('payment_date', 'desc')
                ->get();
        }

        // 📄 Invoice History
        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->orderByDesc('payment_date')
            ->paginate(5, ['*'], 'invoice_page');

        return view('tenant.dashboard', compact(
            'tenant',
            'contract',
            'invoices',
            'pendingRentPayments',
            'paidRentPayments',
            'pendingUtilityPayments',
            'paidUtilityPayments'
        ));
    }
}
