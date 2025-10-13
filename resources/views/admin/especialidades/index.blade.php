<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Especialidades - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow p-8 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">🎓 Gestión de Especialidades</h1>
                    <p class="text-gray-600 mt-2">Administra las especialidades disponibles para cada tipo de
                        profesional</p>
                </div>
                <button onclick="abrirModalCrear()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-full font-semibold">
                    <i class="fas fa-plus mr-2"></i>Nueva Especialidad
                </button>
                <a href="{{ route('dashboard') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- Especialidades por Rol -->
        <div class="space-y-8">
            @foreach ($especialidades as $rol => $especialidadesRol)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 capitalize">{{ $rol }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($especialidadesRol as $especialidad)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-semibold text-gray-800">{{ $especialidad->nombre }}</h3>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                        {{ $especialidad->sintomas_count }} síntomas
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">{{ Str::limit($especialidad->descripcion, 80) }}
                                </p>
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.especialidades.configurar', $especialidad->id_especialidad) }}"
                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded text-sm transition duration-200">
                                        <i class="fas fa-cog mr-1"></i>Configurar Síntomas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @foreach ($especialidades as $rol => $especialidadesRol)
                <div class="bg-white rounded-2xl shadow overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-user-tag mr-3"></i>
                            {{ $rolesPermitidos[$rol] ?? $rol }}
                            <span class="ml-2 text-blue-200 text-sm font-normal">
                                ({{ $especialidadesRol->count() }} especialidades)
                            </span>
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($especialidadesRol as $especialidad)
                                <div class="border rounded-lg p-4 hover:shadow-md transition duration-200"
                                    style="border-left: 4px solid {{ $especialidad->color }};">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center">
                                            <i class="{{ $especialidad->icono }} mr-2"
                                                style="color: {{ $especialidad->color }};"></i>
                                            <h3 class="font-semibold text-lg">{{ $especialidad->nombre }}</h3>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button onclick="editarEspecialidad({{ $especialidad->id_especialidad }})"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button
                                                onclick="eliminarEspecialidad({{ $especialidad->id_especialidad }})"
                                                class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <p class="text-gray-600 text-sm mb-3">{{ $especialidad->descripcion }}</p>

                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>Código: {{ $especialidad->codigo }}</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                                {{ $especialidad->profesionales_count }} profesionales
                                            </span>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                {{ $especialidad->palabras_clave_count }} palabras
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal para Crear/Editar Especialidad -->
    <div id="modalEspecialidad"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitulo">Nueva Especialidad</h3>

                <form id="formEspecialidad">
                    @csrf
                    <input type="hidden" id="especialidadId" name="id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                            <input type="text" id="nombre" name="nombre"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código *</label>
                            <input type="text" id="codigo" name="codigo"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                            <p class="text-xs text-gray-500 mt-1">Identificador único (ej: psicologia_clinica)</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rol Permitido *</label>
                            <select id="rol_permitido" name="rol_permitido"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">Seleccionar rol...</option>
                                @foreach ($rolesPermitidos as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <input type="color" id="color" name="color" value="#3B82F6"
                                class="w-full h-10 px-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icono</label>
                        <input type="text" id="icono" name="icono" placeholder="fas fa-user-md"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Usar clases de Font Awesome (ej: fas fa-brain)</p>
                    </div>

                    <div class="mb-4" id="campoEstadoEspecialidad" style="display: none;">
                        <label class="flex items-center">
                            <input type="checkbox" id="activo" name="activo"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Especialidad activa</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="cerrarModalEspecialidad()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-save mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalCrear() {
            document.getElementById('modalTitulo').textContent = 'Nueva Especialidad';
            document.getElementById('formEspecialidad').reset();
            document.getElementById('especialidadId').value = '';
            document.getElementById('campoEstadoEspecialidad').style.display = 'none';
            document.getElementById('modalEspecialidad').classList.remove('hidden');
        }

        function editarEspecialidad(id) {
            fetch(`/admin/especialidades/${id}`)
                .then(response => response.json())
                .then(especialidad => {
                    document.getElementById('modalTitulo').textContent = 'Editar Especialidad';
                    document.getElementById('especialidadId').value = especialidad.id_especialidad;
                    document.getElementById('nombre').value = especialidad.nombre;
                    document.getElementById('codigo').value = especialidad.codigo;
                    document.getElementById('descripcion').value = especialidad.descripcion;
                    document.getElementById('rol_permitido').value = especialidad.rol_permitido;
                    document.getElementById('color').value = especialidad.color || '#3B82F6';
                    document.getElementById('icono').value = especialidad.icono || '';
                    document.getElementById('activo').checked = especialidad.activo;
                    document.getElementById('campoEstadoEspecialidad').style.display = 'block';

                    document.getElementById('modalEspecialidad').classList.remove('hidden');
                });
        }

        function eliminarEspecialidad(id) {
            if (confirm('¿Estás seguro de eliminar esta especialidad?')) {
                fetch(`/admin/especialidades/${id}`, {
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
                    });
            }
        }

        function cerrarModalEspecialidad() {
            document.getElementById('modalEspecialidad').classList.add('hidden');
        }

        document.getElementById('formEspecialidad').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const id = document.getElementById('especialidadId').value;
            const url = id ? `/admin/especialidades/${id}` : '/admin/especialidades';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        cerrarModalEspecialidad();
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        });
    </script>
</body>

</html>
