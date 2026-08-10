@extends('layouts.app')
@section('titulo', '- Proyectos')
@section('contenido')

    <x-page-header titulo="Proyectos" subtitulo="Proyectos de la Asociación Pukllasunchis">
        <x-slot:actions>
            <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus mr-1"></i> Nuevo proyecto
            </a>
            <a href="{{ route('admin.principal') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left mr-1"></i> Volver al panel
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Proyecto</th>
                        <th>Roles asociados</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($proyectos as $proyecto)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="align-middle">
                                <img src="{{ $proyecto->logoUrl() }}" width="28" height="28"
                                    style="object-fit:contain;" class="mr-2" alt="">
                                {{ $proyecto->nombre }}
                            </td>
                            <td>
                                @foreach ($proyecto->roles as $role)
                                    <span class="badge badge-secondary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $proyecto->orden }}</td>
                            <td>
                                <span class="badge {{ $proyecto->activo ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $proyecto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('proyectos.edit', $proyecto) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDeleteProyecto('{{ route('proyectos.destroy', $proyecto) }}')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Aún no hay proyectos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteProyectoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este proyecto? Esta acción no elimina los registros
                    (libros, videos, etc.) ya creados por su admin, solo la ficha del proyecto.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteProyectoForm" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteProyecto(url) {
            document.getElementById('deleteProyectoForm').action = url;
            $('#deleteProyectoModal').modal('show');
        }
    </script>
@endsection
