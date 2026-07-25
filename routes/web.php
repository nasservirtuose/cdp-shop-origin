<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Auth\PlanipetsAuthController;
use App\Http\Controllers\Pro\ProSelectionController;
use App\Http\Controllers\Pro\ProCatalogController;
use App\Http\Controllers\Pro\ProPackController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/planipets', [PlanipetsAuthController::class, 'login'])
    ->middleware('throttle:10,1')->name('pro.sso');
Route::get('/pro/login', [PlanipetsAuthController::class, 'showLoginPage'])->name('pro.login.page');

Route::prefix('pro')->middleware('pro.auth')->group(function () {
    Route::post('/logout', [PlanipetsAuthController::class, 'logout'])->name('pro.logout');
    Route::get('/dashboard', [ProSelectionController::class, 'index'])->name('pro.dashboard');

    // Ma sélection / Favoris / Packs
    Route::get('/selection', [ProSelectionController::class, 'index'])->name('pro.selection.index');
    Route::post('/selection/add', [ProSelectionController::class, 'addSelection'])->name('pro.selection.add');
    Route::post('/selection/remove', [ProSelectionController::class, 'removeSelection'])->name('pro.selection.remove');
    Route::post('/favorites/add', [ProSelectionController::class, 'addFavorite'])->name('pro.favorite.add');
    Route::post('/favorites/remove', [ProSelectionController::class, 'removeFavorite'])->name('pro.favorite.remove');

    // Catalogue
    Route::get('/catalog', [ProCatalogController::class, 'index'])->name('pro.catalog.index');
    Route::get('/catalog/{product}', [ProCatalogController::class, 'show'])->name('pro.catalog.show');

    // Packs
    Route::post('/packs', [ProPackController::class, 'store'])->name('pro.packs.store');
    Route::get('/packs/{pack}', [ProPackController::class, 'show'])->name('pro.packs.show');
    Route::post('/packs/{pack}/items', [ProPackController::class, 'addItem'])->name('pro.packs.items.add');
    Route::post('/packs/{pack}/items/remove', [ProPackController::class, 'removeItem'])->name('pro.packs.items.remove');
    Route::delete('/packs/{pack}', [ProPackController::class, 'destroy'])->name('pro.packs.destroy');
});

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/', fn () => redirect()->route('admin.categories.index'))->name('admin.dashboard');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
});

