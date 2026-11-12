@extends('layouts.app')
@section('titulo', '- Tema Kawsay')


@section('contenido')

    @php
        // En producción las columnas nuevas llegan con el .sql 20261111: hasta entonces se ocultan sus campos.
        $conDescripcion = \App\Services\Contador::columnaExiste('kawsay_archivos', 'descripcion');
        $conYoutube = \App\Services\Contador::columnaExiste('kawsay_archivos', 'youtube_id');
        $nivelesSugeridos = ['Inicial', 'Primaria 1.º y 2.º grado', 'Primaria 3.º y 4.º grado', 'Primaria 5.º y 6.º grado', 'Secundaria', 'Formación docente', 'Todos los niveles'];
    @endphp

    <x-hero-banner :titulo="$tema->titulo" :subtitulo="$tema->area->nombre" icono="fa-map-signs" color="kawsay">
        <x-slot:actions>
            {{-- Tema del mes: tarjeta grande en la portada de Kawsay (solo uno a la vez) --}}
            <form action="{{ route('kawsaytemas.destacar', $tema) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-light btn-sm" aria-pressed="{{ $tema->destacado ? 'true' : 'false' }}">
                    <i class="fa {{ $tema->destacado ? 'fa-star' : 'fa-star-o' }} mr-1"></i>
                    {{ $tema->destacado ? 'Es el tema del mes (quitar)' : 'Destacar como tema del mes' }}
                </button>
            </form>
        </x-slot:actions>
    </x-hero-banner>

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

    @if ($conDescripcion)
        <datalist id="kawsay-niveles">
            @foreach ($nivelesSugeridos as $n)
                <option value="{{ $n }}">
            @endforeach
        </datalist>
    @endif

    <div class="bv-form-card" id="contenido">
        <div class="bv-form-section-title">Contenido del tema ({{ $tema->archivos->count() }})</div>
        <p class="text-muted small mb-3">Fichas, teoría, videos, audios, mapas y demás archivos de este tema. Elige un tipo y sube el archivo — no hace falta volver a elegir el tema.</p>

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
                {{-- Qué se puede subir en esta pestaña --}}
                <p class="small text-muted mb-2">
                    <i class="fa fa-info-circle mr-1"></i>{{ \App\Services\KawsayTaxonomia::aceptaTexto($tipoSlug) }}
                    @if ($tipoSlug === 'video' && $conYoutube)
                        · o un enlace de YouTube
                    @endif
                </p>

                @if (!empty($archivosPorTipo[$tipoSlug]))
                    <div class="bv-crud-table-wrap mb-3">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach ($archivosPorTipo[$tipoSlug] as $archivo)
                                    <tr class="bv-table-row">
                                        @if (in_array($tipoSlug, ['video', 'galeria', 'mapa', 'experiencia'], true))
                                            {{-- Video: portada propia (Drive muestra el primer fotograma, casi siempre negro).
                                                 Mapas y experiencias: miniatura para reconocer la imagen. --}}
                                            <td class="pr-0 sisi-col-min">
                                                @if ($tipoSlug === 'video' || $archivo->tipo === 'imagen')
                                                    <img src="{{ $archivo->miniaturaUrl(160) }}" alt="" class="adm-thumb adm-thumb--48x36" loading="lazy">
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            {{ $archivo->titulo }}
                                            @if ($archivo->esYoutube())
                                                <span class="adm-chip adm-chip--video ml-1"><i class="fa fa-youtube-play"></i> YouTube</span>
                                            @endif
                                            @if ($archivo->nivel)
                                                <span class="adm-chip ml-1">{{ $archivo->nivel }}</span>
                                            @endif
                                            @if ($archivo->descripcion)
                                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($archivo->descripcion, 140) }}</div>
                                            @endif

                                            <div class="d-flex flex-wrap align-items-center adm-gap-2 mt-1">
                                                @if ($tipoSlug === 'video')
                                                    <form action="{{ route('kawsayarchivos.portada', $archivo) }}" method="POST" enctype="multipart/form-data" class="d-inline">
                                                        @csrf
                                                        <label class="btn btn-sm btn-outline-secondary mb-0">
                                                            <i class="fa fa-picture-o"></i> {{ $archivo->portada ? 'Cambiar portada' : 'Subir portada' }}
                                                            <input type="file" name="portada" accept=".jpg,.jpeg,.png,.webp" class="d-none" onchange="this.form.submit()">
                                                        </label>
                                                    </form>
                                                    @if ($archivo->portada)
                                                        <form action="{{ route('kawsayarchivos.portada.destroy', $archivo) }}" method="POST" class="d-inline"
                                                            data-confirm-delete="¿Quitar la portada de &quot;{{ $archivo->titulo }}&quot;?"
                                                            data-confirm-delete-text="{{ $archivo->esYoutube() ? 'Se volverá a usar la miniatura de YouTube.' : 'Se volverá a usar la miniatura de Google Drive.' }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-link text-muted">Quitar</button>
                                                        </form>
                                                    @endif
                                                @endif

                                                {{-- Título, "para qué sirve" y grado: lo que ayuda al docente a decidir si le sirve --}}
                                                <details class="kawsay-editar-archivo w-100">
                                                    <summary class="btn btn-sm btn-link p-0">
                                                        <i class="fa fa-pencil"></i> {{ $conDescripcion ? 'Editar título, para qué sirve y grado' : 'Editar título' }}
                                                    </summary>
                                                    <form action="{{ route('kawsayarchivos.update', $archivo) }}" method="POST" class="form-row mt-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="col-md-7 form-group mb-2">
                                                            <label class="small mb-1" for="titulo-{{ $archivo->id }}">Título</label>
                                                            <input type="text" id="titulo-{{ $archivo->id }}" name="titulo" class="form-control form-control-sm" maxlength="255" required value="{{ $archivo->titulo }}">
                                                        </div>
                                                        @if ($conDescripcion)
                                                            <div class="col-md-5 form-group mb-2">
                                                                <label class="small mb-1" for="nivel-{{ $archivo->id }}">Grado o nivel</label>
                                                                <input type="text" id="nivel-{{ $archivo->id }}" name="nivel" class="form-control form-control-sm" maxlength="100" list="kawsay-niveles" value="{{ $archivo->nivel }}" placeholder="Ej.: Primaria 3.º y 4.º grado">
                                                            </div>
                                                            <div class="col-12 form-group mb-2">
                                                                <label class="small mb-1" for="desc-{{ $archivo->id }}">Para qué sirve</label>
                                                                <textarea id="desc-{{ $archivo->id }}" name="descripcion" rows="2" class="form-control form-control-sm" maxlength="500" placeholder="Ej.: Ficha para trabajar en parejas el ciclo del agua en la chacra.">{{ $archivo->descripcion }}</textarea>
                                                            </div>
                                                        @endif
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Guardar</button>
                                                        </div>
                                                    </form>
                                                </details>
                                            </div>
                                        </td>
                                        {{-- En celular esta columna se oculta (no cabe): el formato no es lo que se busca en el panel --}}
                                        <td class="text-muted small d-none d-md-table-cell">
                                            @if ($archivo->esYoutube())
                                                YouTube
                                            @else
                                                {{ strtoupper($archivo->tipo) }} @if($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif
                                            @endif
                                        </td>
                                        <td class="text-right sisi-col-min">
                                            <div class="d-flex flex-column flex-md-row justify-content-end adm-gap-2">
                                            <a href="{{ $archivo->getUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"
                                                title="{{ $archivo->esYoutube() ? 'Ver en YouTube' : 'Ver en Drive' }}">
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                            <form action="{{ route('kawsayarchivos.destroy', $archivo) }}" method="POST" class="d-inline"
                                                data-confirm-delete="¿Eliminar &quot;{{ $archivo->titulo }}&quot;?"
                                                data-confirm-delete-text="Esta acción no se puede deshacer.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            </div>
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
                    <div class="{{ $conDescripcion ? 'col-md-6' : 'col-md-4' }} form-group mb-2">
                        <input type="text" name="titulo" class="form-control form-control-sm" maxlength="255"
                            placeholder="Título (opcional)" aria-label="Título (opcional)">
                    </div>
                    @if ($conDescripcion)
                        <div class="col-md-6 form-group mb-2">
                            <input type="text" name="nivel" class="form-control form-control-sm" maxlength="100" list="kawsay-niveles"
                                placeholder="Grado o nivel (opcional)" aria-label="Grado o nivel (opcional)">
                        </div>
                        <div class="col-12 form-group mb-2">
                            <input type="text" name="descripcion" class="form-control form-control-sm" maxlength="500"
                                placeholder="Para qué sirve (opcional). Ej.: Para conversar en asamblea sobre la siembra." aria-label="Para qué sirve (opcional)">
                        </div>
                    @endif
                    <div class="{{ $conDescripcion ? 'col-md-9' : 'col-md-6' }} form-group mb-0">
                        <input type="file" name="archivo[]" class="form-control-file kawsay-subida__archivos" multiple required
                            accept="{{ implode(',', array_map(fn($m) => '.' . $m, $tipo['mimes'])) }}" aria-label="Archivos">
                    </div>
                    <div class="{{ $conDescripcion ? 'col-md-3' : 'col-md-2' }} form-group mb-0">
                        <button type="submit" class="btn btn-sm btn-primary btn-block kawsay-subida__btn">
                            <i class="fa fa-upload"></i> <span>Subir</span>
                        </button>
                    </div>
                    <small class="col-12 text-muted mt-2">
                        Puedes elegir varios archivos a la vez (hasta 20). Sin título, cada uno usa el nombre de su archivo;
                        con título y varios archivos se numeran (“Título 1”, “Título 2”…).
                    </small>
                </form>

                @if ($tipoSlug === 'video' && $conYoutube)
                    {{-- YouTube ajusta la calidad a la señal de cada persona: mejor para quien ve con datos móviles --}}
                    <form action="{{ route('kawsayarchivos.youtube') }}" method="POST" class="form-row align-items-end border-top pt-3 mt-3">
                        @csrf
                        <input type="hidden" name="kawsay_tema_id" value="{{ $tema->id }}">
                        <div class="col-12 small font-weight-bold mb-2"><i class="fa fa-youtube-play mr-1"></i> O agrega un video de YouTube</div>
                        <div class="col-md-6 form-group mb-2">
                            <input type="text" name="enlace" class="form-control form-control-sm" maxlength="255" required
                                placeholder="Enlace del video (https://youtu.be/…)" aria-label="Enlace del video de YouTube" value="{{ old('enlace') }}">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <input type="text" name="titulo" class="form-control form-control-sm" maxlength="255" required
                                placeholder="Título del video" aria-label="Título del video">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <input type="text" name="nivel" class="form-control form-control-sm" maxlength="100" list="kawsay-niveles"
                                placeholder="Grado o nivel (opcional)" aria-label="Grado o nivel (opcional)">
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <input type="text" name="descripcion" class="form-control form-control-sm" maxlength="500"
                                placeholder="Para qué sirve (opcional)" aria-label="Para qué sirve (opcional)">
                        </div>
                        <div class="col-md-3 form-group mb-0">
                            <button type="submit" class="btn btn-sm btn-outline-primary btn-block"><i class="fa fa-plus"></i> Agregar video</button>
                        </div>
                        <small class="col-12 text-muted mt-2">El video no se descarga a Drive: se reproduce desde YouTube y carga solo cuando el visitante pulsa “Ver”, para ahorrar datos.</small>
                    </form>
                @endif
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
        function activarTipo(tipo) {
            var panel = document.querySelector('.kawsay-tipo-panel[data-tipo-panel="' + tipo + '"]');
            if (!panel) return;
            document.querySelectorAll('.kawsay-tipo-tab').forEach(function (t) {
                t.classList.toggle('is-active', t.getAttribute('data-tipo-tab') === tipo);
            });
            document.querySelectorAll('.kawsay-tipo-panel').forEach(function (p) { p.classList.remove('is-active'); });
            panel.classList.add('is-active');
            try { sessionStorage.setItem('kawsayTipoTab', tipo); } catch (e) {}
        }
        document.querySelectorAll('.kawsay-tipo-tab').forEach(function (tab) {
            tab.addEventListener('click', function () { activarTipo(tab.getAttribute('data-tipo-tab')); });
        });
        // Tras subir, editar o borrar, la página recarga: se vuelve a la pestaña en la que estabas.
        try { var previa = sessionStorage.getItem('kawsayTipoTab'); if (previa) activarTipo(previa); } catch (e) {}
    </script>

@endsection
