@extends('layouts.app')
@section('titulo', 'Préstamos')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .select2-container .select2-selection--single { height: calc(1.5em + .5rem + 2px); }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 1.9; padding-left: .5rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: calc(1.5em + .5rem + 2px); }
</style>
@endpush

@section('contenido')
    @include('partials.biblioteca-fisica-crud-header', ['active' => 'prestamos'])

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
        <div>
            <h1 class="bv-crud-page-title mb-1">Préstamos</h1>
            <div class="bv-stat-chips">
                <span class="bv-stat-chip">
                    <i class="fa fa-layer-group"></i> {{ number_format($totalEjemplares) }} ejemplares
                </span>
                <span class="bv-stat-chip">
                    <i class="fa fa-hand-holding"></i> {{ number_format($prestadosActualmente) }} prestados
                </span>
                <span class="bv-stat-chip">
                    <i class="fa fa-check-circle"></i> {{ number_format($disponibles) }} disponibles
                </span>
                <span class="bv-stat-chip bv-stat-chip--danger">
                    <i class="fa fa-exclamation-triangle"></i> {{ number_format($atrasados) }} atrasados
                </span>
            </div>
        </div>
        <a href="{{ route('biblioteca-fisica.prestamos.create') }}" class="btn btn-info btn-sm mt-2">
            <i class="fa fa-plus mr-1"></i>Registrar préstamo
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show bv-alert" role="alert">
            <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show bv-alert" role="alert">
            <i class="fa fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form method="get" action="{{ route('biblioteca-fisica.prestamos.index') }}" class="bv-crud-toolbar">
        <div class="form-row align-items-end">
            <div class="form-group col-md-4 mb-2 mb-md-0">
                <label for="pre-q" class="small text-muted mb-1 d-block">
                    <i class="fa fa-search mr-1"></i>Buscar
                </label>
                <input type="text" id="pre-q" name="q" class="form-control form-control-sm"
                    value="{{ request('q') }}" placeholder="Nombre o DNI del prestatario…" autocomplete="off">
            </div>
            <div class="form-group col-md-3 mb-2 mb-md-0">
                <label for="pre-libro" class="small text-muted mb-1 d-block">
                    <i class="fa fa-book mr-1"></i>Libro
                </label>
                <select id="pre-libro" name="libro_fisico_id" class="form-control form-control-sm" style="width:100%">
                    <option value="">Todos los libros</option>
                    @if ($libroFiltroSeleccionado)
                        <option value="{{ $libroFiltroSeleccionado->id }}" selected>
                            {{ $libroFiltroSeleccionado->nombre }}
                        </option>
                    @endif
                </select>
            </div>
            <div class="form-group col-md-2 mb-2 mb-md-0">
                <label for="pre-estado" class="small text-muted mb-1 d-block">
                    <i class="fa fa-flag mr-1"></i>Estado
                </label>
                <select id="pre-estado" name="estado" class="custom-select custom-select-sm">
                    <option value="">Todos</option>
                    <option value="prestado" @selected(request('estado') === 'prestado')>Prestado</option>
                    <option value="atrasado" @selected(request('estado') === 'atrasado')>Atrasado</option>
                    <option value="devuelto" @selected(request('estado') === 'devuelto')>Devuelto</option>
                </select>
            </div>
            <div class="form-group col-md-3 mb-0">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-info">
                        <i class="fa fa-filter mr-1"></i>Filtrar
                    </button>
                    <a href="{{ route('biblioteca-fisica.prestamos.index') }}" class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if ($prestamos->total() > 0)
        <p class="bv-crud-meta mb-2">
            Mostrando {{ $prestamos->firstItem() }}–{{ $prestamos->lastItem() }} de {{ $prestamos->total() }} resultado(s)
        </p>
    @endif

    <div class="bv-crud-table-wrap table-responsive">
        <table class="table table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th style="width:3rem;">#</th>
                    <th>Libro</th>
                    <th>Prestatario</th>
                    <th>Contacto</th>
                    <th style="width:6.5rem;">F. préstamo</th>
                    <th style="width:6.5rem;">F. límite</th>
                    <th style="width:6rem;">Estado</th>
                    <th style="width:9rem;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($prestamos as $prestamo)
                    <tr class="bv-table-row">
                        <td class="text-muted small align-middle">
                            {{ $prestamos->firstItem() + $loop->index }}
                        </td>
                        <td class="align-middle">
                            <div class="font-weight-bold">{{ $prestamo->libroFisico->nombre }}</div>
                            <code class="small text-muted">{{ $prestamo->libroFisico->codigo }}</code>
                        </td>
                        <td class="align-middle">
                            <div>{{ $prestamo->nombre_prestatario }}</div>
                            <span class="small text-muted">{{ $prestamo->dni_prestatario }}</span>
                        </td>
                        <td class="align-middle small text-muted">
                            {{ $prestamo->contacto_prestatario ?: '—' }}
                        </td>
                        <td class="align-middle small">{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</td>
                        <td class="align-middle small">{{ $prestamo->fecha_limite->format('d/m/Y') }}</td>
                        <td class="align-middle">
                            <span class="badge {{ $prestamo->estado_badge_class }}">{{ $prestamo->estado_label }}</span>
                        </td>
                        <td class="bv-crud-actions text-center text-nowrap align-middle">
                            @if ($prestamo->estado !== 'devuelto')
                                <form method="POST" action="{{ route('biblioteca-fisica.prestamos.devolver', $prestamo) }}"
                                    class="d-inline" onsubmit="return confirm('¿Marcar este préstamo como devuelto?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Registrar devolución">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('biblioteca-fisica.prestamos.edit', $prestamo) }}"
                                class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-prestamo"
                                data-url="{{ route('biblioteca-fisica.prestamos.destroy', $prestamo) }}"
                                data-nombre="{{ $prestamo->nombre_prestatario }} — {{ $prestamo->libroFisico->nombre }}"
                                title="Eliminar">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0 border-0">
                            <div class="bv-empty-state">
                                <i class="fa fa-hand-holding bv-empty-icon"></i>
                                <p>No se encontraron préstamos con esos criterios.</p>
                                @if (request('q') || request('libro_fisico_id') || request('estado'))
                                    <a href="{{ route('biblioteca-fisica.prestamos.index') }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-times mr-1"></i>Limpiar filtros
                                    </a>
                                @else
                                    <a href="{{ route('biblioteca-fisica.prestamos.create') }}"
                                        class="btn btn-sm btn-info">
                                        <i class="fa fa-plus mr-1"></i>Registrar el primero
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bv-crud-pagination">{{ $prestamos->links() }}</div>

    {{-- Modal de confirmación --}}
    <div class="modal fade" id="deleteModalPrestamo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle text-danger mr-2"></i>Confirmar eliminación
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-3">
                    <p class="mb-1">¿Eliminar el préstamo de <strong id="prestamoNombre" class="text-info"></strong>?</p>
                    <small class="text-muted">Esta acción no se puede deshacer. Si el préstamo está activo, el ejemplar quedará disponible sin dejar rastro de este registro.</small>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteFormPrestamo" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa fa-trash mr-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-delete-prestamo');
            if (!btn) return;
            document.getElementById('deleteFormPrestamo').action = btn.getAttribute('data-url');
            document.getElementById('prestamoNombre').textContent = btn.getAttribute('data-nombre');
            $('#deleteModalPrestamo').modal('show');
        });
    </script>
@endsection

@push('scripts')
<script>
    (function () {
        var rows = document.querySelectorAll('.bv-table-row');
        if (rows.length) {
            gsap.from(rows, { opacity: 0, y: 12, duration: 0.32, stagger: 0.04, ease: 'power2.out' });
        }
        gsap.from('.bv-crud-toolbar', { opacity: 0, y: -8, duration: 0.3, delay: 0.05, ease: 'power2.out' });
        gsap.from('.bv-stat-chips',   { opacity: 0, x: -10, duration: 0.3, delay: 0.1, ease: 'power2.out' });
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
    // El evento "load" garantiza que jQuery y select2.min.js (ambos con defer)
    // ya terminaron de ejecutarse, sin depender del orden exacto entre scripts defer.
    window.addEventListener('load', function () {
        $('#pre-libro').select2({
            width: '100%',
            language: {
                inputTooShort: function () { return 'Escribe al menos 2 letras…'; },
                searching: function () { return 'Buscando…'; },
                noResults: function () { return 'Sin resultados'; },
                loadingMore: function () { return 'Cargando más resultados…'; },
            },
            placeholder: 'Todos los libros',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                // El jQuery local de este panel es un build sin el módulo $.ajax,
                // así que se reemplaza el transporte por fetch() nativo.
                transport: function (params, success, failure) {
                    var url = '{{ route("biblioteca-fisica.libros.buscar-ajax") }}'
                        + '?q=' + encodeURIComponent(params.data.q || '')
                        + '&page=' + (params.data.page || 1);
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (r) { return r.json(); })
                        .then(success)
                        .catch(failure);
                },
                delay: 300,
                data: function (params) {
                    return { q: params.term, page: params.page || 1 };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });
    });
</script>
@endpush
