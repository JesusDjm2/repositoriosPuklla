@extends('layouts.app')
@section('titulo', 'Inicio')


@section('contenido')
    <div class="row">
        <div class="col-lg-12">
            @php
                $tituloDashboard = 'Administrador General Puklla Virtual';

                if (auth()->check()) {
                    $rol = auth()->user()->getRoleNames()->first();

                    switch ($rol) {
                        case 'admin':
                            $tituloDashboard = 'Administrador General Puklla Virtual';
                            break;
                        case 'biblioteca':
                            $tituloDashboard = 'Panel Biblioteca EESP Pukllasunchis';
                            break;
                        case 'videos':
                            $tituloDashboard = 'Administrador General de Audios y Videos';
                            break;
                        case 'audios':
                            $tituloDashboard = 'Administrador General de Audios';
                            break;
                        case 'sisicha':
                            $tituloDashboard = 'Administrador General Sisipedia';
                            break;
                        case 'alumno':
                            $tituloDashboard = 'Panel del Alumno - Puklla Virtual';
                            break;
                        default:
                            $tituloDashboard = 'Bienvenido a Puklla Virtual';
                    }
                }
            @endphp
            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                <div>
                    <h4 class="font-weight-bold mb-1">{{ $tituloDashboard }}</h4>
                    <p class="text-muted small mb-0">Bienvenido, {{ Auth::user()->name }}. Accede a la gestión de repositorios.</p>
                </div>
                @role('admin')
                    <a href="{{ route('admin.principal') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-sitemap mr-1"></i> Ver proyectos Hub
                    </a>
                @endrole
            </div>
        </div>

        @hasanyrole('admin|biblioteca')
            <div class="col-12 mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="font-weight-bold text-dark mb-0">Módulos de Biblioteca EESP</h5>
                    <a href="{{ route('bibliotecario') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-th-large mr-1"></i> Abrir panel completo
                    </a>
                </div>
            </div>

            {{-- BIBLIOTECA FÍSICA --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('biblioteca-fisica.libros.index') }}"
                    icono="fa-archive"
                    variante="fisica"
                    titulo="Biblioteca Física"
                    descripcion="Catálogo en estantería, ejemplares, autores y clasificación Dewey.">
                    <x-slot:badges>
                        <span class="stat-badge badge-categorias"><i class="fa fa-folder"></i> Categorías</span>
                        <span class="stat-badge badge-registros"><i class="fa fa-book"></i> Ejemplares</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>

            {{-- PRÉSTAMOS --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('biblioteca-fisica.prestamos.index') }}"
                    icono="fa-handshake-o"
                    variante="prestamos"
                    titulo="Préstamos"
                    descripcion="Control de préstamos de libros físicos y material didáctico.">
                    <x-slot:badges>
                        <span class="stat-badge badge-registros"><i class="fa fa-list"></i> Préstamos</span>
                        <span class="stat-badge badge-secciones"><i class="fa fa-clock-o"></i> Activos</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>

            {{-- MATERIAL DIDÁCTICO --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('biblioteca-fisica.materiales.index') }}"
                    icono="fa-cubes"
                    variante="material"
                    titulo="Material Didáctico"
                    descripcion="Inventario pedagógico de kits y materiales didácticos de aula.">
                    <x-slot:badges>
                        <span class="stat-badge badge-categorias"><i class="fa fa-tags"></i> Categorías</span>
                        <span class="stat-badge badge-registros"><i class="fa fa-cubes"></i> Materiales</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>

            {{-- BIBLIOTECA DIGITAL --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('libros.index') }}"
                    icono="fa-laptop"
                    variante="virtual"
                    titulo="Biblioteca Digital"
                    descripcion="Publicaciones digitales, enlaces a Drive y secciones temáticas.">
                    <x-slot:badges>
                        <span class="stat-badge badge-secciones"><i class="fa fa-layer-group"></i> Secciones</span>
                        <span class="stat-badge badge-registros"><i class="fa fa-file-text-o"></i> Libros</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>

            {{-- TAQ'E --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('admin.taqe.trabajos.index') }}"
                    icono="fa-graduation-cap"
                    variante="taqe"
                    titulo="Taq'e — Investigaciones"
                    descripcion="Tesis y trabajos de investigación pedagógica de estudiantes EIB.">
                    <x-slot:badges>
                        <span class="stat-badge badge-taqe"><i class="fa fa-folder-open"></i> Líneas</span>
                        <span class="stat-badge badge-registros"><i class="fa fa-graduation-cap"></i> Trabajos</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>

            {{-- VIDEOS EESP --}}
            <div class="col-md-6 col-lg-4 mb-4">
                <x-dash-card
                    href="{{ route('videos.index') }}"
                    icono="fa-video-camera"
                    variante="videos"
                    titulo="Videos Institucionales"
                    descripcion="Registro de conferencias y videos pedagógicos EESP.">
                    <x-slot:badges>
                        <span class="stat-badge badge-categorias"><i class="fa fa-tags"></i> Categorías</span>
                        <span class="stat-badge badge-registros"><i class="fa fa-play"></i> Videos</span>
                    </x-slot:badges>
                </x-dash-card>
            </div>
        @endhasanyrole

        @role('sisicha')
            <div class="col-lg-12 px-3 pt-0">
                @include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'home'])
            </div>
            <div class="col-lg-3 p-3">
                <a href="{{ route('sisipedia.categories.index') }}">
                    <img src="{{ asset('img/Sisichakuna.webp') }}" width="100%" loading="lazy">
                </a>
            </div>
            <div class="col-lg-9 p-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-2">
                        <span class="font-weight-bold">
                            <i class="fa fa-layer-group text-primary mr-2"></i>Aportaciones recientes
                        </span>
                        <a href="{{ route('sisipedia.aportaciones.index') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-th-list mr-1"></i>Ver todas
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $recientes = \App\Models\sisipedia\Aportacion::with('category')
                                ->latest()->limit(6)->get();
                            $rolColors = [
                                'Equipo Puklla'   => 'info',
                                'Docente'         => 'primary',
                                'Líder'           => 'success',
                                'Niño/Estudiante' => 'warning',
                            ];
                            $rolIcons = [
                                'Equipo Puklla'   => 'fa-users',
                                'Docente'         => 'fa-graduation-cap',
                                'Líder'           => 'fa-star',
                                'Niño/Estudiante' => 'fa-child',
                            ];
                        @endphp
                        @if($recientes->isEmpty())
                            <div class="text-center py-4 text-muted small">
                                <i class="fa fa-inbox fa-lg mb-1 d-block"></i>
                                Aún no hay aportaciones registradas.
                            </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Categoría</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recientes as $ap)
                                    <tr>
                                        <td class="align-middle small">{{ $ap->nombre_ol }}</td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ $rolColors[$ap->rol_nombre] ?? 'secondary' }}">
                                                <i class="fa {{ $rolIcons[$ap->rol_nombre] ?? 'fa-user' }} mr-1"></i>
                                                {{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}
                                            </span>
                                        </td>
                                        <td class="align-middle small text-muted">
                                            {{ $ap->category->display_name ?? '—' }}
                                        </td>
                                        <td class="align-middle small text-muted text-nowrap">
                                            {{ $ap->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="align-middle text-right">
                                            @if($ap->category)
                                            <a href="{{ route('sisipedia.categories.admin-show', $ap->category) }}"
                                               class="btn btn-xs btn-outline-secondary">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endrole
    </div>
@endsection
