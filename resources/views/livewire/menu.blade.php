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

    // Comprobación de página de inicio para mostrar u ocultar el buscador del menú
    $isHome = match ($proyectoActual) {
        'kawsay' => request()->routeIs('kawsay'),
        'sisipedia' => request()->routeIs('index'),
        'eesp' => request()->routeIs('eesppukllasunchis'),
        'radio' => request()->routeIs('cancionesPuklla'),
        default => request()->routeIs('hub'),
    };

    $searchHref = match ($proyectoActual) {
        'kawsay' => route('kawsay.buscar'),
        'sisipedia' => route('sisipedia.buscar'),
        default => route('busqueda'),
    };

    $searchPlaceholder = match ($proyectoActual) {
        'kawsay' => 'Buscar en Kawsay: temas, fichas, videos, fotos…',
        'sisipedia' => 'Buscar en Sisipedia…',
        default => 'Buscar en todos los registros…',
    };

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

                <div class="menu-bar-actions">
                    @if (!$isHome)
                        <button type="button" class="menu-bar-search" id="menuBarSearchToggle"
                            aria-label="Abrir buscador" title="Buscar" aria-expanded="false" aria-controls="menuSearchModal">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    @endif

                    <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false"
                        aria-controls="menuSidebar">
                        <span class="hamburger-line" aria-hidden="true"></span>
                        <span class="hamburger-line" aria-hidden="true"></span>
                        <span class="hamburger-line" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            @if (!$isHome)
                {{-- Modal Buscador Spotlight / Nivel Senior --}}
                <div class="puklla-search-modal" id="menuSearchModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="pukllaSearchModalTitle">
                    <div class="puklla-search-modal__backdrop" id="menuSearchBackdrop"></div>
                    <div class="puklla-search-modal__dialog">
                        <div class="puklla-search-modal__card">
                            <header class="puklla-search-modal__header">
                                <span class="puklla-search-modal__eyebrow" id="pukllaSearchModalTitle">
                                    <i class="fa fa-compass" aria-hidden="true"></i> Explorar Repositorio
                                </span>
                                <button type="button" class="puklla-search-modal__close" id="menuSearchClose" aria-label="Cerrar buscador (Esc)">
                                    <span class="puklla-search-modal__kbd">ESC</span>
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </header>

                            <form action="{{ $searchHref }}" method="GET" role="search" class="puklla-search-modal__form">
                                <div class="puklla-search-modal__input-wrap">
                                    <i class="fa fa-search puklla-search-modal__icon" aria-hidden="true"></i>
                                    <input type="search" name="query" id="menuSearchInput" class="puklla-search-modal__input"
                                        placeholder="{{ $searchPlaceholder }}" minlength="3" maxlength="120"
                                        autocomplete="off" enterkeyhint="search" required>
                                    <button type="submit" class="puklla-search-modal__submit" aria-label="Buscar">
                                        <span>Buscar</span>
                                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </form>

                            <footer class="puklla-search-modal__footer">
                                <div class="puklla-search-modal__hints">
                                    <span class="puklla-search-modal__hint"><kbd>↵</kbd> Buscar</span>
                                    <span class="puklla-search-modal__hint"><kbd>ESC</kbd> Cerrar</span>
                                </div>
                                <div class="puklla-search-modal__badge">
                                    <span class="puklla-search-modal__dot"></span>
                                    <span>{{ $isKawsay ? 'Kawsay' : ($isSisipedia ? 'Sisipedia' : ($isBiblioteca ? 'Biblioteca' : 'Pukllasunchis')) }}</span>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            @endif

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
                        @if (!$isHome)
                            <button type="button" class="menu-link menu-link--search" id="menuSearchToggle"
                                title="Buscar" aria-label="Abrir buscador" aria-expanded="false" aria-controls="menuSearchModal">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <span class="menu-link__label">Buscar</span>
                            </button>
                        @endif

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
                                aria-label="{{ $accountLabel }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span class="menu-link__label">{{ $accountLabel }}</span>
                            </a>
                            <form id="logout-form" action="{{ $accountHref }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        @else
                            <a href="{{ $accountHref }}" class="menu-link menu-link--login" title="{{ $accountLabel }}"
                                aria-label="{{ $accountLabel }}">
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

            // Modal Buscador Spotlight / Nivel Senior (cuando no estamos en inicio)
            const searchModal = document.getElementById('menuSearchModal');
            const searchBackdrop = document.getElementById('menuSearchBackdrop');
            const searchToggle = document.getElementById('menuSearchToggle');
            const barSearchToggle = document.getElementById('menuBarSearchToggle');
            const searchClose = document.getElementById('menuSearchClose');
            const searchInput = document.getElementById('menuSearchInput');

            function toggleSearchModal(open) {
                if (!searchModal) return;
                const shouldOpen = typeof open === 'boolean' ? open : !searchModal.classList.contains('is-open');
                searchModal.classList.toggle('is-open', shouldOpen);
                searchModal.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
                searchToggle?.classList.toggle('is-active', shouldOpen);
                searchToggle?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                barSearchToggle?.classList.toggle('is-active', shouldOpen);
                barSearchToggle?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

                // Bloquear scroll de la página de fondo mientras el popup está abierto
                if (shouldOpen) {
                    document.body.classList.add('puklla-modal-open');
                    document.body.style.overflow = 'hidden';
                    setTimeout(function() {
                        searchInput?.focus();
                        searchInput?.select();
                    }, 120);
                } else {
                    document.body.classList.remove('puklla-modal-open');
                    if (!menuSidebar?.classList.contains('active')) {
                        document.body.style.overflow = '';
                    }
                }
            }

            searchToggle?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSearchModal(true);
            });

            barSearchToggle?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSearchModal(true);
            });

            searchClose?.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSearchModal(false);
            });

            searchBackdrop?.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSearchModal(false);
            });

            // Atajos de teclado: ESC para cerrar buscador (o menú), Ctrl+K / Cmd+K para abrir buscador
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (searchModal?.classList.contains('is-open')) {
                        toggleSearchModal(false);
                    } else if (menuSidebar?.classList.contains('active')) {
                        toggleMenu();
                    }
                } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    if (searchModal) {
                        e.preventDefault();
                        toggleSearchModal(true);
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
        });
    </script>
</header>
