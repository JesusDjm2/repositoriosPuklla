@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $tema->titulo)

@section('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $tema->titulo }} — Kawsay">
    @if ($tema->descripcion)
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($tema->descripcion), 200) }}">
    @endif
    @if ($tema->imagen)
        <meta property="og:image" content="{{ asset($tema->imagen) }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <meta property="og:url" content="{{ request()->url() }}">
@endsection

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="kawsay-tema-header">
            <a href="{{ route('kawsay.area', $areaSlug) }}" class="kawsay-back-link kawsay-tema-header__back">
                <i class="fa fa-arrow-left"></i> {{ $area->nombre }}
            </a>
            <form action="{{ route('kawsay.buscar') }}" method="GET" class="kawsay-header-search kawsay-tema-header__search">
                <input type="text" name="query" placeholder="Buscar en Kawsay..." minlength="3" required aria-label="Buscar en Kawsay">
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>

        <div class="kawsay-tema-hero">
            @if ($tema->imagen)
                <figure class="kawsay-tema-hero__foto">
                    <img src="{{ asset($tema->imagen) }}" alt="{{ $tema->titulo }}" fetchpriority="high">
                </figure>
            @endif
            <div class="kawsay-tema-hero__texto">
                <h1 class="kawsay-area-header__title mb-0">{{ $tema->titulo }}</h1>
                @if ($tema->descripcion)
                    <p class="kawsay-area-header__desc">{!! nl2br(e($tema->descripcion)) !!}</p>
                @endif

                <div class="kawsay-share">
                    <a href="https://wa.me/?text={{ urlencode($tema->titulo . ' - ' . request()->url()) }}" target="_blank" rel="noopener" aria-label="Compartir por WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" aria-label="Compartir en Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <button type="button" class="kawsay-share__copy" data-url="{{ request()->url() }}" aria-label="Copiar enlace">
                        <i class="fa fa-link"></i>
                    </button>
                </div>
            </div>

        </div>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    <h2 class="h5 fw-bold mb-3">En este tema encontrarás</h2>
    <div class="row g-3">
        @foreach ($tiposContenido as $tipoSlug => $tipo)
            @php $total = $conteos[$tipoSlug] ?? 0; @endphp
            @if ($total > 0 && $tipoSlug === 'galeria' && $fotos->isNotEmpty())
                {{-- Galería fotográfica: no lleva a otra página, abre el visor en popup. --}}
                <div class="col-6 col-md-3">
                    <button type="button" class="kawsay-tipo-card" id="kawsayGaleriaBtn" aria-haspopup="dialog">
                        <span class="kawsay-tipo-card__icon"><i class="fa {{ $tipo['icono'] }}"></i></span>
                        <span class="kawsay-tipo-card__title">{{ $tipo['label'] }}</span>
                        <span class="kawsay-tipo-card__count">{{ $fotos->count() }} {{ $fotos->count() === 1 ? 'foto' : 'fotos' }}</span>
                    </button>
                </div>
            @elseif ($total > 0)
                <div class="col-6 col-md-3">
                    <a href="{{ route('kawsay.contenido', [$areaSlug, $temaSlug, $tipoSlug]) }}" class="kawsay-tipo-card">
                        <span class="kawsay-tipo-card__icon"><i class="fa {{ $tipo['icono'] }}"></i></span>
                        <span class="kawsay-tipo-card__title">{{ $tipo['label'] }}</span>
                        <span class="kawsay-tipo-card__count">{{ $total }} {{ $total === 1 ? 'archivo' : 'archivos' }}</span>
                    </a>
                </div>
            @else
                <div class="col-6 col-md-3">
                    <span class="kawsay-tipo-card kawsay-tipo-card--vacio">
                        <span class="kawsay-tipo-card__icon"><i class="fa {{ $tipo['icono'] }}"></i></span>
                        <span class="kawsay-tipo-card__title">{{ $tipo['label'] }}</span>
                        <span class="kawsay-tipo-card__count">Próximamente</span>
                    </span>
                </div>
            @endif
        @endforeach
    </div>

    @include('kawsay.temas.partials.comentarios')
</div>

@if ($fotos->isNotEmpty())
    @include('kawsay.temas.partials.visor-galeria', ['fotos' => $fotos])
@endif

@include('kawsay.partials.estilos')

<script>
    (function () {
        var copyBtn = document.querySelector('.kawsay-share__copy');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                var url = copyBtn.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(function () {
                    var icon = copyBtn.querySelector('i');
                    copyBtn.classList.add('is-copiado');
                    icon.className = 'fa fa-check';
                    setTimeout(function () {
                        copyBtn.classList.remove('is-copiado');
                        icon.className = 'fa fa-link';
                    }, 1800);
                });
            });
        }
    })();
</script>
@endsection
