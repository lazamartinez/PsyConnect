@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">🎯 Configurar Síntomas y Períodos de Atención</h1>
                    <p class="text-gray-600 mt-2">Selecciona los síntomas que atiendes y configura tus períodos de
                        disponibilidad</p>
                </div>
                <div class="flex space-x-3">
                    <button id="btnGuardarConfiguracion"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                    </button>
                    <a href="{{ route('dashboard') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>

            <!-- Información de la Especialidad -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-user-md text-blue-500 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 text-lg">Especialidad: {{ $especialidad->nombre }}</h3>
                        <p class="text-blue-700">{{ $especialidad->descripcion }}</p>
                    </div>
                </div>
            </div>

            <!-- Lista de Síntomas Disponibles -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Síntomas Disponibles para tu Especialidad</h2>

                @if ($sintomasEspecialidad->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-3"></i>
                        <p class="text-yellow-800">No hay síntomas configurados para tu especialidad.</p>
                        <p class="text-yellow-700 text-sm mt-2">Contacta al administrador del sistema.</p>
                    </div>
                @else
                    <div class="space-y-4" id="listaSintomasConfiguracion">
                        @foreach ($sintomasEspecialidad as $sintoma)
                            <div class="border border-gray-200 rounded-lg p-4 sintoma-item"
                                data-sintoma-id="{{ $sintoma->id_sintoma }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="sintoma_{{ $sintoma->id_sintoma }}"
                                                class="sintoma-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3"
                                                {{ isset($configuracionesActuales[$sintoma->id_sintoma]) ? 'checked' : '' }}>
                                            <label for="sintoma_{{ $sintoma->id_sintoma }}"
                                                class="font-semibold text-gray-800 text-lg cursor-pointer">
                                                {{ $sintoma->sintoma }}
                                            </label>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-2">{{ $sintoma->descripcion }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                {{ $sintoma->palabraClave->palabra }}
                                            </span>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                                Gravedad: {{ ucfirst($sintoma->nivel_gravedad) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración del Síntoma (solo visible si está seleccionado) -->
                                <div class="configuracion-sintoma mt-3 p-3 bg-gray-50 rounded-lg hidden">
                                    <h4 class="font-medium text-gray-700 mb-3">📅 Configurar Período de Atención</h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <!-- Período Activo -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                                            <select name="periodo_activo"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                                <option value="diario">Diario</option>
                                                <option value="semanal">Semanal</option>
                                                <option value="quincenal">Quincenal</option>
                                                <option value="mensual">Mensual</option>
                                                <option value="personalizado">Personalizado</option>
                                            </select>
                                        </div>

                                        <!-- Fechas Personalizadas -->
                                        <div class="fechas-personalizadas hidden">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                                            <input type="datetime-local" name="fecha_inicio"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        </div>

                                        <div class="fechas-personalizadas hidden">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                                            <input type="datetime-local" name="fecha_fin"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        </div>

                                        <!-- Máximo de Pacientes -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Máx.
                                                Pacientes</label>
                                            <input type="number" name="max_pacientes" value="10" min="1"
                                                max="50"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        </div>
                                    </div>

                                    <!-- Prioridad - NOMBRE ÚNICO POR SÍNTOMA -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de
                                            Prioridad</label>
                                        <div class="flex space-x-4">
                                            <label class="flex items-center">
                                                <input type="radio" name="prioridad_{{ $sintoma->id_sintoma }}"
                                                    value="baja" class="mr-2">
                                                <span class="text-sm text-gray-700">Baja</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="prioridad_{{ $sintoma->id_sintoma }}"
                                                    value="media" class="mr-2" checked>
                                                <span class="text-sm text-gray-700">Media</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="prioridad_{{ $sintoma->id_sintoma }}"
                                                    value="alta" class="mr-2">
                                                <span class="text-sm text-gray-700">Alta</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="prioridad_{{ $sintoma->id_sintoma }}"
                                                    value="urgente" class="mr-2">
                                                <span class="text-sm text-gray-700">Urgente</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Resumen de Configuración -->
                                    <div class="bg-white border border-green-200 rounded p-3">
                                        <h5 class="font-medium text-green-800 mb-2">Resumen de Configuración:</h5>
                                        <div class="text-sm text-green-700 espacio-resumen">
                                            Atenderás este síntoma con prioridad <span class="font-semibold">media</span>
                                            para máximo <span class="font-semibold">10</span> pacientes.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <div>
                    <button id="btnSeleccionarTodos"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-check-square mr-2"></i>Seleccionar Todos
                    </button>
                    <button id="btnDeseleccionarTodos"
                        class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-times-circle mr-2"></i>Limpiar Selección
                    </button>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('dashboard') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                        Cancelar
                    </a>
                    <button id="btnGuardarFinal"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i>Guardar Configuración
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir el componente de notificación -->
    @include('components.notificacion-match')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando configuración de síntomas...');

            // Inicializar configuraciones existentes
            inicializarConfiguracionesExistentes();

            // Manejar checkboxes de síntomas
            document.querySelectorAll('.sintoma-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const sintomaItem = this.closest('.sintoma-item');
                    const configSection = sintomaItem.querySelector('.configuracion-sintoma');

                    if (this.checked) {
                        configSection.classList.remove('hidden');
                        actualizarResumenConfiguracion(sintomaItem);
                    } else {
                        configSection.classList.add('hidden');
                    }
                });
            });

            // Manejar cambio de período
            document.querySelectorAll('select[name="periodo_activo"]').forEach(select => {
                select.addEventListener('change', function() {
                    const sintomaItem = this.closest('.sintoma-item');
                    const fechasPersonalizadas = sintomaItem.querySelectorAll('.fechas-personalizadas');
                    
                    if (this.value === 'personalizado') {
                        fechasPersonalizadas.forEach(el => el.classList.remove('hidden'));
                    } else {
                        fechasPersonalizadas.forEach(el => el.classList.add('hidden'));
                    }
                    actualizarResumenConfiguracion(sintomaItem);
                });
            });

            // Manejar cambios en radio buttons de prioridad
            document.querySelectorAll('input[type="radio"][name^="prioridad_"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const sintomaItem = this.closest('.sintoma-item');
                    actualizarResumenConfiguracion(sintomaItem);
                });
            });

            // Manejar cambios en inputs numéricos
            document.querySelectorAll('input[name="max_pacientes"]').forEach(input => {
                input.addEventListener('change', function() {
                    const sintomaItem = this.closest('.sintoma-item');
                    actualizarResumenConfiguracion(sintomaItem);
                });
            });

            // Botones de selección masiva
            document.getElementById('btnSeleccionarTodos').addEventListener('click', function() {
                document.querySelectorAll('.sintoma-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                    const event = new Event('change');
                    checkbox.dispatchEvent(event);
                });
            });

            document.getElementById('btnDeseleccionarTodos').addEventListener('click', function() {
                document.querySelectorAll('.sintoma-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                    const event = new Event('change');
                    checkbox.dispatchEvent(event);
                });
            });

            // Configurar botones de guardar
            document.getElementById('btnGuardarFinal').addEventListener('click', guardarConfiguracionSintomas);
            document.getElementById('btnGuardarConfiguracion').addEventListener('click', guardarConfiguracionSintomas);
        });

        function inicializarConfiguracionesExistentes() {
            // Activar las configuraciones que ya estaban seleccionadas
            document.querySelectorAll('.sintoma-checkbox:checked').forEach(checkbox => {
                const event = new Event('change');
                checkbox.dispatchEvent(event);
            });
        }

        function actualizarResumenConfiguracion(sintomaItem) {
            const sintomaId = sintomaItem.dataset.sintomaId;
            const periodoSelect = sintomaItem.querySelector('select[name="periodo_activo"]');
            const maxPacientesInput = sintomaItem.querySelector('input[name="max_pacientes"]');
            
            // Buscar prioridad con nombre único
            const prioridadRadio = sintomaItem.querySelector(`input[name="prioridad_${sintomaId}"]:checked`);
            
            const periodo = periodoSelect ? periodoSelect.value : 'semanal';
            const maxPacientes = maxPacientesInput ? (maxPacientesInput.value || 10) : 10;
            const prioridad = prioridadRadio ? prioridadRadio.value : 'media';

            const resumen = sintomaItem.querySelector('.espacio-resumen');
            if (resumen) {
                let textoPeriodo = periodo === 'personalizado' ? 'período personalizado' : `período ${periodo}`;
                resumen.innerHTML = 
                    `Atenderás este síntoma con prioridad <span class="font-semibold">${prioridad}</span> para máximo <span class="font-semibold">${maxPacientes}</span> pacientes en ${textoPeriodo}.`;
            }
        }

        function guardarConfiguracionSintomas() {
            const sintomasConfig = [];
            let tieneConfiguraciones = false;

            console.log('Iniciando guardado de configuración...');

            // Recopilar todas las configuraciones
            document.querySelectorAll('.sintoma-item').forEach(item => {
                const checkbox = item.querySelector('.sintoma-checkbox');
                
                if (checkbox && checkbox.checked) {
                    tieneConfiguraciones = true;
                    
                    const sintomaId = item.dataset.sintomaId;
                    const periodoSelect = item.querySelector('select[name="periodo_activo"]');
                    const fechaInicioInput = item.querySelector('input[name="fecha_inicio"]');
                    const fechaFinInput = item.querySelector('input[name="fecha_fin"]');
                    const maxPacientesInput = item.querySelector('input[name="max_pacientes"]');
                    
                    // Buscar el radio button seleccionado con el nombre único
                    const prioridadRadio = item.querySelector(`input[name="prioridad_${sintomaId}"]:checked`);
                    
                    // Validar que los elementos existan
                    if (!periodoSelect) {
                        console.error('No se encontró select de periodo para síntoma:', sintomaId);
                        return;
                    }

                    const config = {
                        sintoma_id: sintomaId,
                        periodo_activo: periodoSelect.value,
                        fecha_inicio: fechaInicioInput?.value || null,
                        fecha_fin: fechaFinInput?.value || null,
                        max_pacientes: maxPacientesInput ? (parseInt(maxPacientesInput.value) || 10) : 10,
                        prioridad: prioridadRadio ? prioridadRadio.value : 'media'
                    };
                    
                    console.log('Configuración para síntoma', sintomaId, ':', config);
                    sintomasConfig.push(config);
                }
            });

            if (!tieneConfiguraciones) {
                alert('Por favor selecciona al menos un síntoma para configurar.');
                return;
            }

            if (sintomasConfig.length === 0) {
                alert('Error: No se pudieron recopilar las configuraciones.');
                return;
            }

            console.log('Configuraciones a enviar:', sintomasConfig);

            const btnGuardar = document.getElementById('btnGuardarConfiguracion');
            const originalText = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            btnGuardar.disabled = true;

            // Enviar como JSON
            fetch('{{ route("profesional.configuracion-sintomas.actualizar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    sintomas: sintomasConfig 
                })
            })
            .then(response => {
                console.log('Respuesta status:', response.status);
                
                if (response.status === 422) {
                    return response.json().then(data => {
                        throw new Error('Validation: ' + JSON.stringify(data.errors));
                    });
                }
                
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Respuesta recibida:', data);
                
                if (data.success) {
                    alert('✅ ' + data.message);
                    window.location.href = '{{ route("dashboard") }}';
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                if (error.message.includes('Validation:')) {
                    const errors = JSON.parse(error.message.replace('Validation: ', ''));
                    alert('❌ Error de validación: ' + Object.values(errors).flat().join(', '));
                } else {
                    alert('❌ Error al guardar la configuración: ' + error.message);
                }
            })
            .finally(() => {
                btnGuardar.innerHTML = originalText;
                btnGuardar.disabled = false;
            });
        }
    </script>
@endsection