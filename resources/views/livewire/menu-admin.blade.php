<nav id="sidebar">
    <div class="custom-menu">
        <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
        </button>
    </div>
    <div class="p-4 pt-5">
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
        <ul class="list-unstyled components mb-5">
            @role('admin')
                <li class="{{ request()->routeIs('admin.principal') ? 'active' : '' }}">
                    <a href="{{ route('admin.principal') }}"><i class="fa fa-th-large"></i> Panel principal</a>
                </li>
                <li class="{{ request()->routeIs('proyectos.*') ? 'active' : '' }}">
                    <a href="{{ route('proyectos.index') }}"><i class="fa fa-sitemap"></i> Proyectos</a>
                </li>
                <li>
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-book"></i> Biblioteca</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li>
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                        <li>
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>


                    </ul>
                </li>
                <li>
                    <a href="#canciones" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-music"></i> Canciones</a>
                    <ul class="collapse list-unstyled" id="canciones">
                        <li>
                            <a href="{{ route('canciones.index') }}"><span class="flecha-admin">→</span> Canciones</a>
                        </li>
                        <li>
                            <a href="{{ route('cancionescat.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                    </ul>
                </li>
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
            @role('sisicha')
                <li class="{{ request()->routeIs('sisipedia.categories*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.categories.index') }}"><i class="fa fa-th-list"></i> Registros</a>
                </li>
                <li class="{{ request()->routeIs('sisipedia.aportaciones*') ? 'active' : '' }}">
                    <a href="{{ route('sisipedia.aportaciones.index') }}"><i class="fa fa-inbox"></i> Aportaciones</a>
                </li>
                <li>
                    <a href="{{ route('public.categoria.sisi') }}" target="_blank" rel="noopener">
                        <i class="fa fa-globe"></i> Catálogo público
                    </a>
                </li>
            @endrole
            @role('biblioteca')
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i
                            class="fa fa-book"></i> Biblioteca</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="{{ route('libros.index') }}"><span class="flecha-admin">→</span> Libros</a>
                        </li>
                        <li>
                            <a href="{{ route('categorias.index') }}"><span class="flecha-admin">→</span> Categorias</a>
                        </li>
                        <li>
                            <a href="{{ route('secciones.index') }}"><span class="flecha-admin">→</span> Secciones</a>
                        </li>
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
            <li>
                <a href="{{ route('index') }}"><i class="fa fa-home"></i> Volver al inicio</a>
            </li>
        </ul>
        <div class="mb-3">
            <button id="darkModeToggle" class="btn btn-sm btn-outline-light w-100" type="button">
                <i class="fa fa-moon-o" id="darkModeIcon"></i>
                <span id="darkModeText"> Modo oscuro</span>
            </button>
        </div>

        <div class="mb-5">
            <a class="nav-link text-white text-center" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();">
                {{ Auth::user()->name }} - {{ __('Logout') }} <i class="fa fa-sign-out"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
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
