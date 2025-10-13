<div id="notificacionMatch" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-t-lg">
                <div class="flex items-center">
                    <i class="fas fa-bell text-2xl mr-3"></i>
                    <h3 class="text-xl font-bold">¡Nuevo Paciente Potencial!</h3>
                </div>
            </div>
            
            <div class="p-6">
                <div id="contenidoNotificacion">
                    <!-- Contenido dinámico de la notificación -->
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button id="btnRechazarMatch" 
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-times mr-2"></i>Rechazar
                    </button>
                    <button id="btnAceptarMatch" 
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition duration-200">
                        <i class="fas fa-check mr-2"></i>Aceptar Paciente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Escuchar eventos de notificación en tiempo real
Echo.private('profesional.{{ Auth::user()->profesional->id_profesional ?? 0 }}')
    .listen('nuevo.paciente.match', (e) => {
        mostrarNotificacionMatch(e);
    });

function mostrarNotificacionMatch(datos) {
    const notificacion = document.getElementById('notificacionMatch');
    const contenido = document.getElementById('contenidoNotificacion');
    
    contenido.innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-lg">${datos.paciente.nombre} ${datos.paciente.apellido}</h4>
                    <p class="text-gray-600">${datos.paciente.edad} años • ${datos.paciente.genero}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-3 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">${datos.compatibilidad}%</div>
                    <div class="text-blue-700 text-sm">Compatibilidad</div>
                </div>
                <div class="bg-purple-50 p-3 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">${datos.sintomas_detectados.length}</div>
                    <div class="text-purple-700 text-sm">Síntomas detectados</div>
                </div>
            </div>
            
            <div class="bg-gray-50 p-3 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-2">Síntomas principales:</h5>
                <div class="flex flex-wrap gap-1">
                    ${datos.sintomas_detectados.map(sintoma => 
                        `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">${sintoma}</span>`
                    ).join('')}
                </div>
            </div>
        </div>
    `;
    
    notificacion.classList.remove('hidden');
    
    // Configurar botones
    document.getElementById('btnAceptarMatch').onclick = () => aceptarMatch(datos.paciente.id);
    document.getElementById('btnRechazarMatch').onclick = () => rechazarMatch(datos.paciente.id);
}

function aceptarMatch(pacienteId) {
    fetch(`/profesional/match/${pacienteId}/aceptar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Paciente aceptado exitosamente');
            cerrarNotificacion();
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}

function rechazarMatch(pacienteId) {
    if (confirm('¿Estás seguro de rechazar este paciente?')) {
        fetch(`/profesional/match/${pacienteId}/rechazar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Paciente rechazado');
                cerrarNotificacion();
            }
        });
    }
}

function cerrarNotificacion() {
    document.getElementById('notificacionMatch').classList.add('hidden');
}
</script>