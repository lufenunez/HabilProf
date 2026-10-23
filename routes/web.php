<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListadoController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard solo para usuarios autenticados
Route::get('/dashboard', function () {
    return Inertia::render('Habilitacion/Ingreso');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo protegido por auth
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Habilitaciones
    Route::get('/habilitacion/listado', function () {
        return Inertia::render('Habilitacion/Listado'); 
    })->name('habilitacion.listado');

    Route::get('/habilitacion/ingreso', function () {
        return Inertia::render('Habilitacion/Ingreso'); 
    })->name('habilitacion.ingreso');

    Route::get('/habilitacion/modificar', function () {
        return Inertia::render('Habilitacion/Modificar_Eliminar'); 
    })->name('habilitacion.modificar');

    // Listados varios
    Route::get('/listado-semestre', [ListadoController::class, 'listadoSemestre'])
    ->name('listado.semestre');

    Route::get('/listado-profesor', [ListadoController::class, 'listadoProfesor'])
    ->name('listado.profesor');

    

});

require __DIR__.'/auth.php';
