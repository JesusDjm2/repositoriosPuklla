@extends('layouts.app')
@section('titulo', 'Inicio')
@section('contenido')
    <div class="row">
        <div class="col-lg-12">
            <h3>Usuarios
                @if (Auth::user()->hasRole('admin'))
                    <a href="{{ route('admin.create') }}" class="btn btn-info btn-sm float-right">Crear Nuevo
                        Usuario</a>
                @else
                    <a href="" class="btn btn-info btn-sm float-right disabled" aria-disabled="true">Crear Nuevo
                        Usuario</a>
                @endif
            </h3>

            <ul class="nav nav-tabs mt-3" id="usuariosTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-administradores" data-toggle="tab" href="#panel-administradores"
                        role="tab" aria-controls="panel-administradores" aria-selected="true">Administradores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-alumnos" data-toggle="tab" href="#panel-alumnos" role="tab"
                        aria-controls="panel-alumnos" aria-selected="false">Alumnos</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active pt-3" id="panel-administradores" role="tabpanel"
                    aria-labelledby="tab-administradores">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Tipo de Admin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $counter = 1; @endphp
                            @foreach ($admins as $admin)
                                <tr>
                                    <td> {{ $counter }}</td>
                                    <td> {{ $admin->name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>
                                        @php
                                            $role = $admin->getRoleNames()->first();
                                        @endphp
                                        @if ($role == 'admin')
                                            Admin General
                                        @elseif ($role == 'biblioteca')
                                            Admin Biblioteca
                                        @elseif ($role == 'alumno')
                                            Alumno
                                        @elseif ($role == 'video')
                                            Videos
                                        @elseif ($role == 'audios')
                                            Admin Canciones
                                        @elseif ($role == 'sisicha')
                                            Admin Sisichakunay
                                        @elseif ($role == 'videos')
                                            Admin Videos
                                        @elseif ($role == 'fredy')
                                            Admin Take
                                        @else
                                            {{ $role }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (Auth::user()->hasRole('admin'))
                                            <a href="{{ route('admin.edit', $admin->id) }}" class="btn btn-sm btn-info"><i
                                                    class="fa fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('{{ route('admin.destroy', $admin->id) }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @else
                                            <a href="#" title="Solo Administrador General puede habilitar"
                                                class="btn btn-sm btn-info disabled" aria-disabled="true"><i
                                                    class="fa fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger disabled" aria-disabled="true"><i
                                                    class="fa fa-trash"></i></button>
                                        @endif
                                    </td>
                                </tr>
                                @php $counter++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade pt-3" id="panel-alumnos" role="tabpanel" aria-labelledby="tab-alumnos">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>DNI</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $counterAlumno = 1; @endphp
                            @foreach ($alumnos as $alumno)
                                <tr>
                                    <td> {{ $counterAlumno }}</td>
                                    <td> {{ $alumno->name }}</td>
                                    <td>{{ $alumno->email }}</td>
                                    <td>{{ $alumno->dni }}</td>
                                    <td>
                                        @if (Auth::user()->hasRole('admin'))
                                            <a href="" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('{{ route('admin.destroy', $alumno->id) }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @else
                                            <a href="" class="btn btn-sm btn-info" aria-disabled="true"><i
                                                    class="fa fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-danger disabled" aria-disabled="true"><i
                                                    class="fa fa-trash"></i></button>
                                        @endif
                                    </td>
                                </tr>
                                @php $counterAlumno++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este usuario?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(url) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = url;
            $('#deleteModal').modal('show');
        }
    </script>
@endsection
