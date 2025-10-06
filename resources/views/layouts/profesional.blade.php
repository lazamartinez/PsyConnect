<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PsyConnect - Configuración Profesional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-brain text-2xl text-blue-500 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">PsyConnect - Profesional</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">{{ Auth::user()->nombre }}</span>
                    <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <script>
        // JavaScript para el formulario
        document.getElementById('configForm')?.addEventListener('submit', function(e) {
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
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Actualizar resumen
                    const resumen = document.getElementById('resumenEspecialidades');
                    if (resumen && data.sintomas_atiende) {
                        resumen.innerHTML = data.sintomas_atiende.map(sintoma => 
                            `<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">${sintoma}</span>`
                        ).join('');
                    }
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al guardar la configuración');
            });
        });
    </script>
</body>
</html>