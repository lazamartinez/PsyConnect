<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PsyConnect - Administración</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.2/echarts.min.js"></script>
    <style>
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        .content {
            margin-left: 16rem;
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -16rem;
            }
            .content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-20px) translateX(10px); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white">
        <div class="p-4">
            <h1 class="text-2xl font-bold text-white">
                <i class="fas fa-brain mr-2 text-purple-400"></i>
                PsyConnect
            </h1>
            <p class="text-gray-400 text-sm mt-1">Panel de Administración</p>
        </div>
        
        <nav class="mt-6">
            <a href="{{ route('dashboard') }}" 
               class="block py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </a>
            
            <a href="{{ route('admin.especialidades.index') }}" 
               class="block py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200 {{ request()->routeIs('admin.especialidades.*') ? 'bg-gray-700 text-white' : '' }}">
                <i class="fas fa-graduation-cap mr-3"></i>Especialidades
            </a>
            
            <a href="#" class="block py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <i class="fas fa-users mr-3"></i>Profesionales
            </a>
            
            <a href="#" class="block py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <i class="fas fa-user-injured mr-3"></i>Pacientes
            </a>
            
            <a href="#" class="block py-3 px-6 text-gray-300 hover:bg-gray-700 hover:text-white transition duration-200">
                <i class="fas fa-cog mr-3"></i>Configuración
            </a>
        </nav>
        
        <div class="absolute bottom-0 w-full p-4 border-t border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-shield text-2xl text-purple-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">{{ Auth::user()->nombre }}</p>
                    <p class="text-xs text-gray-400">Administrador</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-left text-sm text-gray-400 hover:text-white transition duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex justify-between items-center py-4 px-6">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="text-gray-500 hover:text-gray-700 lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800 ml-4">
                        @yield('title', 'Administración')
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-bell text-gray-500 hover:text-gray-700 cursor-pointer"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center">3</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>