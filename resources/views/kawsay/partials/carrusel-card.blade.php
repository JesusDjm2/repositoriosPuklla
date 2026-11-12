{{--
    Tarjeta de los carruseles de la portada de Kawsay.
    $item: objeto normalizado por App\Services\KawsayPortada (formato, titulo, etiqueta,
    detalle, thumb, fecha, accion, visorIndice, area{nombre,color}, temaUrl, temaTitulo).
    $posicion / $total: para el lector de pantalla ("3 de 12").
    Estilos: public/css/kawsay.css (.kcar-card)
--}}
@php
    $iconos = ['video' => 'fa-play', 'audio' => 'fa-headphones', 'foto' => 'fa-image', 'documento' => 'fa-file-pdf', 'libro' => 'fa-book'];
    $verbos = ['video' => 'Reproducir', 'audio' => 'Escuchar', 'foto' => 'Ver foto', 'documento' => 'Ver documento', 'libro' => 'Ver libro'];
    $esFoto = $item->formato === 'foto';
    $tieneAccion = $esFoto ? $item->visorIndice !== null : (bool) $item->accion;
@endphp
<article class="kcar-card kcar-card--{{ $item->formato }}"
    @if ($item->area) style="--area-color: {{ $item->area->color }}" @endif
    role="group" aria-roledescription="publicación" aria-label="{{ $posicion }} de {{ $total }}">
    <button type="button" class="kcar-card__main"
        @if ($esFoto && $tieneAccion) data-kvis-indice="{{ $item->visorIndice }}"
        @elseif ($tieneAccion) onclick="{{ $item->accion }}" @endif
        @unless ($tieneAccion) disabled @endunless
        @if ($item->vistaUrl ?? null) data-kawsay-vista="{{ $item->vistaUrl }}" @endif
        aria-label="{{ $verbos[$item->formato] ?? 'Abrir' }}: {{ $item->titulo }}">
        <span class="kcar-card__media">
            <span class="kcar-card__ph" aria-hidden="true"><i class="fa {{ $iconos[$item->formato] ?? 'fa-file' }}"></i></span>
            @if ($item->thumb)
                <img src="{{ $item->thumb }}" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer"
                    onerror="this.remove()">
            @endif
            <span class="kcar-card__badge">{{ $item->etiqueta }}</span>
            @if (in_array($item->formato, ['video', 'audio'], true))
                <span class="kcar-card__play" aria-hidden="true"><i class="fa fa-play"></i></span>
            @endif
        </span>
        <span class="kcar-card__body">
            <span class="kcar-card__title">{{ $item->titulo }}</span>
            @if ($item->formato === 'libro' && $item->detalle)
                <span class="kcar-card__detail"><i class="fa fa-user" aria-hidden="true"></i> {{ $item->detalle }}</span>
            @endif
            @if ($item->vistas ?? null)
                <span class="kcar-card__date"><i class="fa fa-eye" aria-hidden="true"></i>
                    {{ \App\Services\Contador::formato($item->vistas) }} {{ $item->vistas === 1 ? 'vista' : 'vistas' }}</span>
            @elseif ($item->fecha)
                <span class="kcar-card__date">{{ \App\Services\KawsayPortada::hace($item->fecha) }}</span>
            @endif
        </span>
    </button>
    @if ($item->temaUrl)
        <a href="{{ $item->temaUrl }}" class="kcar-card__tema"
            title="{{ $item->area ? $item->area->nombre . ' · ' : '' }}{{ $item->temaTitulo ?? $item->detalle }}">
            <span class="kcar-card__dot" aria-hidden="true"></span>
            <span class="kcar-card__tema-text">{{ $item->temaTitulo ?? $item->detalle }}</span>
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    @endif
</article>
