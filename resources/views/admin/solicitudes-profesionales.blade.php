@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Profesionales</h1>
            <a href="{{ route('dashboard') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
        </div>

        @if(session('exito'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('exito') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($solicitudes->isEmpty())
            <div class="bg-white shadow rounded-lg p-6 text-center">
                <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">¡No hay solicitudes pendientes!</h3>
                <p class="text-gray-600">Todas las solicitudes han sido procesadas.</p>
            </div>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesional</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Especialidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Experiencia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Solicitud</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($solicitudes as $solicitud)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $solicitud->usuario->nombre }} {{ $solicitud->usuario->apellido }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $solicitud->usuario->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $solicitud->usuario->telefono }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $solicitud->especialidad_principal }}</div>
                                @if($solicitud->matricula)
                                <div class="text-sm text-gray-500">Matrícula: {{ $solicitud->matricula }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $solicitud->anios_experiencia ?? 0 }} años
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $solicitud->institucion ?? 'Sin institución' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $solicitud->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="aprobarSolicitud('{{ $solicitud->id }}')" 
                                        class="text-green-600 hover:text-green-900 mr-3 bg-green-100 px-3 py-1 rounded">
                                    <i class="fas fa-check mr-1"></i>Aprobar
                                </button>
                                <button onclick="rechazarSolicitud('{{ $solicitud->id }}')" 
                                        class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded">
                                    <i class="fas fa-times mr-1"></i>Rechazar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal para rechazar solicitud -->
<div id="rechazarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rechazar Solicitud</h3>
        <form id="rechazarForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del rechazo</label>
                <textarea name="motivo_rechazo" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Explica el motivo del rechazo..." required></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cerrarModal('rechazarModal')" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancelar</button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-500 text-white rounded-md">Rechazar</button>
            </div>
        </form>
    </div>
</div>

<script>
function aprobarSolicitud(id) {
    if (confirm('¿Estás seguro de que quieres aprobar esta solicitud?')) {
        fetch(`/admin/solicitudes/${id}/aprobar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Solicitud aprobada exitosamente');
                location.reload();
            } else {
                alert('Error al aprobar la solicitud: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al aprobar la solicitud');
        });
    }
}

function rechazarSolicitud(id) {
    document.getElementById('rechazarForm').action = `/admin/solicitudes/${id}/rechazar`;
    document.getElementById('rechazarModal').classList.remove('hidden');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    const modal = document.getElementById('rechazarModal');
    if (event.target == modal) {
        modal.classList.add('hidden');
    }
}

// Manejar envío del formulario de rechazo
document.getElementById('rechazarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = this.action;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Solicitud rechazada exitosamente');
            location.reload();
        } else {
            alert('Error al rechazar la solicitud: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al rechazar la solicitud');
    });
});
</script>
@endsection