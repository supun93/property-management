<?php

use App\Http\Controllers\BillingTypesController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceLineController;
use App\Http\Controllers\PaymentSlipController;
use App\Http\Controllers\PropertyCategoryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitBillingTypesController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UnitPaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('', [TenantController::class, 'dashboard'])->name('dashboard');

    Route::prefix('slips')->name('slip.')->group(function () {
        Route::post('upload', [PaymentSlipController::class, 'upload'])->name('upload');
        Route::post('trash/{id}', [PaymentSlipController::class, 'delete'])->name('trash');
    });
    
});

Route::group(['middleware' => 'is_manager'], function () {

    Route::prefix('property-category')->name('property-category.')->group(function () {
        Route::get('', [PropertyCategoryController::class, 'index'])->name('index');
        Route::post('', [PropertyCategoryController::class, 'index']);
        Route::get('trash-list', [PropertyCategoryController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [PropertyCategoryController::class, 'trash']);
        Route::get('create', [PropertyCategoryController::class, 'create'])->name('create');
        Route::post('save', [PropertyCategoryController::class, 'save'])->name('save');
        Route::get('edit/{id}', [PropertyCategoryController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [PropertyCategoryController::class, 'update'])->name('update');
        Route::post('trash/{id}', [PropertyCategoryController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [PropertyCategoryController::class, 'restore'])->name('restore');
        Route::post('search_data', [PropertyCategoryController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('property')->group(function () {
        Route::get('', [PropertyController::class, 'index'])->name('property.index');
        Route::post('', [PropertyController::class, 'index']);
        Route::get('trash-list', [PropertyController::class, 'trash'])->name('property.trash-list');
        Route::post('trash-list', [PropertyController::class, 'trash']);
        Route::get('create', [PropertyController::class, 'create'])->name('property.create');
        Route::post('save', [PropertyController::class, 'save'])->name('property.save');
        Route::get('edit/{id}', [PropertyController::class, 'edit'])->name('property.edit');
        Route::post('update/{id}', [PropertyController::class, 'update'])->name('property.update');
        Route::post('trash/{id}', [PropertyController::class, 'delete'])->name('property.trash');
        Route::post('restore/{id}', [PropertyController::class, 'restore'])->name('property.restore');
        Route::post('search_data', [PropertyController::class, 'searchData'])->name('property.search_data');
    });

    Route::prefix('unit')->name('unit.')->group(function () {
        Route::get('', [UnitController::class, 'index'])->name('index');
        Route::post('', [UnitController::class, 'index']);
        Route::get('trash-list', [UnitController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [UnitController::class, 'trash']);
        Route::get('create', [UnitController::class, 'create'])->name('create');
        Route::post('save', [UnitController::class, 'save'])->name('save');
        Route::get('edit/{id}', [UnitController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [UnitController::class, 'update'])->name('update');
        Route::post('trash/{id}', [UnitController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [UnitController::class, 'restore'])->name('restore');
        Route::post('search_data', [UnitController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('billing-types')->name('billing-types.')->group(function () {
        Route::get('', [BillingTypesController::class, 'index'])->name('index');
        Route::post('', [BillingTypesController::class, 'index']);
        Route::get('trash-list', [BillingTypesController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [BillingTypesController::class, 'trash']);
        Route::get('create', [BillingTypesController::class, 'create'])->name('create');
        Route::post('save', [BillingTypesController::class, 'save'])->name('save');
        Route::get('edit/{id}', [BillingTypesController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [BillingTypesController::class, 'update'])->name('update');
        Route::post('trash/{id}', [BillingTypesController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [BillingTypesController::class, 'restore'])->name('restore');
        Route::post('search_data', [BillingTypesController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('unit-billing-types')->name('unit-billing-types.')->group(function () {
        Route::get('{id}', [UnitBillingTypesController::class, 'index'])->name('index');
        Route::post('{id}', [UnitBillingTypesController::class, 'index']);
        Route::get('trash-list/{id}', [UnitBillingTypesController::class, 'trash'])->name('trash-list');
        Route::post('trash-list/{id}', [UnitBillingTypesController::class, 'trash']);
        Route::get('create/{id}', [UnitBillingTypesController::class, 'create'])->name('create');
        Route::post('save/{id}', [UnitBillingTypesController::class, 'save'])->name('save');
        Route::get('edit/{id}', [UnitBillingTypesController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [UnitBillingTypesController::class, 'update'])->name('update');
        Route::post('trash/{id}', [UnitBillingTypesController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [UnitBillingTypesController::class, 'restore'])->name('restore');
        Route::post('search_data/{id}', [UnitBillingTypesController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('tenant')->name('tenants.')->group(function () {
        Route::get('', [TenantController::class, 'index'])->name('index');
        Route::post('', [TenantController::class, 'index']);
        Route::get('trash-list', [TenantController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [TenantController::class, 'trash']);
        Route::get('create', [TenantController::class, 'create'])->name('create');
        Route::post('save', [TenantController::class, 'save'])->name('save');
        Route::get('edit/{id}', [TenantController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [TenantController::class, 'update'])->name('update');
        Route::post('trash/{id}', [TenantController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [TenantController::class, 'restore'])->name('restore');
        Route::post('search_data', [TenantController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('contracts')->name('unit-contracts.')->group(function () {
        Route::get('', [ContractController::class, 'index'])->name('index');
        Route::post('', [ContractController::class, 'index']);
        Route::get('trash-list', [ContractController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [ContractController::class, 'trash']);
        Route::get('create', [ContractController::class, 'create'])->name('create');
        Route::post('save', [ContractController::class, 'save'])->name('save');
        Route::get('edit/{id}', [ContractController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [ContractController::class, 'update'])->name('update');
        Route::post('trash/{id}', [ContractController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [ContractController::class, 'restore'])->name('restore');
        Route::post('search_data', [ContractController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('payments')->name('unit-payment-schedules.')->group(function () {
        Route::get('{id}', [UnitPaymentController::class, 'index'])->name('index');
        Route::post('{id}', [UnitPaymentController::class, 'index']);
        Route::get('trash-list/{id}', [UnitPaymentController::class, 'trash'])->name('trash-list');
        Route::post('trash-list/{id}', [UnitPaymentController::class, 'trash']);
        Route::get('create/{id}', [UnitPaymentController::class, 'create'])->name('create');
        Route::post('save/{id}', [UnitPaymentController::class, 'save'])->name('save');
        Route::get('edit/{id}', [UnitPaymentController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [UnitPaymentController::class, 'update'])->name('update');
        Route::post('trash/{id}', [UnitPaymentController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [UnitPaymentController::class, 'restore'])->name('restore');
        Route::post('search_data/{id}', [UnitPaymentController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('invoices')->name('invoice.')->group(function () {
        Route::get('{id}', [InvoiceController::class, 'index'])->name('index');
        Route::post('{id}', [InvoiceController::class, 'index']);
        Route::get('trash-list/{id}', [InvoiceController::class, 'trash'])->name('trash-list');
        Route::post('trash-list/{id}', [InvoiceController::class, 'trash']);
        Route::get('create/{id}', [InvoiceController::class, 'create'])->name('create');
        Route::post('save/{id}', [InvoiceController::class, 'save'])->name('save');
        Route::get('edit/{id}', [InvoiceController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [InvoiceController::class, 'update'])->name('update');
        Route::post('trash/{id}', [InvoiceController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [InvoiceController::class, 'restore'])->name('restore');
        Route::post('search_data/{id}', [InvoiceController::class, 'searchData'])->name('search_data');
        Route::get('download/{id}', [InvoiceController::class, 'download'])->name('download');
        Route::get('upload/form/{id}', [InvoiceController::class, 'uploadForm'])->name('upload-form');

        Route::get('pending/list', [InvoiceController::class, 'pendingIndex'])->name('pending.index');
        Route::post('pending/list', [InvoiceController::class, 'pendingIndex']);
        Route::get('payment/history', [InvoiceController::class, 'paymentHistory'])->name('payment.history');
        Route::post('payment/history', [InvoiceController::class, 'paymentHistory']);
    });

    Route::prefix('invoice_lines')->name('invoice_lines.')->group(function () {
        Route::get('', [InvoiceLineController::class, 'index'])->name('index');
        Route::post('', [InvoiceLineController::class, 'index']);
        Route::get('trash-list', [InvoiceLineController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [InvoiceLineController::class, 'trash']);
        Route::get('create', [InvoiceLineController::class, 'create'])->name('create');
        Route::post('save', [InvoiceLineController::class, 'save'])->name('save');
        Route::get('edit/{id}', [InvoiceLineController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [InvoiceLineController::class, 'update'])->name('update');
        Route::post('trash/{id}', [InvoiceLineController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [InvoiceLineController::class, 'restore'])->name('restore');
        Route::post('search_data', [InvoiceLineController::class, 'searchData'])->name('search_data');
    });

    Route::prefix('slips')->name('slips.')->group(function () {
        Route::get('pending', [PaymentSlipController::class, 'pending'])->name('pending');
        Route::post('{id}/action', [PaymentSlipController::class, 'updateStatus'])->name('update_status');
    });
});

Route::group(['middleware' => 'is_admin'], function () {

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');
        Route::post('', [UserController::class, 'index']);
        Route::get('trash-list', [UserController::class, 'trash'])->name('trash-list');
        Route::post('trash-list', [UserController::class, 'trash']);
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('save', [UserController::class, 'save'])->name('save');
        Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('trash/{id}', [UserController::class, 'delete'])->name('trash');
        Route::post('restore/{id}', [UserController::class, 'restore'])->name('restore');
        Route::post('search_data', [UserController::class, 'searchData'])->name('search_data');
    });
});


require __DIR__ . '/auth.php';
