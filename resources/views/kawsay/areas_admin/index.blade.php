@extends('layouts.app')
@section('titulo', '- Áreas Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    @php
        // Resumen global y por tema: qué tiene contenido y qué no, para decidir dónde trabajar.
        $totalTipos = count($tiposContenido);
        $resumenTema = [];
        $global = ['temas' => 0, 'con' => 0, 'sin' => 0, 'archivos' => 0];

        foreach ($areas as $a) {
            foreach ($a->temas as $t) {
                $porTipo = $conteos[$t->id] ?? [];
                $total = array_sum($porTipo);
                $resumenTema[$t->id] = ['porTipo' => $porTipo, 'total' => $total, 'cubiertos' => count($porTipo)];
                $global['temas']++;
                $global['archivos'] += $total;
                $total > 0 ? $global['con']++ : $global['sin']++;
            }
        }
    @endphp

    <x-hero-banner titulo="Áreas Kawsay" subtitulo="Las áreas que organizan todo el repositorio educativo — y el estado de contenido de cada tema"
        icono="fa-th-large" color="kawsay">
        <x-slot:stats>
            <x-hero-stat icono="fa-th-large" :valor="$areas->count()" etiqueta="Áreas" />
            <x-hero-stat icono="fa-map-signs" :valor="$global['temas']" etiqueta="Temas" />
            <x-hero-stat icono="fa-circle-check" :valor="$global['con']" etiqueta="Con contenido" />
            <x-hero-stat icono="fa-circle-exclamation" :valor="$global['sin']" etiqueta="Sin contenido" />
            <x-hero-stat icono="fa-file" :valor="$global['archivos']" etiqueta="Archivos" />
        </x-slot:stats>
        <x-slot:actions>
            <a href="{{ route('kawsayareas.create') }}" class="btn btn-sm btn-light">
                <i class="fa fa-plus"></i> Nueva área
            </a>
        </x-slot:actions>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @error('area')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    @if ($global['temas'] > 0)
        <div class="kac-toolbar">
            <div class="kac-filtros" role="group" aria-label="Filtrar temas">
                <button type="button" class="kac-filtro is-activo" data-filtro="todos">Todos <span>{{ $global['temas'] }}</span></button>
                <button type="button" class="kac-filtro" data-filtro="vacio">Sin contenido <span>{{ $global['sin'] }}</span></button>
                <button type="button" class="kac-filtro" data-filtro="con">Con contenido <span>{{ $global['con'] }}</span></button>
            </div>
            <p class="kac-leyenda">
                <i class="fa fa-circle-info" aria-hidden="true"></i>
                Cada ícono es un tipo de contenido: <strong>encendido</strong> = tiene archivos (el número es la cantidad), <strong>apagado</strong> = falta.
            </p>
        </div>
    @endif

    @forelse ($areas as $area)
        @php
            $temasArea = $area->temas;
            $conArea = $temasArea->filter(fn ($t) => $resumenTema[$t->id]['total'] > 0)->count();
            $archivosArea = $temasArea->sum(fn ($t) => $resumenTema[$t->id]['total']);
            $pct = $temasArea->count() ? round($conArea / $temasArea->count() * 100) : 0;
        @endphp
        <div class="kawsay-area-card">
            <div class="kawsay-area-card__head">
                <span class="kawsay-area-card__circle" style="border-color: {{ $area->color ?: '#6e7d1e' }};">
                    @if ($area->imagen)
                        <img src="{{ asset($area->imagen) }}" alt="{{ $area->nombre }}">
                    @else
                        <i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i>
                    @endif
                </span>
                <div class="kawsay-area-card__info">
                    <div class="kawsay-area-card__title">
                        {{ $area->nombre }}
                        <span class="kawsay-area-card__orden" title="Orden">#{{ $area->orden }}</span>
                    </div>
                    @if ($area->descripcion)
                        <p class="kawsay-area-card__desc">{{ $area->descripcion }}</p>
                    @endif

                    @if ($temasArea->count())
                        <div class="kac-progreso" title="{{ $conArea }} de {{ $temasArea->count() }} temas tienen contenido">
                            <div class="kac-progreso__barra"><span style="width: {{ $pct }}%; background: {{ $area->color ?: '#6e7d1e' }};"></span></div>
                            <span class="kac-progreso__texto">
                                <strong>{{ $conArea }}</strong> de {{ $temasArea->count() }} temas con contenido
                                · <strong>{{ $archivosArea }}</strong> {{ $archivosArea === 1 ? 'archivo' : 'archivos' }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="kawsay-area-card__actions">
                    <a href="{{ route('kawsay.area', $area->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en el sitio">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('kawsayareas.edit', $area) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <form action="{{ route('kawsayareas.destroy', $area) }}" method="POST" style="display:inline"
                        data-confirm-delete="¿Eliminar &quot;{{ $area->nombre }}&quot;?"
                        data-confirm-delete-text="Solo se puede si no tiene temas asociados.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="kac-temas">
                @forelse ($temasArea as $tema)
                    @php
                        $r = $resumenTema[$tema->id];
                        $vacio = $r['total'] === 0;
                        $ratio = $r['cubiertos'] / max($totalTipos, 1);
                        $estado = $vacio ? 'vacio' : ($ratio >= .5 ? 'bueno' : 'parcial');
                    @endphp
                    <div class="kac-tema kac-tema--{{ $estado }}" data-estado="{{ $vacio ? 'vacio' : 'con' }}">
                        <div class="kac-tema__nombre">
                            <a href="{{ route('kawsaytemas.edit', $tema) }}" class="kac-tema__titulo">{{ $tema->titulo }}</a>
                            <span class="kac-tema__flags">
                                <i class="fa fa-image {{ $tema->imagen ? 'is-on' : '' }}" title="{{ $tema->imagen ? 'Tiene portada' : 'Sin portada' }}"></i>
                                <i class="fa fa-align-left {{ $tema->descripcion ? 'is-on' : '' }}" title="{{ $tema->descripcion ? 'Tiene descripción' : 'Sin descripción' }}"></i>
                                <span class="kac-tema__coment {{ $tema->comentarios_count ? 'is-on' : '' }}" title="{{ $tema->comentarios_count }} {{ $tema->comentarios_count === 1 ? 'comentario' : 'comentarios' }}">
                                    <i class="fa fa-comment"></i>{{ $tema->comentarios_count }}
                                </span>
                            </span>
                        </div>

                        <div class="kac-tipos" role="list" aria-label="Contenido por tipo">
                            @foreach ($tiposContenido as $slug => $tipo)
                                @php $n = $r['porTipo'][$slug] ?? 0; @endphp
                                <span class="kac-tipo {{ $n ? 'is-on' : '' }}" role="listitem"
                                    title="{{ $tipo['label'] }}: {{ $n ? $n . ($n === 1 ? ' archivo' : ' archivos') : 'sin archivos' }}">
                                    <i class="fa {{ \App\Services\KawsayTaxonomia::iconoAdmin($tipo['icono']) }}" aria-hidden="true"></i>
                                    @if ($n)<b>{{ $n }}</b>@endif
                                    <span class="sr-only visually-hidden">{{ $tipo['label'] }}: {{ $n }}</span>
                                </span>
                            @endforeach
                        </div>

                        <div class="kac-tema__estado">
                            @if ($vacio)
                                <span class="kac-badge kac-badge--vacio"><i class="fa fa-circle-exclamation"></i> Sin contenido</span>
                            @else
                                <span class="kac-badge kac-badge--{{ $estado }}">
                                    <i class="fa fa-circle-check"></i> {{ $r['total'] }} {{ $r['total'] === 1 ? 'archivo' : 'archivos' }}
                                </span>
                                <small>{{ $r['cubiertos'] }} de {{ $totalTipos }} tipos</small>
                            @endif
                        </div>

                        <div class="kac-tema__acciones">
                            <a href="{{ route('kawsaytemas.edit', $tema) }}#contenido"
                                class="btn btn-sm {{ $vacio ? 'btn-primary' : 'btn-outline-secondary' }}" title="Subir o gestionar contenido">
                                <i class="fa fa-upload"></i> <span>{{ $vacio ? 'Agregar' : 'Gestionar' }}</span>
                            </a>
                            <a href="{{ route('kawsay.tema', [$area->slug, $tema->slug]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en el sitio">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <span class="kawsay-area-card__vacio">Todavía no tiene temas.</span>
                @endforelse
            </div>

            <a href="{{ route('kawsaytemas.create', ['area' => $area->id]) }}" class="kawsay-area-card__tema kawsay-area-card__tema--nuevo mt-3">
                <i class="fa fa-plus"></i> Agregar tema
            </a>
        </div>
    @empty
        <div class="bv-crud-table-wrap">
            <div class="bv-empty-state">
                <i class="fa fa-th-large bv-empty-icon"></i>
                Todavía no hay áreas creadas.
            </div>
        </div>
    @endforelse

    <style>
        .kawsay-area-card {
            background: #fff;
            border: 1px solid rgba(20, 20, 40, 0.06);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(20, 20, 40, 0.04), 0 0.5rem 1.25rem rgba(20, 20, 40, 0.06);
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.25rem;
        }
        .kawsay-area-card__head { display: flex; align-items: flex-start; gap: 1rem; }
        .kawsay-area-card__circle {
            flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
            width: 56px; height: 56px; border-radius: 50%; border: 3px solid; overflow: hidden;
            background: #f1f3f5; color: #6c757d; font-size: 1.3rem;
        }
        .kawsay-area-card__circle img { width: 100%; height: 100%; object-fit: cover; }
        .kawsay-area-card__info { flex: 1 1 auto; min-width: 0; }
        .kawsay-area-card__title { font-weight: 700; font-size: 1.05rem; color: #212529; display: flex; align-items: center; gap: 0.5rem; }
        .kawsay-area-card__orden { font-size: 0.72rem; font-weight: 600; color: #adb5bd; }
        .kawsay-area-card__desc { margin: 0.2rem 0 0; color: #6c757d; font-size: 0.85rem; }
        .kawsay-area-card__actions { flex-shrink: 0; display: flex; gap: 0.35rem; }
        .kawsay-area-card__vacio { color: #adb5bd; font-size: 0.85rem; font-style: italic; }
        .kawsay-area-card__tema {
            display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.8rem; border-radius: 999px;
            background: #f1f3f5; color: #495057; font-size: 0.82rem; font-weight: 600; text-decoration: none;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }
        .kawsay-area-card__tema--nuevo { background: transparent; border: 1px dashed #ced4da; color: #868e96; }
        .kawsay-area-card__tema--nuevo:hover { border-color: #6e7d1e; color: #6e7d1e; background: rgba(110, 125, 30, 0.06); text-decoration: none; }

        /* Barra de filtros y leyenda */
        .kac-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem 1.5rem; margin-bottom: 1rem; }
        .kac-filtros { display: inline-flex; gap: .4rem; flex-wrap: wrap; }
        .kac-filtro {
            border: 1px solid #dee2e6; background: #fff; border-radius: 999px; padding: .35rem .9rem;
            font-size: .82rem; font-weight: 600; color: #495057; cursor: pointer; display: inline-flex; align-items: center; gap: .45rem;
        }
        .kac-filtro span { background: #f1f3f5; border-radius: 999px; padding: 0 .5rem; font-size: .72rem; }
        .kac-filtro.is-activo { background: #343a40; border-color: #343a40; color: #fff; }
        .kac-filtro.is-activo span { background: rgba(255, 255, 255, .2); }
        .kac-leyenda { margin: 0; font-size: .8rem; color: #868e96; }
        .kac-leyenda i { margin-right: .25rem; }

        /* Progreso del área */
        .kac-progreso { margin-top: .7rem; max-width: 520px; }
        .kac-progreso__barra { height: 6px; border-radius: 999px; background: #edf0e6; overflow: hidden; }
        .kac-progreso__barra span { display: block; height: 100%; border-radius: 999px; }
        .kac-progreso__texto { display: block; margin-top: .3rem; font-size: .78rem; color: #6c757d; }

        /* Lista de temas */
        .kac-temas { margin-top: 1rem; padding-top: .5rem; border-top: 1px dashed #e9ecef; }
        .kac-tema {
            display: grid; align-items: center; gap: .5rem 1.25rem;
            grid-template-columns: minmax(180px, 1.5fr) minmax(250px, 2fr) 150px auto;
            padding: .7rem .5rem .7rem .75rem; border-bottom: 1px solid #f1f3f5; border-left: 4px solid transparent;
            border-radius: 8px; transition: background .15s ease;
        }
        .kac-tema:last-child { border-bottom: 0; }
        .kac-tema:hover { background: #fafbf7; }
        .kac-tema--vacio { border-left-color: #e5484d; }
        .kac-tema--parcial { border-left-color: #f5a623; }
        .kac-tema--bueno { border-left-color: #46a758; }
        .kac-tema.is-oculto { display: none; }

        .kac-tema__nombre { min-width: 0; }
        .kac-tema__titulo { font-weight: 700; font-size: .92rem; color: #212529; text-decoration: none; display: block; }
        .kac-tema__titulo:hover { color: #6e7d1e; text-decoration: none; }
        .kac-tema__flags { display: inline-flex; align-items: center; gap: .55rem; margin-top: .2rem; font-size: .72rem; color: #ced4da; }
        .kac-tema__flags .is-on { color: #6e7d1e; }
        .kac-tema__coment { display: inline-flex; align-items: center; gap: .25rem; }
        .kac-tema__coment i { margin-right: 0; }

        .kac-tipos { display: flex; flex-wrap: wrap; gap: .35rem; }
        .kac-tipo {
            position: relative; width: 2.15rem; height: 2.15rem; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #f4f5f2; color: #c4c9bd; font-size: .85rem; border: 1px solid transparent;
        }
        .kac-tipo.is-on { background: #eef3dc; color: #55631a; border-color: #d5e0b0; }
        .kac-tipo b {
            position: absolute; top: -.4rem; right: -.4rem; min-width: 1.1rem; height: 1.1rem; padding: 0 .25rem;
            border-radius: 999px; background: #6e7d1e; color: #fff; font-size: .62rem; line-height: 1.1rem; text-align: center;
        }

        .kac-tema__estado { display: flex; flex-direction: column; align-items: flex-start; gap: .15rem; }
        .kac-tema__estado small { color: #868e96; font-size: .72rem; }
        .kac-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .65rem; border-radius: 999px; font-size: .76rem; font-weight: 700; }
        .kac-badge--vacio { background: #fdecec; color: #b42525; }
        .kac-badge--parcial { background: #fff3dc; color: #96620a; }
        .kac-badge--bueno { background: #e6f4ea; color: #2a6e3a; }

        .kac-tema__acciones { display: flex; gap: .35rem; justify-content: flex-end; }

        @media (max-width: 991.98px) {
            .kac-tema { grid-template-columns: 1fr auto; }
            .kac-tipos { grid-column: 1 / -1; order: 3; }
            .kac-tema__estado { flex-direction: row; align-items: center; gap: .5rem; grid-column: 1 / -1; order: 2; }
        }
        .hero-banner__stats { flex-wrap: wrap; }
        @media (max-width: 575.98px) {
            .kac-tipos { gap: .3rem; }
            .kac-tipo { width: 1.85rem; height: 1.85rem; font-size: .75rem; border-radius: 8px; }
            .kawsay-area-card { padding: 1rem; }
            .kawsay-area-card__head { flex-wrap: wrap; }
            .kawsay-area-card__actions { width: 100%; }
            .kac-tema__acciones .btn span { display: none; }
            .kac-leyenda { display: none; }
        }
    </style>

    <script>
        (function () {
            var botones = document.querySelectorAll('.kac-filtro');
            if (!botones.length) return;
            botones.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var filtro = btn.getAttribute('data-filtro');
                    botones.forEach(function (b) { b.classList.toggle('is-activo', b === btn); });
                    document.querySelectorAll('.kac-tema').forEach(function (fila) {
                        var estado = fila.getAttribute('data-estado');
                        fila.classList.toggle('is-oculto', filtro !== 'todos' && estado !== filtro);
                    });
                });
            });
        })();
    </script>

@endsection
