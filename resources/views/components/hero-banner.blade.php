@props(['titulo', 'subtitulo' => null, 'icono' => 'fa-th-large', 'color' => null])

@php
    $fecha = \Illuminate\Support\Carbon::now()->locale('es');
    $fechaTexto = ucfirst($fecha->translatedFormat('l')) . ', ' . $fecha->format('d') . ' de ' . $fecha->translatedFormat('F');
@endphp

<div class="hero-banner @if ($color) hero-banner--{{ $color }} @endif" data-animate-hero>
    <div class="hero-banner__decor" aria-hidden="true"></div>
    <div class="hero-banner__row">
        <div class="hero-banner__content">
            <div class="hero-banner__eyebrow">
                <i class="fa {{ $icono }}"></i> {{ $fechaTexto }}
            </div>
            <h1 class="hero-banner__title">{{ $titulo }}</h1>
            @if ($subtitulo)
                <p class="hero-banner__subtitle">{{ $subtitulo }}</p>
            @endif
            @isset($stats)
                <div class="hero-banner__stats" data-animate-stagger>
                    {{ $stats }}
                </div>
            @endisset
        </div>
        @isset($actions)
            <div class="hero-banner__actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
