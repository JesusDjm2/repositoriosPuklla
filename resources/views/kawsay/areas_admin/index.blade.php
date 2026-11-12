@extends('layouts.app')
@section('titulo', '- Áreas Kawsay')


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
        <div class="kawsay-area-card" style="--area-color: {{ $area->color ?: '#6e7d1e' }}">
            <div class="kawsay-area-card__head">
                <span class="kawsay-area-card__circle">
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
                            <div class="kac-progreso__barra"><span style="width: {{ $pct }}%;"></span></div>
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
