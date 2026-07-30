@extends('layouts.app')
@section('titulo', 'Nuevo préstamo')

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
    @include('partials.admin-breadcrumb', [
        'items' => [
            ['label' => 'Panel',          'url' => route('home')],
            ['label' => 'Préstamos',      'url' => route('biblioteca-fisica.prestamos.index')],
            ['label' => 'Nuevo registro', 'url' => null],
        ],
    ])

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="bv-crud-page-title mb-0">
            <i class="fa fa-plus-circle text-info mr-2"></i>Registrar préstamo
        </h1>
        <a href="{{ route('biblioteca-fisica.prestamos.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left mr-1"></i>Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger bv-alert mb-3">
            <i class="fa fa-exclamation-circle mr-2"></i><strong>Corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$hayLibros)
        <div class="alert alert-warning bv-alert">
            <i class="fa fa-exclamation-triangle mr-2"></i>
            Primero debe
            <a href="{{ route('biblioteca-fisica.libros.create') }}" class="alert-link">registrar un libro físico</a>
            antes de crear préstamos.
        </div>
    @else
        <form action="{{ route('biblioteca-fisica.prestamos.store') }}" method="POST" id="prestamoForm">
            @csrf

            <div class="bv-form-card" id="bvCardLibro">
                <div class="bv-form-section-title">
                    <i class="fa fa-book mr-1"></i>Libro
                </div>

                <div class="form-group mb-0">
                    <label class="bv-form-label" for="libro_fisico_id">
                        Libro físico <span class="text-danger">*</span>
                    </label>
                    <select name="libro_fisico_id" id="libro_fisico_id"
                        class="form-control @error('libro_fisico_id') is-invalid @enderror" style="width:100%" required>
                        <option value=""></option>
                        @if ($libroSeleccionado)
                            <option value="{{ $libroSeleccionado->id }}" selected>
                                {{ $libroSeleccionado->nombre }} — {{ $libroSeleccionado->autor }}
                                ({{ $libroSeleccionado->ejemplares_disponibles }} disp.)
                            </option>
                        @endif
                    </select>
                    @error('libro_fisico_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="bv-form-card" id="bvCardDetalle" style="{{ $libroSeleccionado ? '' : 'display:none;' }}">
                <div class="bv-form-section-title">
                    <i class="fa fa-circle-info mr-1"></i>Detalle del libro
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Nombre</div>
                        <div class="font-weight-bold" id="det-nombre">{{ $libroSeleccionado->nombre ?? '' }}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Autor</div>
                        <div id="det-autor">{{ $libroSeleccionado->autor ?? '' }}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="small text-muted">Editorial</div>
                        <div id="det-editorial">{{ $libroSeleccionado->editorial ?? '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="small text-muted">Año</div>
                        <div id="det-anio">{{ $libroSeleccionado->anio_publicacion ?? '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="small text-muted">Páginas</div>
                        <div id="det-paginas">{{ $libroSeleccionado?->numero_paginas ? $libroSeleccionado->numero_paginas.' p.' : '—' }}</div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="small text-muted">Código</div>
                        <div><code id="det-codigo">{{ $libroSeleccionado->codigo ?? '—' }}</code></div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="small text-muted">Ejemplares</div>
                        <div id="det-ejemplares">{{ $libroSeleccionado->cantidad_ejemplares ?? '—' }}</div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="small text-muted">Disponibles</div>
                        <div id="det-disponibles">{{ $libroSeleccionado->ejemplares_disponibles ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted mb-1">Sección / Categoría</div>
                        <div id="det-categorias">
                            @forelse ($libroSeleccionado->categoriasFisicas ?? [] as $cat)
                                <span class="badge mr-1 mb-1" style="background: {{ \App\Models\CategoriaFisica::colorForCodigo($cat->codigo) }}; color:#fff;">
                                    {{ $cat->nombre }}
                                </span>
                            @empty
                                <span class="text-muted">Sin categoría</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bv-form-card" id="bvCardPrestatario">
                <div class="bv-form-section-title">
                    <i class="fa fa-user mr-1"></i>Datos del prestatario
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="bv-form-label" for="nombre_prestatario">
                            Nombre completo <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_prestatario" id="nombre_prestatario"
                            class="form-control form-control-sm @error('nombre_prestatario') is-invalid @enderror"
                            value="{{ old('nombre_prestatario') }}" required maxlength="255"
                            placeholder="Ej: Ana Quispe Mamani" autofocus>
                        @error('nombre_prestatario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label class="bv-form-label" for="dni_prestatario">
                            DNI / código <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="dni_prestatario" id="dni_prestatario"
                            class="form-control form-control-sm @error('dni_prestatario') is-invalid @enderror"
                            value="{{ old('dni_prestatario') }}" required maxlength="20"
                            placeholder="Ej: 70458123">
                        @error('dni_prestatario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label class="bv-form-label" for="contacto_prestatario">
                            Contacto <small class="text-muted font-weight-normal">(opcional)</small>
                        </label>
                        <input type="text" name="contacto_prestatario" id="contacto_prestatario"
                            class="form-control form-control-sm @error('contacto_prestatario') is-invalid @enderror"
                            value="{{ old('contacto_prestatario') }}" maxlength="255"
                            placeholder="Tel. o email">
                        @error('contacto_prestatario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bv-form-card" id="bvCardFechas">
                <div class="bv-form-section-title">
                    <i class="fa fa-calendar mr-1"></i>Fechas
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="bv-form-label" for="fecha_prestamo">
                            Fecha de préstamo <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="fecha_prestamo" id="fecha_prestamo"
                            class="form-control form-control-sm @error('fecha_prestamo') is-invalid @enderror"
                            value="{{ old('fecha_prestamo', now()->toDateString()) }}" required>
                        @error('fecha_prestamo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label class="bv-form-label" for="fecha_limite">
                            Fecha límite de devolución <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="fecha_limite" id="fecha_limite"
                            class="form-control form-control-sm @error('fecha_limite') is-invalid @enderror"
                            value="{{ old('fecha_limite', now()->addDays(7)->toDateString()) }}" required>
                        @error('fecha_limite')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="bv-form-label" for="observaciones">
                        Observaciones <small class="text-muted font-weight-normal">(opcional)</small>
                    </label>
                    <textarea name="observaciones" id="observaciones" rows="3"
                        class="form-control form-control-sm @error('observaciones') is-invalid @enderror"
                        maxlength="2000"
                        placeholder="Notas adicionales sobre este préstamo…">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-info btn-sm px-4">
                    <i class="fa fa-save mr-1"></i>Registrar préstamo
                </button>
                <a href="{{ route('biblioteca-fisica.prestamos.index') }}" class="btn btn-outline-secondary btn-sm">
                    Cancelar
                </a>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
    gsap.from(['#bvCardLibro', '#bvCardPrestatario', '#bvCardFechas'], {
        opacity: 0, y: 22, duration: 0.42, stagger: 0.1, ease: 'power2.out'
    });

    // El evento "load" garantiza que jQuery y select2.min.js (ambos con defer)
    // ya terminaron de ejecutarse, sin depender del orden exacto entre scripts defer.
    window.addEventListener('load', function () {
        $('#libro_fisico_id').select2({
            width: '100%',
            language: {
                inputTooShort: function () { return 'Escribe al menos 2 letras…'; },
                searching: function () { return 'Buscando…'; },
                noResults: function () { return 'Sin resultados'; },
                loadingMore: function () { return 'Cargando más resultados…'; },
            },
            placeholder: 'Busca por título, autor o código…',
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

        function pintarDetalleLibro(data) {
            var $card = $('#bvCardDetalle');
            if (!data || !data.id) {
                $card.hide();
                return;
            }
            $('#det-nombre').text(data.nombre || '');
            $('#det-autor').text(data.autor || '');
            $('#det-editorial').text(data.editorial || '—');
            $('#det-anio').text(data.anio_publicacion || '—');
            $('#det-paginas').text(data.numero_paginas ? (data.numero_paginas + ' p.') : '—');
            $('#det-codigo').text(data.codigo || '—');
            $('#det-ejemplares').text(data.cantidad_ejemplares != null ? data.cantidad_ejemplares : '—');
            $('#det-disponibles').text(data.ejemplares_disponibles != null ? data.ejemplares_disponibles : '—');

            var $cats = $('#det-categorias').empty();
            if (data.categorias && data.categorias.length) {
                data.categorias.forEach(function (c) {
                    $('<span class="badge mr-1 mb-1"></span>')
                        .css({ background: c.color, color: '#fff' })
                        .text(c.nombre)
                        .appendTo($cats);
                });
            } else {
                $('<span class="text-muted"></span>').text('Sin categoría').appendTo($cats);
            }

            $card.show();
        }

        $('#libro_fisico_id').on('select2:select', function (e) {
            pintarDetalleLibro(e.params.data);
        });
        $('#libro_fisico_id').on('select2:clear select2:unselect', function () {
            pintarDetalleLibro(null);
        });
    });
</script>
@endpush
