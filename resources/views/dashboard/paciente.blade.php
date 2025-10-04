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
        .iea-critical { background: linear-gradient(135deg, #FECACA 0%, #FCA5A5 100%); }
        .iea-low { background: linear-gradient(135deg, #FEF3C7 0%, #FCD34D 100%); }
        .iea-neutral { background: linear-gradient(135deg, #D1FAE5 0%, #34D399 100%); }
        .iea-high { background: linear-gradient(135deg, #DBEAFE 0%, #60A5FA 100%); }
        .iea-very-high { background: linear-gradient(135deg, #E0E7FF 0%, #8B5CF6 100%); }
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
                    
                    <button class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition duration-200">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Ver Progreso</h3>
                            <p class="text-sm text-gray-600">Analiza tu evolución emocional</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Manuscritos Recientes -->
            <div class="bg-white rounded-2xl card-shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Manuscritos Recientes</h2>
                
                @if($manuscritosRecientes->isEmpty())
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
                        @foreach($manuscritosRecientes as $manuscrito)
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
                                @if($manuscrito->indiceEstadoAnimico)
                                    @php
                                        $iea = $manuscrito->indiceEstadoAnimico;
                                        $claseIEA = 'iea-' . str_replace('_', '-', $iea->categoria_emotional);
                                    @endphp
                                    <div class="{{ $claseIEA }} text-white px-3 py-1 rounded-full text-sm font-semibold">
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
                        <a href="{{ route('manuscritos.index') }}" 
                           class="text-blue-class="text-blue-500 hover:text-blue-700 font-semibold transition duration-200">
                            Ver todos los manuscritos →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Estado Emocional Actual -->
        @if($ieaReciente)
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
                        @if($ieaReciente->emociones_detectadas)
                            @foreach(array_slice($ieaReciente->emociones_detectadas, 0, 4) as $emocion => $intensidad)
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ $emocion }}</span>
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
                {{ $ieaReciente ? 
                   ($ieaReciente->valor_numerico < 40 ? 
                    'Te recomendamos practicar 5 minutos de respiración consciente hoy. Pequeños momentos de mindfulness pueden hacer una gran diferencia.' :
                    ($ieaReciente->valor_numerico < 70 ?
                     'Mantén tu rutina de autocuidado. Considera escribir tres cosas por las que estés agradecido.' :
                     '¡Excelente estado! Aprovecha esta energía para conectar con seres queridos o iniciar un proyecto personal.'
                    )
                   ) : 
                   'Comienza registrando tu primer manuscrito para recibir recomendaciones personalizadas.'
                }}
            </p>
            <div class="flex space-x-4">
                <button class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50 transition duration-200">
                    <i class="fas fa-play-circle mr-2"></i>Ejercicio Guiado
                </button>
                <button class="bg-blue-400 bg-opacity-20 text-white px-4 py-2 rounded-lg font-semibold hover:bg-opacity-30 transition duration-200">
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
</html>