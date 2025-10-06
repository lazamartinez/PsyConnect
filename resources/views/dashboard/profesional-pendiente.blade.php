@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Estado Pendiente -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-center">
                <i class="fas fa-clock text-yellow-500 text-2xl mr-4"></i>
                <div>
                    <h2 class="text-xl font-semibold text-yellow-800">Cuenta en Revisión</h2>
                    <p class="text-yellow-700 mt-1">
                        Tu solicitud como profesional está siendo revisada por el administrador. 
                        Recibirás una notificación una vez que tu cuenta sea aprobada.
                    </p>
                </div>
            </div>
        </div>

        <!-- Información del Profesional -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Tu Información</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nombre</p>
                        <p class="font-medium">{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Especialidad</p>
                        <p class="font-medium">{{ $profesional->especialidad_principal }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Clínica</p>
                        <p class="font-medium">{{ $clinica->nombre ?? 'No asignada' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estado</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Pendiente de Aprobación
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Qué esperar después de la aprobación -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">¿Qué podrás hacer una vez aprobado?</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start">
                    <i class="fas fa-keywords text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-800">Configurar Palabras Clave</h4>
                        <p class="text-blue-700 text-sm">Define tus especialidades para el matching automático</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-users text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-800">Recibir Pacientes</h4>
                        <p class="text-blue-700 text-sm">El sistema te asignará pacientes compatibles</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-chart-line text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-800">Dashboard Completo</h4>
                        <p class="text-blue-700 text-sm">Accede a todas las herramientas profesionales</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <i class="fas fa-calendar text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-blue-800">Gestionar Citas</h4>
                        <p class="text-blue-700 text-sm">Organiza tu agenda y seguimiento de pacientes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacto de Soporte -->
        <div class="text-center mt-8">
            <p class="text-gray-600 text-sm">
                ¿Tienes preguntas? Contacta al administrador: 
                <a href="mailto:admin@psyconnect.com" class="text-blue-600 hover:underline">admin@psyconnect.com</a>
            </p>
        </div>
    </div>
</div>
@endsection