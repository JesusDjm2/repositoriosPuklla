<nav id="sidebar">
    <div class="p-4">
        <div class="sidebar-brand">
            <h1>
                @role('sisicha')
                    <a href="{{ route('sisipedia.categories.index') }}" class="logo">
                        <img src="{{ asset('img/Asociacion-logo-blanco.png') }}" width="100%" alt="">
                    </a>
                @else
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ asset('img/Asociacion-logo-blanco.png') }}" width="100%" alt="">
                    </a>
                @endrole
            </h1>
        </div>
        <ul class="list-unstyled components">
            @php
                // Qué pantallas tiene cada rol, tanto para el menú "Proyectos" del admin
                // (ver MenuAdmin::render()) como para el menú propio de cada rol más abajo.
                // El nombre/orden de cada proyecto y qué roles le pertenecen sí vienen de la
                // tabla proyectos (admin/proyectos): crear un proyecto o mover un rol de uno
                // a otro se refleja solo. Lo que NO puede salir de la base de datos es qué
                // pantallas tiene cada rol (Libros, Categorías, ...), cómo se agrupan entre
                // sí, ni el "panel" (mini-dashboard con estadísticas) al que ese rol aterriza
                // — cada uno es código a mano de un controlador/vista distinta, así que ese
                // agrupamiento sigue viviendo aquí. Un rol puede tener 'items' (lista plana)
                // o 'grupos' (subsecciones con nombre propio, cuando sus tablas se relacionan
                // en más de un bloque temático, como Biblioteca digital/física).
                $rolesMenu = [
                    'biblioteca' => [
                        'label' => 'Biblioteca',
                        'icono' => 'fa-book',
                        'panel' => ['ruta' => 'bibliotecario', 'label' => 'Panel de Biblioteca'],
                        'grupos' => [
                            'Biblioteca digital' => [
                                'icono' => 'fa-laptop',
                                'items' => [
                                    ['ruta' => 'secciones.index', 'label' => 'Secciones'],
                                    ['ruta' => 'categorias.index', 'label' => 'Categorías'],
                                    ['ruta' => 'libros.index', 'label' => 'Libros'],
                                ],
                            ],
                            'Biblioteca física' => [
                                'icono' => 'fa-archive',
                                'items' => [
                                    ['ruta' => 'biblioteca-fisica.categorias.index', 'label' => 'Categorías físicas'],
                                    ['ruta' => 'biblioteca-fisica.libros.index', 'label' => 'Libros físicos'],
                                    ['ruta' => 'biblioteca-fisica.prestamos.index', 'label' => 'Préstamos'],
                                ],
                            ],
                        ],
                    ],
                    'sisicha' => [
                        'label' => 'Sisichakunay',
                        'icono' => 'fa-th-list',
                        'panel' => ['ruta' => 'adminSisicha', 'label' => 'Panel Sisichakunay'],
                        'items' => [
                            ['ruta' => 'sisipedia.categories.index', 'label' => 'Registros'],
                            ['ruta' => 'sisipedia.aportaciones.index', 'label' => 'Aportaciones'],
                        ],
                    ],
                    'audios' => [
                        'label' => 'Canciones',
                        'icono' => 'fa-music',
                        'panel' => ['ruta' => 'adminCanciones', 'label' => 'Panel de Canciones'],
                        'items' => [
                            ['ruta' => 'canciones.index', 'label' => 'Canciones'],
                            ['ruta' => 'cancionescat.index', 'label' => 'Categorías de canciones'],
                        ],
                    ],
                    'videos' => [
                        'label' => 'Videos',
                        'icono' => 'fa-video-camera',
                        'panel' => ['ruta' => 'adminVideos', 'label' => 'Panel de Videos'],
                        'items' => [
                            ['ruta' => 'videos.index', 'label' => 'Videos'],
                            ['ruta' => 'videoscat.index', 'label' => 'Categorías de video'],
                        ],
                    ],
                    'kawsay' => [
                        'label' => 'Kawsay',
                        'icono' => 'fa-leaf',
                        'panel' => ['ruta' => 'adminKawsay', 'label' => 'Panel Kawsay'],
                        'items' => [
                            ['ruta' => 'kawsayareas.index', 'label' => 'Áreas'],
                            ['ruta' => 'kawsaytemas.index', 'label' => 'Temas'],
                            ['ruta' => 'kawsayarchivos.index', 'label' => 'Archivos'],
                            ['ruta' => 'kawsaylibros.index', 'label' => 'Libros'],
                            ['ruta' => 'kawsaycategorias.index', 'label' => 'Categorías'],
                            ['ruta' => 'kawsayvideos.index', 'label' => 'Videos'],
                            ['ruta' => 'kawsayvideoscat.index', 'label' => 'Categorías de video'],
                        ],
                    ],
                ];

                // Convierte la definición de un rol ('items' plano o 'grupos' con nombre) en
                // una lista uniforme de secciones [{titulo, icono, items}] que consume
                // livewire.partials.menu-secciones. titulo=null → se listan sin sub-toggle.
                $seccionesDesdeRol = function (array $definicionRol): array {
                    if (isset($definicionRol['grupos'])) {
                        return collect($definicionRol['grupos'])
                            ->map(fn ($grupo, $nombreGrupo) => [
                                'titulo' => $nombreGrupo,
                                'icono' => $grupo['icono'] ?? null,
                                'items' => $grupo['items'],
                            ])
                            ->values()
                            ->all();
                    }

                    return [['titulo' => null, 'icono' => null, 'items' => $definicionRol['items'] ?? []]];
                };
            @endphp
            @role('admin')
                @php
                    // Ícono del proyecto en el menú. Un proyecto nuevo que no esté en este
                    // mapa cae en el ícono genérico de abajo (no requiere código para existir
                    // en el menú, solo para tener un ícono a medida).
                    $proyectoIconos = [
                        'eesp-pukllasunchis' => 'fa-book',
                        'radio-pukllasunchis' => 'fa-broadcast-tower',
                        'kawsay' => 'fa-leaf',
                    ];

                    // Proyectos con más de un rol (p. ej. Radio Pukllasunchis) que ya
                    // tienen una vista propia con pestañas (ver admin/radio.blade.php)
                    // en vez de tener que repartirse en un desplegable de N enlaces.
                    // Un proyecto multi-rol que no esté aquí cae en el enlace directo
                    // al primer panel (ver más abajo) — nunca en un submenú.
                    $proyectoPanelCombinado = [
                        'radio-pukllasunchis' => ['ruta' => 'adminRadio'],
                    ];
                @endphp
                <li class="{{ request()->routeIs('admin.principal') ? 'active' : '' }}">
                    <a href="{{ route('admin.principal') }}"><i class="fa fa-th-large"></i> Panel principal</a>
                </li>
                <li class="{{ request()->routeIs('proyectos.*') ? 'active' : '' }}">
                    <a href="{{ route('proyectos.index') }}"><i class="fa fa-sitemap"></i> Proyectos</a>
                </li>

                @if ($proyectos->isNotEmpty())
                    <li class="sidebar-section-title">Proyectos</li>
                    @foreach ($proyectos as $proyecto)
                        @php
                            // Un proyecto puede agrupar más de un rol — p. ej. Radio
                            // Pukllasunchis junta videos + audios + sisicha en un solo
                            // registro de la tabla proyectos — y cada rol con panel propio
                            // aporta su pantalla. Con 1 solo panel el link va directo (su
                            // panel ya lista sus pantallas); con más de uno, es un
                            // desplegable con un link por panel, para no perder acceso a
                            // ninguno (antes de quedarse con uno solo vía first(), acá
                            // vivía el bug: Radio Pukllasunchis mostraba solo Sisichakunay
                            // y escondía Canciones y Videos del sidebar).
                            $definicionesProyecto = collect($rolesMenu)
                                ->filter(fn ($definicion, $nombreRol) => $proyecto->roles->contains('name', $nombreRol))
                                ->filter(fn ($definicion) => isset($definicion['panel']) && \Illuminate\Support\Facades\Route::has($definicion['panel']['ruta']))
                                ->values();
                            $combinado = $proyectoPanelCombinado[$proyecto->slug] ?? null;
                            $combinado = $combinado && \Illuminate\Support\Facades\Route::has($combinado['ruta']) ? $combinado : null;
                            $rutasProyecto = $definicionesProyecto
                                ->flatMap(fn ($definicion) => collect($seccionesDesdeRol($definicion))
                                    ->flatMap(fn ($seccion) => collect($seccion['items'])->pluck('ruta'))
                                    ->push($definicion['panel']['ruta']))
                                ->push($combinado['ruta'] ?? null)
                                ->filter()
                                ->all();
                        @endphp
                        <li class="{{ $rutasProyecto && request()->routeIs(...$rutasProyecto) ? 'active' : '' }}">
                            @if ($definicionesProyecto->isEmpty())
                                <a href="{{ route('proyectos.edit', $proyecto) }}">
                                    <i class="fa {{ $proyectoIconos[$proyecto->slug] ?? 'fa-folder-open' }}"></i>
                                    {{ $proyecto->nombre }}
                                    <span class="menu-note">Sin configurar</span>
                                </a>
                            @elseif ($definicionesProyecto->count() === 1)
                                <a href="{{ route($definicionesProyecto->first()['panel']['ruta']) }}">
                                    <i class="fa {{ $proyectoIconos[$proyecto->slug] ?? ($definicionesProyecto->first()['icono'] ?? 'fa-folder-open') }}"></i>
                                    {{ $proyecto->nombre }}
                                </a>
                            @else
                                {{-- Más de un rol, sin desplegable: si hay un panel combinado
                                     con pestañas para este proyecto se enlaza ahí directo; si
                                     no, al primer panel disponible (nunca a un submenú). --}}
                                <a href="{{ route($combinado['ruta'] ?? $definicionesProyecto->first()['panel']['ruta']) }}">
                                    <i class="fa {{ $proyectoIconos[$proyecto->slug] ?? 'fa-folder-open' }}"></i>
                                    {{ $proyecto->nombre }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                @endif

                <li class="sidebar-section-title">Usuarios</li>
                <li class="{{ request()->routeIs('admin.index', 'alumnos') ? 'active' : '' }}">
                    <a href="{{ route('admin.index') }}"><i class="fa fa-building"></i> Usuarios</a>
                </li>

                <li class="sidebar-section-title">Sistema</li>
                <li class="{{ request()->routeIs('backups.*') ? 'active' : '' }}">
                    <a href="{{ route('backups.index') }}"><i class="fa fa-database"></i> Backups</a>
                </li>
            @else
                {{-- Cualquier rol que no sea admin: mismo patrón que arriba, a partir de
                     la misma definición de $rolesMenu — sin duplicar nada por rol. El
                     panel de cada rol (ver admin/bibliotecario.blade.php y hermanos) ya
                     lista sus pantallas como module-cards (agrupadas en tabs cuando el
                     rol tiene más de un grupo, p. ej. Biblioteca digital/física), así que
                     el link va directo ahí en vez de repetir esa jerarquía en el sidebar. --}}
                @foreach ($rolesMenu as $nombreRol => $definicion)
                    @role($nombreRol)
                        @php
                            $idMenuRol = 'rolMenu' . $nombreRol;
                            $rutaPanelRol = $definicion['panel']['ruta'] ?? null;
                            $rutaPanelRol = $rutaPanelRol && \Illuminate\Support\Facades\Route::has($rutaPanelRol)
                                ? $rutaPanelRol
                                : null;
                            $rutasRol = collect($seccionesDesdeRol($definicion))
                                ->flatMap(fn ($seccion) => collect($seccion['items'])->pluck('ruta'))
                                ->push($rutaPanelRol)
                                ->filter()
                                ->all();
                        @endphp
                        <li class="{{ $rutasRol && request()->routeIs(...$rutasRol) ? 'active' : '' }}">
                            @if ($rutaPanelRol)
                                <a href="{{ route($rutaPanelRol) }}">
                                    <i class="fa {{ $definicion['icono'] }}"></i> {{ $definicion['panel']['label'] }}
                                </a>
                            @else
                                {{-- Sin panel configurado: se listan sus secciones directo para
                                     no dejar al rol sin acceso a sus pantallas. --}}
                                <a href="#{{ $idMenuRol }}" data-toggle="collapse" aria-expanded="false"
                                    class="dropdown-toggle">
                                    <i class="fa {{ $definicion['icono'] }}"></i> {{ $definicion['label'] }}
                                </a>
                                <ul class="collapse list-unstyled" id="{{ $idMenuRol }}">
                                    @include('livewire.partials.menu-secciones', [
                                        'secciones' => $seccionesDesdeRol($definicion),
                                        'idPrefix' => $idMenuRol,
                                    ])
                                </ul>
                            @endif
                        </li>
                    @endrole
                @endforeach
            @endrole
        </ul>
        <div class="footer">
            <p class="text-center">
                Copyright
                &copy;
                <script>
                    document.write(new Date().getFullYear());
                </script> <br> Made by <a href="" target="_blank">DJM2 </a>
            </p>
        </div>
    </div>
</nav>
