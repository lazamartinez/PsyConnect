<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profesional - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <i class="fas fa-user-md text-2xl text-green-500 mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-800">PsyConnect - Profesional</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Dr. {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</span>
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
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Mi Dashboard Profesional</h1>

                <!-- Estadísticas del Profesional -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-blue-600">{{ $pacientesActivos }}</div>
                        <div class="text-gray-600">Pacientes Activos</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-green-600">{{ $citasHoy }}</div>
                        <div class="text-gray-600">Citas Hoy</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-purple-600">{{ $compatibilidadPromedio }}%</div>
                        <div class="text-gray-600">Compatibilidad Promedio</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-3xl font-bold text-orange-600">{{ $nuevosPacientes }}</div>
                        <div class="text-gray-600">Nuevos Pacientes (7d)</div>
                    </div>
                </div>

                <!-- Modal Mejorado para Configurar Palabras Clave -->
                <div id="modalPalabrasClave"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                    <div
                        class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-key text-blue-500 mr-2"></i>
                                Palabras Clave para {{ ucfirst($profesional->especialidad_principal) }}
                            </h3>

                            <div class="mb-6">
                                <p class="text-sm text-gray-600 mb-4">
                                    Selecciona las palabras clave de tu especialidad. Estas palabras ayudarán al sistema
                                    a hacer matching con pacientes que describan síntomas relacionados con tu expertise.
                                </p>

                                <!-- Información de la especialidad -->
                                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-md text-blue-500 text-xl mr-3"></i>
                                        <div>
                                            <h4 class="font-semibold text-blue-900">Especialidad:
                                                {{ ucfirst($profesional->especialidad_principal) }}</h4>
                                            <p class="text-blue-700 text-sm">Selecciona las palabras que mejor definan
                                                tu área de expertise</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gestión de Palabras Clave -->
                            {{-- <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Buscar y Agregar Palabras Clave
                                </label>
                                <div class="flex space-x-2">
                                    <input type="text" id="buscarPalabra"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Buscar palabras clave..." onkeyup="filtrarPalabras()">
                                    <button type="button" onclick="agregarPalabraPersonalizada()"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-plus mr-1"></i>Personalizada
                                    </button>
                                </div>
                            </div> --}}

                            <!-- Palabras Clave del Sistema por Categoría -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Palabras Clave Disponibles para tu Especialidad
                                </label>

                                <div id="contenedorCategorias" class="space-y-4 max-h-96 overflow-y-auto p-2">
                                    @foreach ($palabrasClaveSistema as $categoria => $palabras)
                                        <div class="border border-gray-200 rounded-lg">
                                            <div class="bg-gray-50 px-4 py-2 border-b">
                                                <h4 class="font-semibold text-gray-800 capitalize flex items-center">
                                                    <i class="fas fa-folder mr-2 text-blue-500"></i>
                                                    {{ $categoria }}
                                                    <span class="ml-2 text-gray-500 text-sm">({{ $palabras->count() }}
                                                        palabras)</span>
                                                </h4>
                                            </div>
                                            <div class="p-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach ($palabras as $palabra)
                                                    <div class="flex items-center">
                                                        <input type="checkbox"
                                                            id="palabra_{{ $palabra->id_palabra_clave }}"
                                                            value="{{ $palabra->palabra }}" {{-- {{ in_array($palabra->palabra, $profesional->palabras_clave_especialidad ?? []) ? 'checked' : '' }} --}}
                                                            {{ in_array($palabra->palabra, $profesional->palabras_clave_especialidad->pluck('palabra')->toArray() ?? []) ? 'checked' : '' }}
                                                            class="palabra-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                                        <label for="palabra_{{ $palabra->id_palabra_clave }}"
                                                            class="text-sm text-gray-700 cursor-pointer flex items-center">
                                                            <span class="mr-1">{{ $palabra->palabra }}</span>
                                                            @if ($palabra->nivel_alerta == 'critico')
                                                                <i class="fas fa-exclamation-triangle text-red-500 text-xs"
                                                                    title="Palabra crítica"></i>
                                                            @elseif($palabra->nivel_alerta == 'alto')
                                                                <i class="fas fa-exclamation-circle text-orange-500 text-xs"
                                                                    title="Alta prioridad"></i>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($palabrasClaveSistema->isEmpty())
                                        <div class="text-center py-8 text-gray-500">
                                            <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                                            <p>No hay palabras clave configuradas para tu especialidad.</p>
                                            <p class="text-sm">Contacta al administrador del sistema.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Palabras Clave Seleccionadas -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tus Palabras Clave Seleccionadas
                                    <span id="contadorPalabras" class="text-blue-600 font-semibold">0</span>
                                </label>
                                <div id="palabrasSeleccionadasContainer"
                                    class="flex flex-wrap gap-2 p-4 border border-green-200 rounded-lg bg-green-50 min-h-20">
                                    <!-- Aquí se mostrarán las palabras seleccionadas -->
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Mínimo recomendado: 5 palabras para un matching efectivo
                                </p>
                            </div>

                            <!-- Estadísticas -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <h4 class="font-semibold text-gray-800 mb-2">Estadísticas de Selección</h4>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-blue-600" id="statsTotal">0</div>
                                        <div class="text-gray-600">Total Seleccionadas</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-green-600" id="statsCategorias">0</div>
                                        <div class="text-gray-600">Categorías</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-purple-600" id="statsCriticas">0</div>
                                        <div class="text-gray-600">Críticas</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-6">
                                <div>
                                    <button type="button" onclick="seleccionarTodas()"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <i class="fas fa-check-square mr-1"></i>Seleccionar todas
                                    </button>
                                    <button type="button" onclick="deseleccionarTodas()"
                                        class="ml-4 text-gray-600 hover:text-gray-800 text-sm font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>Limpiar
                                    </button>
                                </div>
                                <div class="flex space-x-3">
                                    <button type="button" onclick="cerrarModal('modalPalabrasClave')"
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                        Cancelar
                                    </button>
                                    <button type="button" onclick="guardarPalabrasClave()" id="btnGuardarPalabras"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-save mr-2"></i>Guardar Selección
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($pacientesPendientes->count() > 0)
                    <div class="bg-white rounded-lg shadow mb-8">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">🆕 Nuevos Pacientes Potenciales</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach ($pacientesPendientes as $paciente)
                                    <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center">
                                                <div class="bg-green-100 p-3 rounded-lg mr-4">
                                                    <i class="fas fa-user text-green-600 text-xl"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-800">
                                                        {{ $paciente->usuario->nombre }}
                                                        {{ $paciente->usuario->apellido }}
                                                    </h3>
                                                    <p class="text-sm text-gray-600">
                                                        Compatibilidad: <span
                                                            class="font-bold text-green-600">{{ $paciente->pivot->puntuacion_compatibilidad }}%</span>
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        <strong>Motivo:</strong>
                                                        {{ $paciente->pivot->motivo_asignacion }}
                                                    </p>
                                                    <div class="flex flex-wrap gap-1 mt-2">
                                                        @foreach ($paciente->triaje->analisis_sintomatologia['sintomas_detectados'] ?? [] as $sintoma)
                                                            <span
                                                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                                {{ $sintoma }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm text-gray-500">
                                                    {{ $paciente->pivot->fecha_asignacion->diffForHumans() }}
                                                </span>
                                                <div class="mt-2">
                                                    <button
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                                        <i class="fas fa-eye mr-1"></i>Ver Detalles
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif


                <!-- Configuración de Perfil y Matching -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Información del Perfil -->
                    <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Mi Perfil</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Especialidad Principal</label>
                                <p class="text-gray-900">{{ $profesional->especialidad_principal }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Años de Experiencia</label>
                                <p class="text-gray-900">{{ $profesional->anios_experiencia }} años</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Clínica Principal</label>
                                <p class="text-gray-900">{{ $clinicaPrincipal->nombre ?? 'No asignada' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Estado</label>
                                <span
                                    class="px-2 py-1 text-xs rounded-full 
                                    {{ $profesional->estado_verificacion == 'verificado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $profesional->estado_verificacion }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Matching -->
                    <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">

                        <!-- Botón para abrir modal mejorado -->
                        <div class="flex justify-between items-center mb-4">
                            <button onclick="abrirModalPalabrasClave()"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <i class="fas fa-key mr-2"></i>Seleccionar Palabras Clave de Mi Especialidad
                            </button>
                        </div>

                        {{-- <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Configuración de Matching</h2>
                            <a href="{{ route('profesional.configuracion') }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-edit mr-2"></i>Configurar
                            </a>
                        </div>                         --}}

                        <!-- Palabras Clave Actuales -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-700 mb-3">Palabras Clave de Especialidad</h3>
                            <div class="flex flex-wrap gap-2">
                                @if (!empty($profesional->palabras_clave_especialidad))
                                    @foreach ($profesional->palabras_clave_especialidad as $palabra)
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            {{ $palabra }}
                                        </span>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-sm">No hay palabras clave configuradas</p>
                                    <p class="text-red-500 text-xs mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Sin palabras clave, el sistema no podrá hacer matching con pacientes
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Síntomas que Atiende -->
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-700 mb-3">Síntomas y Problemáticas que Atiendo</h3>
                            <div class="flex flex-wrap gap-2">
                                @if (!empty($profesional->sintomas_atiende))
                                    @foreach ($profesional->sintomas_atiende as $sintoma)
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                            {{ $sintoma }}
                                        </span>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-sm">No hay síntomas configurados</p>
                                @endif
                            </div>
                        </div>

                        <!-- Estadísticas de Matching -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-medium text-gray-700 mb-2">Estadísticas de Matching</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Coincidencias este mes:</span>
                                    <span class="font-semibold">{{ $coincidenciasMes }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Tasa de aceptación:</span>
                                    <span class="font-semibold">{{ $tasaAceptacion }}%</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-600">Estado del sistema:</span>
                                    @if (!empty($profesional->palabras_clave_especialidad))
                                        <span class="font-semibold text-green-600">✅ Activo - Listo para
                                            matching</span>
                                    @else
                                        <span class="font-semibold text-red-600">❌ Inactivo - Configura palabras
                                            clave</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pacientes Asignados Recientemente -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Pacientes Asignados Recientemente</h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Paciente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Fecha Asignación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Compatibilidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Síntomas Principales</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($pacientesRecientes as $paciente)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">
                                                    {{ $paciente->usuario->nombre }}
                                                    {{ $paciente->usuario->apellido }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $paciente->usuario->email }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $paciente->pivot->fecha_asignacion->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $paciente->pivot->puntuacion_compatibilidad >= 80
                                                    ? 'bg-green-100 text-green-800'
                                                    : ($paciente->pivot->puntuacion_compatibilidad >= 60
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-red-100 text-red-800') }}">
                                                    {{ $paciente->pivot->puntuacion_compatibilidad }}%
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach (array_slice($paciente->triaje->analisis_sintomatologia['palabras_clave'] ?? [], 0, 3) as $palabra)
                                                        <span
                                                            class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                                            {{ $palabra }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-eye mr-1"></i>Ver
                                                </button>
                                                <button class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-calendar-plus mr-1"></i>Cita
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar arrays
        let palabrasClave = @json($profesional->palabras_clave_especialidad ?? []);
        let todasLasPalabrasSistema = @json($palabrasClaveSistema->flatten()->pluck('palabra'));

        // Abrir modal
        function abrirModalPalabrasClave() {
            document.getElementById('modalPalabrasClave').classList.remove('hidden');
            actualizarVistaPalabrasSeleccionadas();
            actualizarEstadisticas();
        }

        // Cerrar modal
        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Filtrar palabras
        function filtrarPalabras() {
            const busqueda = document.getElementById('buscarPalabra').value.toLowerCase();
            const categorias = document.querySelectorAll('#contenedorCategorias > div');

            categorias.forEach(categoria => {
                let palabrasVisibles = 0;
                const palabras = categoria.querySelectorAll('.flex.items-center');

                palabras.forEach(palabraDiv => {
                    const label = palabraDiv.querySelector('label');
                    const textoPalabra = label.textContent.toLowerCase();
                    if (textoPalabra.includes(busqueda)) {
                        palabraDiv.style.display = 'flex';
                        palabrasVisibles++;
                    } else {
                        palabraDiv.style.display = 'none';
                    }
                });

                categoria.style.display = palabrasVisibles > 0 ? 'block' : 'none';
            });
        }

        // Agregar palabra personalizada
        function agregarPalabraPersonalizada() {
            const input = document.getElementById('buscarPalabra');
            const palabra = input.value.trim().toLowerCase();
            if (palabra && !palabrasClave.includes(palabra)) {
                palabrasClave.push(palabra);
                input.value = '';
                actualizarVistaPalabrasSeleccionadas();
                actualizarEstadisticas();

                const checkbox = document.querySelector(`input[value="${palabra}"]`);
                if (checkbox) checkbox.checked = true;
            }
        }

        // Manejar cambios en checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.palabra-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const palabra = this.value;
                    if (this.checked && !palabrasClave.includes(palabra)) {
                        palabrasClave.push(palabra);
                    } else if (!this.checked) {
                        palabrasClave = palabrasClave.filter(p => p !== palabra);
                    }
                    actualizarVistaPalabrasSeleccionadas();
                    actualizarEstadisticas();
                });
            });

            actualizarEstadisticas();
        });

        // Actualizar vista palabras seleccionadas
        function actualizarVistaPalabrasSeleccionadas() {
            const container = document.getElementById('palabrasSeleccionadasContainer');
            container.innerHTML = '';
            palabrasClave.forEach(palabra => {
                const span = document.createElement('span');
                span.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm flex items-center';
                span.innerHTML = `
                    ${palabra}
                    <button type="button" onclick="eliminarPalabra('${palabra}')"
                            class="ml-2 text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(span);
            });
        }

        // Eliminar palabra
        function eliminarPalabra(palabra) {
            palabrasClave = palabrasClave.filter(p => p !== palabra);
            const checkbox = document.querySelector(`input[value="${palabra}"]`);
            if (checkbox) checkbox.checked = false;
            actualizarVistaPalabrasSeleccionadas();
            actualizarEstadisticas();
        }

        // Actualizar estadísticas
        function actualizarEstadisticas() {
            const total = palabrasClave.length;
            const categoriasUnicas = new Set();
            const criticas = palabrasClave.filter(p => {
                const palabraInfo = @json($palabrasClaveSistema->flatten()->whereIn('palabra', $profesional->palabras_clave_especialidad ?? [])->toArray());
                return palabraInfo.some(info => info.nivel_alerta === 'critico' && info.palabra === p);
            }).length;

            palabrasClave.forEach(palabra => {
                @foreach ($palabrasClaveSistema as $categoria => $palabras)
                    if (@json($palabras->pluck('palabra')).includes(palabra)) {
                        categoriasUnicas.add('{{ $categoria }}');
                    }
                @endforeach
            });

            document.getElementById('contadorPalabras').textContent = total;
            document.getElementById('statsTotal').textContent = total;
            document.getElementById('statsCategorias').textContent = categoriasUnicas.size;
            document.getElementById('statsCriticas').textContent = criticas;

            const btnGuardar = document.getElementById('btnGuardarPalabras');
            btnGuardar.disabled = total < 3;
        }

        // Seleccionar todas
        function seleccionarTodas() {
            document.querySelectorAll('.palabra-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                const palabra = checkbox.value;
                if (!palabrasClave.includes(palabra)) palabrasClave.push(palabra);
            });
            actualizarVistaPalabrasSeleccionadas();
            actualizarEstadisticas();
        }

        // Deseleccionar todas
        function deseleccionarTodas() {
            document.querySelectorAll('.palabra-checkbox').forEach(checkbox => checkbox.checked = false);
            palabrasClave = [];
            actualizarVistaPalabrasSeleccionadas();
            actualizarEstadisticas();
        }

        // Guardar palabras clave
        function guardarPalabrasClave() {
            if (palabrasClave.length < 3) {
                alert('Por favor selecciona al menos 3 palabras clave para un matching efectivo.');
                return;
            }

            const btnGuardar = document.getElementById('btnGuardarPalabras');
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            btnGuardar.disabled = true;

            const formData = new FormData();
            palabrasClave.forEach(p => formData.append('palabras_clave_especialidad[]', p));

            fetch('/profesional/palabras-clave', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        cerrarModal('modalPalabrasClave');
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('❌ Error al guardar las palabras clave');
                })
                .finally(() => {
                    btnGuardar.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar Selección';
                    btnGuardar.disabled = false;
                });
        }
    </script>
</body>

</html>
