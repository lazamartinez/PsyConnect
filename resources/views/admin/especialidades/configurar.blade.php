@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                Configurar Síntomas para: {{ $especialidad->nombre }}
            </h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Formulario para agregar síntomas -->
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Agregar Nuevo Síntoma</h3>
                    <form id="formAgregarSintoma">
                        @csrf
                        <input type="hidden" name="especialidad_id" value="{{ $especialidad->id_especialidad }}">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Palabra Clave Asociada</label>
                                <select name="palabra_clave_id"
                                    class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="">Seleccionar palabra clave</option>
                                    @foreach ($palabrasClave as $palabra)
                                        <option value="{{ $palabra->id_palabra_clave }}">{{ $palabra->palabra }}
                                            ({{ $palabra->categoria }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Síntoma/Problemática</label>
                                <input type="text" name="sintoma"
                                    class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2"
                                    placeholder="Ej: Ansiedad generalizada">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nivel de Gravedad</label>
                                <select name="nivel_gravedad"
                                    class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2">
                                    <option value="leve">Leve</option>
                                    <option value="moderado">Moderado</option>
                                    <option value="grave">Grave</option>
                                    <option value="critico">Crítico</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea name="descripcion" rows="3" class="w-full mt-1 border border-gray-300 rounded-lg px-3 py-2"
                                    placeholder="Descripción detallada del síntoma..."></textarea>
                            </div>

                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Agregar Síntoma
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de síntomas existentes -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Síntomas Configurados</h3>
                    <div id="listaSintomas" class="space-y-3">
                        @foreach ($especialidad->sintomas as $sintoma)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold">{{ $sintoma->sintoma }}</h4>
                                        <p class="text-sm text-gray-600">{{ $sintoma->descripcion }}</p>
                                        <div class="flex items-center mt-2 space-x-2">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                {{ $sintoma->palabraClave->palabra }}
                                            </span>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                                {{ ucfirst($sintoma->nivel_gravedad) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="editarSintoma({{ $sintoma->id_sintoma }})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                        <button onclick="eliminarSintoma({{ $sintoma->id_sintoma }})"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                            <i class="fas fa-trash mr-1"></i>Eliminar
                                        </button>
                                        <button onclick="verProfesionalesConfigurados({{ $sintoma->id_sintoma }})"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                            <i class="fas fa-user-md mr-1"></i>Ver Profesionales
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('formAgregarSintoma').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;

            // Mostrar loading
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Agregando...';
            button.disabled = true;

            fetch('{{ route('admin.especialidades.agregar-sintoma', $especialidad->id_especialidad) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al agregar el síntoma');
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
        });

        function eliminarSintoma(sintomaId) {
            if (!confirm('¿Estás seguro de que quieres eliminar este síntoma?')) {
                return;
            }

            fetch(`/admin/especialidades/{{ $especialidad->id_especialidad }}/sintomas/${sintomaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al eliminar el síntoma');
                });
        }

        function editarSintoma(sintomaId) {
            alert('Funcionalidad de edición en desarrollo para el síntoma ID: ' + sintomaId);
            // Aquí implementarías la lógica de edición
        }
    </script>
@endsection
