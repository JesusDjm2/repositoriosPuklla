@props(['icono' => 'fa-chart-bar', 'valor' => 0, 'etiqueta' => '', 'animar' => true])

<div class="hero-banner__stat">
    <i class="fa {{ $icono }}"></i>
    <span class="hero-banner__stat-value" @if ($animar) data-counter="{{ (int) $valor }}" @endif>
        {{ $animar ? 0 : number_format((int) $valor) }}
    </span>
    <span class="hero-banner__stat-label">{{ $etiqueta }}</span>
</div>
