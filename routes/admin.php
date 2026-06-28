<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RifaController;
use App\Http\Controllers\Admin\SorteioController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Rifas CRUD
        Route::resource('rifas', RifaController::class)->except(['show']);
        Route::get('rifas/{rifa}', [RifaController::class, 'show'])->name('rifas.show');
        
        // Números da Rifa
        Route::get('rifas/{rifa}/numeros', [RifaController::class, 'numbers'])->name('rifas.numbers');
        Route::post('rifas/{rifa}/numeros', [RifaController::class, 'updateNumberStatus'])->name('rifas.numbers.update');
        Route::post('rifas/{rifa}/numeros/multiple', [RifaController::class, 'updateMultipleNumbers'])->name('rifas.numbers.update-multiple');

        // Sorteio
        Route::get('rifas/{rifa}/sorteio', [SorteioController::class, 'index'])->name('sorteio.index');
        Route::post('rifas/{rifa}/sorteio/sortear', [SorteioController::class, 'sortear'])->name('sorteio.sortear');
        Route::post('rifas/{rifa}/sorteio/definir-ganhador', [SorteioController::class, 'definirGanhador'])->name('sorteio.definir-ganhador');
        Route::get('rifas/{rifa}/sorteio/verificar', [SorteioController::class, 'verificarNumeros'])->name('sorteio.verificar');
    });
