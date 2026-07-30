@props(['icono' => 'fa-chart-bar', 'valor' => 0, 'etiqueta' => '', 'animar' => true])

<div class="stat-card">
    <div class="stat-card__icon">
        <i class="fa {{ $icono }}"></i>
    </div>
    <div>
        <div class="stat-card__value" @if ($animar) data-counter="{{ (int) $valor }}" @endif>
            {{ $animar ? 0 : number_format((int) $valor) }}
        </div>
        <div class="stat-card__label">{{ $etiqueta }}</div>
    </div>
</div>
