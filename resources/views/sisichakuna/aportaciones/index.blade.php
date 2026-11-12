@extends('layouts.app')
@section('titulo', '- Aportaciones Sisipedia')
@push('scripts')
    {{-- Summernote (editor de texto enriquecido para "detalle") se carga solo aquí
         porque es la única vista admin que usa este editor (jQuery ya está global). --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
@endpush
@section('contenido')
@include('sisichakuna.partials.sisipedia-hero')
@include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'aportaciones'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="adm-page-head">
            <div class="adm-page-head__title">
                <h1 class="h4">
                    <i class="fa fa-users mr-2 text-primary"></i>Aportaciones
                </h1>
                <small class="text-muted">Registros enviados por el equipo, docentes, líderes y estudiantes</small>
            </div>
            <div class="adm-page-head__actions">
                <span class="adm-chip">
                    {{ $aportaciones->total() }} total
                </span>
                @if($pendientesGeneral > 0)
                    <span class="adm-chip adm-chip--aporte">
                        <i class="fa fa-exclamation-circle mr-1"></i>{{ $pendientesGeneral }} pendiente(s)
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($pendientesGeneral > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4 sisipedia-aportes-alert d-flex align-items-start">
        <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center mr-3 flex-shrink-0 sisipedia-aportes-alert-icon">
            <i class="fa fa-link"></i>
        </span>
        <div>
            <div class="font-weight-bold mb-1">Pendientes de vincular a un registro</div>
            <p class="mb-0 small text-dark">
                Hay <strong>{{ $pendientesGeneral }}</strong> aporte(s) desde la portada sin registro asignado.
                Usa <strong>Vincular registro</strong> en la tabla para elegir la ficha Sisipedia donde se publicará el material.
            </p>
        </div>
    </div>
@endif

@php
    $rolMeta = [
        'Equipo Puklla'   => ['color' => 'info', 'icon' => 'fa-users'],
        'Docente'         => ['color' => 'primary',   'icon' => 'fa-graduation-cap'],
        'Líder'           => ['color' => 'success',   'icon' => 'fa-star'],
        'Niño/Estudiante' => ['color' => 'warning',   'icon' => 'fa-child'],
    ];
@endphp

{{-- ── Tarjetas de resumen ───────────────────────────────────── --}}
<div class="row mb-4 justify-content-center">
    <div class="col-6 col-lg-2 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">Total</div>
                <div class="h3 mb-0 font-weight-bold">{{ $aportaciones->total() }}</div>
            </div>
        </div>
    </div>
    @foreach($roles as $rol)
        <div class="col-6 col-lg-2 mb-3">
            <div class="card border-0 shadow-sm h-100 sisi-stat sisi-stat--{{ $rolMeta[$rol]['color'] ?? 'secondary' }}">
                <div class="card-body text-center py-3">
                    <div class="text-muted small mb-1">
                        <i class="fa {{ $rolMeta[$rol]['icon'] ?? 'fa-user' }} mr-1"></i>{{ $rol }}
                    </div>
                    <div class="h3 mb-0 font-weight-bold text-{{ $rolMeta[$rol]['color'] ?? 'secondary' }}">
                        {{ $totals[$rol] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ── Filtros ───────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('sisipedia.aportaciones.index') }}" id="filtrosAportForm"
              class="adm-toolbar">

            {{-- Keyword --}}
            <div class="input-group input-group-sm adm-toolbar__grow">
                <div class="input-group-prepend">
                    <span class="input-group-text border-right-0">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" name="q" id="filtroQ"
                       value="{{ request('q') }}"
                       class="form-control form-control-sm border-left-0"
                       placeholder="Buscar por nombre, título…"
                       autocomplete="off">
                <div class="input-group-append">
                    <button type="button" id="clearQ"
                            class="btn btn-outline-secondary btn-sm {{ request('q') ? '' : 'd-none' }}"
                            tabindex="-1" title="Limpiar búsqueda">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Rol --}}
            <select name="rol" id="filtroRol" class="form-control form-control-sm adm-toolbar__item" aria-label="Filtrar por rol">
                <option value="">Todos los roles</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol }}" @selected(request('rol') === $rol)>{{ $rol }}</option>
                @endforeach
            </select>

            {{-- Estado --}}
            <select name="estado" id="filtroEstado" class="form-control form-control-sm adm-toolbar__item" aria-label="Filtrar por estado">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                <option value="aprobada"  @selected(request('estado') === 'aprobada')>Aprobada</option>
            </select>

            {{-- Limpiar todo --}}
            @if(request()->hasAny(['q','rol','estado']))
                <a href="{{ route('sisipedia.aportaciones.index') }}"
                   class="btn btn-sm btn-outline-secondary" title="Quitar filtros">
                    <i class="fa fa-times mr-1"></i>Limpiar
                </a>
            @endif

        </form>
    </div>
</div>

{{-- ── Tabla ─────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($aportaciones->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                No hay aportaciones que coincidan con los filtros.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 sisi-aportes-table">
                <thead class="thead-light">
                    <tr>
                        <th class="sisi-col-min">#</th>
                        <th>Nombre en lengua original</th>
                        <th>Título</th>
                        <th>Rol</th>
                        <th>Registro (categoría)</th>
                        <th class="text-center">Estado</th>
                        <th>Institución / Trabajo / Ubicación</th>
                        <th class="text-center">Archivos</th>
                        <th>Fecha</th>
                        <th class="sisi-col-min"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aportaciones as $ap)
                    @php
                        $cat  = $ap->category;
                        $path = [];
                        $cur  = $cat;
                        while ($cur) { array_unshift($path, $cur); $cur = $cur->parent ?? null; }
                    @endphp
                    <tr>
                        <td class="text-muted small align-middle sisi-td-id" data-label="#">{{ $ap->id }}</td>

                        {{-- Nombre + detalle colapsable --}}
                        <td class="align-middle sisi-td-nombre" data-label="Nombre">
                            <div class="font-weight-semibold">{{ $ap->nombre_ol }}</div>
                            @if($ap->detalle)
                                <div class="text-muted small mt-1 sisi-aporte-preview"
                                     title="{{ html_entity_decode(strip_tags($ap->detalle)) }}">
                                    {{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags($ap->detalle))), 160) }}
                                </div>
                            @endif
                        </td>
                        <td class="align-middle small" data-label="Título">
                            @if($ap->titulo)
                                <span class="sisi-aporte-titulo">{{ $ap->titulo }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Rol --}}
                        <td class="align-middle" data-label="Rol">
                            <span class="badge badge-{{ $rolMeta[$ap->rol_nombre]['color'] ?? 'secondary' }}">
                                <i class="fa {{ $rolMeta[$ap->rol_nombre]['icon'] ?? 'fa-user' }} mr-1"></i>
                                {{ \App\Models\sisipedia\Aportacion::etiquetaRol($ap->rol_nombre) }}
                            </span>
                        </td>

                        {{-- Ruta de categoría --}}
                        <td class="align-middle sisi-aporte-registro" data-label="Registro">
                            @if($cat)
                                <div class="d-flex flex-wrap align-items-center adm-gap-1">
                                    @foreach($path as $i => $node)
                                        @if($i > 0)
                                            <i class="fa fa-angle-right text-muted small" aria-hidden="true"></i>
                                        @endif
                                        <a href="{{ route('sisipedia.categories.admin-show', $node) }}"
                                           class="adm-chip">
                                            {{ $node->display_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="adm-chip">Sin registro</span>
                                @if(!$ap->is_approved)
                                    <span class="adm-chip adm-chip--aporte">Requiere asignación</span>
                                @endif
                            @endif
                        </td>

                        <td class="align-middle text-center" data-label="Estado">
                            @if($ap->is_approved)
                                <span class="adm-chip adm-chip--ok">Aprobada</span>
                            @else
                                <span class="adm-chip adm-chip--aporte">Pendiente</span>
                            @endif
                        </td>

                        {{-- Institución / Ubicación --}}
                        <td class="align-middle small" data-label="Institución">
                            @if($ap->institucion)
                                <div><i class="fa fa-building text-muted mr-1"></i>{{ $ap->institucion }}</div>
                            @endif
                            @if($ap->ubicacion)
                                <div><i class="fa fa-map-marker text-muted mr-1"></i>{{ $ap->ubicacion }}</div>
                            @endif
                            @if(!$ap->institucion && !$ap->ubicacion)
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Archivos (desde nueva tabla) --}}
                        <td class="align-middle text-center" data-label="Archivos">
                            @php
                                $archivos = $ap->relationLoaded('archivos') ? $ap->archivos : $ap->archivos()->get();
                            @endphp
                            <div class="d-flex justify-content-center flex-wrap adm-gap-1 sisi-aporte-archivos">
                                @forelse($archivos as $archivo)
                                    @php
                                        $iconMap = ['imagen' => 'fa-file-image-o', 'pdf' => 'fa-file-pdf-o', 'doc' => 'fa-file-word-o', 'audio' => 'fa-music', 'video' => 'fa-video-camera'];
                                        $colorMap = ['imagen' => 'info', 'pdf' => 'danger', 'doc' => 'primary', 'audio' => 'success', 'video' => 'warning'];
                                    @endphp
                                    @if($archivo->pendiente)
                                        <span class="badge badge-secondary" title="Subiéndose a Drive en segundo plano">
                                            <i class="fa fa-spinner fa-spin"></i>
                                        </span>
                                    @elseif($archivo->fallo_en)
                                        <span class="badge badge-danger" title="No se pudo subir a Drive">
                                            <i class="fa fa-exclamation-triangle"></i>
                                        </span>
                                    @elseif($archivo->tipo === 'imagen')
                                        <a href="{{ $archivo->getUrl() }}" target="_blank" title="Ver imagen">
                                            <img src="https://drive.google.com/thumbnail?id={{ $archivo->drive_id }}&sz=w60"
                                                 alt="Imagen" width="60" height="60"
                                                 class="sisi-aporte-thumb"
                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';">
                                            <span class="badge badge-info d-none"><i class="fa fa-image"></i></span>
                                        </a>
                                    @else
                                        <a href="{{ $archivo->getUrl() }}" target="_blank"
                                           class="badge badge-{{ $colorMap[$archivo->tipo] ?? 'secondary' }}"
                                           title="{{ $archivo->tipo }}: {{ $archivo->nombre_original ?? '' }}">
                                            <i class="fa {{ $iconMap[$archivo->tipo] ?? 'fa-file' }}"></i>
                                        </a>
                                    @endif
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Fecha --}}
                        <td class="align-middle small text-nowrap text-muted" data-label="Fecha">
                            {{ $ap->created_at->format('d/m/Y') }}
                        </td>

                        {{-- Acciones --}}
                        <td class="align-middle text-right text-nowrap sisi-td-acciones">
                            @if($cat)
                                <a href="{{ route('sisipedia.categories.admin-show', $cat) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Ver en su registro" aria-label="Ver en su registro">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary sisipedia-btn-editar-ap"
                                    data-toggle="modal"
                                    data-target="#modalEditarAporte"
                                    data-action="{{ route('sisipedia.aportaciones.update', $ap) }}"
                                    data-nombre="{{ e($ap->nombre_ol) }}"
                                    data-rol="{{ $ap->rol_nombre }}"
                                    data-titulo="{{ e($ap->titulo ?? '') }}"
                                    data-institucion="{{ e($ap->institucion ?? '') }}"
                                    data-ubicacion="{{ e($ap->ubicacion ?? '') }}"
                                    data-detalle="{{ \App\Services\HtmlSeguro::limpiar($ap->detalle) }}"
                                    data-category-id="{{ $ap->category_id ?? '' }}"
                                    data-is-approved="{{ $ap->is_approved ? '1' : '0' }}"
                                    data-archivos-json="{{ $ap->archivos->toJson() }}"
                                    title="Editar / Vincular aportación">
                                <i class="fa fa-edit mr-1"></i>Editar
                            </button>
                            @if($cat)
                                <form action="{{ route('sisipedia.categories.aportaciones.destroy', [$cat, $ap]) }}"
                                      method="POST" class="d-inline"
                                      data-confirm-delete="¿Eliminar la aportación de {{ $ap->nombre_ol }}?"
                                      data-confirm-delete-text="Se borrarán también sus archivos. No se puede deshacer.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar aportación">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('sisipedia.aportaciones.destroy', $ap) }}"
                                      method="POST" class="d-inline"
                                      data-confirm-delete="¿Eliminar la aportación de {{ $ap->nombre_ol }}?"
                                      data-confirm-delete-text="Se borrarán también sus archivos. No se puede deshacer.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar aportación">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($aportaciones->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $aportaciones->links() }}
            </div>
        @endif
        @endif
    </div>
</div>

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

    // Inicializar Summernote editor para el campo de detalle
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery) {
            $('.summernote-editor').summernote({
                height: 120,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                placeholder: 'Describe los detalles de esta aportación...',
                callbacks: {
                    onInit: function() {
                        $('.note-editable').css('font-size', '14px');
                    }
                }
            });
        }
    });
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
(function () {
    var baseUrl  = '{{ route('sisipedia.aportaciones.index') }}';
    var qInput   = document.getElementById('filtroQ');
    var clearBtn = document.getElementById('clearQ');
    var rolSel   = document.getElementById('filtroRol');
    var estSel   = document.getElementById('filtroEstado');
    var timer;

    // Construye la URL con los valores actuales y navega a ella
    function navigate() {
        var params = [];
        var q   = qInput  ? qInput.value.trim()  : '';
        var rol = rolSel  ? rolSel.value          : '';
        var est = estSel  ? estSel.value          : '';
        if (q)   params.push('q='      + encodeURIComponent(q));
        if (rol) params.push('rol='    + encodeURIComponent(rol));
        if (est) params.push('estado=' + encodeURIComponent(est));
        window.location.href = baseUrl + (params.length ? '?' + params.join('&') : '');
    }

    // Keyword — debounce 420 ms
    if (qInput) {
        qInput.addEventListener('input', function () {
            if (clearBtn) clearBtn.classList.toggle('d-none', !this.value);
            clearTimeout(timer);
            timer = setTimeout(navigate, 420);
        });
    }

    // Limpiar campo X
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (qInput) qInput.value = '';
            clearBtn.classList.add('d-none');
            clearTimeout(timer);
            navigate();
        });
    }

    // Dropdowns — navegar inmediatamente al cambiar
    [rolSel, estSel].forEach(function (el) {
        if (el) el.addEventListener('change', function () {
            clearTimeout(timer);
            navigate();
        });
    });
})();
</script>

@endsection
