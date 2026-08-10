@php
    // Detecta a qué proyecto pertenece la página actual (por prefijo del
    // nombre de ruta) para mostrar una etiqueta de módulo en el topbar. El
    // sidebar ya no cambia de color por módulo (esquema institucional único,
    // ver resources/sass/admin.scss): esto es solo para orientar al usuario
    // sobre en qué sección está.
    $rutaActual = request()->route()?->getName() ?? '';
    $proyectoActual = match (true) {
        str_starts_with($rutaActual, 'kawsay') || $rutaActual === 'adminKawsay' => 'kawsay',
        str_starts_with($rutaActual, 'libros')
            || str_starts_with($rutaActual, 'categorias.')
            || str_starts_with($rutaActual, 'secciones.')
            || str_starts_with($rutaActual, 'biblioteca-fisica.')
            || $rutaActual === 'bibliotecario' => 'biblioteca',
        str_starts_with($rutaActual, 'canciones') || $rutaActual === 'adminCanciones' => 'canciones',
        str_starts_with($rutaActual, 'videos') || str_starts_with($rutaActual, 'videoscat.') || $rutaActual === 'adminVideos' => 'videos',
        str_starts_with($rutaActual, 'sisipedia.') || str_starts_with($rutaActual, 'categories.') || $rutaActual === 'adminSisicha' => 'sisicha',
        default => null,
    };
    $modulosInfo = [
        'biblioteca' => ['label' => 'Biblioteca', 'icono' => 'fa-book'],
        'kawsay' => ['label' => 'Kawsay', 'icono' => 'fa-leaf'],
        'videos' => ['label' => 'Videos', 'icono' => 'fa-video-camera'],
        'canciones' => ['label' => 'Canciones', 'icono' => 'fa-music'],
        'sisicha' => ['label' => 'Sisichakunay', 'icono' => 'fa-th-list'],
    ];
@endphp
<!doctype html>
<html lang="es">

<head>
    <title>@yield('titulo') Biblioteca Asociación Pukllasunchis</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('admin/css/style.css') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/img/logoiesp.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/Icono-Puklla.png') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    {{-- Sistema de diseño del Admin Principal: paleta de marca, componentes nuevos
         (stat-card, project-card, sidebar), GSAP y Chart.js. No reemplaza Bootstrap 4:
         ~20 vistas admin existentes siguen dependiendo de jQuery/Bootstrap4 (modales,
         colapsables) hasta que se rediseñen en fase 2. --}}
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])

    <!-- Dark mode: init before render to avoid flash -->
    <script>
        (function () {
            var t = localStorage.getItem('adminTheme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>

<body>
    <div class="wrapper d-flex align-items-stretch">
        @livewire('menu-admin')
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>
        <div id="content">
            <header class="admin-topbar">
                <button type="button" id="sidebarToggle" class="admin-topbar__toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebar">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="admin-topbar__title">
                    <span class="admin-topbar__title-text">Panel Pukllasunchis</span>
                    @if ($proyectoActual && isset($modulosInfo[$proyectoActual]))
                        <span class="admin-topbar__module-badge">
                            <i class="fa {{ $modulosInfo[$proyectoActual]['icono'] }}"></i>
                            <span class="admin-topbar__module-badge-label">{{ $modulosInfo[$proyectoActual]['label'] }}</span>
                        </span>
                    @endif
                </div>
                <div class="admin-topbar__actions">
                    <a href="{{ route('hub') }}" class="admin-topbar__icon-btn" title="Volver al inicio" aria-label="Volver al inicio">
                        <i class="fa fa-home"></i>
                    </a>
                    @role('admin|sisicha')
                        <a href="{{ route('public.categoria.sisi') }}" target="_blank" rel="noopener" class="admin-topbar__icon-btn" title="Catálogo público" aria-label="Catálogo público de Sisipedia">
                            <i class="fa fa-globe"></i>
                        </a>
                    @endrole
                    <button type="button" id="darkModeToggle" class="admin-topbar__icon-btn" aria-label="Cambiar tema">
                        <i class="fa fa-moon-o" id="darkModeIcon"></i>
                    </button>
                    @auth
                        <div class="admin-topbar__user">
                            <div class="admin-topbar__avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            <span class="admin-topbar__username d-none d-md-inline">{{ Auth::user()->name }}</span>
                            <a href="{{ route('logout') }}" class="admin-topbar__logout" title="Cerrar sesión"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out"></i>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    @endauth
                </div>
            </header>
            <div class="admin-content">
                @yield('contenido')
            </div>
        </div>
    </div>
    <script src="{{ asset('admin/js/jquery.min.js') }}" defer></script>
    <script src="{{ asset('admin/js/popper.js') }}" defer></script>
    <script src="{{ asset('admin/js/bootstrap.min.js') }}" defer></script>
    <script src="{{ asset('admin/js/main.js') }}" defer></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" defer></script>

    {{-- Todas las notificaciones de acción (creado/actualizado/eliminado/error) del
         panel admin pasan por aquí: resources/js/admin.js las lee y las muestra
         como SweetAlert, en vez de que cada vista dibuje su propia alerta Bootstrap. --}}
    <script>
        window.__flashMessages = {
            success: @json(session('success') ?? session('aporte_success')),
            error: @json(session('error')),
        };
    </script>

    @stack('scripts')
</body>

</html>
