<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeoordelingController;
use App\Http\Controllers\RitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route(Auth::user()->isChauffeur() ? 'ritten.index' : 'ritten.create');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registreren', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registreren', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/account', [RitController::class, 'index'])->name('ritten.index');
    Route::get('/ritten/nieuw', [RitController::class, 'create'])->name('ritten.create');
    Route::post('/ritten', [RitController::class, 'store'])->name('ritten.store');

    Route::get('/ritten/{rit}', [RitController::class, 'show'])->name('ritten.show');
    Route::get('/ritten/{rit}/taxi-scherm', [RitController::class, 'taxiScherm'])->name('ritten.taxi');
    Route::get('/ritten/{rit}/status', [RitController::class, 'status'])->name('ritten.status');
    Route::post('/ritten/{rit}/start', [RitController::class, 'start'])->name('ritten.start');
    Route::post('/ritten/{rit}/locatie', [RitController::class, 'locatie'])->name('ritten.locatie');
    Route::post('/ritten/{rit}/beeindig', [RitController::class, 'beeindig'])->name('ritten.beeindig');
    Route::post('/ritten/{rit}/afrekenen', [RitController::class, 'afrekenen'])->name('ritten.afrekenen');
    Route::post('/ritten/{rit}/beoordeling', [BeoordelingController::class, 'store'])->name('beoordeling.store');
});
