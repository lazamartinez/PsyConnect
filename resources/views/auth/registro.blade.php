<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PsyConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #E6F3FF 0%, #B0E2FF 100%);
        }

        .card-shadow {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .user-type-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .user-type-card:hover {
            transform: translateY(-5px);
            border-color: #87CEEB;
        }

        .user-type-card.selected {
            border-color: #4682B4;
            background: #F0F8FF;
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

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center py-8">
    <div class="max-w-4xl w-full mx-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <div class="md:flex">
                <!-- Lado Izquierdo - Formulario -->
                <div class="md:w-2/3 p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">🌟 Únete a PsyConnect</h1>
                        <p class="text-gray-600 mt-2">Comienza tu journey de bienestar emocional</p>
                    </div>

                    <form action="{{ route('registro') }}" method="POST" id="registroForm">
                        @csrf

                        <!-- MOSTRAR ERRORES GENERALES -->
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

                        <!-- MOSTRAR MENSAJES DE ÉXITO/ERROR -->
                        @if (session('exito'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 fade-in">
                                {{ session('exito') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 fade-in">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Información Básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>Nombre
                                </label>
                                <input type="text" id="nombre" name="nombre"
                                    class="form-input"
                                    value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2 text-blue-500"></i>Apellido
                                </label>
                                <input type="text" id="apellido" name="apellido"
                                    class="form-input"
                                    value="{{ old('apellido') }}" required>
                                @error('apellido')
                                    <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email y Teléfono -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2 text-blue-500"></i>Email
                                </label>
                                <input type="email" id="email" name="email"
                                    class="form-input"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-2 text-blue-500"></i>Teléfono
                                </label>
                                <input type="tel" id="telefono" name="telefono"
                                    class="form-input"
                                    value="{{ old('telefono') }}">
                                @error('telefono')
                                    <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Contraseñas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="contrasenia" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock mr-2 text-blue-500"></i>Contraseña
                                </label>
                                <input type="password" id="contrasenia" name="contrasenia"
                                    class="form-input"
                                    required minlength="8">
                                @error('contrasenia')
                                    <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="contrasenia_confirmation"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock mr-2 text-blue-500"></i>Confirmar Contraseña
                                </label>
                                <input type="password" id="contrasenia_confirmation" name="contrasenia_confirmation"
                                    class="form-input"
                                    required>
                                <div id="passwordMatchMessage" class="text-sm mt-1 hidden"></div>
                            </div>
                        </div>

                        <!-- Tipo de Usuario -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                <i class="fas fa-users mr-2 text-blue-500"></i>¿Cómo usarás PsyConnect?
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="userTypeSelection">
                                <div class="user-type-card p-4 border rounded-lg cursor-pointer text-center interactive-card"
                                    data-type="paciente" onclick="selectUserType('paciente')">
                                    <i class="fas fa-user-heart text-3xl text-blue-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Paciente</h3>
                                    <p class="text-sm text-gray-600 mt-1">Quiero seguir mi bienestar emocional</p>
                                </div>
                                <div class="user-type-card p-4 border rounded-lg cursor-pointer text-center interactive-card"
                                    data-type="psicologo" onclick="selectUserType('psicologo')">
                                    <i class="fas fa-brain text-3xl text-green-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Psicólogo</h3>
                                    <p class="text-sm text-gray-600 mt-1">Acompañaré pacientes en su proceso</p>
                                </div>
                                <div class="user-type-card p-4 border rounded-lg cursor-pointer text-center interactive-card"
                                    data-type="psiquiatra" onclick="selectUserType('psiquiatra')">
                                    <i class="fas fa-stethoscope text-3xl text-purple-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Psiquiatra</h3>
                                    <p class="text-sm text-gray-600 mt-1">Brindaré atención médica especializada</p>
                                </div>
                                <div class="user-type-card p-4 border rounded-lg cursor-pointer text-center interactive-card"
                                    data-type="nutricionista" onclick="selectUserType('nutricionista')">
                                    <i class="fas fa-apple-alt text-3xl text-orange-500 mb-2"></i>
                                    <h3 class="font-semibold text-gray-800">Nutricionista</h3>
                                    <p class="text-sm text-gray-600 mt-1">Acompañaré el bienestar nutricional</p>
                                </div>
                            </div>
                            <input type="hidden" name="tipo_usuario" id="tipo_usuario"
                                value="{{ old('tipo_usuario') }}" required>
                            @error('tipo_usuario')
                                <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Campos específicos por tipo de usuario -->
                        <div id="additionalFields">
                            <!-- Campos para Paciente -->
                            <div id="pacienteFields" class="hidden space-y-4 mb-6 fade-in">
                                <h3 class="text-lg font-semibold text-blue-600 mb-4">
                                    <i class="fas fa-user-heart mr-2"></i>Información Personal
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="fecha_nacimiento"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-birthday-cake mr-2 text-blue-500"></i>Fecha de Nacimiento
                                        </label>
                                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                            class="form-input"
                                            value="{{ old('fecha_nacimiento') }}" required>
                                        @error('fecha_nacimiento')
                                            <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="genero" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-venus-mars mr-2 text-blue-500"></i>Género
                                        </label>
                                        <select id="genero" name="genero"
                                            class="form-select" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="masculino"
                                                {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                            <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>
                                                Femenino</option>
                                            <option value="otro" {{ old('genero') == 'otro' ? 'selected' : '' }}>Otro
                                            </option>
                                            <option value="prefiero_no_decir"
                                                {{ old('genero') == 'prefiero_no_decir' ? 'selected' : '' }}>Prefiero no
                                                decir</option>
                                        </select>
                                        @error('genero')
                                            <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- NOTA: Descripción de síntomas se hará en el dashboard después del registro -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-blue-800 text-sm">
                                                <strong>Importante:</strong> Una vez registrado, podrás completar 
                                                el proceso de triaje desde tu dashboard para ser asignado al 
                                                profesional más adecuado según tus necesidades.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para Profesionales -->
                            <div id="profesionalFields" class="hidden space-y-4 mb-6 fade-in">
                                <h3 class="text-lg font-semibold text-green-600 mb-4">
                                    <i class="fas fa-user-md mr-2"></i>Información Profesional
                                </h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="especialidad_principal"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>Especialidad Principal
                                        </label>
                                        <select id="especialidad_principal" name="especialidad_principal"
                                            class="form-select" required>
                                            <option value="">Seleccionar especialidad...</option>
                                            <option value="Terapia Cognitivo-Conductual" {{ old('especialidad_principal') == 'Terapia Cognitivo-Conductual' ? 'selected' : '' }}>Terapia Cognitivo-Conductual</option>
                                            <option value="Psicoanálisis" {{ old('especialidad_principal') == 'Psicoanálisis' ? 'selected' : '' }}>Psicoanálisis</option>
                                            <option value="Terapia Familiar" {{ old('especialidad_principal') == 'Terapia Familiar' ? 'selected' : '' }}>Terapia Familiar</option>
                                            <option value="Psiquiatría General" {{ old('especialidad_principal') == 'Psiquiatría General' ? 'selected' : '' }}>Psiquiatría General</option>
                                            <option value="Psiquiatría Infantil" {{ old('especialidad_principal') == 'Psiquiatría Infantil' ? 'selected' : '' }}>Psiquiatría Infantil</option>
                                            <option value="Nutrición Clínica" {{ old('especialidad_principal') == 'Nutrición Clínica' ? 'selected' : '' }}>Nutrición Clínica</option>
                                            <option value="Nutrición Deportiva" {{ old('especialidad_principal') == 'Nutrición Deportiva' ? 'selected' : '' }}>Nutrición Deportiva</option>
                                        </select>
                                        @error('especialidad_principal')
                                            <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="matricula" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-id-card mr-2 text-blue-500"></i>Número de Matrícula
                                        </label>
                                        <input type="text" id="matricula" name="matricula"
                                            class="form-input"
                                            value="{{ old('matricula') }}" placeholder="Opcional (ej: MP 12345)">
                                        @error('matricula')
                                            <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Selector de Clínica -->
                                    <div>
                                        <label for="clinica_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-hospital mr-2 text-blue-500"></i>Clínica/Sede de Trabajo
                                        </label>
                                        <select id="clinica_id" name="clinica_id"
                                            class="form-select" required>
                                            <option value="">Seleccionar clínica...</option>
                                            @foreach($clinicas as $clinica)
                                                <option value="{{ $clinica->id_clinica }}" 
                                                    {{ old('clinica_id') == $clinica->id_clinica ? 'selected' : '' }}>
                                                    {{ $clinica->nombre }} - {{ $clinica->ciudad }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('clinica_id')
                                            <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                                        @enderror
                                        <p class="text-sm text-gray-500 mt-2">
                                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                            Tu solicitud será revisada por el administrador de la clínica. 
                                            Una vez aprobado, podrás configurar tus especialidades y palabras clave.
                                        </p>
                                    </div>
                                </div>

                                <!-- Información sobre configuración posterior -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-cog text-green-500 mt-1 mr-3"></i>
                                        <div>
                                            <h4 class="font-semibold text-green-800 mb-1">Configuración Posterior</h4>
                                            <p class="text-green-700 text-sm">
                                                Después de ser aprobado por el administrador, podrás configurar 
                                                tus palabras clave de especialidad desde tu dashboard profesional.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Términos y Condiciones -->
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" id="terminos" name="terminos" value="1"
                                    class="mt-1 mr-3 rounded focus:ring-blue-500 border-gray-300 text-blue-600"
                                    {{ old('terminos') ? 'checked' : '' }} required>
                                <span class="text-sm text-gray-700">
                                    Acepto los <a href="#" class="text-blue-600 hover:underline font-medium">Términos de
                                        Servicio</a>
                                    y la <a href="#" class="text-blue-600 hover:underline font-medium">Política de
                                        Privacidad</a> de PsyConnect
                                </span>
                            </label>
                            @error('terminos')
                                <p class="text-red-500 text-sm mt-1 fade-in">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botón de Registro -->
                        <button type="submit" id="submitBtn"
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-4 px-6 rounded-lg font-semibold text-lg transition duration-300 transform hover:scale-105 focus:ring-4 focus:ring-blue-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <i class="fas fa-rocket mr-2"></i>Crear Mi Cuenta
                        </button>

                        <!-- Enlace a Login -->
                        <div class="text-center mt-6">
                            <p class="text-gray-600">
                                ¿Ya tienes una cuenta?
                                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold transition duration-200">
                                    Inicia Sesión
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Lado Derecho - Información -->
                <div class="md:w-1/3 bg-gradient-to-b from-blue-500 to-blue-600 text-white p-8">
                    <div class="h-full flex flex-col justify-center">
                        <div class="text-center mb-8">
                            <i class="fas fa-brain text-6xl mb-4 opacity-90"></i>
                            <h2 class="text-2xl font-bold mb-2">PsyConnect</h2>
                            <p class="opacity-90">Tu compañero en el camino del bienestar emocional</p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-shield-alt mt-1 mr-3 text-blue-200"></i>
                                <div>
                                    <h4 class="font-semibold">Seguro y Confidencial</h4>
                                    <p class="text-sm opacity-90 mt-1">Tus datos están protegidos con encriptación de
                                        grado médico</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-chart-line mt-1 mr-3 text-blue-200"></i>
                                <div>
                                    <h4 class="font-semibold">Seguimiento Continuo</h4>
                                    <p class="text-sm opacity-90 mt-1">Monitoriza tu progreso emocional con métricas
                                        precisas</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-hands-helping mt-1 mr-3 text-blue-200"></i>
                                <div>
                                    <h4 class="font-semibold">Apoyo Profesional</h4>
                                    <p class="text-sm opacity-90 mt-1">Conecta con especialistas en salud mental</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <i class="fas fa-bolt mt-1 mr-3 text-blue-200"></i>
                                <div>
                                    <h4 class="font-semibold">Asignación Inteligente</h4>
                                    <p class="text-sm opacity-90 mt-1">Sistema automático de matching con profesionales</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-4 bg-blue-400 bg-opacity-20 rounded-lg">
                            <p class="text-sm text-center">
                                <i class="fas fa-heart mr-1"></i>
                                Más de 1,000 personas ya confían en PsyConnect para su bienestar emocional
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectUserType(type) {
            // Remover selección anterior
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Agregar selección actual
            document.querySelector(`[data-type="${type}"]`).classList.add('selected');
            document.getElementById('tipo_usuario').value = type;

            // Mostrar campos adicionales según el tipo
            document.getElementById('pacienteFields').classList.add('hidden');
            document.getElementById('profesionalFields').classList.add('hidden');

            if (type === 'paciente') {
                document.getElementById('pacienteFields').classList.remove('hidden');
                
                // Hacer requeridos los campos de paciente
                document.getElementById('fecha_nacimiento').required = true;
                document.getElementById('genero').required = true;

                // Hacer opcionales campos de profesional
                document.getElementById('especialidad_principal').required = false;
                document.getElementById('clinica_id').required = false;

            } else {
                document.getElementById('profesionalFields').classList.remove('hidden');
                
                // Hacer requeridos los campos de profesional
                document.getElementById('especialidad_principal').required = true;
                document.getElementById('clinica_id').required = true;

                // Hacer opcionales campos de paciente
                document.getElementById('fecha_nacimiento').required = false;
                document.getElementById('genero').required = false;
            }

            validarFormulario();
        }

        // Validación de contraseña en tiempo real
        function validarContrasenia() {
            const password = document.getElementById('contrasenia').value;
            const confirm = document.getElementById('contrasenia_confirmation').value;
            const message = document.getElementById('passwordMatchMessage');

            if (confirm === '') {
                message.classList.add('hidden');
                return true;
            }

            if (password === confirm) {
                message.textContent = '✓ Las contraseñas coinciden';
                message.className = 'text-green-600 text-sm mt-1 fade-in';
                message.classList.remove('hidden');
                return true;
            } else {
                message.textContent = '✗ Las contraseñas no coinciden';
                message.className = 'text-red-600 text-sm mt-1 fade-in';
                message.classList.remove('hidden');
                return false;
            }
        }

        // Validación general del formulario
        function validarFormulario() {
            const tipoUsuario = document.getElementById('tipo_usuario').value;
            const terminos = document.getElementById('terminos').checked;
            const passwordValida = validarContrasenia();
            const submitBtn = document.getElementById('submitBtn');

            let formularioValido = tipoUsuario && terminos && passwordValida;

            // Validaciones específicas por tipo de usuario
            if (tipoUsuario === 'paciente') {
                const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
                const genero = document.getElementById('genero').value;
                formularioValido = formularioValido && fechaNacimiento && genero;
            } else if (tipoUsuario) {
                const especialidad = document.getElementById('especialidad_principal').value;
                const clinica = document.getElementById('clinica_id').value;
                formularioValido = formularioValido && especialidad && clinica;
            }

            submitBtn.disabled = !formularioValido;
            return formularioValido;
        }

        // Event listeners para validación en tiempo real
        document.getElementById('contrasenia').addEventListener('input', validarContrasenia);
        document.getElementById('contrasenia_confirmation').addEventListener('input', validarContrasenia);
        document.getElementById('terminos').addEventListener('change', validarFormulario);

        // Event listeners para campos que afectan la validación
        document.querySelectorAll('#pacienteFields input, #pacienteFields select, #profesionalFields input, #profesionalFields select').forEach(element => {
            element.addEventListener('input', validarFormulario);
            element.addEventListener('change', validarFormulario);
        });

        // Seleccionar tipo de usuario si hay valor en old
        document.addEventListener('DOMContentLoaded', function() {
            const oldType = "{{ old('tipo_usuario') }}";
            if (oldType) {
                selectUserType(oldType);
            } else {
                // Por defecto, seleccionar paciente
                selectUserType('paciente');
            }

            // Validación inicial
            validarFormulario();

            // Mostrar mensajes de error de Laravel con scroll suave
            @if ($errors->any())
                setTimeout(() => {
                    const firstError = document.querySelector('.text-red-500');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }, 300);
            @endif
        });

        // Prevenir envío del formulario si no es válido
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
                
                // Mostrar mensaje de error
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 fade-in';
                errorDiv.innerHTML = `
                    <strong>Error:</strong> Por favor completa todos los campos requeridos correctamente.
                `;
                
                const existingError = document.querySelector('.bg-red-100');
                if (!existingError) {
                    this.prepend(errorDiv);
                }

                // Scroll al primer error
                setTimeout(() => {
                    const firstInvalid = this.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }
                }, 100);
            }
        });

        // Efectos visuales mejorados
        document.querySelectorAll('.form-input, .form-select').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-blue-200', 'rounded-lg');
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-200', 'rounded-lg');
            });
        });
    </script>
</body>
</html>