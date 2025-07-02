<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyCategoryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

    Route::prefix('property-category')->group(function () {
        Route::get('', [PropertyCategoryController::class, 'index'])->name('property-category.index');
        Route::post('', [PropertyCategoryController::class, 'index']);
        Route::get('trash-list', [PropertyCategoryController::class, 'trash'])->name('property-category.trash-list');
        Route::post('trash-list', [PropertyCategoryController::class, 'trash']);
        Route::get('create', [PropertyCategoryController::class, 'create'])->name('property-category.create');
        Route::post('save', [PropertyCategoryController::class, 'save'])->name('property-category.save');
        Route::get('edit/{id}', [PropertyCategoryController::class, 'edit'])->name('property-category.edit');
        Route::post('update/{id}', [PropertyCategoryController::class, 'update'])->name('property-category.update');
        Route::post('trash/{id}', [PropertyCategoryController::class, 'delete'])->name('property-category.trash');
        Route::post('restore/{id}', [PropertyCategoryController::class, 'restore'])->name('property-category.restore');
        Route::post('search_data', [PropertyCategoryController::class, 'searchData'])->name('property-category.search_data');
    
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

     

});


require __DIR__ . '/auth.php';
