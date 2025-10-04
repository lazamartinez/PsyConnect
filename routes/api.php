<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IEAController;
use App\Http\Controllers\ManuscritoController;
use App\Http\Controllers\PacienteController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::post('/registro', [AuthController::class, 'registro']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Rutas de pacientes
    Route::apiResource('pacientes', PacienteController::class);
    Route::post('pacientes/{id}/manuscritos', [ManuscritoController::class, 'procesar']);
    Route::get('pacientes/{id}/iea', [IEAController::class, 'obtenerHistorial']);
    
    // Rutas de alertas y emergencias
    Route::post('alertas/ayuda-inmediata', [AlertaController::class, 'ayudaInmediata']);
    Route::apiResource('alertas', AlertaController::class);
    
    // Rutas de actividades
    Route::apiResource('actividades', ActividadController::class);
    Route::post('actividades/{id}/validar', [ActividadController::class, 'validar']);
});