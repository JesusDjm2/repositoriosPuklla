@php
    $inGroup = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
    $user = auth()->user();
    $rolActual = $user?->getRoleNames()->first();
    $nombreCompleto = $user?->name ?? 'Usuario';
    $nombreCorto = explode(' ', trim($nombreCompleto))[0];
    $inicialAvatar = strtoupper(substr($nombreCorto, 0, 1));

    // Página pública del proyecto según el rol del encargado o contexto actual del admin
    $proyectoUrl = null;
    $proyectoNombre = null;

    if ($user) {
        if ($user->hasRole('biblioteca')) {
            $proyectoUrl = route('eesppukllasunchis');
            $proyectoNombre = 'Web EESP Pukllasunchis';
        } elseif ($user->hasRole('sisicha') || $user->hasRole('audios') || $user->hasRole('videos')) {
            $proyectoUrl = route('index');
            $proyectoNombre = 'Web Sisipedia / Radio';
        } elseif ($user->hasRole('kawsay')) {
            $proyectoUrl = route('kawsay');
            $proyectoNombre = 'Web Portal Kawsay';
        } elseif ($user->hasRole('admin')) {
            if ($inGroup(['bibliotecario', 'libros.*', 'categorias.*', 'secciones.*', 'biblioteca-fisica.*', 'admin.taqe.*'])) {
                $proyectoUrl = route('eesppukllasunchis');
                $proyectoNombre = 'Web EESP Pukllasunchis';
            } elseif ($inGroup(['adminRadio', 'adminSisicha', 'sisipedia.*', 'canciones.*', 'cancionescat.*', 'videos.*', 'videoscat.*'])) {
                $proyectoUrl = route('index');
                $proyectoNombre = 'Web Sisipedia / Radio';
            } elseif ($inGroup(['adminKawsay', 'kawsayareas.*', 'kawsaytemas.*', 'kawsaycomentarios.*', 'kawsayarchivos.*', 'kawsaylibros.*', 'kawsaycategorias.*'])) {
                $proyectoUrl = route('kawsay');
                $proyectoNombre = 'Web Portal Kawsay';
            }
        }
    }
@endphp
<nav id="sidebar">
    <div class="bv-sidebar-brand">
        <a href="{{ route('hub') }}" class="logo">
            <img src="{{ asset('img/Asociacion-logo-blanco.png') }}" width="100%" alt="Asociación Pukllasunchis">
        </a>
    </div>

    <div class="bv-sidebar-user">
        <div class="bv-sidebar-user__avatar">{{ $inicialAvatar }}</div>
        <div class="bv-sidebar-user__info">
            <span class="bv-sidebar-user__name">{{ $nombreCorto }}</span>
            @if ($rolActual)
                <span class="bv-sidebar-user__role">{{ ucfirst($rolActual) }}</span>
            @endif
        </div>
        <button type="button" id="bvThemeToggle" class="bv-theme-toggle" data-theme-toggle title="Cambiar tema"
            aria-label="Cambiar entre tema claro y oscuro">
            <i class="fa fa-moon-o" data-theme-icon="dark"></i>
            <i class="fa fa-sun-o" data-theme-icon="light"></i>
        </button>
    </div>

    <div class="bv-sidebar-nav">
        <ul class="list-unstyled components mb-4">
            {{-- SECCIÓN PARA ROL ADMIN --}}
            @if (auth()->user()->hasRole('admin'))
                <li class="sidebar-menu-label">General</li>
                <li class="{{ request()->routeIs('admin.principal') ? 'active' : '' }}">
                    <a href="{{ route('admin.principal') }}"><i class="fa fa-th-large"></i> Panel principal</a>
                </li>
                <li class="{{ request()->routeIs('proyectos.*') ? 'active' : '' }}">
                    <a href="{{ route('proyectos.index') }}"><i class="fa fa-sitemap"></i> Proyectos Hub</a>
                </li>

                <li class="sidebar-menu-label">Biblioteca EESP</li>
                <li class="{{ request()->routeIs('bibliotecario') ? 'active' : '' }}">
                    <a href="{{ route('bibliotecario') }}"><i class="fa fa-tachometer"></i> Panel Biblioteca</a>
                </li>

                @php $openBibVirt = $inGroup(['libros.*', 'categorias.*', 'secciones.*']); @endphp
                <li>
                    <a href="#admBibVirt" data-toggle="collapse" aria-expanded="{{ $openBibVirt ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-laptop"></i> Biblioteca virtual</a>
                    <ul class="collapse list-unstyled {{ $openBibVirt ? 'show' : '' }}" id="admBibVirt">
                        <li class="{{ request()->routeIs('libros.*') ? 'active' : '' }}">
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li class="{{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                        <li class="{{ request()->routeIs('secciones.*') ? 'active' : '' }}">
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>
                    </ul>
                </li>

                @php
                    $openBibFis = $inGroup([
                        'biblioteca-fisica.categorias.*',
                        'biblioteca-fisica.libros.*',
                        'biblioteca-fisica.prestamos.*',
                        'biblioteca-fisica.material-categorias.*',
                        'biblioteca-fisica.materiales.*',
                    ]);
                @endphp
                <li>
                    <a href="#admBibFis" data-toggle="collapse" aria-expanded="{{ $openBibFis ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-archive"></i> Biblioteca física</a>
                    <ul class="collapse list-unstyled {{ $openBibFis ? 'show' : '' }}" id="admBibFis">
                        <li class="{{ request()->routeIs('biblioteca-fisica.categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.libros.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.prestamos.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.prestamos.index') }}"><span class="flecha-admin">→</span> Préstamos</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.materiales.*', 'biblioteca-fisica.material-categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.materiales.index') }}"><span class="flecha-admin">→</span> Material didáctico</a>
                        </li>
                    </ul>
                </li>

                @php $openBibTaqe = $inGroup(['admin.taqe.*']); @endphp
                <li>
                    <a href="#admBibTaqe" data-toggle="collapse" aria-expanded="{{ $openBibTaqe ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-graduation-cap"></i> Taq'e (Tesis)</a>
                    <ul class="collapse list-unstyled {{ $openBibTaqe ? 'show' : '' }}" id="admBibTaqe">
                        <li class="{{ request()->routeIs('admin.taqe.trabajos.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.taqe.trabajos.index') }}"><span class="flecha-admin">→</span> Trabajos</a>
                        </li>
                        <li class="{{ request()->routeIs('admin.taqe.categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.taqe.categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>

                @php $openVideos = $inGroup(['videos.*', 'videoscat.*']); @endphp
                <li class="sidebar-menu-label">Multimedia EESP</li>
                <li>
                    <a href="#admVideos" data-toggle="collapse" aria-expanded="{{ $openVideos ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-video-camera"></i> Videos EESP</a>
                    <ul class="collapse list-unstyled {{ $openVideos ? 'show' : '' }}" id="admVideos">
                        <li class="{{ request()->routeIs('videos.index') ? 'active' : '' }}">
                            <a href="{{ route('videos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li class="{{ request()->routeIs('videoscat.*') ? 'active' : '' }}">
                            <a href="{{ route('videoscat.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>

                @php $openCanciones = $inGroup(['canciones.*', 'cancionescat.*']); @endphp
                <li>
                    <a href="#admCanciones" data-toggle="collapse" aria-expanded="{{ $openCanciones ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-music"></i> Canciones & Audios</a>
                    <ul class="collapse list-unstyled {{ $openCanciones ? 'show' : '' }}" id="admCanciones">
                        <li class="{{ request()->routeIs('canciones.index') ? 'active' : '' }}">
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li class="{{ request()->routeIs('cancionescat.*') ? 'active' : '' }}">
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>

                @if (!empty($proyectos) && $proyectos->isNotEmpty())
                    <li class="sidebar-menu-label">Otros Proyectos Hub</li>
                    @foreach ($proyectos as $proyecto)
                        @if ($proyecto->slug !== 'eesp-pukllasunchis')
                            @php
                                $linkUrl = match ($proyecto->slug) {
                                    'radio-pukllasunchis' => route('adminRadio'),
                                    'kawsay' => route('adminKawsay'),
                                    default => route('proyectos.edit', $proyecto),
                                };
                                $iconoProy = match ($proyecto->slug) {
                                    'radio-pukllasunchis' => 'fa-podcast',
                                    'kawsay' => 'fa-leaf',
                                    default => 'fa-folder-open',
                                };
                            @endphp
                            <li>
                                <a href="{{ $linkUrl }}">
                                    <i class="fa {{ $iconoProy }}"></i> {{ $proyecto->nombre }}
                                </a>
                            </li>
                            @if ($proyecto->slug === 'kawsay' && \Illuminate\Support\Facades\Route::has('kawsaycomentarios.index'))
                                <li class="{{ request()->routeIs('kawsaycomentarios.index') ? 'active' : '' }}">
                                    <a href="{{ route('kawsaycomentarios.index') }}">
                                        <i class="fa fa-comments"></i> Comentarios Kawsay
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endif

                <li class="sidebar-menu-label">Administración</li>
                <li class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.index') }}"><i class="fa fa-users"></i> Administradores</a>
                </li>
                <li class="{{ request()->routeIs('alumnos') ? 'active' : '' }}">
                    <a href="{{ route('alumnos') }}"><i class="fa fa-graduation-cap"></i> Alumnos</a>
                </li>
                <li class="{{ request()->routeIs('backups.*') ? 'active' : '' }}">
                    <a href="{{ route('backups.index') }}"><i class="fa fa-database"></i> Backups</a>
                </li>

            {{-- SECCIÓN PARA ROL BIBLIOTECA (EXACTA A BIBLIOTECA STANDALONE) --}}
            @elseif (auth()->user()->hasRole('biblioteca'))
                <li class="sidebar-menu-label">Biblioteca EESP</li>
                <li class="{{ request()->routeIs('bibliotecario') ? 'active' : '' }}">
                    <a href="{{ route('bibliotecario') }}"><i class="fa fa-tachometer"></i> Panel Biblioteca</a>
                </li>

                @php $openBibVirt = $inGroup(['secciones.*', 'categorias.*', 'libros.*']); @endphp
                <li>
                    <a href="#bibVirt" data-toggle="collapse" aria-expanded="{{ $openBibVirt ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-laptop"></i> Biblioteca virtual</a>
                    <ul class="collapse list-unstyled {{ $openBibVirt ? 'show' : '' }}" id="bibVirt">
                        <li class="{{ request()->routeIs('secciones.*') ? 'active' : '' }}">
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>
                        <li class="{{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                        <li class="{{ request()->routeIs('libros.*') ? 'active' : '' }}">
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                    </ul>
                </li>

                @php
                    $openBibFis = $inGroup([
                        'biblioteca-fisica.categorias.*',
                        'biblioteca-fisica.libros.*',
                        'biblioteca-fisica.prestamos.*',
                        'biblioteca-fisica.material-categorias.*',
                        'biblioteca-fisica.materiales.*',
                    ]);
                @endphp
                <li>
                    <a href="#bibFis" data-toggle="collapse" aria-expanded="{{ $openBibFis ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-archive"></i> Biblioteca física</a>
                    <ul class="collapse list-unstyled {{ $openBibFis ? 'show' : '' }}" id="bibFis">
                        <li class="{{ request()->routeIs('biblioteca-fisica.categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.libros.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.prestamos.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.prestamos.index') }}"><span class="flecha-admin">→</span> Préstamos</a>
                        </li>
                        <li class="{{ request()->routeIs('biblioteca-fisica.materiales.*', 'biblioteca-fisica.material-categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('biblioteca-fisica.materiales.index') }}"><span class="flecha-admin">→</span> Material didáctico</a>
                        </li>
                    </ul>
                </li>

                @php $openBibTaqe = $inGroup(['admin.taqe.*']); @endphp
                <li>
                    <a href="#bibTaqe" data-toggle="collapse" aria-expanded="{{ $openBibTaqe ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-graduation-cap"></i> Taq'e</a>
                    <ul class="collapse list-unstyled {{ $openBibTaqe ? 'show' : '' }}" id="bibTaqe">
                        <li class="{{ request()->routeIs('admin.taqe.trabajos.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.taqe.trabajos.index') }}"><span class="flecha-admin">→</span> Trabajos</a>
                        </li>
                        <li class="{{ request()->routeIs('admin.taqe.categorias.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.taqe.categorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>

            {{-- SECCIÓN PARA ROL SISICHA (RADIO & SISIPEDIA) --}}
            @elseif (auth()->user()->hasRole('sisicha'))
                <li class="sidebar-menu-label">Radio & Sisichakunay</li>
                <li class="{{ request()->routeIs('adminRadio', 'adminSisicha') ? 'active' : '' }}">
                    <a href="{{ route('adminRadio') }}"><i class="fa fa-tachometer"></i> Panel Radio / Sisicha</a>
                </li>
                <li class="{{ request()->routeIs('sisipedia.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.categories.index') }}"><i class="fa fa-sitemap"></i> Registros Sisipedia</a>
                </li>
                <li class="{{ request()->routeIs('sisipedia.aportaciones.*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.aportaciones.index') }}"><i class="fa fa-comments"></i> Aportaciones</a>
                </li>
                <li class="{{ request()->routeIs('sisipedia.busquedas.*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.busquedas.sin-resultados') }}"><i class="fa fa-search"></i> Búsquedas vacías</a>
                </li>

                @php $openRadioMedia = $inGroup(['canciones.*', 'cancionescat.*', 'videos.*', 'videoscat.*']); @endphp
                <li>
                    <a href="#sisiMedia" data-toggle="collapse" aria-expanded="{{ $openRadioMedia ? 'true' : 'false' }}"
                        class="dropdown-toggle"><i class="fa fa-play-circle"></i> Multimedia Radio</a>
                    <ul class="collapse list-unstyled {{ $openRadioMedia ? 'show' : '' }}" id="sisiMedia">
                        <li class="{{ request()->routeIs('canciones.index') ? 'active' : '' }}">
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li class="{{ request()->routeIs('cancionescat.*') ? 'active' : '' }}">
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span> Categorías Canciones</a>
                        </li>
                        <li class="{{ request()->routeIs('videos.index') ? 'active' : '' }}">
                            <a href="{{ route('videos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li class="{{ request()->routeIs('videoscat.*') ? 'active' : '' }}">
                            <a href="{{ route('videoscat.index') }}"><span class="flecha-admin">→</span> Categorías Videos</a>
                        </li>
                    </ul>
                </li>

            {{-- SECCIÓN PARA ROL KAWSAY --}}
            @elseif (auth()->user()->hasRole('kawsay'))
                <li class="sidebar-menu-label">Kawsay</li>
                <li class="{{ request()->routeIs('adminKawsay') ? 'active' : '' }}">
                    <a href="{{ route('adminKawsay') }}"><i class="fa fa-leaf"></i> Panel Kawsay</a>
                </li>
                <li class="{{ request()->routeIs('kawsayareas.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsayareas.index') }}"><i class="fa fa-sitemap"></i> Áreas</a>
                </li>
                <li class="{{ request()->routeIs('kawsaytemas.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsaytemas.index') }}"><i class="fa fa-book"></i> Temas</a>
                </li>
                <li class="{{ request()->routeIs('kawsaycomentarios.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsaycomentarios.index') }}"><i class="fa fa-comments"></i> Comentarios</a>
                </li>
                <li class="{{ request()->routeIs('kawsayarchivos.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsayarchivos.index') }}"><i class="fa fa-file-text"></i> Archivos</a>
                </li>
                <li class="{{ request()->routeIs('kawsaylibros.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsaylibros.index') }}"><i class="fa fa-book"></i> Libros</a>
                </li>
                <li class="{{ request()->routeIs('kawsaycategorias.*') ? 'active' : '' }}">
                    <a href="{{ route('kawsaycategorias.index') }}"><i class="fa fa-tags"></i> Categorías</a>
                </li>

            {{-- SECCIÓN PARA ROL VIDEOS --}}
            @elseif (auth()->user()->hasRole('videos'))
                <li class="sidebar-menu-label">Videos</li>
                <li class="{{ request()->routeIs('videos.index') ? 'active' : '' }}">
                    <a href="{{ route('videos.index') }}"><i class="fa fa-video-camera"></i> Videos</a>
                </li>
                <li class="{{ request()->routeIs('videoscat.*') ? 'active' : '' }}">
                    <a href="{{ route('videoscat.index') }}"><i class="fa fa-tags"></i> Categorías</a>
                </li>

            {{-- SECCIÓN PARA ROL AUDIOS --}}
            @elseif (auth()->user()->hasRole('audios'))
                <li class="sidebar-menu-label">Canciones</li>
                <li class="{{ request()->routeIs('canciones.index') ? 'active' : '' }}">
                    <a href="{{ route('canciones.index') }}"><i class="fa fa-music"></i> Canciones</a>
                </li>
                <li class="{{ request()->routeIs('cancionescat.*') ? 'active' : '' }}">
                    <a href="{{ route('cancionescat.index') }}"><i class="fa fa-compact-disc"></i> Categorías</a>
                </li>
            @endif
        </ul>
    </div>

    <div class="bv-sidebar-footer">
        @if ($proyectoUrl)
            <a href="{{ $proyectoUrl }}" target="_blank" rel="noopener noreferrer" class="bv-sidebar-link" title="{{ $proyectoNombre }}">
                <i class="fa fa-external-link"></i> {{ $proyectoNombre }}
            </a>
        @endif
        <a href="{{ route('hub') }}" target="_blank" rel="noopener noreferrer" class="bv-sidebar-link">
            <i class="fa fa-home"></i> Página de inicio
        </a>
        <a href="#" class="bv-sidebar-link bv-sidebar-link--danger" id="bvLogoutLink" data-logout-confirm>
            <i class="fa fa-sign-out"></i> {{ __('Salir') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    <div class="bv-sidebar-copy">
        <p class="mb-0">Copyright &copy; <span id="bvSidebarYear">{{ date('Y') }}</span><br>Made by
            <a href="https://www.facebook.com/DjmWebMaster" target="_blank" rel="noopener">DJM2</a>
        </p>
    </div>
</nav>
