@extends('layouts.profesional')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Configurar Especialidades y Palabras Clave</h1>
        
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                Selecciona las palabras clave que definen los tipos de casos que atiendes. 
                El sistema asignará pacientes basándose en estas especialidades.
            </p>
        </div>

        <form id="configForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($catalogo as $categoria => $palabras)
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold text-lg mb-3 capitalize">{{ $categoria }}</h3>
                    <div class="space-y-2">
                        @foreach($palabras as $palabra)
                        <label class="flex items-center">
                            <input type="checkbox" name="palabras_clave[]" value="{{ $palabra }}"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ in_array($palabra, $profesional->palabras_clave_especialidad->toArray() ?? []) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700 capitalize">
                                {{ str_replace('_', ' ', $palabra) }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Resumen de selección -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold mb-2">Resumen de tu especialización:</h4>
                <div id="resumenEspecialidades" class="flex flex-wrap gap-2">
                    @foreach($profesional->sintomas_atiende ?? [] as $sintoma)
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                        {{ $sintoma }}
                    </span>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('configForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("profesional.actualizar-palabras-clave") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            const resumen = document.getElementById('resumenEspecialidades');
            resumen.innerHTML = data.sintomas_atiende.map(sintoma => 
                `<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">${sintoma}</span>`
            ).join('');
            
            alert('Configuración guardada exitosamente');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la configuración');
    });
});
</script>
@endsection  <!-- Solo este es necesario -->
