<!-- resources/views/matching/resultado-visual.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Matching - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .pulse-match {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .progress-bar {
            transition: width 2s ease-in-out;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-4">🎯 Resultado del Matching</h1>
            <p class="text-white text-lg opacity-90">Hemos analizado tus síntomas y encontrado el profesional más adecuado</p>
        </div>

        @if($resultado['match_encontrado'])
            <!-- MATCH EXITOSO -->
            <div class="max-w-4xl mx-auto">
                <!-- Tarjeta de Match Exitoso -->
                <div class="card-glass rounded-2xl p-8 mb-8 pulse-match">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">¡Match Perfecto Encontrado!</h2>
                        <p class="text-white opacity-90">Hemos encontrado un profesional altamente compatible contigo</p>
                    </div>
                </div>

                <!-- Información del Profesional -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Columna 1: Información del Profesional -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <div class="text-center mb-6">
                            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user-md text-blue-600 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $resultado['profesional']->usuario->nombre }} {{ $resultado['profesional']->usuario->apellido }}</h3>
                            <p class="text-blue-600 font-semibold capitalize">{{ $resultado['profesional']->especialidad_principal }}</p>
                            <div class="mt-2">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $resultado['puntaje_compatibilidad'] }}% Compatibilidad
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">📊 Especialización</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($resultado['profesional']->palabras_clave_especialidad ?? [] as $palabra)
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                            {{ $palabra }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-700 mb-2">⭐ Experiencia</h4>
                                <p class="text-gray-600">{{ $resultado['profesional']->anios_experiencia }} años de experiencia</p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Detalles del Matching -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">🔍 Análisis de Compatibilidad</h3>
                        
                        <!-- Factores de Matching -->
                        <div class="space-y-4">
                            @if(isset($resultado['detalles_matching']))
                                @foreach($resultado['detalles_matching'][0]['factores'] ?? [] as $factor => $detalle)
                                    <div class="border-l-4 border-blue-500 pl-4">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-semibold text-gray-700 capitalize">
                                                {{ str_replace('_', ' ', $factor) }}
                                            </span>
                                            <span class="text-blue-600 font-bold">{{ number_format($detalle['contribucion'], 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full progress-bar" 
                                                 style="width: {{ $detalle['contribucion'] }}%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $detalle['explicacion'] }}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Coincidencias de Palabras Clave -->
                        <div class="mt-6">
                            <h4 class="font-semibold text-gray-700 mb-3">🎯 Coincidencias Encontradas</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($resultado['analisis_sintomas']['palabras_clave_encontradas'] as $palabra)
                                    <div class="flex items-center">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        <span class="text-sm text-gray-700">{{ $palabra['palabra'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Columna 3: Acciones -->
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">🚀 Siguientes Pasos</h3>
                        
                        <div class="space-y-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 mb-2">
                                    {{ $resultado['puntaje_compatibilidad'] }}%
                                </div>
                                <p class="text-gray-600">Nivel de compatibilidad</p>
                            </div>

                            <div class="space-y-3">
                                <button class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    Agendar Primera Cita
                                </button>
                                
                                <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    Ver Perfil Completo
                                </button>
                                
                                <button class="w-full bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-semibold transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-sync mr-2"></i>
                                    Buscar Otro Profesional
                                </button>
                            </div>

                            <!-- Estadísticas Rápidas -->
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <h4 class="font-semibold text-blue-800 mb-2">📈 Estadísticas del Match</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Profesionales evaluados:</span>
                                        <span class="font-semibold">{{ $resultado['total_profesionales_evaluados'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Coincidencias encontradas:</span>
                                        <span class="font-semibold">{{ count($resultado['analisis_sintomas']['palabras_clave_encontradas']) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Nivel de urgencia:</span>
                                        <span class="font-semibold capitalize">{{ $resultado['analisis_sintomas']['nivel_urgencia'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparativa con Otros Profesionales -->
                <div class="bg-white rounded-2xl shadow-xl p-6 mt-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">📊 Comparativa con Otros Profesionales</h3>
                    
                    <div class="space-y-4">
                        @foreach(array_slice($resultado['todos_los_resultados'], 0, 5) as $index => $opcion)
                            <div class="flex items-center justify-between p-4 border rounded-lg {{ $index === 0 ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-{{ $index === 0 ? 'green' : 'blue' }}-100 rounded-full flex items-center justify-center mr-4">
                                        <span class="font-semibold text-{{ $index === 0 ? 'green' : 'blue' }}-600">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">
                                            {{ $opcion['profesional']->usuario->nombre }} {{ $opcion['profesional']->usuario->apellido }}
                                        </h4>
                                        <p class="text-sm text-gray-600 capitalize">{{ $opcion['profesional']->especialidad_principal }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-{{ $index === 0 ? 'green' : 'blue' }}-600">
                                        {{ $opcion['puntaje'] }}%
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $opcion['compatibilidad_detallada']['coincidencias'] }} coincidencias
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @else
            <!-- NO SE ENCONTRÓ MATCH -->
            <div class="max-w-2xl mx-auto">
                <div class="card-glass rounded-2xl p-8 text-center">
                    <div class="w-20 h-20 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-white text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-4">Buscando el Profesional Ideal</h2>
                    <p class="text-white opacity-90 mb-6">
                        Estamos analizando tu caso. Actualmente no tenemos un match perfecto, 
                        pero estamos trabajando para encontrar al profesional más adecuado para ti.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <h4 class="font-semibold text-white mb-2">📋 Análisis Realizado</h4>
                            <div class="text-white text-sm space-y-1">
                                <div>Síntomas detectados: {{ count($resultado['analisis_sintomas']['sintomas_detectados']) }}</div>
                                <div>Palabras clave encontradas: {{ $resultado['analisis_sintomas']['total_palabras_clave'] }}</div>
                                <div>Nivel de urgencia: <span class="capitalize">{{ $resultado['analisis_sintomas']['nivel_urgencia'] }}</span></div>
                            </div>
                        </div>

                        <div class="flex gap-4 justify-center">
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                                <i class="fas fa-redo mr-2"></i>Reintentar Búsqueda
                            </button>
                            <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold">
                                <i class="fas fa-headset mr-2"></i>Contactar Soporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Animación de las barras de progreso
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        });
    </script>
</body>
</html>