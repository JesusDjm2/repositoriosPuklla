@extends('layouts.app')
@section('titulo', '- Secciones')
@section('contenido')
    @include('partials.biblioteca-virtual-crud-header', ['active' => 'secciones'])

    <x-data-table titulo="Secciones" subtitulo="Agrupan las categorías del catálogo digital"
        :nuevoHref="route('secciones.create')" nuevoLabel="Nueva sección" :columnas="['Sección', 'Categorías', '']"
        :vacio="$secciones->isEmpty()" vacioIcono="fa-folder-open" vacioMensaje="Aún no hay secciones registradas."
        :vacioAccionHref="route('secciones.create')" vacioAccionLabel="Crear la primera">
        @foreach ($secciones as $seccion)
            <tr>
                <td class="font-weight-bold">{{ $seccion->nombre }}</td>
                <td>
                    @if ($seccion->categorias->isEmpty())
                        <span class="text-muted small">Sin categorías</span>
                    @else
                        @foreach ($seccion->categorias as $categoria)
                            <div class="small">
                                <a href="{{ route('detallesCat', $categoria->id) }}">{{ $categoria->nombre }}</a>
                                <span class="text-muted">({{ $categoria->libros_count }}
                                    {{ Str::plural('libro', $categoria->libros_count) }})</span>
                            </div>
                        @endforeach
                    @endif
                </td>
                <td>
                    <x-data-table.actions :editHref="route('secciones.edit', $seccion->id)"
                        :deleteAction="route('secciones.destroy', $seccion->id)"
                        deleteConfirmTitle="¿Eliminar «{{ $seccion->nombre }}»?">
                        <a href="{{ route('secciones.show', $seccion->id) }}" class="admin-table__icon-btn" title="Ver"
                            aria-label="Ver sección">
                            <i class="fa fa-eye"></i>
                        </a>
                    </x-data-table.actions>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
