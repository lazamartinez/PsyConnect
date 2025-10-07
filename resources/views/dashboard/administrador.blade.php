<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <i class="fas fa-brain text-2xl text-blue-500 mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-800">PsyConnect - Administrador</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Hola, {{ Auth::user()->nombre }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Administrador</h1>

                <!-- Acciones Rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-blue-600">{{ $totalClinicas }}</div>
                        <div class="text-gray-600">Clínicas Activas</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-green-600">{{ $totalProfesionales }}</div>
                        <div class="text-gray-600">Profesionales</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-purple-600">{{ $totalPacientes }}</div>
                        <div class="text-gray-600">Pacientes</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-orange-600">{{ $triajesPendientes }}</div>
                        <div class="text-gray-600">Triajes Pendientes</div>
                    </div>
                </div>

                <!-- Sección de Aprobación de Profesionales Pendientes -->
                @if ($solicitudesPendientes > 0)
                    <div class="bg-white rounded-lg shadow mb-8">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-user-md text-orange-500 mr-2"></i>
                                    Profesionales Pendientes de Aprobación
                                </h2>
                                <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $solicitudesPendientes }} pendientes
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Profesional</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Especialidad</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Experiencia</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Fecha Solicitud</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($solicitudesRecientes as $solicitud)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user-md text-blue-600"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $solicitud->usuario->nombre }}
                                                                {{ $solicitud->usuario->apellido }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $solicitud->usuario->email }}</div>
                                                            <div class="text-xs text-gray-400">
                                                                {{ $solicitud->usuario->telefono }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 capitalize">
                                                        {{ $solicitud->especialidad_principal }}</div>
                                                    @if ($solicitud->matricula)
                                                        <div class="text-xs text-gray-500">Matrícula:
                                                            {{ $solicitud->matricula }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $solicitud->anios_experiencia ?? 0 }} años</div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $solicitud->institucion ?? 'Sin institución' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $solicitud->created_at->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <button
                                                            onclick="aprobarProfesional('{{ $solicitud->id_profesional }}')"
                                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs flex items-center">
                                                            <i class="fas fa-check mr-1"></i>Aprobar
                                                        </button>

                                                        <button
                                                            onclick="mostrarModalRechazo('{{ $solicitud->id_profesional }}')"
                                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs flex items-center">
                                                            <i class="fas fa-times mr-1"></i>Rechazar
                                                        </button>

                                                        <button
                                                            onclick="verDetallesProfesional('{{ $solicitud->id_profesional }}')"
                                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs flex items-center">
                                                            <i class="fas fa-eye mr-1"></i>Ver
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($solicitudesRecientes->count() < $solicitudesPendientes)
                                <div class="mt-4 text-center">
                                    <a href="{{ route('admin.solicitudes.index') }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        Ver todas las {{ $solicitudesPendientes }} solicitudes pendientes →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Modal para Rechazar Profesional -->
                <div id="modalRechazo"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Rechazar Solicitud</h3>
                        <form id="formRechazo">
                            @csrf
                            <input type="hidden" id="profesionalIdRechazo" name="profesional_id">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del rechazo</label>
                                <textarea name="motivo_rechazo" id="motivoRechazo" rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Explica el motivo del rechazo..." required></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="cerrarModal('modalRechazo')"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
                                <button type="submit"
                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Rechazar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal para Ver Detalles del Profesional -->
                <div id="modalDetalles"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                    <div
                        class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                        <div id="contenidoDetalles">
                            <!-- Aquí se cargarán los detalles del profesional -->
                        </div>
                        <div class="mt-4 text-center">
                            <button onclick="cerrarModal('modalDetalles')"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel de Configuración Avanzada -->
                <div class="bg-white rounded-lg shadow mb-8">

                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-tools text-orange-500 mr-2"></i>
                                Reparación del Sistema
                            </h3>
                            <button onclick="ejecutarReparacionSistema()"
                                class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-magic mr-2"></i>Reparar Sistema
                            </button>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Ejecuta una reparación automática del sistema de matching. Esto normalizará especialidades
                            y asignará palabras clave a profesionales sin configuración.
                        </p>
                    </div>

                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">⚙️ Configuración del Sistema</h2>
                            <div class="flex space-x-3">
                                <!-- Botón de Configuración Avanzada -->
                                <a href="{{ route('admin.configuracion-avanzada') }}"
                                    class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center">
                                    <i class="fas fa-cogs mr-2"></i>Configuración Avanzada
                                </a>

                                <!-- Botón de Palabras Clave -->
                                <a href="{{ route('admin.palabras-clave.index') }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center">
                                    <i class="fas fa-list mr-2"></i>Palabras Clave
                                </a>
                            </div>
                        </div>
                        {{-- En una sección destacada --}}
                        <div class="mt-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-bold mb-2">🎓 Configuración de Especialidades</h3>
                                    <p class="text-blue-100">Gestiona las especialidades disponibles para cada tipo de
                                        profesional</p>
                                </div>
                                <a href="{{ route('admin.especialidades.index') }}"
                                    class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-200 flex items-center">
                                    <i class="fas fa-graduation-cap mr-2"></i>
                                    Gestionar Especialidades
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Configuración de Matching -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                                    <i class="fas fa-sliders-h mr-2"></i>Algoritmo de Matching
                                </h3>
                                <p class="text-blue-700 text-sm mb-4">
                                    Configura los pesos y reglas del sistema de matching automático entre pacientes y
                                    profesionales.
                                </p>
                                <a href="{{ route('admin.configuracion-avanzada') }}?tab=pesos"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Configurar Pesos →
                                </a>
                            </div>

                            <!-- Reglas de Especialidad -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h3 class="font-semibold text-green-900 mb-3 flex items-center">
                                    <i class="fas fa-stethoscope mr-2"></i>Reglas de Especialidad
                                </h3>
                                <p class="text-green-700 text-sm mb-4">
                                    Define las reglas y palabras clave para cada especialidad médica del sistema.
                                </p>
                                <a href="{{ route('admin.configuracion-avanzada') }}?tab=reglas"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Configurar Reglas →
                                </a>
                            </div>

                            <!-- Gestión de Palabras Clave -->
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <h3 class="font-semibold text-orange-900 mb-3 flex items-center">
                                    <i class="fas fa-key mr-2"></i>Palabras Clave
                                </h3>
                                <p class="text-orange-700 text-sm mb-4">
                                    Gestiona el catálogo de palabras clave para la detección de síntomas y asignación
                                    automática.
                                </p>
                                <a href="{{ route('admin.palabras-clave.index') }}"
                                    class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                                    Gestionar Palabras →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">🚀 Gestión Rápida</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                            <!-- Configuración Avanzada -->
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 text-center">
                                <div class="text-4xl font-bold text-purple-600 mb-2">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h3 class="font-semibold text-purple-900 mb-3">Configuración Avanzada</h3>
                                <p class="text-purple-700 text-sm mb-4">
                                    Configura algoritmos de matching y parámetros del sistema.
                                </p>
                                <a href="{{ route('admin.configuracion-avanzada') }}"
                                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                    <i class="fas fa-sliders-h mr-2"></i>Configurar Sistema
                                </a>
                            </div>

                            <!-- Palabras Clave -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                                <div class="text-4xl font-bold text-blue-600 mb-2">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h3 class="font-semibold text-blue-900 mb-3">Palabras Clave</h3>
                                <p class="text-blue-700 text-sm mb-4">
                                    Gestiona el catálogo de palabras clave para detección de síntomas.
                                </p>
                                <a href="{{ route('admin.palabras-clave.index') }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                    <i class="fas fa-list mr-2"></i>Gestionar Palabras
                                </a>
                            </div>

                            <!-- Gestión de Clínicas -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                                <div class="text-4xl font-bold text-green-600 mb-2">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <h3 class="font-semibold text-green-900 mb-3">Gestión de Clínicas</h3>
                                <p class="text-green-700 text-sm mb-4">
                                    Administra clínicas y sus profesionales asociados.
                                </p>
                                <button onclick="abrirModalClinica()"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nueva Clínica
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gestión de Clínicas -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">🏥 Gestión de Clínicas</h2>
                            <button onclick="abrirModalClinica()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i>Nueva Clínica
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Ubicación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Profesionales</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($clinicas as $clinica)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $clinica->nombre }}</div>
                                                <div class="text-sm text-gray-500">{{ $clinica->telefono }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $clinica->ciudad }},
                                                    {{ $clinica->provincia }}</div>
                                                <div class="text-sm text-gray-500">{{ $clinica->direccion }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ $clinica->profesionales_count ?? 0 }} profesionales
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $clinica->estado == 'activa' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $clinica->estado }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="editarClinica({{ $clinica->id_clinica }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-edit mr-1"></i>Editar
                                                </button>
                                                <button onclick="cambiarEstadoClinica({{ $clinica->id_clinica }})"
                                                    class="text-orange-600 hover:text-orange-900">
                                                    <i class="fas fa-sync-alt mr-1"></i>Estado
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Configuración del Sistema de Matching -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">🎯 Configuración del Sistema de Matching</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pesos del Algoritmo -->
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900 mb-4">Pesos del Algoritmo de Matching</h3>
                                <div id="pesosActuales">
                                    @php
                                        $pesos = \App\Models\ConfiguracionMatching::obtenerPesosMatching();
                                    @endphp
                                    @foreach ($pesos as $key => $value)
                                        <div class="flex justify-between items-center mb-2">
                                            <span
                                                class="text-sm text-blue-800 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                            <span
                                                class="font-bold text-blue-600">{{ number_format($value * 100, 1) }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('admin.configuracion-avanzada') }}?tab=pesos"
                                    class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                    <i class="fas fa-sliders-h mr-2"></i>Configurar Pesos
                                </a>
                            </div>

                            <!-- Reglas de Especialidad -->
                            <div class="bg-green-50 rounded-lg p-4">
                                <h3 class="font-semibold text-green-900 mb-4">Reglas de Especialidad</h3>
                                <div class="space-y-2">
                                    @php
                                        $reglas = \App\Models\ConfiguracionMatching::obtenerReglasEspecialidad();
                                    @endphp
                                    @foreach ($reglas as $especialidad => $config)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-green-800 capitalize">{{ $especialidad }}</span>
                                            <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">
                                                {{ count($config['palabras_clave'] ?? []) }} palabras clave
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('admin.configuracion-avanzada') }}?tab=reglas"
                                    class="mt-4 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                    <i class="fas fa-cog mr-2"></i>Configurar Reglas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reportes de Matching -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">📊 Reportes de Matching</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900 mb-2">Efectividad del Matching</h3>
                                <div class="text-2xl font-bold text-blue-600">{{ $efectividadMatching }}%</div>
                                <p class="text-sm text-blue-700">Asignaciones exitosas</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <h3 class="font-semibold text-green-900 mb-2">Tiempo Promedio de Asignación</h3>
                                <div class="text-2xl font-bold text-green-600">{{ $tiempoPromedioAsignacion }}h</div>
                                <p class="text-sm text-green-700">Desde registro hasta asignación</p>
                            </div>
                        </div>

                        <!-- Gráfico de efectividad por especialidad -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-4">Efectividad por Especialidad</h3>
                            <canvas id="graficoEfectividad" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Configurar Pesos -->
    <div id="modalPesos" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Configurar Pesos del Algoritmo</h3>

                <form id="formPesos">
                    @csrf
                    <div class="space-y-4">
                        @foreach ($pesos as $key => $value)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 capitalize mb-1">
                                    {{ str_replace('_', ' ', $key) }}
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input type="range" name="pesos[{{ $key }}]"
                                        value="{{ $value * 100 }}" min="0" max="100" step="1"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                        oninput="document.getElementById('valor_{{ $key }}').textContent = this.value + '%'">
                                    <span id="valor_{{ $key }}" class="text-sm font-medium w-16">
                                        {{ number_format($value * 100, 0) }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="cerrarModal('modalPesos')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funciones para modales
        function abrirModalPesos() {
            document.getElementById('modalPesos').classList.remove('hidden');
        }

        function abrirModalReglas() {
            // Implementar modal para reglas
            alert('Modal de reglas - Implementar según necesidades');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Gráfico de efectividad
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('graficoEfectividad').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Psicólogos', 'Psiquiatras', 'Nutricionistas'],
                    datasets: [{
                        label: 'Efectividad (%)',
                        data: [85, 92, 78],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        });

        // Manejo del formulario de pesos
        document.getElementById('formPesos').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const pesos = {};

            // Convertir valores de porcentaje a decimal
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('pesos[')) {
                    const pesoKey = key.match(/\[(.*?)\]/)[1];
                    pesos[pesoKey] = parseFloat(value) / 100;
                }
            }

            fetch('/admin/configuracion/matching', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        pesos: pesos
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert('Configuración guardada exitosamente');
                        cerrarModal('modalPesos');
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al guardar la configuración');
                });
        });
    </script>
    <script>
        // Funciones para gestionar profesionales
        function aprobarProfesional(id) {
            if (confirm('¿Estás seguro de que quieres aprobar este profesional?')) {
                fetch(`/admin/profesionales/${id}/aprobar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Profesional aprobado exitosamente');
                            location.reload();
                        } else {
                            alert('❌ Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Error al aprobar el profesional');
                    });
            }
        }

        function mostrarModalRechazo(id) {
            document.getElementById('profesionalIdRechazo').value = id;
            document.getElementById('modalRechazo').classList.remove('hidden');
        }

        function verDetallesProfesional(id) {
            fetch(`/admin/profesionales/${id}`)
                .then(response => response.json())
                .then(data => {
                    const contenido = document.getElementById('contenidoDetalles');
                    contenido.innerHTML = `
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Detalles del Profesional</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-700">Información Personal</h4>
                                <p><strong>Nombre:</strong> ${data.usuario.nombre} ${data.usuario.apellido}</p>
                                <p><strong>Email:</strong> ${data.usuario.email}</p>
                                <p><strong>Teléfono:</strong> ${data.usuario.telefono}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-700">Información Profesional</h4>
                                <p><strong>Especialidad:</strong> ${data.especialidad_principal}</p>
                                <p><strong>Experiencia:</strong> ${data.anios_experiencia} años</p>
                                <p><strong>Institución:</strong> ${data.institucion || 'No especificada'}</p>
                                <p><strong>Matrícula:</strong> ${data.matricula || 'No especificada'}</p>
                            </div>
                        </div>
                        ${data.bio ? `
                                                        <div class="mt-4">
                                                            <h4 class="font-semibold text-gray-700">Biografía</h4>
                                                            <p class="text-gray-600">${data.bio}</p>
                                                        </div>
                                                        ` : ''}
                    `;
                    document.getElementById('modalDetalles').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles del profesional');
                });
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Manejar formulario de rechazo
        document.getElementById('formRechazo').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const id = document.getElementById('profesionalIdRechazo').value;

            fetch(`/admin/profesionales/${id}/rechazar`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Solicitud rechazada exitosamente');
                        cerrarModal('modalRechazo');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al rechazar la solicitud');
                });
        });

        // Cerrar modales al hacer click fuera
        window.onclick = function(event) {
            const modals = ['modalRechazo', 'modalDetalles'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    </script>

    <script>
        function ejecutarReparacionSistema() {
            if (!confirm('¿Estás seguro de que quieres ejecutar la reparación del sistema?')) {
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Reparando...';
            btn.disabled = true;

            fetch('/admin/sistema/reparar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error: ' + error.message);
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>


</body>

</html>
