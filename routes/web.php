<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartaPorteController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ConceptoGastoController;
use App\Http\Controllers\NotaGastoController;
use App\Http\Controllers\PagoPilotoController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::prefix('pagos-pilotos')->name('pagos-pilotos.')->group(function () {
        Route::get('/', [PagoPilotoController::class, 'index'])->name('index');
        Route::get('historial', [PagoPilotoController::class, 'historial'])->name('historial');
        Route::get('crear', [PagoPilotoController::class, 'create'])->name('create');
        Route::post('/', [PagoPilotoController::class, 'store'])->name('store');
        Route::get('pilotos/{piloto}/conceptos', [PagoPilotoController::class, 'conceptos'])->name('conceptos');
        Route::put('pilotos/{piloto}/conceptos', [PagoPilotoController::class, 'guardarConceptos'])->name('guardar-conceptos');
        Route::get('{pagoPiloto}/imprimir', [PagoPilotoController::class, 'imprimir'])->name('imprimir');
        Route::get('{pagoPiloto}/editar', [PagoPilotoController::class, 'edit'])->name('edit');
        Route::put('{pagoPiloto}/pagar', [PagoPilotoController::class, 'pagar'])->name('pagar');
        Route::put('{pagoPiloto}/anular', [PagoPilotoController::class, 'anular'])->name('anular');
        Route::get('{pagoPiloto}', [PagoPilotoController::class, 'show'])->name('show');
        Route::put('{pagoPiloto}', [PagoPilotoController::class, 'update'])->name('update');
    });
    Route::get('/', [CartaPorteController::class, 'index'])->name('home');
    Route::get('catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
    Route::get('catalogos/{catalogo}/crear', [CatalogoController::class, 'create'])->name('catalogos.create');
    Route::post('catalogos/{catalogo}', [CatalogoController::class, 'store'])->name('catalogos.store');
    Route::post('catalogos/{catalogo}/rapido', [CatalogoController::class, 'quickStore'])->name('catalogos.quick-store');
    Route::get('catalogos/{catalogo}/{id}/editar', [CatalogoController::class, 'edit'])->name('catalogos.edit');
    Route::put('catalogos/{catalogo}/{id}', [CatalogoController::class, 'update'])->name('catalogos.update');
    Route::delete('catalogos/{catalogo}/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');

    Route::prefix('facturacion')->name('facturacion.')->group(function () {
        Route::get('notas-gastos/desde-carta/{cartaPorte}/descripcion', [NotaGastoController::class, 'descripcionDesdeCarta'])->name('notas-gastos.descripcion-desde-carta');
        Route::get('notas-gastos/{notaGasto}/descripcion', [NotaGastoController::class, 'regenerarDescripcion'])->name('notas-gastos.descripcion');
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
