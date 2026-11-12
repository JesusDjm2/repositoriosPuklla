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
