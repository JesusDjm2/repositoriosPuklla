@props([
    'href',
    'icono' => 'fa-folder',
    'variante' => 'virtual',
    'titulo',
    'descripcion' => null,
    'badge' => null,
    'badgeVariante' => 'categorias',
    'target' => null,
    'rel' => null,
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'dash-card-link text-decoration-none text-dark d-block h-100']) }}
   @if ($target) target="{{ $target }}" @endif
   @if ($rel) rel="{{ $rel }}" @elseif($target === '_blank') rel="noopener noreferrer" @endif
   aria-label="{{ $titulo }}{{ $descripcion ? ': ' . $descripcion : '' }}">
    <div class="card h-100 shadow-sm border-0 dash-card p-3 p-md-3">
        <div class="dash-card__body">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="dash-icon-circle icon-{{ $variante }}" aria-hidden="true">
                    <i class="fa {{ $icono }}"></i>
                </div>
                @if ($badge)
                    <span class="stat-badge badge-{{ $badgeVariante }}">{{ $badge }}</span>
                @endif
            </div>
            <h5 class="dash-card__title font-weight-bold mb-1">{{ $titulo }}</h5>
            @if ($descripcion)
                <p class="dash-card__desc small text-muted mb-2">{{ $descripcion }}</p>
            @endif
        </div>

        @if (isset($badges) && $badges->isNotEmpty())
            <div class="dash-card__badges mt-auto pt-2">
                {{ $badges }}
            </div>
        @elseif (isset($slot) && $slot->isNotEmpty())
            <div class="dash-card__badges mt-auto pt-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</a>
