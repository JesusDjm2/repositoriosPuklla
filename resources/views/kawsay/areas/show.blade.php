@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $area->nombre)

@section('meta')
    <meta property="og:title" content="{{ $area->nombre }} — Kawsay">
    @if ($area->descripcion)
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($area->descripcion, 200) }}">
    @endif
    @if ($area->imagen)
        <meta property="og:image" content="{{ asset($area->imagen) }}">
    @endif
@endsection

@section('contenido')
    @php
        $color = $area->color ?: '#6e7d1e';
        $totalMateriales = $temas->sum('archivos_count');
        $formatoInfo = [
            'video' => ['icono' => 'fa-play-circle', 'uno' => 'video', 'varios' => 'videos'],
            'audio' => ['icono' => 'fa-headphones', 'uno' => 'audio', 'varios' => 'audios'],
            'foto' => ['icono' => 'fa-camera', 'uno' => 'foto', 'varios' => 'fotos'],
            'documento' => ['icono' => 'fa-file-pdf', 'uno' => 'documento', 'varios' => 'documentos'],
        ];
    @endphp

    {{-- ═══ Banner del área: su foto, su color y lo que contiene. Estilos: public/css/kawsay.css (.karea-*) ═══ --}}
    <header class="karea-hero" style="--area-color: {{ $color }}">
        @if ($area->imagen)
            <img class="karea-hero__fondo" src="{{ asset($area->imagen) }}" alt="" fetchpriority="high" decoding="async">
        @endif
        <div class="container karea-hero__contenido">
            <a href="{{ route('kawsay') }}" class="karea-hero__volver"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kawsay</a>
            <div class="karea-hero__fila">
                <span class="karea-hero__avatar" aria-hidden="true">
                    @if ($area->imagen)
                        <img src="{{ asset($area->imagen) }}" alt="">
                    @else
                        <i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i>
                    @endif
                </span>
                <div class="karea-hero__texto">
                    <p class="karea-hero__eyebrow">Área de trabajo</p>
                    <h1 class="karea-hero__titulo">{{ $area->nombre }}</h1>
                    @if ($area->descripcion)
                        <p class="karea-hero__desc">{{ $area->descripcion }}</p>
                    @endif
                    <p class="karea-hero__datos">
                        <span><strong>{{ $temas->count() }}</strong> {{ $temas->count() === 1 ? 'tema' : 'temas' }}</span>
                        <span><strong>{{ \App\Services\Contador::formato($totalMateriales) }}</strong> {{ $totalMateriales === 1 ? 'material' : 'materiales' }}</span>
                    </p>
                </div>
            </div>
            <form action="{{ route('kawsay.buscar') }}" method="GET" class="karea-hero__buscar" role="search">
                <input type="search" name="query" placeholder="Busca un tema o material…" minlength="3" required aria-label="Buscar en Kawsay">
                <button type="submit"><i class="fa fa-search" aria-hidden="true"></i><span>Buscar</span></button>
            </form>
        </div>
    </header>

    <div class="container py-4 py-md-5">
        @if ($temas->isEmpty())
            <div class="kport-vacio">
                <i class="fa {{ $area->icono ?: 'fa-leaf' }}" aria-hidden="true"></i>
                <p class="mb-0">Pronto vamos a publicar los temas de esta área.</p>
            </div>
        @else
            <h2 class="karea-seccion">Temas de esta área</h2>
            <div class="karea-temas">
                @foreach ($temas as $tema)
                    @php $formatos = $formatosPorTema[$tema->id] ?? collect(); @endphp
                    <a href="{{ route('kawsay.tema', [$areaSlug, $tema->slug]) }}" class="karea-tema" style="--area-color: {{ $color }}">
                        <span class="karea-tema__media">
                            @if ($tema->imagen)
                                <img src="{{ asset($tema->imagen) }}" alt="" loading="lazy" decoding="async">
                            @else
                                {{-- Sin imagen propia: la foto del área, atenuada, para que no todo sea un recuadro gris --}}
                                @if ($area->imagen)
                                    <img src="{{ asset($area->imagen) }}" alt="" class="karea-tema__media-area" loading="lazy" decoding="async">
                                @endif
                                <i class="fa {{ $area->icono ?: 'fa-leaf' }} karea-tema__icono" aria-hidden="true"></i>
                            @endif
                            @if ($tema->archivos_count === 0)
                                <span class="karea-tema__pronto">Próximamente</span>
                            @endif
                        </span>
                        <span class="karea-tema__body">
                            <span class="karea-tema__titulo">{{ $tema->titulo }}</span>
                            @if ($tema->descripcion)
                                <span class="karea-tema__desc">{{ \Illuminate\Support\Str::limit($tema->descripcion, 110) }}</span>
                            @endif
                            @if ($formatos->isNotEmpty())
                                <span class="karea-tema__formatos">
                                    @foreach ($formatoInfo as $clave => $info)
                                        @if (($formatos[$clave] ?? 0) > 0)
                                            @php $n = (int) $formatos[$clave]; @endphp
                                            <span class="karea-tema__chip"><i class="fa {{ $info['icono'] }}" aria-hidden="true"></i>
                                                {{ $n }} {{ $n === 1 ? $info['uno'] : $info['varios'] }}</span>
                                        @endif
                                    @endforeach
                                </span>
                            @endif
                            <span class="karea-tema__cta">Explorar <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @include('kawsay.partials.estilos')
@endsection
