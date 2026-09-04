<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartaPorteController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ConceptoGastoController;
use App\Http\Controllers\NotaGastoController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [CartaPorteController::class, 'index'])->name('home');
    Route::get('catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
    Route::get('catalogos/{catalogo}/crear', [CatalogoController::class, 'create'])->name('catalogos.create');
    Route::post('catalogos/{catalogo}', [CatalogoController::class, 'store'])->name('catalogos.store');
    Route::post('catalogos/{catalogo}/rapido', [CatalogoController::class, 'quickStore'])->name('catalogos.quick-store');
    Route::get('catalogos/{catalogo}/{id}/editar', [CatalogoController::class, 'edit'])->name('catalogos.edit');
    Route::put('catalogos/{catalogo}/{id}', [CatalogoController::class, 'update'])->name('catalogos.update');
    Route::delete('catalogos/{catalogo}/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');

    Route::prefix('facturacion')->name('facturacion.')->group(function () {
        Route::get('notas-gastos/desde-carta/{cartaPorte}', [NotaGastoController::class, 'desdeCarta'])->name('notas-gastos.desde-carta');
        Route::post('notas-gastos/desde-carta/{cartaPorte}', [NotaGastoController::class, 'storeDesdeCarta'])->name('notas-gastos.store-desde-carta');
        Route::get('notas-gastos/{notaGasto}/imprimir', [NotaGastoController::class, 'imprimir'])->name('notas-gastos.imprimir');
        Route::get('notas-gastos/{notaGasto}/facturar', [NotaGastoController::class, 'editFacturacion'])->name('notas-gastos.facturar');
        Route::put('notas-gastos/{notaGasto}/facturar', [NotaGastoController::class, 'updateFacturacion'])->name('notas-gastos.facturar.update');
        Route::put('notas-gastos/{notaGasto}/anular', [NotaGastoController::class, 'anular'])->name('notas-gastos.anular');
        Route::resource('notas-gastos', NotaGastoController::class)
            ->only(['index', 'show', 'edit', 'update', 'destroy'])
            ->parameters(['notas-gastos' => 'notaGasto']);

        Route::resource('conceptos-gastos', ConceptoGastoController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['conceptos-gastos' => 'conceptosGasto']);
    });

    Route::get('cartas-porte/{cartaPorte}/imprimir', [CartaPorteController::class, 'imprimir'])->name('cartas-porte.imprimir');
    Route::resource('cartas-porte', CartaPorteController::class)->parameters([
        'cartas-porte' => 'cartaPorte',
    ]);
});
