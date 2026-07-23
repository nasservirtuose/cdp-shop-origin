<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PlanipetsAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/planipets', [PlanipetsAuthController::class, 'login'])
    ->middleware('throttle:10,1')->name('pro.sso');
Route::get('/pro/login', [PlanipetsAuthController::class, 'showLoginPage'])->name('pro.login.page');

Route::prefix('pro')->middleware('pro.auth')->group(function () {
    Route::post('/logout', [PlanipetsAuthController::class, 'logout'])->name('pro.logout');
    Route::get('/dashboard', function () {
        $pro = \App\Support\CurrentPro::pro();
        return response('SHOP OK — connecté : ' . $pro->displayName() . ' (pro_id=' . $pro->proId() . ')');
    })->name('pro.dashboard');
});

