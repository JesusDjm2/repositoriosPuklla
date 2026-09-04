@extends('layouts.app')
@section('titulo', 'Nuevo préstamo')

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
                    <i class="fa fa-book mr-1"></i>Libros a prestar
                </div>

                <div id="librosSeleccionadosList" class="mb-2"></div>

                <div id="libroSeleccionError" class="text-danger small mb-2" style="display:none;">
                    <i class="fa fa-exclamation-circle mr-1"></i>Elige al menos un libro para prestar.
                </div>

                <div class="form-group mb-0" style="position:relative;">
                    <label class="bv-form-label" for="libroBuscar">
                        Buscar libro <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="libroBuscar" class="form-control form-control-sm" autocomplete="off"
                        placeholder="Busca por título, autor o código…">

                    <div class="bv-libro-results" id="libroResultadosWrap" style="display:none;">
                        <table class="table table-sm mb-0">
                            <tbody id="libroResultadosBody"></tbody>
                        </table>
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
                            placeholder="Ej: Ana Quispe Mamani">
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
<script>
    window.bvLibrosPreseleccionados = @json($librosSeleccionados);
</script>
<script>
    (function () {
        gsap.from(['#bvCardLibro', '#bvCardPrestatario', '#bvCardFechas'], {
            opacity: 0, y: 22, duration: 0.42, stagger: 0.1, ease: 'power2.out'
        });

        var librosElegidos = {}; // id (string) -> datos del libro

        var $lista = document.getElementById('librosSeleccionadosList');
        var $error = document.getElementById('libroSeleccionError');
        var $buscar = document.getElementById('libroBuscar');
        var $resultadosWrap = document.getElementById('libroResultadosWrap');
        var $resultadosBody = document.getElementById('libroResultadosBody');
        var $form = document.getElementById('prestamoForm');

        function esc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function badge(color, texto) {
            return '<span class="bv-s2-result-badge" style="background:' + esc(color) + '">' + esc(texto) + '</span>';
        }

        // Reutilizada tanto para pintar una fila de resultados como un chip elegido.
        function construirDetalle(libro) {
            var meta = '';
            if (libro.codigo) meta += '<code>' + esc(libro.codigo) + '</code>';
            (libro.categorias || []).forEach(function (c) {
                meta += badge(c.color, c.nombre);
            });
            if (libro.tipo_material) meta += badge(libro.tipo_material.color, libro.tipo_material.nombre);
            if (libro.ubicacion) meta += '<span class="bv-s2-result-badge" style="background:#6c757d;">' + esc(libro.ubicacion) + '</span>';

            var disp = libro.ejemplares_disponibles != null ? libro.ejemplares_disponibles : 0;
            var dispClass = disp > 0 ? 'bv-s2-result-disp--ok' : 'bv-s2-result-disp--no';
            meta += '<span class="bv-s2-result-disp ' + dispClass + '">' + disp + ' disp.</span>';

            return '<div class="bv-s2-result bv-s2-result--detalle">' +
                '<div class="bv-s2-result-title">' + esc(libro.nombre) + ' — ' + esc(libro.autor) + '</div>' +
                '<div class="bv-s2-result-meta">' + meta + '</div>' +
                '</div>';
        }

        function actualizarErrorVisibilidad() {
            var hay = Object.keys(librosElegidos).length > 0;
            $error.style.display = hay ? 'none' : $error.dataset.forzado === '1' ? 'block' : 'none';
        }

        function pintarChips() {
            $lista.innerHTML = '';
            Object.keys(librosElegidos).forEach(function (id) {
                var libro = librosElegidos[id];
                var $chip = document.createElement('div');
                $chip.className = 'bv-libro-chip mb-2';
                $chip.dataset.id = id;
                $chip.innerHTML =
                    '<input type="hidden" name="libro_fisico_id[]" value="' + esc(id) + '">' +
                    construirDetalle(libro) +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar-libro" data-id="' + esc(id) + '" title="Quitar">' +
                    '<i class="fa fa-times"></i></button>';
                $lista.appendChild($chip);
            });
            actualizarErrorVisibilidad();
        }

        function elegirLibro(libro) {
            var id = String(libro.id);
            if (librosElegidos[id]) return;
            if (!libro.ejemplares_disponibles || libro.ejemplares_disponibles < 1) return;
            librosElegidos[id] = libro;
            pintarChips();
            $buscar.value = '';
            ocultarResultados();
            $buscar.focus();
        }

        $lista.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-quitar-libro');
            if (!btn) return;
            delete librosElegidos[btn.dataset.id];
            pintarChips();
        });

        function ocultarResultados() {
            $resultadosWrap.style.display = 'none';
            $resultadosBody.innerHTML = '';
        }

        function pintarResultados(libros) {
            $resultadosBody.innerHTML = '';
            var visibles = libros.filter(function (l) { return !librosElegidos[String(l.id)]; });

            if (!visibles.length) {
                $resultadosBody.innerHTML = '<tr><td class="text-muted small py-2">Sin resultados.</td></tr>';
                $resultadosWrap.style.display = 'block';
                return;
            }

            visibles.forEach(function (libro) {
                var disponible = libro.ejemplares_disponibles > 0;
                var $tr = document.createElement('tr');
                $tr.className = 'bv-libro-row' + (disponible ? '' : ' bv-libro-row--disabled');
                $tr.tabIndex = disponible ? 0 : -1;
                $tr.innerHTML = '<td>' + construirDetalle(libro) + '</td>';
                if (disponible) {
                    $tr.addEventListener('click', function () { elegirLibro(libro); });
                    $tr.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') elegirLibro(libro);
                    });
                }
                $resultadosBody.appendChild($tr);
            });

            $resultadosWrap.style.display = 'block';
        }

        var buscarTimer;
        $buscar.addEventListener('input', function () {
            clearTimeout(buscarTimer);
            var termino = $buscar.value.trim();
            if (termino.length < 2) {
                ocultarResultados();
                return;
            }
            buscarTimer = setTimeout(function () {
                fetch('{{ route("biblioteca-fisica.libros.buscar-ajax") }}?q=' + encodeURIComponent(termino),
                    { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { pintarResultados(data.results || []); })
                    .catch(function () { ocultarResultados(); });
            }, 300);
        });

        $buscar.addEventListener('focus', function () {
            if ($resultadosBody.children.length) $resultadosWrap.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            var dentro = e.target.closest('#libroBuscar') || e.target.closest('#libroResultadosWrap');
            if (!dentro) ocultarResultados();
        });

        $form.addEventListener('submit', function (e) {
            if (Object.keys(librosElegidos).length === 0) {
                e.preventDefault();
                $error.dataset.forzado = '1';
                $error.style.display = 'block';
                $buscar.focus();
            }
        });

        // Repuebla la selección tras un error de validación.
        (window.bvLibrosPreseleccionados || []).forEach(function (libro) {
            librosElegidos[String(libro.id)] = libro;
        });
        pintarChips();
    })();
</script>
@endpush
