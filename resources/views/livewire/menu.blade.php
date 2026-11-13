@php
    $proyectoActual = \App\Services\ProyectoDetector::actual();
    $isBiblioteca = $proyectoActual === 'eesp';
    $isKawsay = $proyectoActual === 'kawsay';
    $isRadio = $proyectoActual === 'radio';
    $isSisipedia = $proyectoActual === 'sisipedia';

    if ($isBiblioteca) {
        $navItems = [
            ['label' => 'Inicio', 'href' => route('eesppukllasunchis'), 'active' => request()->routeIs('eesppukllasunchis'), 'icon' => 'fa-home'],
            ['label' => 'Biblioteca', 'href' => route('libros'), 'active' => request()->routeIs('libros', 'libros.show', 'categorias.show', 'detallesCat', 'catalogo-fisico'), 'icon' => 'fa-book'],
            ['label' => 'Videos', 'href' => route('videos'), 'active' => request()->routeIs('videos', 'videoscat.show'), 'icon' => 'fa-video'],
        ];
    } elseif ($isKawsay) {
        $navItems = [
            ['label' => 'Inicio', 'href' => route('kawsay'), 'active' => request()->routeIs('kawsay'), 'icon' => 'fa-home'],
            ['label' => 'Nosotros', 'href' => route('kawsay.nosotros'), 'active' => request()->routeIs('kawsay.nosotros'), 'icon' => 'fa-info-circle'],
            ['label' => 'Contáctanos', 'href' => route('kawsay.contacto'), 'active' => request()->routeIs('kawsay.contacto'), 'icon' => 'fa-envelope'],
        ];
    } elseif ($isRadio) {
        $navItems = [
            ['label' => 'Inicio', 'href' => route('cancionesPuklla'), 'active' => request()->routeIs('cancionesPuklla'), 'icon' => 'fa-music'],
        ];
    } elseif ($isSisipedia) {
        // El buscador + acceso por formato vive en la cabecera de cada vista
        // pública de Sisipedia (ver partials/lib-hero, prop heroFormatButtons),
        // así que ya no hace falta que el menú duplique Audios/Imágenes/Videos
        // como items sueltos — "Inicio" cubre eso, y el logo apunta al mismo
        // lugar (usa $navItems[0]['href']).
        $navItems = [
            ['label' => 'Inicio', 'href' => route('index'), 'active' => request()->routeIs('index'), 'icon' => 'fa-home'],
            ['label' => 'Nosotros', 'href' => route('sisipedia.nosotros'), 'active' => request()->routeIs('sisipedia.nosotros'), 'icon' => 'fa-info-circle'],
            ['label' => 'Contáctanos', 'href' => route('sisipedia.contacto'), 'active' => request()->routeIs('sisipedia.contacto'), 'icon' => 'fa-envelope'],
        ];
    } else {
        // Rutas sin proyecto dueño (ej. búsqueda global): marca neutral de la
        // Asociación, sin fingir pertenecer a Escuela/Kawsay/Radio/Sisipedia.
        $navItems = [
            ['label' => 'Inicio', 'href' => route('hub'), 'active' => false, 'icon' => 'fa-home'],
        ];
    }

    // Redes del proyecto actual. Los proyectos sin URLs reales todavía
    // mantienen los íconos genéricos con href="#".
    if ($isRadio || $isSisipedia) {
        $socialLinks = [
            ['label' => 'Facebook', 'href' => 'https://www.facebook.com/Sisichakunaq', 'icon' => 'fa-facebook-f'],
            ['label' => 'Instagram', 'href' => 'https://www.instagram.com/sisichakunaq/', 'icon' => 'fa-instagram'],
            ['label' => 'Spotify', 'href' => 'https://open.spotify.com/show/21uqGdVqtet2iYx3XkhAVh?si=28d6c5ac81f94329', 'icon' => 'fa-spotify'],
            ['label' => 'YouTube', 'href' => 'https://www.youtube.com/@RadioPukllasunchis', 'icon' => 'fa-youtube'],
        ];
    } else {
        $socialLinks = [
            ['label' => 'Facebook', 'href' => '#', 'icon' => 'fa-facebook-f'],
            ['label' => 'Instagram', 'href' => '#', 'icon' => 'fa-instagram'],
            ['label' => 'YouTube', 'href' => '#', 'icon' => 'fa-youtube'],
        ];
    }

    // Cuenta: icono + destino + etiqueta legible (se reusa en el pill del panel lateral)
    // El admin completo va primero porque puede tener además cualquiera de los
    // otros roles (ej. admin+kawsay) y su panel principal manda sobre el de un
    // rol específico.
    if (Auth::check() && Auth::user()->hasRole('admin')) {
        $accountLabel = 'Panel principal';
        $accountIsLogout = false;
        $accountHref = route('admin.principal');
    } elseif (Auth::check() && Auth::user()->hasRole('alumno')) {
        $accountLabel = 'Cerrar sesión';
        $accountIsLogout = true;
        $accountHref = route('logout');
    } elseif (Auth::check() && Auth::user()->hasRole('biblioteca')) {
        $accountLabel = 'Panel bibliotecario';
        $accountIsLogout = false;
        $accountHref = route('bibliotecario');
    } elseif (Auth::check() && Auth::user()->hasRole('videos')) {
        $accountLabel = 'Administración de videos';
        $accountIsLogout = false;
        $accountHref = route('adminVideos');
    } elseif (Auth::check() && Auth::user()->hasRole('audios')) {
        $accountLabel = 'Administración de canciones';
        $accountIsLogout = false;
        $accountHref = route('adminCanciones');
    } elseif (Auth::check() && Auth::user()->hasRole('sisicha')) {
        $accountLabel = 'Sisicha';
        $accountIsLogout = false;
        $accountHref = route('adminSisicha');
    } elseif (Auth::check() && Auth::user()->hasRole('kawsay')) {
        $accountLabel = 'Panel Kawsay';
        $accountIsLogout = false;
        $accountHref = route('adminKawsay');
    } else {
        $accountLabel = 'Ingresar';
        $accountIsLogout = false;
        $accountHref = route('home');
    }
@endphp
<header class="puklla-main-nav">
    <nav class="menuPuklla" aria-label="Menú principal">
        <div class="menu-container">
            <div class="menu-bar">
                <a href="{{ $navItems[0]['href'] }}" class="menu-brand">
                    @if ($isBiblioteca)
                        <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Logo EESP Pukllasunchis"
                            class="logo" width="168" decoding="async">
                    @elseif ($isKawsay)
                        <img src="{{ asset('img/kawsay.png') }}" alt="Logo Kawsay"
                            class="logo" width="168" decoding="async">
                    @elseif ($isRadio || $isSisipedia)
                        {{-- Arriba del todo el menú va transparente sobre la foto del hero (ver
                             regla :not(.is-scrolled) en estilos.css) y el logo a color se perdía
                             contra fondos con mucho naranja/marrón — se muestra la versión blanca
                             solo mientras no se haya scrolleado; al bajar, el menú vuelve a su
                             fondo claro y ahí sí se ve bien el logo a color de siempre. --}}
                        <img src="{{ asset('img/logo-blanco-radio.png') }}" alt="Logo Radio Pukllasunchis"
                            class="logo logo--on-top" width="168" decoding="async">
                        <img src="{{ asset('img/logo-radio.png') }}" alt="Logo Radio Pukllasunchis"
                            class="logo logo--on-scroll" width="168" decoding="async">
                    @else
                        <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Asociación Pukllasunchis"
                            class="logo" width="168" decoding="async">
                    @endif
                </a>

                <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false"
                    aria-controls="menuSidebar">
                    <span class="hamburger-line" aria-hidden="true"></span>
                    <span class="hamburger-line" aria-hidden="true"></span>
                    <span class="hamburger-line" aria-hidden="true"></span>
                </button>
            </div>

            <div class="menu-overlay" id="menuOverlay" aria-hidden="true"></div>

            <div class="menu-sidebar" id="menuSidebar" role="navigation" aria-label="Menú principal">
                <div class="menu-sidebar-header">
                    <span class="menu-sidebar-title">Menú</span>
                    <button type="button" class="menu-close" id="menuClose" aria-label="Cerrar menú">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="menu-sidebar-scroll">
                    <ul class="menu-nav menu-nav--primary">
                        @foreach ($navItems as $item)
                            <li class="menu-item">
                                <a href="{{ $item['href'] }}"
                                    class="menu-link {{ $item['active'] ? 'is-active' : '' }}"
                                    @if ($item['active']) aria-current="page" @endif>
                                    <i class="fa {{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="menu-actions">
                        {{-- Sin buscador acá: casi todas las vistas públicas ya
                             tienen su propio buscador en el hero. En su lugar,
                             redes sociales con el acento de color de cada
                             proyecto (--project-accent) en vez del logo/color
                             de marca de cada red. Las URLs salen de
                             $socialLinks (arriba, por proyecto). --}}
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social['href'] }}" class="menu-link menu-link--social"
                                title="{{ $social['label'] }}" aria-label="{{ $social['label'] }}"
                                @if ($social['href'] !== '#') target="_blank" rel="noopener" @endif>
                                <i class="fa-brands {{ $social['icon'] }}" aria-hidden="true"></i>
                                <span class="menu-link__label">{{ $social['label'] }}</span>
                            </a>
                        @endforeach

                        @if ($accountIsLogout)
                            <a href="{{ $accountHref }}" class="menu-link menu-link--login" title="{{ $accountLabel }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span class="menu-link__label">{{ $accountLabel }}</span>
                            </a>
                            <form id="logout-form" action="{{ $accountHref }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        @else
                            <a href="{{ $accountHref }}" class="menu-link menu-link--login" title="{{ $accountLabel }}">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                <span class="menu-link__label">{{ $accountLabel }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const menuSidebar = document.getElementById('menuSidebar');
            const menuOverlay = document.getElementById('menuOverlay');
            const menuClose = document.getElementById('menuClose');

            // Toggle menú
            function toggleMenu() {
                const open = !menuSidebar.classList.contains('active');
                menuToggle.classList.toggle('active', open);
                menuSidebar.classList.toggle('active', open);
                menuOverlay.classList.toggle('active', open);
                menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                menuOverlay.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.classList.toggle('menu-drawer-open', open);
                document.body.style.overflow = open ? 'hidden' : '';

                // Entrada escalonada de los ítems (solo móvil, solo GSAP si está cargado)
                if (open && window.innerWidth <= 767 && typeof gsap !== 'undefined') {
                    const items = menuSidebar.querySelectorAll('.menu-sidebar-scroll > *');
                    gsap.fromTo(items, { x: -14, opacity: 0 }, {
                        x: 0, opacity: 1, duration: .35, stagger: .05, ease: 'power2.out', overwrite: true
                    });
                }
            }

            // Cabecera transparente sobre el hero (cualquier vista con hero, antes del scroll)
            const mainNav = document.querySelector('.puklla-main-nav');
            if (mainNav) {
                const scrollThreshold = 60;
                let onNavScroll = function() {
                    const scrolled = (window.scrollY || window.pageYOffset) > scrollThreshold;
                    mainNav.classList.toggle('is-scrolled', scrolled);
                };
                window.addEventListener('scroll', onNavScroll, { passive: true });
                onNavScroll();
            }

            // Event listeners
            menuToggle?.addEventListener('click', toggleMenu);
            menuClose?.addEventListener('click', toggleMenu);
            menuOverlay?.addEventListener('click', toggleMenu);

            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && menuSidebar?.classList.contains('active')) {
                    toggleMenu();
                }
            });

            // Resize: cerrar panel al pasar a escritorio
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 767) {
                        menuSidebar?.classList.remove('active');
                        menuToggle?.classList.remove('active');
                        menuOverlay?.classList.remove('active');
                        menuToggle?.setAttribute('aria-expanded', 'false');
                        menuOverlay?.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('menu-drawer-open');
                        document.body.style.overflow = '';
                    }
                }, 200);
            }, {
                passive: true
            });
        });
    </script>
</header>
