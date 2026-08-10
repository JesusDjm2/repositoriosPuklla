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
                // pantallas tiene cada rol (Libros, Categorías, ...) ni cómo se agrupan entre
                // sí — cada una es código a mano de un controlador distinto, así que ese
                // agrupamiento sigue viviendo aquí. Un rol puede tener 'items' (lista plana)
                // o 'grupos' (subsecciones con nombre propio, cuando sus tablas se relacionan
                // en más de un bloque temático, como Biblioteca digital/física).
                $rolesMenu = [
                    'biblioteca' => [
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
                        'items' => [
                            ['ruta' => 'sisipedia.categories.index', 'label' => 'Registros'],
                            ['ruta' => 'sisipedia.aportaciones.index', 'label' => 'Aportaciones'],
                        ],
                    ],
                    'audios' => [
                        'items' => [
                            ['ruta' => 'canciones.index', 'label' => 'Canciones'],
                            ['ruta' => 'cancionescat.index', 'label' => 'Categorías de canciones'],
                        ],
                    ],
                    'videos' => [
                        'items' => [
                            ['ruta' => 'videos.index', 'label' => 'Videos'],
                            ['ruta' => 'videoscat.index', 'label' => 'Categorías de video'],
                        ],
                    ],
                    'kawsay' => [
                        'items' => [
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
                            // Se recorre $rolesMenu (no $proyecto->roles) para que el orden de
                            // las secciones dentro de cada proyecto sea el que definimos arriba
                            // y no el id interno del rol en la tabla roles.
                            $seccionesProyecto = collect($rolesMenu)
                                ->filter(fn ($definicion, $nombreRol) => $proyecto->roles->contains('name', $nombreRol))
                                ->flatMap(fn ($definicion) => $seccionesDesdeRol($definicion))
                                ->values();
                            $idSubmenuProyecto = 'proyectoMenu' . $proyecto->id;
                        @endphp
                        <li>
                            @if ($seccionesProyecto->isEmpty())
                                <a href="{{ route('proyectos.edit', $proyecto) }}">
                                    <i class="fa {{ $proyectoIconos[$proyecto->slug] ?? 'fa-folder-open' }}"></i>
                                    {{ $proyecto->nombre }}
                                    <span class="menu-note">Sin configurar</span>
                                </a>
                            @else
                                <a href="#{{ $idSubmenuProyecto }}" data-toggle="collapse" aria-expanded="false"
                                    class="dropdown-toggle">
                                    <i class="fa {{ $proyectoIconos[$proyecto->slug] ?? 'fa-folder-open' }}"></i>
                                    {{ $proyecto->nombre }}
                                </a>
                                <ul class="collapse list-unstyled" id="{{ $idSubmenuProyecto }}">
                                    @include('livewire.partials.menu-secciones', [
                                        'secciones' => $seccionesProyecto,
                                        'idPrefix' => $idSubmenuProyecto,
                                    ])
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endrole
            @role('sisicha')
                <li class="{{ request()->routeIs('sisipedia.categories*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.categories.index') }}"><i class="fa fa-th-list"></i> Registros</a>
                </li>
                <li class="{{ request()->routeIs('sisipedia.aportaciones*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.aportaciones.index') }}"><i class="fa fa-inbox"></i> Aportaciones</a>
                </li>
            @endrole
            @role('biblioteca')
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-book"></i> Biblioteca</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        @include('livewire.partials.menu-secciones', [
                            'secciones' => $seccionesDesdeRol($rolesMenu['biblioteca']),
                            'idPrefix' => 'homeSubmenu',
                        ])
                    </ul>
                </li>
            @endrole
            @role('kawsay')
                <li>
                    <a href="#kawsaySubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-leaf"></i> Kawsay</a>
                    <ul class="collapse list-unstyled" id="kawsaySubmenu">
                        <li>
                            <a href="{{ route('kawsaylibros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li>
                            <a href="{{ route('kawsaycategorias.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                        <li>
                            <a href="{{ route('kawsayvideos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li>
                            <a href="{{ route('kawsayvideoscat.index') }}"><span class="flecha-admin">→</span> Categorías de video</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('videos')
                <li>
                    <a href="#videos" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-video-camera"></i> Videos</a>
                    <ul class="collapse list-unstyled" id="videos">
                        <li>
                            <a href="{{ route('videos.index') }}"><span class="flecha-admin">→</span> Videos</a>
                        </li>
                        <li>
                            <a href="{{ route('videoscat.index') }}"><span class="flecha-admin">→</span> Categorías</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('audios')
                <li>
                    <a href="#canciones" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-music"></i> Canciones</a>
                    <ul class="collapse list-unstyled" id="canciones">
                        <li>
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li>
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span>
                                Categorias</a>
                        </li>

                    </ul>
                </li>
            @endrole
            @unlessrole('sisicha')
            <li class="sidebar-section-title">Usuarios</li>
            <li>
                <a href="#admins" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                        class="fa fa-building"></i> Usuarios</a>
                <ul class="collapse list-unstyled" id="admins">
                    <li>
                        <a href="{{ route('admin.index') }}"><span class="flecha-admin">→</span> Administradores</a>
                    </li>
                    <li>
                        <a href="{{ route('alumnos') }}"><span class="flecha-admin">→</span> Alumnos</a>
                    </li>
                </ul>
            </li>
            @endunlessrole
            <style>
                .flecha-admin {
                    opacity: 0;
                    transform: translateX(-20px);
                    transition: opacity 0.5s ease, transform 0.5s ease;
                    display: inline-block;
                    /* Asegúrate de que el span sea un elemento en línea en bloque */
                }

                /* Al hacer hover sobre el enlace, el span aparece con un fade in y se traslada a su posición original */
                a:hover .flecha-admin {
                    opacity: 1;
                    transform: translateX(0);
                }
            </style>
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
