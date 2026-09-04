@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $tema->titulo)

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay.area', $areaSlug) }}" class="kawsay-back-link">
                    <i class="fa fa-arrow-left"></i> {{ $area->nombre }}
                </a>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <span class="kawsay-area-header__icon"><i class="fa {{ $tema->icono ?: 'fa-map-signs' }}"></i></span>
                    <h1 class="kawsay-area-header__title mb-0">{{ $tema->titulo }}</h1>
                </div>
                @if ($tema->descripcion)
                    <p class="kawsay-area-header__desc">{!! nl2br(e($tema->descripcion)) !!}</p>
                @endif
            </div>
            <form action="{{ route('busqueda') }}" method="GET" class="kawsay-header-search">
                <input type="text" name="query" placeholder="Buscar..." minlength="3" required>
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>
        </div>

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

        @if ($tema->imagenes->isNotEmpty())
            <div class="kawsay-tema-galeria" data-total="{{ $tema->imagenes->count() }}">
                <div class="kawsay-tema-galeria__track">
                    @foreach ($tema->imagenes as $img)
                        <div class="kawsay-tema-galeria__slide">
                            <img src="{{ asset($img->imagen) }}" alt="{{ $tema->titulo }}" loading="lazy">
                        </div>
                    @endforeach
                </div>
                @if ($tema->imagenes->count() > 1)
                    <button type="button" class="kawsay-tema-galeria__nav kawsay-tema-galeria__nav--prev" aria-label="Anterior"><i class="fa fa-chevron-left"></i></button>
                    <button type="button" class="kawsay-tema-galeria__nav kawsay-tema-galeria__nav--next" aria-label="Siguiente"><i class="fa fa-chevron-right"></i></button>
                    <div class="kawsay-tema-galeria__dots"></div>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="container py-4 py-md-5">
    <h2 class="h5 fw-bold mb-3">En este tema encontrarás</h2>
    <div class="row g-3">
        @foreach ($tiposContenido as $tipoSlug => $tipo)
            @php $total = $conteos[$tipoSlug] ?? 0; @endphp
            @if ($total > 0)
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

    <div id="comentarios" class="kawsay-comentarios">
        <h2 class="h5 fw-bold mb-3">Comentarios</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('kawsay.tema.comentarios.store', [$areaSlug, $temaSlug]) }}" method="POST" class="kawsay-comentario-form">
            @csrf
            <input type="text" name="nombre" placeholder="Tu nombre" required maxlength="80" value="{{ old('nombre') }}">
            <textarea name="comentario" placeholder="Escribe un comentario..." required maxlength="1000">{{ old('comentario') }}</textarea>
            <button type="submit" class="btn btn-sm btn-primary">Comentar</button>
        </form>

        @forelse ($tema->comentarios as $c)
            <div class="kawsay-comentario">
                <strong>{{ $c->nombre }}</strong> <span class="text-muted small">{{ $c->created_at->diffForHumans() }}</span>
                <p>{{ $c->comentario }}</p>
            </div>
        @empty
            <p class="text-muted">Sé el primero en comentar.</p>
        @endforelse
    </div>
</div>

@include('kawsay.partials.estilos')

<script>
    (function () {
        var galeria = document.querySelector('.kawsay-tema-galeria');
        if (galeria) {
            var track = galeria.querySelector('.kawsay-tema-galeria__track');
            var slides = galeria.querySelectorAll('.kawsay-tema-galeria__slide');
            var prev = galeria.querySelector('.kawsay-tema-galeria__nav--prev');
            var next = galeria.querySelector('.kawsay-tema-galeria__nav--next');
            var dotsWrap = galeria.querySelector('.kawsay-tema-galeria__dots');
            var perGroup = 4;
            var totalGroups = Math.max(1, Math.ceil(slides.length / perGroup));
            var current = 0;

            function isDesktop() {
                return window.matchMedia('(min-width: 768px)').matches;
            }

            function renderDots() {
                if (!dotsWrap) return;
                dotsWrap.innerHTML = '';
                if (!isDesktop() || totalGroups <= 1) return;
                for (var i = 0; i < totalGroups; i++) {
                    var dot = document.createElement('span');
                    if (i === current) dot.classList.add('is-active');
                    (function (index) {
                        dot.addEventListener('click', function () { goTo(index); });
                    })(i);
                    dotsWrap.appendChild(dot);
                }
            }

            function goTo(index) {
                if (!isDesktop()) return;
                current = (index + totalGroups) % totalGroups;
                var offset = current * perGroup * (track.scrollWidth / slides.length);
                track.style.transform = 'translateX(-' + offset + 'px)';
                renderDots();
            }

            if (prev) prev.addEventListener('click', function () { goTo(current - 1); });
            if (next) next.addEventListener('click', function () { goTo(current + 1); });
            track.style.transition = 'transform .3s ease';
            renderDots();
            window.addEventListener('resize', function () {
                if (!isDesktop()) {
                    track.style.transform = '';
                }
                renderDots();
            });
        }

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
