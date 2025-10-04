<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Manuscritos - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #E6F3FF 0%, #B0E2FF 100%);
        }
        .emotion-chip {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .iea-critical { background: #FECACA; color: #DC2626; }
        .iea-low { background: #FEF3C7; color: #D97706; }
        .iea-neutral { background: #D1FAE5; color: #059669; }
        .iea-high { background: #DBEAFE; color: #2563EB; }
        .iea-very-high { background: #E0E7FF; color: #7C3AED; }
    </style>
</head>
<body class="gradient-bg min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">📚 Mis Manuscritos</h1>
                    <p class="text-gray-600 mt-2">Historial de tus escritos y análisis emocionales</p>
                </div>
                <a href="{{ route('manuscritos.create') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-full font-semibold transition duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Nuevo Manuscrito
                </a>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-500">{{ $manuscritos->count() }}</div>
                <div class="text-gray-600">Total Manuscritos</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                @php
                    $ultimoIEA = $manuscritos->first()?->indiceEstadoAnimico?->valor_numerico ?? '--';
                @endphp
                <div class="text-3xl font-bold text-green-500">{{ $ultimoIEA }}</div>
                <div class="text-gray-600">Último IEA</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                @php
                    $procesados = $manuscritos->where('estado_procesamiento', 'procesado')->count();
                @endphp
                <div class="text-3xl font-bold text-purple-500">{{ $procesados }}</div>
                <div class="text-gray-600">Procesados</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center">
                @php
                    $promedioIEA = $manuscritos->avg('indiceEstadoAnimico.valor_numerico') ?? 0;
                @endphp
                <div class="text-3xl font-bold text-orange-500">{{ round($promedioIEA, 1) }}</div>
                <div class="text-gray-600">IEA Promedio</div>
            </div>
        </div>

        <!-- Lista de Manuscritos -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @if($manuscritos->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay manuscritos aún</h3>
                    <p class="text-gray-500 mb-6">Comienza subiendo tu primer manuscrito para analizar tu estado emocional</p>
                    <a href="{{ route('manuscritos.create') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-full font-semibold transition duration-300">
                        <i class="fas fa-plus mr-2"></i>Crear Primer Manuscrito
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manuscrito</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IEA</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emociones</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($manuscritos as $manuscrito)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-alt text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                Manuscrito #{{ $manuscrito->id_manuscrito }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Confianza OCR: {{ $manuscrito->confianza_ocr ? round($manuscrito->confianza_ocr, 1) . '%' : '--' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($manuscrito->indiceEstadoAnimico)
                                        @php
                                            $iea = $manuscrito->indiceEstadoAnimico;
                                            $claseIEA = match($iea->categoria_emotional) {
                                                'muy_bajo' => 'iea-critical',
                                                'bajo' => 'iea-low',
                                                'neutral' => 'iea-neutral',
                                                'alto' => 'iea-high',
                                                'muy_alto' => 'iea-very-high',
                                                default => 'iea-neutral'
                                            };
                                        @endphp
                                        <div class="flex items-center">
                                            <span class="text-lg font-bold {{ $claseIEA }} emotion-chip">
                                                {{ $iea->valor_numerico }}
                                            </span>
                                            <span class="ml-2 text-sm text-gray-500 capitalize">
                                                {{ str_replace('_', ' ', $iea->categoria_emotional) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($manuscrito->indiceEstadoAnimico && $manuscrito->indiceEstadoAnimico->emociones_detectadas)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($manuscrito->indiceEstadoAnimico->emociones_detectadas, 0, 3) as $emocion => $intensidad)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                    {{ $emocion }} ({{ round($intensidad * 100) }}%)
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $manuscrito->fecha_captura->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('manuscritos.show', $manuscrito) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition duration-150">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                        <form action="{{ route('manuscritos.destroy', $manuscrito) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 transition duration-150"
                                                    onclick="return confirm('¿Estás seguro de eliminar este manuscrito?')">
                                                <i class="fas fa-trash mr-1"></i>Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Botón flotante para nuevo manuscrito -->
        <a href="{{ route('manuscritos.create') }}" 
           class="fixed bottom-8 right-8 bg-blue-500 hover:bg-blue-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg transition duration-300 transform hover:scale-110">
            <i class="fas fa-plus text-xl"></i>
        </a>
    </div>
</body>
</html>