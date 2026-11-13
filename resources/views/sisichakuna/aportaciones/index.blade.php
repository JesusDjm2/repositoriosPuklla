@extends('layouts.app')
@section('titulo', '- Aportaciones Sisipedia')
@push('scripts')
    {{-- Summernote (editor de texto enriquecido para "detalle") se carga solo aquí
         porque es la única vista admin que usa este editor (jQuery ya está global). --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    {{-- defer: jQuery también se carga con defer; sin esto Summernote corría antes que jQuery y fallaba. --}}
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js" defer></script>
@endpush
@section('contenido')
@include('sisichakuna.partials.sisipedia-hero')
@include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'aportaciones'])

@php
    // Tono, ícono y etiqueta corta de cada rol (los mismos colores en KPIs, avatar y chip).
    $rolMeta = [
        'Equipo Puklla'   => ['tone' => 'info',    'icon' => 'fa-users'],
        'Docente'         => ['tone' => 'primary', 'icon' => 'fa-graduation-cap'],
        'Líder'           => ['tone' => 'success', 'icon' => 'fa-star'],
        'Niño/Estudiante' => ['tone' => 'warning', 'icon' => 'fa-child'],
    ];
    $fileMeta = [
        'imagen' => 'fa-picture-o', 'pdf' => 'fa-file-pdf-o', 'doc' => 'fa-file-word-o',
        'audio' => 'fa-music', 'video' => 'fa-video-camera',
    ];
    $filtroEstado = request('estado');
    $filtroRol = request('rol');
    // URL de la lista conservando los demás filtros (para KPIs y segmentos).
    $urlCon = fn (array $cambios) => route('sisipedia.aportaciones.index', array_filter(
        array_merge(request()->only(['q', 'rol', 'estado']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $iniciales = fn (string $nombre) => mb_strtoupper(collect(preg_split('/\s+/u', trim($nombre)))
        ->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp

{{-- ── Cabecera + resumen (los contadores son atajos de filtro) ───────────── --}}
<section class="adm-panel" aria-labelledby="aportesTitulo">
    <div class="adm-panel__head adm-page-head">
        <div class="adm-page-head__title">
            <span class="adm-panel__eyebrow">Sisipedia · Moderación</span>
            <h1 class="adm-panel__title" id="aportesTitulo">Aportaciones</h1>
            <p class="adm-panel__sub">Material enviado por el equipo, docentes, líderes y estudiantes desde la portada o desde un registro.</p>
        </div>
    </div>

    <nav class="adm-kpis" aria-label="Resumen y filtros rápidos">
        <a class="adm-kpi adm-tone--accent {{ ! $filtroEstado && ! $filtroRol ? 'is-active' : '' }}"
           href="{{ route('sisipedia.aportaciones.index', request()->only('q')) }}">
            <span class="adm-kpi__icon"><i class="fa fa-inbox" aria-hidden="true"></i></span>
            <span class="adm-kpi__value">{{ $conteo['total'] }}</span>
            <span class="adm-kpi__label">Todas</span>
        </a>
        <a class="adm-kpi adm-tone--warning {{ $filtroEstado === 'pendiente' ? 'is-active' : '' }}"
           href="{{ $urlCon(['estado' => $filtroEstado === 'pendiente' ? null : 'pendiente']) }}">
            <span class="adm-kpi__icon"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
            <span class="adm-kpi__value">{{ $conteo['pendientes'] }}</span>
            <span class="adm-kpi__label">Por revisar</span>
        </a>
        @foreach ($roles as $rol)
            @php $m = $rolMeta[$rol] ?? ['tone' => 'muted', 'icon' => 'fa-user']; @endphp
            <a class="adm-kpi adm-tone--{{ $m['tone'] }} {{ $filtroRol === $rol ? 'is-active' : '' }}"
               href="{{ $urlCon(['rol' => $filtroRol === $rol ? null : $rol]) }}">
                <span class="adm-kpi__icon"><i class="fa {{ $m['icon'] }}" aria-hidden="true"></i></span>
                <span class="adm-kpi__value">{{ $totals[$rol] ?? 0 }}</span>
                {{-- <wbr> tras "/": "Niño/Estudiante" baja de línea en la barra, no a mitad de palabra --}}
                <span class="adm-kpi__label">{!! str_replace('/', '/<wbr>', e(\App\Models\sisipedia\Aportacion::etiquetaRol($rol))) !!}</span>
            </a>
        @endforeach
    </nav>

    {{-- Filtros: funcionan sin JS (formulario GET); el JS solo los aplica al escribir/cambiar --}}
    <form method="GET" action="{{ route('sisipedia.aportaciones.index') }}" id="filtrosAportForm" class="adm-panel__bar" role="search">
        <div class="adm-seg" role="group" aria-label="Estado">
            <a class="adm-seg__item {{ ! $filtroEstado ? 'is-active' : '' }}" href="{{ $urlCon(['estado' => null]) }}">
                Todas <span class="adm-seg__count">{{ $conteo['total'] }}</span>
            </a>
            <a class="adm-seg__item {{ $filtroEstado === 'pendiente' ? 'is-active' : '' }}" href="{{ $urlCon(['estado' => 'pendiente']) }}">
                Pendientes <span class="adm-seg__count">{{ $conteo['pendientes'] }}</span>
            </a>
            <a class="adm-seg__item {{ $filtroEstado === 'aprobada' ? 'is-active' : '' }}" href="{{ $urlCon(['estado' => 'aprobada']) }}">
                Publicadas <span class="adm-seg__count">{{ $conteo['aprobadas'] }}</span>
            </a>
        </div>
        @if ($filtroEstado)
            <input type="hidden" name="estado" value="{{ $filtroEstado }}">
        @endif

        <div class="adm-search adm-panel__grow">
            <i class="fa fa-search" aria-hidden="true"></i>
            <input type="search" name="q" id="filtroQ" value="{{ request('q') }}"
                   class="form-control form-control-sm" placeholder="Buscar por nombre, título, institución o lugar…"
                   aria-label="Buscar aportaciones" autocomplete="off">
        </div>

        <select name="rol" id="filtroRol" class="form-control form-control-sm adm-toolbar__item" aria-label="Filtrar por rol">
            <option value="">Todos los roles</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol }}" @selected($filtroRol === $rol)>{{ \App\Models\sisipedia\Aportacion::etiquetaRol($rol) }}</option>
            @endforeach
        </select>

        @if (request()->hasAny(['q', 'rol', 'estado']))
            <a href="{{ route('sisipedia.aportaciones.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-times mr-1" aria-hidden="true"></i>Quitar filtros
            </a>
        @endif
        <noscript><button type="submit" class="btn btn-sm btn-primary">Filtrar</button></noscript>
    </form>
</section>

{{-- ── Aviso: aportes de la portada que esperan un registro ──────────────── --}}
@if ($pendientesGeneral > 0 && $filtroEstado !== 'pendiente')
    <div class="adm-callout" role="status">
        <span class="adm-callout__icon" aria-hidden="true"><i class="fa fa-link"></i></span>
        <div class="adm-callout__body">
            <span class="adm-callout__title">{{ $pendientesGeneral }} {{ $pendientesGeneral === 1 ? 'aporte espera' : 'aportes esperan' }} un registro</span>
            Llegaron desde la portada sin ficha asignada. Pulsa <strong>Aprobar</strong> en cada uno para elegir el registro donde se publicará.
        </div>
        <a href="{{ $urlCon(['estado' => 'pendiente']) }}" class="btn btn-sm btn-warning font-weight-bold">Revisar ahora</a>
    </div>
@endif

{{-- ── Lista ─────────────────────────────────────────────────────────────── --}}
@if ($aportaciones->isEmpty())
    <div class="adm-empty">
        <i class="fa fa-inbox" aria-hidden="true"></i>
        <strong>No hay aportaciones con estos filtros</strong>
        <span class="small">Prueba con otras palabras o quita los filtros.</span>
    </div>
@else
    <p class="small text-muted mb-2">
        Mostrando {{ $aportaciones->firstItem() }}–{{ $aportaciones->lastItem() }} de {{ $aportaciones->total() }}
    </p>
    <div class="adm-feed">
        @foreach ($aportaciones as $ap)
            @php
                $cat = $ap->category;
                $path = [];
                for ($cur = $cat; $cur; $cur = $cur->parent ?? null) {
                    array_unshift($path, $cur);
                }
                $m = $rolMeta[$ap->rol_nombre] ?? ['tone' => 'muted', 'icon' => 'fa-user'];
                $archivos = $ap->archivos;
                $extracto = $ap->detalle ? trim(html_entity_decode(strip_tags($ap->detalle))) : '';
            @endphp
            <article class="adm-entry adm-tone--{{ $m['tone'] }} {{ $ap->is_approved ? '' : 'adm-entry--pending' }}"
                     aria-labelledby="ap-{{ $ap->id }}">
                <span class="adm-avatar" aria-hidden="true">{{ $iniciales($ap->nombre_ol) }}</span>

                <div class="adm-entry__main">
                    <div class="adm-entry__head">
                        <h2 class="adm-entry__name" id="ap-{{ $ap->id }}">{{ $ap->nombre_ol }}</h2>
                        <span class="adm-pill"><i class="fa {{ $m['icon'] }}" aria-hidden="true"></i>{{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}</span>
                        @if ($ap->is_approved)
                            <span class="adm-pill adm-tone--success"><i class="fa fa-check" aria-hidden="true"></i>Publicada</span>
                        @else
                            <span class="adm-pill adm-tone--warning"><i class="fa fa-clock-o" aria-hidden="true"></i>Por revisar</span>
                        @endif
                    </div>

                    @if ($ap->titulo)
                        <p class="adm-entry__title">{{ $ap->titulo }}</p>
                    @endif
                    @if ($extracto !== '')
                        <p class="adm-entry__excerpt" title="{{ \Illuminate\Support\Str::limit($extracto, 600) }}">{{ $extracto }}</p>
                    @endif

                    <div class="adm-entry__meta">
                        <span>
                            <i class="fa fa-sitemap" aria-hidden="true"></i>
                            @if ($cat)
                                <ol class="adm-crumbs" aria-label="Registro">
                                    @foreach ($path as $node)
                                        <li><a href="{{ route('sisipedia.categories.admin-show', $node) }}">{{ $node->display_name }}</a></li>
                                    @endforeach
                                </ol>
                            @else
                                <em>Sin registro asignado</em>
                            @endif
                        </span>
                        @if ($ap->institucion)
                            <span><i class="fa fa-building-o" aria-hidden="true"></i>{{ $ap->institucion }}</span>
                        @endif
                        @if ($ap->ubicacion)
                            <span><i class="fa fa-map-marker" aria-hidden="true"></i>{{ $ap->ubicacion }}</span>
                        @endif
                        <span><i class="fa fa-calendar-o" aria-hidden="true"></i>
                            <time datetime="{{ $ap->created_at->toDateString() }}" title="{{ $ap->created_at->format('d/m/Y H:i') }}">{{ $ap->created_at->format('d/m/Y') }}</time>
                        </span>
                    </div>

                    @if ($archivos->isNotEmpty())
                        <div class="adm-entry__footer">
                            <div class="adm-files" aria-label="{{ $archivos->count() }} archivo(s)">
                                @foreach ($archivos->take(8) as $archivo)
                                    @if ($archivo->pendiente)
                                        <span class="adm-file" title="Subiéndose a Drive…"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
                                    @elseif ($archivo->fallo_en)
                                        <span class="adm-file adm-file--error" title="No se pudo subir a Drive"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></span>
                                    @else
                                        <a href="{{ $archivo->getUrl() }}" target="_blank" rel="noopener"
                                           class="adm-file adm-file--{{ $archivo->tipo }}"
                                           title="{{ ucfirst($archivo->tipo) }}: {{ $archivo->nombre_original }}">
                                            @if ($archivo->tipo === 'imagen' && $archivo->drive_id)
                                                <img src="https://drive.google.com/thumbnail?id={{ $archivo->drive_id }}&sz=w80" alt="" loading="lazy" width="36" height="36">
                                            @else
                                                <i class="fa {{ $fileMeta[$archivo->tipo] ?? 'fa-file-o' }}" aria-hidden="true"></i>
                                            @endif
                                            <span class="sr-only">Abrir {{ $archivo->tipo }}</span>
                                        </a>
                                    @endif
                                @endforeach
                                @if ($archivos->count() > 8)
                                    <span class="adm-files__more">+{{ $archivos->count() - 8 }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="adm-entry__actions">
                    @unless ($ap->is_approved)
                        <button type="button" class="btn btn-sm btn-success font-weight-bold"
                                data-toggle="modal" data-target="#modalAprobarAporte"
                                data-action="{{ route('sisipedia.aportaciones.approve', $ap) }}"
                                data-nombre="{{ $ap->nombre_ol }}"
                                data-rol="{{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}"
                                data-needs-category="{{ $cat ? '0' : '1' }}">
                            <i class="fa fa-check mr-1" aria-hidden="true"></i>Aprobar
                        </button>
                    @endunless
                    @if ($cat)
                        <a href="{{ route('sisipedia.categories.admin-show', $cat) }}#sisipedia-aportaciones-section"
                           class="btn btn-sm btn-outline-secondary" title="Ver en su registro" aria-label="Ver en su registro">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </a>
                    @endif
                    <button type="button"
                            class="btn btn-sm btn-outline-primary sisipedia-btn-editar-ap"
                            data-toggle="modal"
                            data-target="#modalEditarAporte"
                            data-action="{{ route('sisipedia.aportaciones.update', $ap) }}"
                            data-nombre="{{ $ap->nombre_ol }}"
                            data-rol="{{ $ap->rol_nombre }}"
                            data-titulo="{{ $ap->titulo ?? '' }}"
                            data-institucion="{{ $ap->institucion ?? '' }}"
                            data-ubicacion="{{ $ap->ubicacion ?? '' }}"
                            data-detalle="{{ \App\Services\HtmlSeguro::limpiar($ap->detalle) }}"
                            data-category-id="{{ $ap->category_id ?? '' }}"
                            data-is-approved="{{ $ap->is_approved ? '1' : '0' }}"
                            data-archivos-json="{{ $archivos->toJson() }}">
                        <i class="fa fa-pencil mr-1" aria-hidden="true"></i>Editar
                    </button>
                    <form action="{{ $cat ? route('sisipedia.categories.aportaciones.destroy', [$cat, $ap]) : route('sisipedia.aportaciones.destroy', $ap) }}"
                          method="POST"
                          data-confirm-delete="¿Eliminar la aportación de {{ $ap->nombre_ol }}?"
                          data-confirm-delete-text="Se borrarán también sus archivos. No se puede deshacer.">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar aportación de {{ $ap->nombre_ol }}">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>

    @if ($aportaciones->hasPages())
        <div class="mt-3">{{ $aportaciones->links() }}</div>
    @endif
@endif

{{-- Modal: Editar aportación --}}
<div class="modal fade" id="modalEditarAporte" tabindex="-1" role="dialog" aria-labelledby="modalEditarAporteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form method="POST" id="formEditarAporte" action="#" class="modal-content border-0 shadow-lg" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="modal-header border-0 pb-0 sisipedia-modal-editar-header">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalEditarAporteTitulo">
                        <i class="fa fa-edit mr-2"></i>Editar aportación
                    </h5>
                    <p class="mb-0 small sisipedia-modal-aprobar-sub">Modifica los datos o cambia el registro (categoría) asignado.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">
                            Nombre en lengua original <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_ol" id="editNombreOl"
                               class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Rol <span class="text-danger">*</span></label>
                        <select name="rol_nombre" id="editRolNombre" class="form-control" required>
                            @foreach(App\Models\sisipedia\Aportacion::ROLES as $rol)
                                <option value="{{ $rol }}">{{ App\Models\sisipedia\Aportacion::etiquetaRol($rol) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Título</label>
                        <input type="text" name="titulo" id="editTitulo"
                               class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Institución</label>
                        <input type="text" name="institucion" id="editInstitucion"
                               class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-dark d-block mb-1">Ubicación</label>
                        <input type="text" name="ubicacion" id="editUbicacion"
                               class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="font-weight-bold small text-dark d-block mb-1">Detalle / Descripción</label>
                    <textarea name="detalle" id="editDetalle" class="form-control summernote-editor" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="font-weight-bold small text-dark d-block mb-1">
                        <i class="fa fa-upload text-primary mr-1"></i>Archivos (subir más)
                    </label>
                    <div id="editArchivosPreviewWrap" class="mb-2">
                        <small class="text-muted d-block mb-1">Archivos actuales:</small>
                        <div id="editArchivosLista" class="d-flex flex-wrap adm-gap-1"></div>
                    </div>
                    <input type="file" name="archivos[]" multiple
                           accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp3,.wav,.ogg,.mp4,.webm,.mov,.m4v"
                           class="form-control">
                    <small class="text-muted">
                        <i class="fa fa-info-circle mr-1"></i>
                        Límites: Imagen 10MB · PDF/Word 100MB · Audio 150MB · Video 300MB
                    </small>
                </div>
                <hr class="my-3">
                <div class="mb-2">
                    <label class="font-weight-bold small text-dark d-block mb-1">
                        <i class="fa fa-sitemap text-primary mr-1"></i>Registro (categoría) asignado
                    </label>

                    {{-- Buscador de registros --}}
                    <input type="text" id="editBuscarRegistro"
                           class="form-control mb-2"
                           placeholder="Buscar registro por nombre o ruta…"
                           autocomplete="off">
                    <div class="sisipedia-reg-pick-scroll sisipedia-reg-pick-scroll--sm border rounded position-relative mb-2">
                        @forelse($categoryPickerRows ?? [] as $row)
                            <button type="button"
                                    class="sisipedia-reg-pick-row w-100 text-left border-0 {{ $row['is_root'] ? 'sisipedia-reg-pick-root' : 'sisipedia-reg-pick-child' }}"
                                    data-cat-id="{{ $row['id'] }}"
                                    data-depth="{{ $row['depth'] }}"
                                    data-search="{{ e($row['search_blob']) }}"
                                    style="--reg-depth: {{ $row['depth'] }};">
                                <span class="sisipedia-reg-pick-row-inner d-flex align-items-start w-100">
                                    <span class="sisipedia-reg-pick-check flex-shrink-0 mr-2 mt-1">
                                        <i class="fa fa-circle-o text-muted sisipedia-reg-ico-off" aria-hidden="true"></i>
                                        <i class="fa fa-check-circle text-success sisipedia-reg-ico-on d-none" aria-hidden="true"></i>
                                    </span>
                                    <span class="flex-grow-1 min-w-0">
                                        @if($row['is_root'])
                                            <span class="adm-chip adm-chip--video align-middle mr-1">Raíz</span>
                                        @endif
                                        <span class="sisipedia-reg-pick-name font-weight-bold d-block">{{ $row['name'] }}</span>
                                        <span class="sisipedia-reg-pick-path small text-muted d-block text-truncate" title="{{ $row['path_label'] }}">{{ $row['path_label'] }}</span>
                                    </span>
                                </span>
                            </button>
                        @empty
                            <div class="p-4 text-center text-muted small">No hay categorías configuradas.</div>
                        @endforelse
                    </div>
                    <div class="form-check mb-2">
                        <input type="hidden" name="is_approved" value="0">
                        <input type="checkbox" name="is_approved" id="editIsApproved" value="1" class="form-check-input">
                        <label class="form-check-label font-weight-bold small text-dark" for="editIsApproved">
                            <i class="fa fa-check-circle text-success mr-1"></i>Aprobado (visible al público)
                        </label>
                    </div>
                    <small class="text-muted d-block">
                        <i class="fa fa-info-circle mr-1"></i>Usa el buscador para encontrar y seleccionar el registro destino.
                    </small>
                </div>
                <input type="hidden" name="category_id" id="editCategoryId" value="">
            </div>
            <div class="modal-footer border-0 pt-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 font-weight-semibold">
                    <i class="fa fa-save mr-2"></i>Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formEditarAporte');

    // ── Buscador de registros en el modal editar ──
    var editInputBuscar = document.getElementById('editBuscarRegistro');
    var editPickRows = document.querySelectorAll('#modalEditarAporte .sisipedia-reg-pick-row');
    var editHiddenCat = document.getElementById('editCategoryId');

    function editAplicarFiltro() {
        if (!editInputBuscar) return;
        var q = (editInputBuscar.value || '').trim().toLowerCase();
        var visible = 0;
        editPickRows.forEach(function (row) {
            var blob = (row.getAttribute('data-search') || '').toLowerCase();
            var match = !q || blob.indexOf(q) !== -1;
            row.classList.toggle('d-none', !match);
            if (match) visible++;
        });
    }

    function editLimpiarSeleccion() {
        editPickRows.forEach(function (row) { row.classList.remove('is-selected'); });
        if (editHiddenCat) editHiddenCat.value = '';
    }

    function editSeleccionarRegistro(row) {
        var id = row.getAttribute('data-cat-id');
        editHiddenCat.value = id;
        editPickRows.forEach(function (r) { r.classList.toggle('is-selected', r === row); });
    }

    if (editInputBuscar) {
        editInputBuscar.addEventListener('input', editAplicarFiltro);
    }

    editPickRows.forEach(function (row) {
        row.addEventListener('click', function () { editSeleccionarRegistro(row); });
    });

    // ── Cargar datos al abrir modal ──
    document.querySelectorAll('.sisipedia-btn-editar-ap').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form) return;
            form.setAttribute('action', btn.getAttribute('data-action'));
            document.getElementById('editNombreOl').value    = btn.getAttribute('data-nombre') || '';
            document.getElementById('editTitulo').value      = btn.getAttribute('data-titulo') || '';
            document.getElementById('editInstitucion').value = btn.getAttribute('data-institucion') || '';
            document.getElementById('editUbicacion').value   = btn.getAttribute('data-ubicacion') || '';
            var detalleContent = btn.getAttribute('data-detalle') || '';

            var rolSel = document.getElementById('editRolNombre');
            if (rolSel) rolSel.value = btn.getAttribute('data-rol') || '';

            var catId = btn.getAttribute('data-category-id') || '';
            editLimpiarSeleccion();
            if (catId && editPickRows.length) {
                editPickRows.forEach(function (row) {
                    if (row.getAttribute('data-cat-id') === catId) {
                        editSeleccionarRegistro(row);
                    }
                });
            }
            if (editInputBuscar) editInputBuscar.value = '';
            editPickRows.forEach(function (row) { row.classList.remove('d-none'); });

            // Checkbox aprobado
            var isApproved = btn.getAttribute('data-is-approved') === '1';
            var chk = document.getElementById('editIsApproved');
            if (chk) chk.checked = isApproved;

            // Archivos actuales
            var archivosJson = btn.getAttribute('data-archivos-json');
            var listaArchivos = document.getElementById('editArchivosLista');
            if (listaArchivos) {
                listaArchivos.innerHTML = '';
                if (archivosJson) {
                    try {
                        var archivos = JSON.parse(archivosJson);
                        if (archivos.length > 0) {
                            var iconMap = {imagen: 'fa-file-image-o', pdf: 'fa-file-pdf-o', doc: 'fa-file-word-o', audio: 'fa-music', video: 'fa-video-camera'};
                            var colorMap = {imagen: 'info', pdf: 'danger', doc: 'primary', audio: 'success', video: 'warning'};
                            archivos.forEach(function(a) {
                                var badge = document.createElement('span');
                                badge.className = 'badge badge-' + (colorMap[a.tipo] || 'secondary');
                                badge.innerHTML = '<i class="fa ' + (iconMap[a.tipo] || 'fa-file') + ' mr-1"></i>' + (a.nombre_original || a.tipo);
                                listaArchivos.appendChild(badge);
                            });
                        } else {
                            listaArchivos.innerHTML = '<small class="text-muted">Sin archivos</small>';
                        }
                    } catch(e) {
                        listaArchivos.innerHTML = '<small class="text-muted">Sin archivos</small>';
                    }
                } else {
                    listaArchivos.innerHTML = '<small class="text-muted">Sin archivos</small>';
                }
            }

            setTimeout(function() {
                if (window.jQuery && $('.summernote-editor').length) {
                    $('.summernote-editor').summernote('code', detalleContent);
                }
            }, 100);
        });
    });

    // Inicializar Summernote para "detalle". Ya estamos dentro de DOMContentLoaded:
    // antes había un segundo listener anidado que nunca se ejecutaba y el editor
    // arrancaba con la configuración por defecto.
    (function () {
        if (window.jQuery && jQuery.fn.summernote) {
            $('.summernote-editor').summernote({
                height: 120,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                placeholder: 'Describe los detalles de esta aportación...'
            });
        }
    })();
});
</script>

{{-- Modal: asistente de vinculación y aprobación (un solo modal reutilizable) --}}
<div class="modal fade sisipedia-modal-aprobar" id="modalAprobarAporte" tabindex="-1" role="dialog" aria-labelledby="modalAprobarAporteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form method="POST" id="formModalAprobarAporte" action="#" class="modal-content border-0 shadow-lg sisipedia-modal-aprobar-content">
            @csrf
            <div class="modal-header border-0 pb-0 sisipedia-modal-aprobar-header text-white">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalAprobarAporteTitulo">
                        <i class="fa fa-map-signs mr-2"></i>Aprobar aportación
                    </h5>
                    <p class="mb-0 small sisipedia-modal-aprobar-sub">Confirma el destino público del material en Sisipedia.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-3">
                <div class="sisipedia-aporte-resumen card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="sisi-eyebrow mb-2">Vas a aprobar</div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between adm-gap-3">
                            <div>
                                <div class="h6 mb-1 font-weight-bold text-dark" id="modalAprobarNombre">—</div>
                                <span class="badge badge-secondary" id="modalAprobarRol">—</span>
                            </div>
                            <div class="text-muted small text-right">
                                <i class="fa fa-info-circle mr-1"></i>Visible para visitantes tras confirmar
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bloqueElegirRegistro">
                    <input type="hidden" id="modalPickerCategoryId" value="">

                    <label for="fnBuscarRegistro" class="font-weight-bold text-dark mb-1 d-block">
                        <i class="fa fa-search text-primary mr-1"></i>Buscar en el árbol de registros
                    </label>
                    <input type="text"
                           class="form-control mb-2"
                           id="fnBuscarRegistro"
                           placeholder="Nombre del registro o parte de la ruta (ej. tema, padre › hijo)…"
                           autocomplete="off">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                        <small class="form-text text-muted mb-0">
                            Los <strong>registros raíz</strong> agrupan a los <strong>hijos</strong> (indentados). Pulsa una fila para elegir.
                        </small>
                        <span class="adm-chip" id="fnRegistroContador"></span>
                    </div>

                    <label class="font-weight-bold text-dark mb-1 d-block">
                        <i class="fa fa-sitemap text-primary mr-1"></i>Elegir registro destino
                    </label>
                    <div class="sisipedia-reg-pick-scroll border rounded position-relative bg-white">
                        @forelse($categoryPickerRows ?? [] as $row)
                            <button type="button"
                                    class="sisipedia-reg-pick-row w-100 text-left border-0 {{ $row['is_root'] ? 'sisipedia-reg-pick-root' : 'sisipedia-reg-pick-child' }}"
                                    data-cat-id="{{ $row['id'] }}"
                                    data-depth="{{ $row['depth'] }}"
                                    data-search="{{ e($row['search_blob']) }}"
                                    style="--reg-depth: {{ $row['depth'] }};">
                                <span class="sisipedia-reg-pick-row-inner d-flex align-items-start w-100">
                                    <span class="sisipedia-reg-pick-check flex-shrink-0 mr-2 mt-1">
                                        <i class="fa fa-circle-o text-muted sisipedia-reg-ico-off" aria-hidden="true"></i>
                                        <i class="fa fa-check-circle text-success sisipedia-reg-ico-on d-none" aria-hidden="true"></i>
                                    </span>
                                    <span class="flex-grow-1 min-w-0">
                                        @if($row['is_root'])
                                            <span class="adm-chip adm-chip--video align-middle mr-1">Raíz</span>
                                        @endif
                                        <span class="sisipedia-reg-pick-name font-weight-bold d-block">{{ $row['name'] }}</span>
                                        <span class="sisipedia-reg-pick-path small text-muted d-block text-truncate" title="{{ $row['path_label'] }}">{{ $row['path_label'] }}</span>
                                    </span>
                                </span>
                            </button>
                        @empty
                            <div class="p-4 text-center text-muted small">No hay categorías configuradas.</div>
                        @endforelse
                    </div>
                    <p class="text-danger small mb-0 mt-2 d-none" id="fnRegistroSinCoincidencias">
                        <i class="fa fa-exclamation-triangle mr-1"></i>No hay registros que coincidan. Prueba otras palabras clave o borra el filtro.
                    </p>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fa fa-lightbulb-o text-warning mr-1"></i>
                        El aporte se mostrará en la ficha pública del registro elegido (el mismo nivel que ves en «Registros» del sitio).
                    </p>
                </div>

                <div id="bloqueSoloConfirmar" class="d-none alert alert-light border mb-0">
                    <i class="fa fa-check text-success mr-2"></i>
                    Este aporte ya tiene un registro asociado. Solo confirma la aprobación para publicarlo.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4 font-weight-semibold" id="btnModalAprobarSubmit">
                    <i class="fa fa-check mr-2"></i><span id="btnModalAprobarSubmitTexto">Confirmar y publicar</span>
                </button>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    var form = document.getElementById('formModalAprobarAporte');
    var inputBuscar = document.getElementById('fnBuscarRegistro');
    var hiddenCat = document.getElementById('modalPickerCategoryId');
    var pickRows = document.querySelectorAll('.sisipedia-reg-pick-row');
    var bloqueElegir = document.getElementById('bloqueElegirRegistro');
    var bloqueSolo = document.getElementById('bloqueSoloConfirmar');
    var msgVacío = document.getElementById('fnRegistroSinCoincidencias');
    var contador = document.getElementById('fnRegistroContador');
    var btnSubmitTexto = document.getElementById('btnModalAprobarSubmitTexto');

    function limpiarSeleccionRegistro() {
        if (hiddenCat) {
            hiddenCat.value = '';
            hiddenCat.removeAttribute('name');
        }
        pickRows.forEach(function (row) {
            row.classList.remove('is-selected');
        });
    }

    function seleccionarRegistro(row) {
        if (!hiddenCat) return;
        var id = row.getAttribute('data-cat-id');
        hiddenCat.value = id;
        pickRows.forEach(function (r) {
            r.classList.toggle('is-selected', r === row);
        });
    }

    function aplicarFiltroRegistros() {
        if (!inputBuscar) return;
        var q = (inputBuscar.value || '').trim().toLowerCase();
        var visible = 0;
        pickRows.forEach(function (row) {
            var blob = (row.getAttribute('data-search') || '').toLowerCase();
            var match = !q || blob.indexOf(q) !== -1;
            row.classList.toggle('d-none', !match);
            if (match) visible++;
        });
        if (hiddenCat && hiddenCat.value) {
            var sel = document.querySelector('.sisipedia-reg-pick-row.is-selected');
            if (sel && sel.classList.contains('d-none')) {
                limpiarSeleccionRegistro();
            }
        }
        if (msgVacío) {
            msgVacío.classList.toggle('d-none', !(q && visible === 0));
        }
        if (contador) {
            contador.textContent = visible + ' visible(s)';
        }
    }

    pickRows.forEach(function (row) {
        row.addEventListener('click', function () {
            seleccionarRegistro(row);
        });
    });

    if (inputBuscar) {
        inputBuscar.addEventListener('input', aplicarFiltroRegistros);
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            var needs = bloqueElegir && !bloqueElegir.classList.contains('d-none');
            if (needs && hiddenCat && !hiddenCat.value) {
                e.preventDefault();
                alert('Elige un registro destino en la lista (raíz o hijo).');
                return false;
            }
        });
    }

    function abrirModalAsistente(event) {
        var btn = event.relatedTarget;
        if (!btn || !form) return;

        var action = btn.getAttribute('data-action');
        if (action) form.setAttribute('action', action);

        var nombreEl = document.getElementById('modalAprobarNombre');
        var rolEl = document.getElementById('modalAprobarRol');
        if (nombreEl) nombreEl.textContent = btn.getAttribute('data-nombre') || '—';
        if (rolEl) {
            rolEl.textContent = btn.getAttribute('data-rol') || '—';
            rolEl.className = 'badge badge-secondary';
        }

        var needs = btn.getAttribute('data-needs-category') === '1';
        if (inputBuscar) inputBuscar.value = '';
        pickRows.forEach(function (row) {
            row.classList.remove('d-none');
        });
        aplicarFiltroRegistros();

        if (needs) {
            bloqueElegir.classList.remove('d-none');
            bloqueSolo.classList.add('d-none');
            limpiarSeleccionRegistro();
            if (hiddenCat) hiddenCat.setAttribute('name', 'category_id');
            if (btnSubmitTexto) btnSubmitTexto.textContent = 'Vincular registro y publicar';
        } else {
            bloqueElegir.classList.add('d-none');
            bloqueSolo.classList.remove('d-none');
            limpiarSeleccionRegistro();
            if (btnSubmitTexto) btnSubmitTexto.textContent = 'Confirmar y publicar';
        }
    }

    if (window.jQuery) {
        window.jQuery('#modalAprobarAporte').on('show.bs.modal', abrirModalAsistente);
        window.jQuery('#modalAprobarAporte').on('hidden.bs.modal', function () {
            if (form) form.setAttribute('action', '#');
            limpiarSeleccionRegistro();
        });
    } else {
        var modal = document.getElementById('modalAprobarAporte');
        if (modal) {
            modal.addEventListener('show.bs.modal', abrirModalAsistente);
            modal.addEventListener('hidden.bs.modal', function () {
                if (form) form.setAttribute('action', '#');
                limpiarSeleccionRegistro();
            });
        }
    }
})();
});
</script>

<script>
// Filtros: el formulario es GET real (funciona sin JS). Aquí solo se envía solo:
// el rol al cambiarlo y la búsqueda tras una pausa al escribir.
(function () {
    var form = document.getElementById('filtrosAportForm');
    var q = document.getElementById('filtroQ');
    var rol = document.getElementById('filtroRol');
    if (!form) return;
    var timer, inicial = q ? q.value : '';

    if (q) {
        // Tras recargar por una búsqueda, seguir escribiendo donde se quedó.
        if (inicial) { q.focus(); q.setSelectionRange(inicial.length, inicial.length); }
        q.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (q.value.trim() !== inicial.trim()) form.submit();
            }, 500);
        });
        // La x nativa del campo search vacía el texto: aplicar de inmediato.
        q.addEventListener('search', function () { if (!q.value) form.submit(); });
    }
    if (rol) rol.addEventListener('change', function () { form.submit(); });
})();
</script>

@endsection
