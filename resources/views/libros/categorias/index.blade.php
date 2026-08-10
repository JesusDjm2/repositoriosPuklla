@extends('layouts.app')
@section('titulo', '- Categorías')
@section('contenido')
    @include('partials.biblioteca-virtual-crud-header', ['active' => 'categorias'])

    <x-data-table titulo="Categorías" subtitulo="Categorías del catálogo digital, agrupadas por sección"
        :nuevoHref="route('categorias.create')" nuevoLabel="Nueva categoría" :columnas="['Categoría', 'Sección', '']"
        buscarPlaceholder="Buscar categoría o sección..." :vacio="$categorias->isEmpty()" vacioIcono="fa-th-large"
        vacioMensaje="Aún no hay categorías registradas." :vacioAccionHref="route('categorias.create')"
        vacioAccionLabel="Crear la primera">
        @foreach ($categorias as $categoria)
            <tr>
                <td>
                    <a href="{{ route('detallesCat', $categoria->id) }}"
                        class="font-weight-bold">{{ $categoria->nombre }}</a>
                    <div class="text-muted small">{{ $categoria->libros_count }}
                        {{ Str::plural('libro', $categoria->libros_count) }}</div>
                </td>
                <td>{{ $categoria->seccion->nombre }}</td>
                <td>
                    <x-data-table.actions :editHref="route('categorias.edit', $categoria->id)"
                        :deleteAction="route('categorias.destroy', $categoria->id)"
                        deleteConfirmTitle="¿Eliminar «{{ $categoria->nombre }}»?">
                        <a href="{{ route('categorias.show', $categoria->url) }}" class="admin-table__icon-btn"
                            title="Ver" aria-label="Ver categoría">
                            <i class="fa fa-eye"></i>
                        </a>
                    </x-data-table.actions>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
