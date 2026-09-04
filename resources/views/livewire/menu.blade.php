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
            ['label' => 'Libros', 'href' => route('kawsaylibros.lista'), 'active' => request()->routeIs('kawsaylibros.lista', 'kawsaylibros.show', 'kawsaycategorias.show'), 'icon' => 'fa-book'],
            ['label' => 'Videos', 'href' => route('kawsayvideos.lista'), 'active' => request()->routeIs('kawsayvideos.lista', 'kawsayvideos.show', 'kawsayvideoscat.show'), 'icon' => 'fa-video'],
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
                        <img src="{{ asset('img/proyectos/kawsay.logo-blanco.png') }}" alt="Logo Kawsay"
                            class="logo logo--on-top" width="168" decoding="async">
                        <img src="{{ asset('img/kawsay.png') }}" alt="Logo Kawsay"
                            class="logo logo--on-scroll" width="168" decoding="async">
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
                        <button type="button" class="menu-link menu-link--search search-toggle" id="searchToggle"
                            aria-expanded="false" aria-controls="searchBar" title="Buscar">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="menu-link__label">Buscar</span>
                        </button>

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
    <!-- position:fixed; no suma altura: el sticky lo sigue siendo solo la barra del nav -->
    <div class="search-bar" id="searchBar" role="search" aria-label="Buscar en el sitio" aria-hidden="true">
        <div class="search-bar__panel">
            <form action="{{ route('busqueda') }}" method="GET" class="search-form-custom">
                <i class="fas fa-search search-form-custom__icon" aria-hidden="true"></i>
                <input type="search" name="query" placeholder="¿Qué estás buscando?" minlength="3" maxlength="120"
                    autocomplete="off" required>
                <button type="submit" aria-label="Buscar"><i class="fas fa-arrow-right"></i></button>
                <button type="button" class="close-search-bar" aria-label="Cerrar buscador">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const menuSidebar = document.getElementById('menuSidebar');
            const menuOverlay = document.getElementById('menuOverlay');
            const menuClose = document.getElementById('menuClose');
            const searchBar = document.getElementById('searchBar');
            const closeSearchBar = document.querySelector('.close-search-bar');

            // Crear overlay del buscador si no existe
            let searchOverlay = document.querySelector('.search-overlay');
            if (!searchOverlay) {
                searchOverlay = document.createElement('div');
                searchOverlay.className = 'search-overlay';
                document.body.appendChild(searchOverlay);
            }

            const searchToggleBtn = document.getElementById('searchToggle');

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

                // Cerrar buscador si está abierto
                if (open && searchBar.classList.contains('active')) {
                    toggleSearch();
                }

                // Entrada escalonada de los ítems (solo móvil, solo GSAP si está cargado)
                if (open && window.innerWidth <= 767 && typeof gsap !== 'undefined') {
                    const items = menuSidebar.querySelectorAll('.menu-sidebar-scroll > *');
                    gsap.fromTo(items, { x: -14, opacity: 0 }, {
                        x: 0, opacity: 1, duration: .35, stagger: .05, ease: 'power2.out', overwrite: true
                    });
                }
            }

            // Toggle buscador
            function toggleSearch() {
                const open = !searchBar.classList.contains('active');
                searchBar.classList.toggle('active', open);
                searchOverlay.classList.toggle('active', open);
                if (searchToggleBtn) {
                    searchToggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
                searchBar.setAttribute('aria-hidden', open ? 'false' : 'true');
                document.body.classList.toggle('search-open', open);

                if (open) {
                    searchBar.querySelector('input')?.focus();
                }

                // Cerrar menú si está abierto
                if (open && menuSidebar.classList.contains('active')) {
                    menuToggle.classList.remove('active');
                    menuSidebar.classList.remove('active');
                    menuOverlay.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuOverlay.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('menu-drawer-open');
                    document.body.style.overflow = '';
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

            searchToggleBtn?.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSearch();
            });

            closeSearchBar?.addEventListener('click', toggleSearch);
            searchOverlay?.addEventListener('click', toggleSearch);

            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (menuSidebar?.classList.contains('active')) {
                        toggleMenu();
                    }
                    if (searchBar?.classList.contains('active')) {
                        toggleSearch();
                    }
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

            // Búsqueda con Enter
            const searchInput = document.querySelector('.search-bar input');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const query = this.value.trim();
                        if (query.length >= 3) {
                            window.location.href = "{{ route('busqueda') }}?query=" + encodeURIComponent(
                                query);
                        }
                    }
                });
            }
        });
    </script>
</header>
