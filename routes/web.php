<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyCategoryController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['middleware' => 'is_admin'], function () {
   Route::get('/property-categories', [PropertyCategoryController::class, 'index'])->name('property-categories.index');
    Route::get('/property-categories/create', [PropertyCategoryController::class, 'create'])->name('property-category.create');
    Route::get('/property-categories/edit', [PropertyCategoryController::class, 'edit'])->name('property-category.edit');
    Route::get('/property-categories/trash', [PropertyCategoryController::class, 'trash'])->name('property-category.trash');
    Route::get('/property-categories/restore', [PropertyCategoryController::class, 'restore'])->name('property-category.restore');
});


require __DIR__ . '/auth.php';