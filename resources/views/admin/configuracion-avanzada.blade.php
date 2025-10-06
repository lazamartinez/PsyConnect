<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Avanzada - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <i class="fas fa-cogs text-2xl text-blue-500 mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-800">Configuración Avanzada del Sistema</h1>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Selector de Clínica -->
            <div class="bg-white rounded-lg shadow mb-6 p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Clínica</label>
                <select id="selectClinica" class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded-lg">
                    @foreach($clinicas as $clinica)
                    <option value="{{ $clinica->id_clinica }}" 
                            {{ $clinicaSeleccionada == $clinica->id_clinica ? 'selected' : '' }}>
                        {{ $clinica->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Pestañas de Configuración -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button id="tab-pesos" class="tab-config py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                            <i class="fas fa-sliders-h mr-2"></i>Pesos del Matching
                        </button>
                        <button id="tab-reglas" class="tab-config py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-stethoscope mr-2"></i>Reglas de Especialidad
                        </button>
                        <button id="tab-umbrales" class="tab-config py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-chart-line mr-2"></i>Umbrales del Sistema
                        </button>
                        <button id="tab-triaje" class="tab-config py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-clipboard-list mr-2"></i>Reglas de Triaje
                        </button>
                    </nav>
                </div>

                <!-- Contenido de las Pestañas -->
                <div class="p-6">
                    <!-- Pesos del Matching -->
                    <div id="contenido-pesos" class="tab-content">
                        <h3 class="text-lg font-semibold mb-4">Configurar Pesos del Algoritmo de Matching</h3>
                        <div class="space-y-4" id="form-pesos">
                            @foreach($configuraciones['pesos_matching'] as $key => $value)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}
                                    </label>
                                    <p class="text-sm text-gray-500">Peso actual: {{ number_format($value * 100, 1) }}%</p>
                                </div>
                                <div class="w-64">
                                    <input type="range" name="pesos[{{ $key }}]" 
                                           value="{{ $value * 100 }}" min="0" max="100" step="1"
                                           class="w-full peso-slider"
                                           oninput="document.getElementById('valor-{{ $key }}').textContent = this.value + '%'">
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>0%</span>
                                        <span id="valor-{{ $key }}">{{ number_format($value * 100, 0) }}%</span>
                                        <span>100%</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            <button onclick="guardarConfiguracion('pesos_matching')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Guardar Pesos
                            </button>
                        </div>
                    </div>

                    <!-- Reglas de Especialidad -->
                    <div id="contenido-reglas" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Configurar Reglas de Especialidad</h3>
                        <div class="space-y-6" id="form-reglas">
                            @foreach($configuraciones['reglas_especialidad'] as $especialidad => $config)
                            <div class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold text-lg capitalize">{{ $config['nombre'] }}</h4>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="reglas[{{ $especialidad }}][activo]" 
                                               {{ $config['activo'] ? 'checked' : '' }} 
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Activo</span>
                                    </label>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                        <input type="text" name="reglas[{{ $especialidad }}][descripcion]"
                                               value="{{ $config['descripcion'] }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nivel Urgencia Mínimo</label>
                                        <select name="reglas[{{ $especialidad }}][nivel_urgencia_minimo]" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                            <option value="bajo" {{ ($config['nivel_urgencia_minimo'] ?? 'bajo') == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                            <option value="medio" {{ ($config['nivel_urgencia_minimo'] ?? 'bajo') == 'medio' ? 'selected' : '' }}>Medio</option>
                                            <option value="alto" {{ ($config['nivel_urgencia_minimo'] ?? 'bajo') == 'alto' ? 'selected' : '' }}>Alto</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Síntomas Automáticos -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Síntomas Automáticos</label>
                                    <div class="space-y-2" id="sintomas-automaticos-{{ $especialidad }}">
                                        @foreach($config['sintomas_automaticos'] as $sintoma)
                                        <div class="flex items-center space-x-2">
                                            <input type="text" 
                                                   name="reglas[{{ $especialidad }}][sintomas_automaticos][]"
                                                   value="{{ $sintoma }}"
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                            <button type="button" onclick="eliminarSintoma(this)" 
                                                    class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="agregarSintoma('{{ $especialidad }}')" 
                                            class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Agregar Síntoma
                                    </button>
                                </div>

                                <!-- Palabras Clave de la Especialidad -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Palabras Clave</label>
                                    <div class="space-y-2" id="palabras-clave-{{ $especialidad }}">
                                        @foreach($config['palabras_clave'] as $palabra => $detalles)
                                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                                            <input type="text" 
                                                   name="reglas[{{ $especialidad }}][palabras_clave][{{ $palabra }}][palabra]"
                                                   value="{{ $palabra }}" 
                                                   class="flex-1 px-2 py-1 border border-gray-300 rounded">
                                            <input type="number" 
                                                   name="reglas[{{ $especialidad }}][palabras_clave][{{ $palabra }}][peso]"
                                                   value="{{ $detalles['peso'] }}" step="0.1" min="0" max="1"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded"
                                                   placeholder="Peso">
                                            <select name="reglas[{{ $especialidad }}][palabras_clave][{{ $palabra }}][nivel_alerta]"
                                                    class="w-24 px-2 py-1 border border-gray-300 rounded">
                                                <option value="bajo" {{ $detalles['nivel_alerta'] == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                                <option value="medio" {{ $detalles['nivel_alerta'] == 'medio' ? 'selected' : '' }}>Medio</option>
                                                <option value="alto" {{ $detalles['nivel_alerta'] == 'alto' ? 'selected' : '' }}>Alto</option>
                                                <option value="critico" {{ $detalles['nivel_alerta'] == 'critico' ? 'selected' : '' }}>Crítico</option>
                                            </select>
                                            <button type="button" onclick="eliminarPalabraClave(this)" 
                                                    class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" onclick="agregarPalabraClave('{{ $especialidad }}')" 
                                            class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Agregar Palabra Clave
                                    </button>
                                </div>

                                <!-- Configuraciones Adicionales -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="reglas[{{ $especialidad }}][puede_recetar_medicamentos]" 
                                                   {{ $config['puede_recetar_medicamentos'] ?? false ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Puede recetar medicamentos</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nivel Urgencia Máximo</label>
                                        <select name="reglas[{{ $especialidad }}][nivel_urgencia_maximo]" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                            <option value="bajo" {{ ($config['nivel_urgencia_maximo'] ?? 'alto') == 'bajo' ? 'selected' : '' }}>Bajo</option>
                                            <option value="medio" {{ ($config['nivel_urgencia_maximo'] ?? 'alto') == 'medio' ? 'selected' : '' }}>Medio</option>
                                            <option value="alto" {{ ($config['nivel_urgencia_maximo'] ?? 'alto') == 'alto' ? 'selected' : '' }}>Alto</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            <button onclick="guardarConfiguracion('reglas_especialidad')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Guardar Reglas
                            </button>
                        </div>
                    </div>

                    <!-- Umbrales del Sistema -->
                    <div id="contenido-umbrales" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Configurar Umbrales del Sistema</h3>
                        <div class="space-y-4" id="form-umbrales">
                            @foreach($configuraciones['umbrales'] as $key => $value)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}
                                    </label>
                                    <p class="text-sm text-gray-500">Valor actual: {{ $value }}</p>
                                </div>
                                <div class="w-64">
                                    <input type="number" 
                                           name="umbrales[{{ $key }}]"
                                           value="{{ $value }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           min="0" 
                                           max="{{ $key == 'compatibilidad_minima' || $key == 'confianza_minima_asignacion' ? 100 : 1000 }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            <button onclick="guardarConfiguracion('umbrales')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Guardar Umbrales
                            </button>
                        </div>
                    </div>

                    <!-- Reglas de Triaje -->
                    <div id="contenido-triaje" class="tab-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Configurar Reglas de Triaje</h3>
                        <div class="space-y-4" id="form-triaje">
                            @foreach($configuraciones['reglas_triaje'] as $key => $value)
                            @if(!is_array($value))
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $key) }}
                                    </label>
                                    <p class="text-sm text-gray-500">Valor actual: {{ $value }}</p>
                                </div>
                                <div class="w-64">
                                    @if($key == 'revision_manual_activada')
                                    <input type="checkbox" 
                                           name="reglas_triaje[{{ $key }}]"
                                           {{ $value ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    @else
                                    <input type="number" 
                                           name="reglas_triaje[{{ $key }}]"
                                           value="{{ $value }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           min="1">
                                    @endif
                                </div>
                            </div>
                            @endif
                            @endforeach

                            <!-- Niveles de Urgencia -->
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-800 mb-4">Niveles de Urgencia</h4>
                                <div class="space-y-4">
                                    @foreach($configuraciones['reglas_triaje']['niveles_urgencia'] as $nivel => $config)
                                    <div class="border rounded-lg p-4">
                                        <h5 class="font-medium text-gray-700 capitalize mb-3">{{ $nivel }}</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Mínimo Palabras</label>
                                                <input type="number" 
                                                       name="reglas_triaje[niveles_urgencia][{{ $nivel }}][min_palabras]"
                                                       value="{{ $config['min_palabras'] }}"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                                       min="0">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                                                <select name="reglas_triaje[niveles_urgencia][{{ $nivel }}][accion]"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                    <option value="contacto_inmediato" {{ $config['accion'] == 'contacto_inmediato' ? 'selected' : '' }}>Contacto Inmediato</option>
                                                    <option value="asignacion_24h" {{ $config['accion'] == 'asignacion_24h' ? 'selected' : '' }}>Asignación 24h</option>
                                                    <option value="asignacion_72h" {{ $config['accion'] == 'asignacion_72h' ? 'selected' : '' }}>Asignación 72h</option>
                                                    <option value="asignacion_normal" {{ $config['accion'] == 'asignacion_normal' ? 'selected' : '' }}>Asignación Normal</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button onclick="guardarConfiguracion('reglas_triaje')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-save mr-2"></i>Guardar Reglas de Triaje
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navegación entre pestañas
        document.querySelectorAll('.tab-config').forEach(tab => {
            tab.addEventListener('click', function() {
                // Ocultar todos los contenidos
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Remover clase activa de todas las pestañas
                document.querySelectorAll('.tab-config').forEach(t => {
                    t.classList.remove('border-blue-500', 'text-blue-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                
                // Activar pestaña actual
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-blue-500', 'text-blue-600');
                
                // Mostrar contenido correspondiente
                const tabId = this.id.replace('tab-', 'contenido-');
                document.getElementById(tabId).classList.remove('hidden');
            });
        });

        // Cambio de clínica
        document.getElementById('selectClinica').addEventListener('change', function() {
            window.location.href = '{{ route("admin.configuracion-avanzada") }}?clinica_id=' + this.value;
        });

        // Guardar configuración
        async function guardarConfiguracion(tipo) {
            const clinicaId = document.getElementById('selectClinica').value;
            let configuracion = {};

            if (tipo === 'pesos_matching') {
                const pesos = {};
                document.querySelectorAll('#form-pesos input[type="range"]').forEach(slider => {
                    const key = slider.name.replace('pesos[', '').replace(']', '');
                    pesos[key] = parseFloat(slider.value) / 100;
                });
                configuracion = pesos;
            } else if (tipo === 'reglas_especialidad') {
                configuracion = obtenerDatosReglas();
            } else if (tipo === 'umbrales') {
                const umbrales = {};
                document.querySelectorAll('#form-umbrales input').forEach(input => {
                    const key = input.name.replace('umbrales[', '').replace(']', '');
                    umbrales[key] = input.type === 'number' ? parseInt(input.value) : input.value;
                });
                configuracion = umbrales;
            } else if (tipo === 'reglas_triaje') {
                configuracion = obtenerDatosTriaje();
            }

            try {
                const response = await fetch('{{ route("admin.configuracion.guardar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        clinica_id: clinicaId,
                        tipo_configuracion: tipo,
                        configuracion: configuracion
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ Configuración guardada exitosamente');
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error al guardar la configuración');
            }
        }

        function agregarPalabraClave(especialidad) {
            const container = document.getElementById(`palabras-clave-${especialidad}`);
            const newIndex = Date.now(); // Usar timestamp para índice único
            
            const newRow = document.createElement('div');
            newRow.className = 'flex items-center space-x-2 p-2 bg-gray-50 rounded';
            newRow.innerHTML = `
                <input type="text" 
                       name="reglas[${especialidad}][palabras_clave][nueva_${newIndex}][palabra]"
                       placeholder="Nueva palabra" 
                       class="flex-1 px-2 py-1 border border-gray-300 rounded">
                <input type="number" 
                       name="reglas[${especialidad}][palabras_clave][nueva_${newIndex}][peso]"
                       value="0.5" step="0.1" min="0" max="1"
                       class="w-20 px-2 py-1 border border-gray-300 rounded"
                       placeholder="Peso">
                <select name="reglas[${especialidad}][palabras_clave][nueva_${newIndex}][nivel_alerta]"
                        class="w-24 px-2 py-1 border border-gray-300 rounded">
                    <option value="bajo">Bajo</option>
                    <option value="medio">Medio</option>
                    <option value="alto">Alto</option>
                    <option value="critico">Crítico</option>
                </select>
                <button type="button" onclick="eliminarPalabraClave(this)" 
                        class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(newRow);
        }

        function agregarSintoma(especialidad) {
            const container = document.getElementById(`sintomas-automaticos-${especialidad}`);
            
            const newRow = document.createElement('div');
            newRow.className = 'flex items-center space-x-2';
            newRow.innerHTML = `
                <input type="text" 
                       name="reglas[${especialidad}][sintomas_automaticos][]"
                       placeholder="Nuevo síntoma"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                <button type="button" onclick="eliminarSintoma(this)" 
                        class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(newRow);
        }

        function eliminarPalabraClave(button) {
            button.closest('div').remove();
        }

        function eliminarSintoma(button) {
            button.closest('div').remove();
        }

        function obtenerDatosReglas() {
            const reglas = {};
            
            // Obtener todas las especialidades
            const especialidades = ['psicologo', 'psiquiatra', 'nutricionista'];
            
            especialidades.forEach(especialidad => {
                reglas[especialidad] = {
                    nombre: document.querySelector(`input[name="reglas[${especialidad}][descripcion]"]`)?.value || '',
                    descripcion: document.querySelector(`input[name="reglas[${especialidad}][descripcion]"]`)?.value || '',
                    activo: document.querySelector(`input[name="reglas[${especialidad}][activo]"]`)?.checked || false,
                    nivel_urgencia_minimo: document.querySelector(`select[name="reglas[${especialidad}][nivel_urgencia_minimo]"]`)?.value || 'bajo',
                    nivel_urgencia_maximo: document.querySelector(`select[name="reglas[${especialidad}][nivel_urgencia_maximo]"]`)?.value || 'alto',
                    puede_recetar_medicamentos: document.querySelector(`input[name="reglas[${especialidad}][puede_recetar_medicamentos]"]`)?.checked || false,
                    palabras_clave: {},
                    sintomas_automaticos: []
                };

                // Obtener palabras clave
                const palabraInputs = document.querySelectorAll(`input[name^="reglas[${especialidad}][palabras_clave]"]`);
                const palabrasProcesadas = new Set();
                
                palabraInputs.forEach(input => {
                    const name = input.name;
                    const matches = name.match(/reglas\[${especialidad}\]\[palabras_clave\]\[([^\]]+)\]\[(\w+)\]/);
                    
                    if (matches) {
                        const palabraKey = matches[1];
                        const campo = matches[2];
                        
                        if (!palabrasProcesadas.has(palabraKey)) {
                            palabrasProcesadas.add(palabraKey);
                            
                            const palabra = document.querySelector(`input[name="reglas[${especialidad}][palabras_clave][${palabraKey}][palabra]"]`)?.value;
                            const peso = parseFloat(document.querySelector(`input[name="reglas[${especialidad}][palabras_clave][${palabraKey}][peso]"]`)?.value) || 0.5;
                            const nivelAlerta = document.querySelector(`select[name="reglas[${especialidad}][palabras_clave][${palabraKey}][nivel_alerta]"]`)?.value || 'medio';
                            
                            if (palabra) {
                                reglas[especialidad].palabras_clave[palabra] = {
                                    peso: peso,
                                    nivel_alerta: nivelAlerta
                                };
                            }
                        }
                    }
                });

                // Obtener síntomas automáticos
                const sintomaInputs = document.querySelectorAll(`input[name="reglas[${especialidad}][sintomas_automaticos][]"]`);
                sintomaInputs.forEach(input => {
                    if (input.value.trim()) {
                        reglas[especialidad].sintomas_automaticos.push(input.value.trim());
                    }
                });

                // Configuraciones por defecto según especialidad
                if (especialidad === 'psiquiatra') {
                    reglas[especialidad].requiere_derivacion_psiquiatra = ['psicosis', 'ideacion_suicida', 'trastorno_bipolar'];
                } else if (especialidad === 'nutricionista') {
                    reglas[especialidad].derivar_a_psicologo = ['anorexia', 'bulimia'];
                }
            });

            return reglas;
        }

        function obtenerDatosTriaje() {
            const reglasTriaje = {};
            
            // Obtener campos simples
            const camposSimples = document.querySelectorAll('#form-triaje input:not([name*="niveles_urgencia"])');
            camposSimples.forEach(input => {
                const key = input.name.replace('reglas_triaje[', '').replace(']', '');
                if (input.type === 'checkbox') {
                    reglasTriaje[key] = input.checked;
                } else if (input.type === 'number') {
                    reglasTriaje[key] = parseInt(input.value);
                } else {
                    reglasTriaje[key] = input.value;
                }
            });

            // Obtener niveles de urgencia
            reglasTriaje.niveles_urgencia = {};
            const niveles = ['critico', 'alto', 'medio', 'bajo'];
            
            niveles.forEach(nivel => {
                const minPalabras = document.querySelector(`input[name="reglas_triaje[niveles_urgencia][${nivel}][min_palabras]"]`);
                const accion = document.querySelector(`select[name="reglas_triaje[niveles_urgencia][${nivel}][accion]"]`);
                
                if (minPalabras && accion) {
                    reglasTriaje.niveles_urgencia[nivel] = {
                        min_palabras: parseInt(minPalabras.value),
                        accion: accion.value
                    };
                }
            });

            return reglasTriaje;
        }
    </script>
</body>
</html>