@extends('layouts.app')
@section('titulo', '- ' . $categoria->nombre)
@section('contenido')
    <div data-animate-stagger>
        <x-page-header :titulo="$categoria->nombre"
            :subtitulo="$categoria->libros->count() . ' ' . Str::plural('libro', $categoria->libros->count())">
            <x-slot:actions>
                <a href="{{ route('libros.create', ['categoria' => $categoria->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus mr-1"></i> Agregar libro
                </a>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card">
            @if ($categoria->libros->isEmpty())
                <div class="admin-empty-state">
                    <i class="fa fa-book"></i>
                    <p>No hay libros relacionados con esta categoría.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <tbody>
                            @foreach ($categoria->libros as $libro)
                                <tr>
                                    <td>{{ $libro->nombre }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
