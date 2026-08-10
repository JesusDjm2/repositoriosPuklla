@props(['href', 'icono' => 'fa-th-large', 'titulo', 'descripcion' => null, 'badge' => null, 'color' => null])

<a href="{{ $href }}" class="module-card @if ($color) module-card--{{ $color }} @endif">
    <div class="module-card__icon">
        <i class="fa {{ $icono }}"></i>
    </div>
    <div class="module-card__body">
        <div class="module-card__title">{{ $titulo }}</div>
        @if ($descripcion)
            <div class="module-card__desc">{{ $descripcion }}</div>
        @endif
    </div>
    @if (!is_null($badge))
        <span class="module-card__badge">{{ $badge }}</span>
    @endif
    <i class="fa fa-angle-right module-card__arrow"></i>
</a>
