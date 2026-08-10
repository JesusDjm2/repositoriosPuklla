@extends('layouts.app')
@section('titulo', '- Libros')
@section('contenido')
    @include('partials.biblioteca-virtual-crud-header', ['active' => 'libros'])

    <x-data-table titulo="Libros" subtitulo="Catálogo de libros del sistema" :nuevoHref="route('libros.create')"
        nuevoLabel="Nuevo libro" :columnas="['Libro', 'Sección → Categoría', '']"
        buscarPlaceholder="Buscar por título o autor..." :vacio="$libros->isEmpty()" vacioIcono="fa-book"
        vacioMensaje="Aún no hay libros registrados." :vacioAccionHref="route('libros.create')"
        vacioAccionLabel="Registrar el primero">
        @foreach ($libros as $libro)
            <tr>
                <td>
                    <div class="font-weight-bold">{{ $libro->nombre }}</div>
                    <div class="text-muted small">{{ $libro->autor }}</div>
                </td>
                <td>
                    @foreach ($libro->categorias as $categoria)
                        <div class="small">
                            <a href="{{ route('secciones.show', $categoria->seccion->id) }}">{{ $categoria->seccion->nombre }}</a>
                            <i class="fa fa-angle-right mx-1 text-muted"></i>
                            <a href="{{ route('detallesCat', $categoria->id) }}">{{ $categoria->nombre }}</a>
                        </div>
                    @endforeach
                </td>
                <td>
                    <x-data-table.actions :editHref="route('libros.edit', $libro->id)"
                        :deleteAction="route('libros.destroy', $libro->id)"
                        deleteConfirmTitle="¿Eliminar «{{ $libro->nombre }}»?">
                        <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/view" target="_blank"
                            rel="noopener" class="admin-table__icon-btn" title="Ver archivo" aria-label="Ver archivo">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </x-data-table.actions>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
