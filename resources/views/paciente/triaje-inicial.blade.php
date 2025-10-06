<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Triaje Inicial - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #E6F3FF 0%, #B0E2FF 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .interactive-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .interactive-card:hover {
            transform: translateY(-5px);
            border-color: #87CEEB;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-brain text-2xl text-blue-500 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">PsyConnect</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Hola, {{ $paciente->usuario->nombre }}</span>
                    <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <!-- Header Informativo -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-8 text-center">
                <h1 class="text-3xl font-bold mb-4">🎯 Triaje Inicial</h1>
                <p class="text-blue-100 text-lg">
                    Completa este formulario para ser asignado al profesional más adecuado según tus necesidades
                </p>
            </div>

            <div class="p-8">
                <!-- Información Importante -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 fade-in">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 text-xl mt-1 mr-4"></i>
                        <div>
                            <h3 class="font-semibold text-blue-800 text-lg mb-2">¿Por qué es importante el triaje?</h3>
                            <p class="text-blue-700">
                                Este proceso nos ayuda a entender tus necesidades específicas y asignarte al profesional 
                                más adecuado. Tu información es completamente confidencial y será utilizada únicamente 
                                para brindarte la mejor atención posible.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Triaje -->
                <form action="{{ route('triaje.procesar') }}" method="POST" id="triajeForm">
                    @csrf

                    <!-- Mostrar errores -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 fade-in">
                            <h4 class="font-bold">Error en el formulario:</h4>
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Sección de Síntomas -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-comment-medical mr-3 text-blue-500"></i>
                            Descripción de Síntomas
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="descripcion_sintomatologia" class="block text-sm font-medium text-gray-700 mb-3">
                                    <span class="text-red-500">*</span> Describe cómo te has estado sintiendo:
                                </label>
                                <textarea 
                                    id="descripcion_sintomatologia" 
                                    name="descripcion_sintomatologia" 
                                    rows="8"
                                    class="form-input"
                                    placeholder="Por favor, describe detalladamente:
• Qué síntomas o sensaciones has experimentado
• Cuándo comenzaron y con qué frecuencia ocurren
• Situaciones específicas que los desencadenan
• Cómo afectan tu vida diaria
• Cualquier otro detalle que consideres importante

Ejemplo: 'Últimamente he estado experimentando ansiedad constante, especialmente por las noches. Me cuesta conciliar el sueño y me despierto con palpitaciones. En el trabajo me siento abrumado y tengo dificultad para concentrarme...'"
                                    required
                                >{{ old('descripcion_sintomatologia') }}</textarea>
                                <div class="flex justify-between items-center mt-2">
                                    <p class="text-sm text-gray-500">
                                        Mínimo 50 caracteres. Sé lo más específico posible para una mejor asignación.
                                    </p>
                                    <span id="charCount" class="text-sm font-medium text-gray-600">0 caracteres</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Áreas de Preocupación -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-list-check mr-3 text-green-500"></i>
                            Áreas de Preocupación
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="ansiedad" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Ansiedad y Estrés</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Preocupación constante, nerviosismo, ataques de pánico</p>
                            </div>
                            
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="depresion" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Estado de Ánimo</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Tristeza, desesperanza, pérdida de interés</p>
                            </div>
                            
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="relaciones" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Relaciones</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Problemas familiares, de pareja o sociales</p>
                            </div>
                            
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="trabajo" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Trabajo/Estudios</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Estrés laboral, problemas académicos, burnout</p>
                            </div>
                            
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="alimentacion" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Alimentación</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Problemas con la comida, imagen corporal</p>
                            </div>
                            
                            <div class="interactive-card bg-gray-50 p-4 rounded-lg">
                                <label class="flex items-center">
                                    <input type="checkbox" name="areas_preocupacion[]" value="sueno" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-3 font-medium text-gray-700">Sueño</span>
                                </label>
                                <p class="text-sm text-gray-600 mt-1 ml-6">Insomnio, pesadillas, sueño no reparador</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Urgencia -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3 text-orange-500"></i>
                            Nivel de Urgencia
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="interactive-card cursor-pointer">
                                <input type="radio" name="urgencia_percibida" value="baja" class="hidden" required>
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all duration-200">
                                    <i class="fas fa-leaf text-3xl text-green-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Baja</h3>
                                    <p class="text-sm text-gray-600 mt-1">Puedo manejar la situación por ahora</p>
                                </div>
                            </label>
                            
                            <label class="interactive-card cursor-pointer">
                                <input type="radio" name="urgencia_percibida" value="media" class="hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all duration-200">
                                    <i class="fas fa-balance-scale text-3xl text-yellow-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Media</h3>
                                    <p class="text-sm text-gray-600 mt-1">Necesito ayuda pronto</p>
                                </div>
                            </label>
                            
                            <label class="interactive-card cursor-pointer">
                                <input type="radio" name="urgencia_percibida" value="alta" class="hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center transition-all duration-200">
                                    <i class="fas fa-exclamation-circle text-3xl text-red-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Alta</h3>
                                    <p class="text-sm text-gray-600 mt-1">Necesito ayuda inmediata</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Información de Confidencialidad -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                        <div class="flex items-start">
                            <i class="fas fa-shield-alt text-green-500 text-xl mt-1 mr-4"></i>
                            <div>
                                <h3 class="font-semibold text-green-800 mb-2">Tu información está protegida</h3>
                                <p class="text-green-700 text-sm">
                                    Todos los datos que proporciones están protegidos por encriptación de grado médico 
                                    y solo serán accesibles para los profesionales de salud mental autorizados. 
                                    Tu privacidad es nuestra prioridad.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end">
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition duration-200 text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                        </a>
                        <button type="submit" 
                                id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <span id="submitText">Enviar Triaje</span>
                            <div id="loadingSpinner" class="hidden ml-2">
                                <div class="spinner border-2 border-white border-t-transparent rounded-full w-5 h-5 animate-spin"></div>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <i class="fas fa-user-md text-3xl text-blue-500 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Asignación Precisa</h3>
                <p class="text-gray-600 text-sm mt-2">Te conectamos con el profesional más adecuado para tus necesidades específicas</p>
            </div>
            
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <i class="fas fa-clock text-3xl text-green-500 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Respuesta Rápida</h3>
                <p class="text-gray-600 text-sm mt-2">Procesamos tu triaje en máximo 24 horas para una asignación oportuna</p>
            </div>
            
            <div class="bg-white rounded-xl card-shadow p-6 text-center">
                <i class="fas fa-heart text-3xl text-purple-500 mb-3"></i>
                <h3 class="font-semibold text-gray-800">Atención Personalizada</h3>
                <p class="text-gray-600 text-sm mt-2">Cada asignación considera tus preferencias y necesidades únicas</p>
            </div>
        </div>
    </div>

    <script>
        // Contador de caracteres
        const textarea = document.getElementById('descripcion_sintomatologia');
        const charCount = document.getElementById('charCount');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + ' caracteres';
            
            if (length < 50) {
                charCount.className = 'text-sm font-medium text-red-600';
            } else if (length < 100) {
                charCount.className = 'text-sm font-medium text-yellow-600';
            } else {
                charCount.className = 'text-sm font-medium text-green-600';
            }
            
            validarFormulario();
        });

        // Validación de selección de urgencia
        const urgenciaLabels = document.querySelectorAll('label.interactive-card input[type="radio"]');
        urgenciaLabels.forEach(radio => {
            radio.addEventListener('change', function() {
                // Remover selección anterior
                document.querySelectorAll('label.interactive-card').forEach(label => {
                    label.querySelector('div').classList.remove('border-blue-500', 'bg-blue-50');
                });
                
                // Agregar selección actual
                if (this.checked) {
                    const parentDiv = this.parentElement.querySelector('div');
                    parentDiv.classList.add('border-blue-500', 'bg-blue-50');
                }
                
                validarFormulario();
            });
        });

        // Validación del formulario
        function validarFormulario() {
            const descripcion = textarea.value.trim();
            const urgenciaSeleccionada = document.querySelector('input[name="urgencia_percibida"]:checked');
            
            const descripcionValida = descripcion.length >= 50;
            const urgenciaValida = urgenciaSeleccionada !== null;
            
            const formularioValido = descripcionValida && urgenciaValida;
            
            submitBtn.disabled = !formularioValido;
            return formularioValido;
        }

        // Manejo del envío del formulario
        document.getElementById('triajeForm').addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                let errorMessage = 'Por favor completa los siguientes campos:';
                if (textarea.value.trim().length < 50) {
                    errorMessage += '\n• Descripción de síntomas (mínimo 50 caracteres)';
                }
                if (!document.querySelector('input[name="urgencia_percibida"]:checked')) {
                    errorMessage += '\n• Nivel de urgencia';
                }
                
                alert(errorMessage);
                return;
            }

            // Mostrar loading
            submitText.textContent = 'Procesando...';
            loadingSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
        });

        // Inicializar validación
        document.addEventListener('DOMContentLoaded', function() {
            validarFormulario();
            
            // Restaurar valores anteriores si hay errores de validación
            @if(old('descripcion_sintomatologia'))
                textarea.dispatchEvent(new Event('input'));
            @endif
            
            @if(old('urgencia_percibida'))
                const urgenciaValue = "{{ old('urgencia_percibida') }}";
                const urgenciaRadio = document.querySelector(`input[name="urgencia_percibida"][value="${urgenciaValue}"]`);
                if (urgenciaRadio) {
                    urgenciaRadio.checked = true;
                    urgenciaRadio.dispatchEvent(new Event('change'));
                }
            @endif
        });

        // Efectos visuales para checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.interactive-card');
                if (this.checked) {
                    card.classList.add('border-green-500', 'bg-green-50');
                } else {
                    card.classList.remove('border-green-500', 'bg-green-50');
                }
            });
        });
    </script>
</body>
</html>