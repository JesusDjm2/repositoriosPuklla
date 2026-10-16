{{-- Ficha del tema: primero se LEE (vista de datos) y desde el botón "Editar datos" se edita en el mismo lugar.
     Requiere: $tema, $areas, $tiposContenido, $archivosPorTipo, $editando (bool: abrir directo en edición, p.ej. tras un error de validación). --}}
@php
    $colorArea = $tema->area->color ?: '#6e7d1e';
    $totalArchivos = $tema->archivos->count();
    $tiposCubiertos = collect($archivosPorTipo)->filter(fn ($g) => $g->count() > 0)->count();
    $totalTipos = count($tiposContenido);
    $comentarios = $tema->comentarios->count();
    $actualizado = $tema->updated_at?->copy()->locale('es');
@endphp

<div class="kt-ficha {{ $editando ? 'is-editando' : '' }}" id="ktFicha">

    {{-- ══ MODO LECTURA ══ --}}
    <div class="kt-vista" id="ktVista">
        <div class="kt-vista__cabecera">
            <span class="kt-vista__seccion"><i class="fa fa-id-card" aria-hidden="true"></i> Datos del tema</span>
            <div class="kt-vista__acciones">
                <a href="{{ route('kawsay.tema', [$tema->area->slug, $tema->slug]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-eye"></i> Ver en el sitio
                </a>
                <button type="button" class="btn btn-sm btn-primary" id="ktEditar">
                    <i class="fa fa-pencil"></i> Editar datos
                </button>
            </div>
        </div>

        <div class="kt-vista__cuerpo">
            <div class="kt-portada {{ $tema->imagen ? '' : 'kt-portada--vacia' }}">
                @if ($tema->imagen)
                    <img src="{{ asset($tema->imagen) }}" alt="Portada de {{ $tema->titulo }}">
                @else
                    <i class="fa fa-image" aria-hidden="true"></i>
                    <span>Sin portada</span>
                @endif
            </div>

            <div class="kt-datos">
                <span class="kt-area" style="--kt-color: {{ $colorArea }}">
                    <i class="kt-area__punto"></i> {{ $tema->area->nombre }}
                </span>
                <h2 class="kt-titulo">{{ $tema->titulo }}</h2>

                @if ($tema->descripcion)
                    <p class="kt-descripcion">{!! nl2br(e($tema->descripcion)) !!}</p>
                @else
                    <p class="kt-descripcion kt-descripcion--vacia">
                        <i class="fa fa-circle-exclamation" aria-hidden="true"></i>
                        Sin descripción. Agrégala para que quien visite el tema sepa qué va a encontrar.
                    </p>
                @endif

                <dl class="kt-meta">
                    <div><dt>Enlace público</dt>
                        <dd><a href="{{ route('kawsay.tema', [$tema->area->slug, $tema->slug]) }}" target="_blank" rel="noopener">/kawsay/{{ $tema->area->slug }}/{{ $tema->slug }}</a></dd></div>
                    <div><dt>Última actualización</dt>
                        <dd title="{{ $tema->updated_at?->translatedFormat('d \d\e F \d\e Y, H:i') }}">{{ $actualizado?->diffForHumans() ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="kt-kpis">
            <a href="#contenido" class="kt-kpi">
                <span class="kt-kpi__valor">{{ $totalArchivos }}</span>
                <span class="kt-kpi__etiqueta">{{ $totalArchivos === 1 ? 'Archivo' : 'Archivos' }}</span>
            </a>
            <a href="#contenido" class="kt-kpi">
                <span class="kt-kpi__valor">{{ $tiposCubiertos }}<small>/{{ $totalTipos }}</small></span>
                <span class="kt-kpi__etiqueta">Tipos con contenido</span>
            </a>
            <a href="#comentarios-tema" class="kt-kpi">
                <span class="kt-kpi__valor">{{ $comentarios }}</span>
                <span class="kt-kpi__etiqueta">{{ $comentarios === 1 ? 'Comentario' : 'Comentarios' }}</span>
            </a>
            <div class="kt-kpi kt-kpi--tipos" aria-label="Contenido por tipo">
                @foreach ($tiposContenido as $slug => $tipo)
                    @php $n = ($archivosPorTipo[$slug] ?? collect())->count(); @endphp
                    <span class="kt-tipo {{ $n ? 'is-on' : '' }}" title="{{ $tipo['label'] }}: {{ $n ? $n . ($n === 1 ? ' archivo' : ' archivos') : 'sin archivos' }}">
                        <i class="fa {{ \App\Services\KawsayTaxonomia::iconoAdmin($tipo['icono']) }}" aria-hidden="true"></i>
                        @if ($n)<b>{{ $n }}</b>@endif
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══ MODO EDICIÓN (mismo lugar, sin cambiar de página) ══ --}}
    <form action="{{ route('kawsaytemas.update', $tema) }}" method="POST" enctype="multipart/form-data" class="kt-edicion" id="ktEdicion">
        @csrf
        @method('PUT')
        <div class="kt-vista__cabecera">
            <span class="kt-vista__seccion kt-vista__seccion--edit"><i class="fa fa-pencil" aria-hidden="true"></i> Editando datos del tema</span>
        </div>

        @include('kawsay.temas.partials.campos', ['tema' => $tema, 'areas' => $areas])

        <div class="bv-form-actions">
            <button type="submit" class="btn btn-sm btn-primary" id="ktGuardar">
                <i class="fa fa-save"></i> <span>Guardar cambios</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="ktCancelar">Cancelar</button>
            <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-link text-muted ml-auto">Volver al listado</a>
        </div>
    </form>
</div>

<style>
    .kt-ficha { background: #fff; border: 1px solid rgba(20, 20, 40, .06); border-radius: 1rem; box-shadow: 0 1px 2px rgba(20, 20, 40, .04), 0 .5rem 1.25rem rgba(20, 20, 40, .06); margin-bottom: 1.25rem; overflow: hidden; }
    .kt-edicion { display: none; padding: 1.25rem 1.4rem 1.4rem; }
    .kt-ficha.is-editando .kt-edicion { display: block; animation: ktEntra .25s ease; }
    .kt-ficha.is-editando .kt-vista { display: none; }
    @keyframes ktEntra { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    .kt-vista__cabecera { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: .9rem 1.4rem; border-bottom: 1px solid #f0f1ec; background: #fbfcf8; }
    .kt-edicion .kt-vista__cabecera { margin: -1.25rem -1.4rem 1.25rem; }
    .kt-vista__seccion { font-size: .78rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #6b7264; }
    .kt-vista__seccion i { color: var(--project-accent, #6e7d1e); margin-right: .35rem; }
    .kt-vista__seccion--edit { color: #55631a; }
    .kt-vista__acciones { display: flex; gap: .5rem; flex-wrap: wrap; }

    .kt-vista__cuerpo { display: grid; grid-template-columns: minmax(220px, 320px) 1fr; gap: 1.75rem; padding: 1.4rem; align-items: start; }
    .kt-portada { aspect-ratio: 4 / 3; border-radius: 14px; overflow: hidden; background: #f0ebe4; box-shadow: 0 8px 22px rgba(20, 30, 10, .14); border: 1px solid #e8e2d8; }
    .kt-portada img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .kt-portada--vacia { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .4rem; color: #b8a898; box-shadow: none; border: 2px dashed #ddd5c8; background: #faf7f2; }
    .kt-portada--vacia i { font-size: 2.2rem; }
    .kt-portada--vacia span { font-size: .78rem; font-weight: 600; }

    .kt-datos { min-width: 0; }
    .kt-area { display: inline-flex; align-items: center; gap: .45rem; padding: .25rem .8rem; border-radius: 999px; font-size: .78rem; font-weight: 700; color: #3d4437; background: color-mix(in srgb, var(--kt-color) 14%, white); border: 1px solid color-mix(in srgb, var(--kt-color) 35%, white); }
    .kt-area__punto { width: .6rem; height: .6rem; border-radius: 50%; background: var(--kt-color); display: inline-block; }
    .kt-titulo { font-size: 1.6rem; font-weight: 800; color: #1f241b; margin: .7rem 0 .6rem; line-height: 1.2; }
    .kt-descripcion { color: #4a5243; font-size: .95rem; line-height: 1.65; margin: 0 0 1.1rem; max-width: 70ch; }
    .kt-descripcion--vacia { color: #96620a; background: #fff8e8; border: 1px dashed #f0d9a3; border-radius: 10px; padding: .6rem .85rem; font-size: .85rem; }
    .kt-meta { display: flex; flex-wrap: wrap; gap: 1rem 2.5rem; margin: 0; padding-top: .9rem; border-top: 1px dashed #e9ecef; }
    .kt-meta dt { font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #9aa190; margin-bottom: .15rem; }
    .kt-meta dd { margin: 0; font-size: .85rem; color: #3d4437; overflow-wrap: anywhere; }
    .kt-meta a { color: var(--project-accent, #6e7d1e); text-decoration: none; }
    .kt-meta a:hover { text-decoration: underline; }

    .kt-kpis { display: grid; grid-template-columns: repeat(3, minmax(110px, 1fr)) minmax(240px, 2fr); border-top: 1px solid #f0f1ec; background: #fbfcf8; }
    .kt-kpi { padding: .95rem 1.2rem; display: flex; flex-direction: column; gap: .1rem; text-decoration: none; color: inherit; border-right: 1px solid #f0f1ec; transition: background .15s ease; }
    a.kt-kpi:hover { background: #f3f6e8; text-decoration: none; color: inherit; }
    .kt-kpi:last-child { border-right: 0; }
    .kt-kpi__valor { font-size: 1.5rem; font-weight: 800; color: #1f241b; line-height: 1.1; }
    .kt-kpi__valor small { font-size: .85rem; font-weight: 700; color: #9aa190; }
    .kt-kpi__etiqueta { font-size: .74rem; font-weight: 600; color: #7a8171; }
    .kt-kpi--tipos { flex-direction: row; flex-wrap: wrap; align-items: center; gap: .4rem; }
    .kt-tipo { position: relative; width: 2rem; height: 2rem; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; background: #f0f1ec; color: #c4c9bd; font-size: .8rem; }
    .kt-tipo.is-on { background: #eef3dc; color: #55631a; border: 1px solid #d5e0b0; }
    .kt-tipo b { position: absolute; top: -.4rem; right: -.4rem; min-width: 1.05rem; height: 1.05rem; padding: 0 .22rem; border-radius: 999px; background: #6e7d1e; color: #fff; font-size: .6rem; line-height: 1.05rem; text-align: center; }

    @media (max-width: 991.98px) {
        .kt-vista__cuerpo { grid-template-columns: 1fr; gap: 1.1rem; }
        .kt-portada { max-width: 420px; }
        .kt-kpis { grid-template-columns: repeat(3, 1fr); }
        .kt-kpi--tipos { grid-column: 1 / -1; border-top: 1px solid #f0f1ec; border-right: 0; }
    }
    @media (max-width: 575.98px) {
        .kt-vista__cabecera { padding: .8rem 1rem; }
        .kt-vista__cuerpo { padding: 1rem; }
        .kt-titulo { font-size: 1.3rem; }
        .kt-edicion { padding: 1rem; }
        .kt-edicion .kt-vista__cabecera { margin: -1rem -1rem 1rem; }
        .kt-kpi { padding: .8rem .8rem; }
        .kt-kpi__valor { font-size: 1.25rem; }
    }
    @media (prefers-reduced-motion: reduce) { .kt-ficha.is-editando .kt-edicion { animation: none; } }
</style>

<script>
    (function () {
        var ficha = document.getElementById('ktFicha');
        var form = document.getElementById('ktEdicion');
        if (!ficha || !form) return;

        var preview = document.getElementById('portadaPreview');
        var previewOriginal = preview ? preview.innerHTML : '';
        var nombreArchivo = document.getElementById('portadaNombre');

        function abrir() {
            ficha.classList.add('is-editando');
            var t = document.getElementById('tema-titulo');
            if (t) { t.focus(); t.setSelectionRange(t.value.length, t.value.length); }
            ficha.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cerrar() {
            // Descarta lo no guardado: campos y vista previa vuelven a lo original.
            form.reset();
            if (preview) { preview.innerHTML = previewOriginal; preview.classList.remove('is-quitada'); }
            if (nombreArchivo) nombreArchivo.textContent = '';
            ficha.classList.remove('is-editando');
            ficha.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.getElementById('ktEditar').addEventListener('click', abrir);
        document.getElementById('ktCancelar').addEventListener('click', cerrar);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && ficha.classList.contains('is-editando')) cerrar();
        });

        form.addEventListener('submit', function () {
            var btn = document.getElementById('ktGuardar');
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Guardando…';
        });
    })();
</script>
