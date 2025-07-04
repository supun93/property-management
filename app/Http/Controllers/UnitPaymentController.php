<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\BillingTypes;
use App\Models\Unit;
use App\Models\UnitPaymentSchedules;
use Illuminate\Http\Request;

class UnitPaymentController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new UnitPaymentSchedules());
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
            $this->repository->setPageTitle("Unit Payments - Trashed");
        } else {
            $this->repository->setPageTitle("Unit Payments");
        }

        $this->repository->setColumns(
            "payment_date",
            "contract.unit.property.name",
            "contract.unit.unit_name",
            "contract.tenant.name",
            "unitBillingType.billingType.name",
            "amount",
            "status",
            "paid_at"
        );

        $this->repository->setColumnDisplay("status", [$this->repository, 'displayStatusAs'], [true])
            ->setColumnLabel("contract.unit.property.name", "Property Name")
            ->setColumnLabel("contract.unit.unit_name", "Unit Name")
            ->setColumnLabel("contract.tenant.name", "Tenent Name")
            ->setColumnLabel("unitBillingType.billingType.name", "Billing Type Name")
            ->setColumnDisplay(
                'status',
                [$this->repository, 'displayStatusAs'],
                [$this->statuses, '', true] // ✅ 3rd param: pass statuses + showChip true
            )
            ->setColumnSearchability("created_at", false)
            ->addFilter('status', 'Status', 'select', [0 => 'Pending', 1 => 'Paid'])
            ->addFilter('unit_billing_type_id', 'Billing Type', 'select', BillingTypes::pluck('name', 'id')->toArray())
            ->addFilter('contract.unit_id', 'Units', 'select', Unit::pluck('unit_name', 'id')->toArray())
            ->setDefaultOrder('payment_date', 'asc');


        $query = UnitPaymentSchedules::with([
            'contract',                 // 🟢 for unit_id
            'contract.unit',            // 🟢 for displaying unit info
            'contract.unit.property',
            'contract.tenant',
            'unitBillingType.billingType'
        ]);

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Unit Payments - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Unit Payments")
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
}
