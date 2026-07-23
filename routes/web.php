<?php

use Illuminate\Support\Facades\Route;
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

