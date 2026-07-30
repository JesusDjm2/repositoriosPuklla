@extends('layouts.app')
@section('titulo', '- Editar Proyecto')
@section('contenido')

    <x-page-header titulo="Editar proyecto: {{ $proyecto->nombre }}">
        <x-slot:actions>
            <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('proyectos.update', $proyecto) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.proyectos._form', ['roles' => $roles, 'proyecto' => $proyecto])
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection
