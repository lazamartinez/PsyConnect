<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #E6F3FF 0%, #B0E2FF 100%);
        }

        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .iea-critical {
            background: linear-gradient(135deg, #FECACA 0%, #FCA5A5 100%);
        }

        .iea-low {
            background: linear-gradient(135deg, #FEF3C7 0%, #FCD34D 100%);
        }

        .iea-neutral {
            background: linear-gradient(135deg, #D1FAE5 0%, #34D399 100%);
        }

        .iea-high {
            background: linear-gradient(135deg, #DBEAFE 0%, #60A5FA 100%);
        }

        .iea-very-high {
            background: linear-gradient(135deg, #E0E7FF 0%, #8B5CF6 100%);
        }
    </style>
</head>

<body class="gradient-bg min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-brain text-2xl text-blue-500 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">PsyConnect</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Hola, {{ $paciente->usuario->nombre }}</span>
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

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Bienvenida -->
        <div class="bg-white rounded-2xl card-shadow p-8 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        ¡Bienvenido de vuelta, {{ $paciente->usuario->nombre }}! 👋
                    </h1>
                    <p class="text-gray-600 text-lg">
                        Tu bienestar emocional es nuestra prioridad.
                        {{ $ieaReciente ? 'Tu último IEA fue ' . $ieaReciente->valor_numerico : 'Comienza registrando tu estado emocional.' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Miembro desde</p>
                    <p class="font-semibold">{{ $paciente->usuario->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-500">{{ $paciente->manuscritos->count() }}</div>
                <div class="text-gray-600">Manuscritos</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-500">
                    {{ $paciente->indicesEstadoAnimico->avg('valor_numerico') ? round($paciente->indicesEstadoAnimico->avg('valor_numerico'), 1) : '--' }}
                </div>
                <div class="text-gray-600">IEA Promedio</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-purple-500">
                    {{ $paciente->manuscritos->where('estado_procesamiento', 'procesado')->count() }}
                </div>
                <div class="text-gray-600">Procesados</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-orange-500">
                    {{ $paciente->obtenerEdad() }}
                </div>
                <div class="text-gray-600">Años</div>
            </div>
        </div>

        <!-- Sección de Triaje Integrado -->
        <div class="bg-white rounded-2xl card-shadow p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">🚨 Iniciar Proceso de Triaje y Matching</h2>
            <p class="text-gray-600 mb-4">Completa nuestro formulario de triaje para encontrar al profesional más
                adecuado para ti.</p>

            <!-- Botón para abrir el modal de triaje -->
            <button onclick="abrirModalTriaje()"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-300">
                <i class="fas fa-stethoscope mr-2"></i>Iniciar Triaje Automático
            </button>

            <!-- Información adicional -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center text-green-600">
                    <i class="fas fa-bolt mr-2"></i>
                    <span>Proceso automático</span>
                </div>
                <div class="flex items-center text-blue-600">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <span>100% confidencial</span>
                </div>
                <div class="flex items-center text-purple-600">
                    <i class="fas fa-user-md mr-2"></i>
                    <span>Matching inteligente</span>
                </div>
            </div>
        </div>

        <!-- Modal de Triaje Integrado -->
        <div id="modalTriaje" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Header del Modal -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-t-lg">
                        <h3 class="text-2xl font-bold text-center">🎯 Triaje Inicial</h3>
                        <p class="text-blue-100 text-center mt-2">
                            Describe cómo te sientes para encontrar al profesional más adecuado
                        </p>
                    </div>

                    <div class="p-6">
                        <!-- Formulario de Triaje -->
                        <form id="formTriajeDashboard" method="POST" action="{{ route('triaje.procesar.matching') }}">
                            @csrf
                            <!-- Área de texto mejorada -->
                            <div class="mb-6">
                                <label for="descripcion_sintomatologia"
                                    class="block text-sm font-medium text-gray-700 mb-3">
                                    <span class="text-red-500">*</span> Describe tus síntomas y cómo te has estado
                                    sintiendo:
                                </label>
                                <textarea id="descripcion_sintomatologia" name="descripcion_sintomatologia" rows="8"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 form-input"
                                    placeholder="Sé lo más específico posible. Por ejemplo:
• Qué síntomas o sensaciones experimentas
• Cuándo comenzaron y con qué frecuencia
• Situaciones que los desencadenan  
• Cómo afectan tu vida diaria
• Cualquier otro detalle importante

Ejemplo: 'Últimamente me he sentido muy ansioso, especialmente por las noches. Tengo problemas para dormir y me despierto con palpitaciones. En el trabajo me siento abrumado y tengo dificultad para concentrarme...'"
                                    required></textarea>
                                <div class="flex justify-between items-center mt-2">
                                    <p class="text-sm text-gray-500">
                                        Mínimo 50 caracteres para un análisis preciso
                                    </p>
                                    <span id="charCountTriaje" class="text-sm font-medium text-gray-600">0
                                        caracteres</span>
                                </div>
                            </div>

                            <!-- Selector de Clínica (Opcional) -->
                            <div class="mb-6">
                                <label for="clinica_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Preferencia de clínica (opcional):
                                </label>
                                <select id="clinica_id" name="clinica_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Cualquier clínica disponible</option>
                                    @foreach ($clinicasActivas ?? [] as $clinica)
                                        <option value="{{ $clinica->id_clinica }}">{{ $clinica->nombre }} -
                                            {{ $clinica->ciudad }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Información de Confidencialidad -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start">
                                    <i class="fas fa-shield-alt text-blue-500 text-xl mt-1 mr-4"></i>
                                    <div>
                                        <h4 class="font-semibold text-blue-800 mb-1">Tu privacidad está protegida</h4>
                                        <p class="text-blue-700 text-sm">
                                            Toda la información que proporciones está encriptada y solo será accesible
                                            para los profesionales de salud mental autorizados.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                                <button type="button" onclick="cerrarModalTriaje()"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition duration-200 text-center">
                                    <i class="fas fa-arrow-left mr-2"></i>Cancelar
                                </button>
                                <button type="submit" id="submitTriajeBtn"
                                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    <span id="submitTriajeText">Iniciar Matching</span>
                                    <div id="loadingTriajeSpinner" class="hidden ml-2">
                                        <div
                                            class="spinner border-2 border-white border-t-transparent rounded-full w-5 h-5 animate-spin">
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Resultados del Matching -->
        <div id="modalResultadoMatching"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Contenido dinámico de resultados -->
                    <div id="contenidoResultadoMatching">
                        <!-- Aquí se cargarán los resultados del matching -->
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Acciones Rápidas -->
            <div class="bg-white rounded-2xl card-shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('manuscritos.create') }}"
                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition duration-200">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Nuevo Manuscrito</h3>
                            <p class="text-sm text-gray-600">Registra tu estado emocional actual</p>
                        </div>
                    </a>

                    <a href="{{ route('manuscritos.index') }}"
                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-200 transition duration-200">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-history text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Ver Historial</h3>
                            <p class="text-sm text-gray-600">Revisa tus manuscritos anteriores</p>
                        </div>
                    </a>

                    <button
                        class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition duration-200">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Ver Progreso</h3>
                            <p class="text-sm text-gray-600">Analiza tu evolución emocional</p>
                        </div>
                    </button>
                </div>
                @if ($matchesPendientes->count() > 0)
                    <div class="bg-white rounded-2xl card-shadow p-6 mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">🎯 Coincidencias con Profesionales</h2>

                        <div class="space-y-4">
                            @foreach ($matchesPendientes as $match)
                                <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                                <i class="fas fa-user-md text-blue-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800">
                                                    Dr. {{ $match->usuario->nombre }} {{ $match->usuario->apellido }}
                                                </h3>
                                                <p class="text-sm text-gray-600">
                                                    {{ $match->especialidad_principal }} •
                                                    Compatibilidad: <span
                                                        class="font-bold text-green-600">{{ $match->pivot->puntuacion_compatibilidad }}%</span>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $match->pivot->motivo_asignacion }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="aceptarMatch({{ $match->id }})"
                                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                                                <i class="fas fa-check mr-2"></i>Aceptar
                                            </button>
                                            <button onclick="rechazarMatch({{ $match->id }})"
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                                                <i class="fas fa-times mr-2"></i>Rechazar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Manuscritos Recientes -->
            <div class="bg-white rounded-2xl card-shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Manuscritos Recientes</h2>

                @if ($manuscritosRecientes->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">Aún no tienes manuscritos registrados</p>
                        <a href="{{ route('manuscritos.create') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            Crear Primer Manuscrito
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($manuscritosRecientes as $manuscrito)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <i class="fas fa-file-alt text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800">
                                            Manuscrito #{{ $manuscrito->id_manuscrito }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ $manuscrito->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if ($manuscrito->indiceEstadoAnimico)
                                        @php
                                            $iea = $manuscrito->indiceEstadoAnimico;
                                            $claseIEA = 'iea-' . str_replace('_', '-', $iea->categoria_emotional);
                                        @endphp
                                        <div
                                            class="{{ $claseIEA }} text-white px-3 py-1 rounded-full text-sm font-semibold">
                                            {{ $iea->valor_numerico }}
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">En proceso</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('manuscritos.index') }}" class="text-blue-class="text-blue-500
                            hover:text-blue-700 font-semibold transition duration-200">
                            Ver todos los manuscritos →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Estado Emocional Actual -->
        @if ($ieaReciente)
            <div class="bg-white rounded-2xl card-shadow p-8 mt-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Tu Estado Emocional Actual</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-5xl font-bold text-blue-500 mb-2">{{ $ieaReciente->valor_numerico }}</div>
                        <div class="text-lg font-semibold text-gray-700">Índice IEA</div>
                        <div class="text-sm text-gray-500 capitalize mt-1">
                            {{ str_replace('_', ' ', $ieaReciente->categoria_emotional) }}
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <h4 class="font-semibold text-gray-700 mb-3">Emociones Detectadas</h4>
                        <div class="grid grid-cols-2 gap-3">
                            @if ($ieaReciente->emociones_detectadas)
                                @foreach (array_slice($ieaReciente->emociones_detectadas, 0, 4) as $emocion => $intensidad)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize">{{ $emocion }}</span>
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-500 h-2 rounded-full"
                                                    style="width: {{ $intensidad * 100 }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ round($intensidad * 100) }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recomendaciones -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl card-shadow p-8 mt-8 text-white">
            <h2 class="text-xl font-bold mb-4">💡 Recomendación del Día</h2>
            <p class="text-lg mb-4">
                {{ $ieaReciente
                    ? ($ieaReciente->valor_numerico < 40
                        ? 'Te recomendamos practicar 5 minutos de respiración consciente hoy. Pequeños momentos de mindfulness pueden hacer una gran diferencia.'
                        : ($ieaReciente->valor_numerico < 70
                            ? 'Mantén tu rutina de autocuidado. Considera escribir tres cosas por las que estés agradecido.'
                            : '¡Excelente estado! Aprovecha esta energía para conectar con seres queridos o iniciar un proyecto personal.'))
                    : 'Comienza registrando tu primer manuscrito para recibir recomendaciones personalizadas.' }}
            </p>
            <div class="flex space-x-4">
                <button
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50 transition duration-200">
                    <i class="fas fa-play-circle mr-2"></i>Ejercicio Guiado
                </button>
                <button
                    class="bg-blue-400 bg-opacity-20 text-white px-4 py-2 rounded-lg font-semibold hover:bg-opacity-30 transition duration-200">
                    <i class="fas fa-book mr-2"></i>Recursos
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <nav class="bg-white border-t fixed bottom-0 w-full md:hidden">
        <div class="flex justify-around items-center py-3">
            <a href="{{ route('dashboard') }}" class="text-blue-500 text-center">
                <i class="fas fa-home text-lg"></i>
                <div class="text-xs mt-1">Inicio</div>
            </a>
            <a href="{{ route('manuscritos.create') }}" class="text-gray-500 text-center">
                <i class="fas fa-plus-circle text-lg"></i>
                <div class="text-xs mt-1">Nuevo</div>
            </a>
            <a href="{{ route('manuscritos.index') }}" class="text-gray-500 text-center">
                <i class="fas fa-history text-lg"></i>
                <div class="text-xs mt-1">Historial</div>
            </a>
            <a href="#" class="text-gray-500 text-center">
                <i class="fas fa-user text-lg"></i>
                <div class="text-xs mt-1">Perfil</div>
            </a>
        </div>
    </nav>
</body>
<script>
    // Funciones para el modal de triaje
    function abrirModalTriaje() {
        document.getElementById('modalTriaje').classList.remove('hidden');
        document.getElementById('descripcion_sintomatologia').focus();
    }

    function cerrarModalTriaje() {
        document.getElementById('modalTriaje').classList.add('hidden');
        document.getElementById('formTriajeDashboard').reset();
        document.getElementById('charCountTriaje').textContent = '0 caracteres';
        document.getElementById('charCountTriaje').className = 'text-sm font-medium text-gray-600';
    }

    // Contador de caracteres para el triaje
    document.getElementById('descripcion_sintomatologia').addEventListener('input', function() {
        const length = this.value.length;
        const charCount = document.getElementById('charCountTriaje');
        charCount.textContent = length + ' caracteres';

        // Validación visual
        if (length < 50) {
            charCount.className = 'text-sm font-medium text-red-600';
            document.getElementById('submitTriajeBtn').disabled = true;
        } else if (length < 100) {
            charCount.className = 'text-sm font-medium text-yellow-600';
            document.getElementById('submitTriajeBtn').disabled = false;
        } else {
            charCount.className = 'text-sm font-medium text-green-600';
            document.getElementById('submitTriajeBtn').disabled = false;
        }
    });

    // Manejo del formulario de triaje
    document.getElementById('formTriajeDashboard').addEventListener('submit', function(e) {
        e.preventDefault();

        const descripcion = document.getElementById('descripcion_sintomatologia').value.trim();

        if (descripcion.length < 50) {
            alert('Por favor, describe tus síntomas con al menos 50 caracteres para un análisis preciso.');
            return;
        }

        // Mostrar loading
        const submitBtn = document.getElementById('submitTriajeBtn');
        const submitText = document.getElementById('submitTriajeText');
        const loadingSpinner = document.getElementById('loadingTriajeSpinner');

        submitText.textContent = 'Analizando y buscando profesional...';
        loadingSpinner.classList.remove('hidden');
        submitBtn.disabled = true;

        // OBTENER EL TOKEN CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Enviar formulario via AJAX
        const formData = new FormData(this);

        fetch('{{ route('triaje.procesar.matching') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken, // Usar el token obtenido
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Cerrar modal de triaje
                    cerrarModalTriaje();

                    // Mostrar resultados del matching
                    mostrarResultadoMatching(data);
                } else {
                    alert('Error: ' + (data.message || 'Error en el proceso de matching'));
                    resetearBotonTriaje();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar el triaje. Por favor, intenta nuevamente.');
                resetearBotonTriaje();
            });
    });

    function resetearBotonTriaje() {
        document.getElementById('submitTriajeText').textContent = 'Iniciar Matching';
        document.getElementById('loadingTriajeSpinner').classList.add('hidden');
        document.getElementById('submitTriajeBtn').disabled = false;
    }

    function mostrarResultadoMatching(data) {
        const contenido = document.getElementById('contenidoResultadoMatching');

        if (data.match_encontrado) {
            // MATCH EXITOSO
            contenido.innerHTML = `
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-lg">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold">¡Match Encontrado!</h3>
                        <p class="text-green-100 mt-2">Hemos encontrado un profesional altamente compatible contigo</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Información del Profesional -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-md text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">${data.profesional.usuario.nombre} ${data.profesional.usuario.apellido}</h4>
                                <p class="text-green-600 font-semibold capitalize">${data.profesional.especialidad_principal}</p>
                                <div class="flex items-center mt-1">
                                    <span class="bg-green-500 text-white px-2 py-1 rounded text-sm font-bold">
                                        ${data.puntaje_compatibilidad}% Compatibilidad
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- Detalles del Matching -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-blue-800 mb-2">📊 Factores de Compatibilidad</h5>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Coincidencia de síntomas:</span>
                                    <span class="font-semibold">${data.analisis_sintomas.total_palabras_clave} encontrados</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Nivel de urgencia:</span>
                                    <span class="font-semibold capitalize">${data.analisis_sintomas.nivel_urgencia}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-purple-800 mb-2">🎯 Especialización</h5>
                            <div class="flex flex-wrap gap-1">
                                ${data.profesional.palabras_clave_especialidad ? data.profesional.palabras_clave_especialidad.slice(0, 5).map(palabra => 
                                    `<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">${palabra}</span>`
                                ).join('') : ''}
                            </div>
                        </div>
                    </div>
    
                    <!-- Acciones -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="aceptarMatch(${data.profesional.id})" 
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>Aceptar Profesional
                        </button>
                        <button onclick="cerrarModalResultado()" 
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i>Rechazar
                        </button>
                    </div>
                </div>
            `;
        } else {
            // NO HAY MATCH
            contenido.innerHTML = `
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-6 rounded-t-lg">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-white text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold">Buscando el Profesional Ideal</h3>
                        <p class="text-yellow-100 mt-2">Estamos analizando tu caso para encontrar la mejor opción</p>
                    </div>
                </div>
                
                <div class="p-6 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-yellow-800 mb-2">📋 Análisis Realizado</h4>
                        <div class="space-y-1 text-sm text-yellow-700">
                            <div>Síntomas detectados: ${data.analisis_sintomas.sintomas_detectados.length}</div>
                            <div>Palabras clave: ${data.analisis_sintomas.total_palabras_clave}</div>
                            <div>Nivel de urgencia: <span class="capitalize">${data.analisis_sintomas.nivel_urgencia}</span></div>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-6">
                        Actualmente no tenemos un match perfecto disponible, pero estamos trabajando 
                        para encontrar al profesional más adecuado para ti.
                    </p>
                    
                    <div class="flex gap-3 justify-center">
                        <button onclick="cerrarModalResultado()" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="fas fa-redo mr-2"></i>Intentar Más Tarde
                        </button>
                        <button onclick="contactarSoporte()" 
                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="fas fa-headset mr-2"></i>Contactar Soporte
                        </button>
                    </div>
                </div>
            `;
        }

        // Mostrar modal de resultados
        document.getElementById('modalResultadoMatching').classList.remove('hidden');
    }

    function cerrarModalResultado() {
        document.getElementById('modalResultadoMatching').classList.add('hidden');
        // Recargar la página para actualizar matches pendientes
        setTimeout(() => location.reload(), 300);
    }

    function aceptarMatch(profesionalId) {
        fetch(`/matching/aceptar/${profesionalId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    cerrarModalResultado();
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            });
    }

    function contactarSoporte() {
        alert('📞 Por favor, contacta a nuestro equipo de soporte para asistencia personalizada.');
        cerrarModalResultado();
    }

    // Cerrar modales al hacer click fuera
    window.onclick = function(event) {
        const modalTriaje = document.getElementById('modalTriaje');
        const modalResultado = document.getElementById('modalResultadoMatching');

        if (event.target == modalTriaje) {
            cerrarModalTriaje();
        }
        if (event.target == modalResultado) {
            cerrarModalResultado();
        }
    }
</script>

</html>
