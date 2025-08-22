<?php

use App\Http\Controllers\MapController;
use App\Http\Controllers\NodoController;
use App\Models\Nodo;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::post('/ruta', [MapController::class, 'ruta'])->name('ruta');
Route::get("/", [MapController::class, 'mapa'])->name('mapa');
Route::get("/all-nodes", [MapController::class, 'nodos'])->name('all.nodes');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::resource('nodos', NodoController::class)->names('nodos');
Route::get("/crear-nodo", [NodoController::class, 'crear_nodo'])->name('crear.nodo');
Route::get("/editar-nodo", [NodoController::class, 'editar_nodo'])->name('editar.nodo');
Route::post('/agregar-nodo', [NodoController::class, 'agregar_nodo'])->name("agregar.nodo");

Route::post('/guardar-nodo-actualizado', [NodoController::class, 'editarNodo'])->name('guarda.nodo');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
