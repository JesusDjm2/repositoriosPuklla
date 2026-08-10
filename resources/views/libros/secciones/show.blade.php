@extends('layouts.app')
@section('titulo', '- ' . $seccion->nombre)
@section('contenido')
    <div data-animate-stagger>
        <x-page-header :titulo="$seccion->nombre" subtitulo="Categorías de esta sección">
            <x-slot:actions>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        <div class="admin-table-card">
            @if ($categorias->isEmpty())
                <div class="admin-empty-state">
                    <i class="fa fa-th-large"></i>
                    <p>No hay categorías asociadas a esta sección.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <tbody>
                            @foreach ($categorias as $categoria)
                                <tr>
                                    <td>
                                        {{ $categoria->nombre }}
                                        <span class="text-muted small">→ {{ $categoria->libros_count }}
                                            {{ Str::plural('libro', $categoria->libros_count) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
