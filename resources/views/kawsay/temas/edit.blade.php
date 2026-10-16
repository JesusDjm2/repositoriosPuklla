@extends('layouts.app')
@section('titulo', '- Tema Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner :titulo="$tema->titulo" :subtitulo="$tema->area->nombre" icono="fa-map-signs" color="kawsay" />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Primero se ve la ficha del tema; "Editar datos" abre el formulario en el mismo lugar.
         Si el guardado falló por validación de estos campos, se reabre directo en edición. --}}
    @include('kawsay.temas.partials.ficha', [
        'tema' => $tema,
        'areas' => $areas,
        'tiposContenido' => $tiposContenido,
        'archivosPorTipo' => $archivosPorTipo,
        'editando' => $errors->hasAny(['kawsay_area_id', 'titulo', 'descripcion', 'imagen']),
    ])

    <div class="bv-form-card" id="contenido">
        <div class="bv-form-section-title">Contenido del tema ({{ $tema->archivos->count() }})</div>
        <p class="text-muted small mb-3">Fichas, teoría, videos, presentaciones y demás archivos de este tema. Elige un tipo y sube el archivo — no hace falta volver a elegir el tema.</p>

        <div class="kawsay-tipo-tabs">
            @foreach ($tiposContenido as $tipoSlug => $tipo)
                <button type="button" class="kawsay-tipo-tab {{ $loop->first ? 'is-active' : '' }}" data-tipo-tab="{{ $tipoSlug }}">
                    <i class="fa {{ \App\Services\KawsayTaxonomia::iconoAdmin($tipo['icono']) }}"></i> {{ $tipo['label'] }}
                    <span class="badge badge-light">{{ ($archivosPorTipo[$tipoSlug] ?? collect())->count() }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($tiposContenido as $tipoSlug => $tipo)
            <div class="kawsay-tipo-panel {{ $loop->first ? 'is-active' : '' }}" data-tipo-panel="{{ $tipoSlug }}">
                @if (!empty($archivosPorTipo[$tipoSlug]))
                    <div class="bv-crud-table-wrap mb-3">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach ($archivosPorTipo[$tipoSlug] as $archivo)
                                    <tr class="bv-table-row">
                                        <td>{{ $archivo->titulo }}</td>
                                        <td class="text-muted small">{{ strtoupper($archivo->tipo) }} @if($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif</td>
                                        <td class="text-right" style="width:1%;white-space:nowrap;">
                                            <a href="{{ $archivo->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en Drive">
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                            <form action="{{ route('kawsayarchivos.destroy', $archivo) }}" method="POST" style="display:inline"
                                                data-confirm-delete="¿Eliminar &quot;{{ $archivo->titulo }}&quot;?"
                                                data-confirm-delete-text="Esta acción no se puede deshacer.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small">Todavía no hay {{ strtolower($tipo['label']) }} en este tema.</p>
                @endif

                <form action="{{ route('kawsayarchivos.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-row align-items-end kawsay-subida">
                    @csrf
                    <input type="hidden" name="kawsay_tema_id" value="{{ $tema->id }}">
                    <input type="hidden" name="tipo_contenido" value="{{ $tipoSlug }}">
                    <div class="col-md-4 form-group mb-0">
                        <input type="text" name="titulo" class="form-control form-control-sm" maxlength="255"
                            placeholder="Título (opcional)">
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <input type="file" name="archivo[]" class="form-control-file kawsay-subida__archivos" multiple required
                            accept="{{ implode(',', array_map(fn($m) => '.' . $m, $tipo['mimes'])) }}">
                    </div>
                    <div class="col-md-2 form-group mb-0">
                        <button type="submit" class="btn btn-sm btn-primary btn-block kawsay-subida__btn">
                            <i class="fa fa-upload"></i> <span>Subir</span>
                        </button>
                    </div>
                    <small class="col-12 text-muted mt-2">
                        Puedes elegir varios archivos a la vez (hasta 20). Sin título, cada uno usa el nombre de su archivo;
                        con título y varios archivos se numeran (“Título 1”, “Título 2”…).
                    </small>
                </form>
            </div>
        @endforeach
    </div>

    <div class="bv-form-card">
        <div class="bv-form-section-title" id="comentarios-tema">Comentarios ({{ $tema->comentarios->count() }})</div>
        @if ($tema->comentarios->isEmpty())
            <p class="text-muted mb-0">Todavía no hay comentarios públicos en este tema.</p>
        @else
            @foreach ($tema->comentarios as $c)
                <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                    <div>
                        <strong>{{ $c->nombre }}</strong>
                        <span class="text-muted small">{{ $c->created_at->diffForHumans() }}</span>
                        <p class="mb-0">{{ $c->comentario }}</p>
                    </div>
                    <form action="{{ route('kawsaytemas.comentarios.destroy', [$tema, $c]) }}" method="POST"
                        data-confirm-delete="¿Eliminar este comentario?"
                        data-confirm-delete-text="Es de {{ $c->nombre }} y no se puede deshacer.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>

    <style>
        .kawsay-tipo-tabs { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1rem; }
        .kawsay-tipo-tab {
            border: 1px solid #dee2e6; background: #fff; border-radius: 999px;
            padding: .35rem .9rem; font-size: .82rem; color: #495057;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .kawsay-tipo-tab .badge { background: #f1f3f5; color: #495057; }
        .kawsay-tipo-tab.is-active { background: #343a40; color: #fff; border-color: #343a40; }
        .kawsay-tipo-tab.is-active .badge { background: rgba(255,255,255,.2); color: #fff; }
        .kawsay-tipo-panel { display: none; }
        .kawsay-tipo-panel.is-active { display: block; }
    </style>
    <script>
        // Subida múltiple: cada archivo va a Drive, así que puede tardar — se avisa y se evita el doble envío.
        document.querySelectorAll('.kawsay-subida').forEach(function (form) {
            form.addEventListener('submit', function () {
                var n = form.querySelector('.kawsay-subida__archivos').files.length;
                var btn = form.querySelector('.kawsay-subida__btn');
                btn.disabled = true;
                btn.querySelector('span').textContent = n > 1 ? 'Subiendo ' + n + '…' : 'Subiendo…';
            });
        });
        document.querySelectorAll('.kawsay-tipo-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var tipo = tab.getAttribute('data-tipo-tab');
                document.querySelectorAll('.kawsay-tipo-tab').forEach(function (t) { t.classList.remove('is-active'); });
                document.querySelectorAll('.kawsay-tipo-panel').forEach(function (p) { p.classList.remove('is-active'); });
                tab.classList.add('is-active');
                document.querySelector('.kawsay-tipo-panel[data-tipo-panel="' + tipo + '"]').classList.add('is-active');
            });
        });
    </script>

@endsection
