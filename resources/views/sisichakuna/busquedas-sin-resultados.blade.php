@extends('layouts.app')
@section('titulo', '- Búsquedas sin resultados')

@section('contenido')
@include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'busquedas'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:.5rem;">
            <div>
                <h4 class="card-title mb-1"><i class="fa fa-search mr-2 text-warning"></i>Búsquedas sin resultados</h4>
                <small class="text-muted">
                    Lo que la gente busca en Sisipedia y no encuentra: ayuda a decidir qué contenido subir o cómo nombrar los registros.
                </small>
            </div>
            <a href="{{ route('sisipedia.buscar') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-external-link mr-1"></i>Abrir buscador
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        @if ($terminos === null)
            <div class="alert alert-warning m-3 mb-3">
                Falta crear la tabla <code>sisipedia_busquedas_sin_resultados</code>: ejecuta
                <code>database/migracion_produccion_busquedas_20261104.sql</code> en phpMyAdmin
                (en local, <code>php artisan migrate</code>). La búsqueda funciona igual; solo no se registran los términos.
            </div>
        @elseif ($terminos->isEmpty())
            <p class="text-muted text-center py-5 mb-0">Todavía no hay búsquedas sin resultados registradas.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Término buscado</th>
                            <th class="text-right">Veces</th>
                            <th>Última vez</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terminos as $t)
                            <tr>
                                <td class="font-weight-bold align-middle">{{ $t->termino }}</td>
                                <td class="text-right align-middle"><span class="badge badge-warning">{{ $t->veces }}</span></td>
                                <td class="text-muted small align-middle">
                                    {{ $t->ultima_vez ? \Illuminate\Support\Carbon::parse($t->ultima_vez)->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="text-right align-middle">
                                    <a href="{{ route('sisipedia.buscar', ['q' => $t->termino]) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-light" title="Probar la búsqueda de nuevo">
                                        <i class="fa fa-refresh"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if ($terminos && $terminos->hasPages())
    {{ $terminos->links() }}
@endif
@endsection
