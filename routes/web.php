<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartaPorteController;
use App\Http\Controllers\CatalogoController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [CartaPorteController::class, 'index'])->name('home');
    Route::get('catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
    Route::get('catalogos/{catalogo}/crear', [CatalogoController::class, 'create'])->name('catalogos.create');
    Route::post('catalogos/{catalogo}', [CatalogoController::class, 'store'])->name('catalogos.store');
    Route::get('catalogos/{catalogo}/{id}/editar', [CatalogoController::class, 'edit'])->name('catalogos.edit');
    Route::put('catalogos/{catalogo}/{id}', [CatalogoController::class, 'update'])->name('catalogos.update');
    Route::delete('catalogos/{catalogo}/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');
    Route::get('cartas-porte/{cartaPorte}/imprimir', [CartaPorteController::class, 'imprimir'])->name('cartas-porte.imprimir');
    Route::resource('cartas-porte', CartaPorteController::class)->parameters([
        'cartas-porte' => 'cartaPorte',
    ]);
});
