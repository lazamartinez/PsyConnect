<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Palabras Clave - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #E6F3FF 0%, #B0E2FF 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="bg-white rounded-2xl card-shadow p-8 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">🔤 Gestión de Palabras Clave</h1>
                    <p class="text-gray-600 mt-2">Configura el sistema de detección de síntomas y asignación automática</p>
                </div>
                <button onclick="abrirModalCrear()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-full font-semibold transition duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>Nueva Palabra Clave
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-500">{{ $palabrasClave->flatten()->count() }}</div>
                <div class="text-gray-600">Total Palabras</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-500">{{ $palabrasClave->flatten()->where('estado', true)->count() }}</div>
                <div class="text-gray-600">Activas</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-red-500">{{ $palabrasClave->flatten()->where('nivel_alerta', 'critico')->count() }}</div>
                <div class="text-gray-600">Críticas</div>
            </div>
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <div class="text-3xl font-bold text-purple-500">{{ count($categorias) }}</div>
                <div class="text-gray-600">Categorías</div>
            </div>
        </div>

        <!-- Lista de Palabras Clave por Categoría -->
        <div class="space-y-6">
            @foreach($palabrasClave as $categoria => $palabras)
            <div class="bg-white rounded-2xl card-shadow overflow-hidden fade-in">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-folder mr-3"></i>
                        {{ $categorias[$categoria] ?? $categoria }}
                        <span class="ml-2 text-blue-200 text-sm font-normal">
                            ({{ $palabras->count() }} palabras)
                        </span>
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($palabras as $palabra)
                        <div class="border rounded-lg p-4 {{ $palabra->color_alerta }} transition duration-300 hover:shadow-md">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-lg capitalize">{{ $palabra->palabra }}</h3>
                                <div class="flex space-x-1">
                                    <button onclick="editarPalabra({{ $palabra->id_palabra_clave }})" 
                                            class="text-blue-600 hover:text-blue-800 transition duration-200">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="eliminarPalabra({{ $palabra->id_palabra_clave }})" 
                                            class="text-red-600 hover:text-red-800 transition duration-200">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button onclick="cambiarEstado({{ $palabra->id_palabra_clave }})" 
                                            class="{{ $palabra->estado ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600' }} transition duration-200">
                                        <i class="fas {{ $palabra->estado ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nivel Alerta:</span>
                                    <span class="font-semibold capitalize">{{ $nivelesAlerta[$palabra->nivel_alerta] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Peso Urgencia:</span>
                                    <span class="font-semibold">{{ $palabra->peso_urgencia }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Especialidad:</span>
                                    <span class="font-semibold capitalize">{{ $palabra->especialidad_recomendada }}</span>
                                </div>
                                @if($palabra->descripcion)
                                <div>
                                    <span class="text-gray-600">Descripción:</span>
                                    <p class="text-gray-700 mt-1">{{ $palabra->descripcion }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Modal para Crear/Editar -->
    <div id="modalPalabra" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitulo">Nueva Palabra Clave</h3>
                
                <form id="formPalabra">
                    @csrf
                    <input type="hidden" id="palabraId" name="id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Palabra *</label>
                            <input type="text" id="palabra" name="palabra" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   required>
                            <div id="error-palabra" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                            <select id="categoria" name="categoria" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    required>
                                <option value="">Seleccionar categoría...</option>
                                @foreach($categorias as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de Alerta *</label>
                            <select id="nivel_alerta" name="nivel_alerta" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    required>
                                <option value="">Seleccionar nivel...</option>
                                @foreach($nivelesAlerta as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Peso Urgencia *</label>
                            <input type="number" id="peso_urgencia" name="peso_urgencia" 
                                   step="0.1" min="0.1" max="1.0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad *</label>
                            <select id="especialidad_recomendada" name="especialidad_recomendada" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    required>
                                <option value="">Seleccionar...</option>
                                <option value="psicologo">Psicólogo</option>
                                <option value="psiquiatra">Psiquiatra</option>
                                <option value="nutricionista">Nutricionista</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                  placeholder="Descripción opcional de la palabra clave..."></textarea>
                    </div>

                    <div class="mb-4" id="campoEstado" style="display: none;">
                        <label class="flex items-center">
                            <input type="checkbox" id="estado" name="estado" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Palabra activa en el sistema</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="cerrarModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-save mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalCrear() {
            document.getElementById('modalTitulo').textContent = 'Nueva Palabra Clave';
            document.getElementById('formPalabra').reset();
            document.getElementById('palabraId').value = '';
            document.getElementById('campoEstado').style.display = 'none';
            document.getElementById('modalPalabra').classList.remove('hidden');
        }

        function editarPalabra(id) {
            fetch(`/admin/palabras-clave/${id}`)
                .then(response => response.json())
                .then(palabra => {
                    document.getElementById('modalTitulo').textContent = 'Editar Palabra Clave';
                    document.getElementById('palabraId').value = palabra.id_palabra_clave;
                    document.getElementById('palabra').value = palabra.palabra;
                    document.getElementById('categoria').value = palabra.categoria;
                    document.getElementById('nivel_alerta').value = palabra.nivel_alerta;
                    document.getElementById('peso_urgencia').value = palabra.peso_urgencia;
                    document.getElementById('especialidad_recomendada').value = palabra.especialidad_recomendada;
                    document.getElementById('descripcion').value = palabra.descripcion || '';
                    document.getElementById('estado').checked = palabra.estado;
                    document.getElementById('campoEstado').style.display = 'block';
                    
                    document.getElementById('modalPalabra').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar la palabra clave');
                });
        }

        function eliminarPalabra(id) {
            if (confirm('¿Estás seguro de eliminar esta palabra clave?')) {
                fetch(`/admin/palabras-clave/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la palabra clave');
                });
            }
        }

        function cambiarEstado(id) {
            fetch(`/admin/palabras-clave/${id}/estado`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cambiar estado');
            });
        }

        function cerrarModal() {
            document.getElementById('modalPalabra').classList.add('hidden');
        }

        // Manejo del formulario
        document.getElementById('formPalabra').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('palabraId').value;
            const url = id ? `/admin/palabras-clave/${id}` : '/admin/palabras-clave';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cerrarModal();
                    location.reload();
                } else {
                    // Mostrar errores de validación
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorElement = document.getElementById(`error-${field}`);
                            if (errorElement) {
                                errorElement.textContent = data.errors[field][0];
                                errorElement.classList.remove('hidden');
                            }
                        });
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar la palabra clave');
            });
        });

        // Limpiar errores al escribir
        document.querySelectorAll('#formPalabra input, #formPalabra select').forEach(element => {
            element.addEventListener('input', function() {
                const errorElement = document.getElementById(`error-${this.name}`);
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>