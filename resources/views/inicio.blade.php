@extends('layouts.hub')
@section('titulo', '- Repositorio Central')
@section('contenido')
    <div class="hub-hero">
        <div class="hub-hero-inner">
            <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Asociación Pukllasunchis"
                class="hub-logo hub-hero-anim">
            <h1 class="hub-titulo hub-hero-anim">Repositorios Pukllasunchis</h1>
            <p class="hub-subtitulo hub-hero-anim">Elige un proyecto de la Asociación para explorar su repositorio.</p>

            <form action="{{ route('busqueda') }}" method="GET" role="search"
                class="hub-hero-search mx-auto hub-hero-anim">
                <label for="hubBuscadorInput" class="visually-hidden">Buscar en todos los registros</label>
                <div class="input-group shadow">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="search" id="hubBuscadorInput" name="query" class="form-control"
                        placeholder="Busca en toda la Asociación: libros, videos, canciones, registros…"
                        minlength="3" maxlength="120" autocomplete="off" required>
                    <button class="btn fw-semibold px-4 text-white" type="submit">Buscar</button>
                </div>
                <p class="hub-hero-search-hint">Un solo buscador para EESP, Radio y Kawsay — cada resultado indica a
                    qué proyecto pertenece.</p>
            </form>

            <div class="hub-grid">
                @foreach ($proyectos as $proyecto)
                    @if ($proyecto['nombre'])
                        <a href="{{ $proyecto['url'] ?? '#' }}"
                            class="hub-card {{ $proyecto['url'] ? '' : 'hub-card--proximamente' }}"
                            @unless ($proyecto['url']) tabindex="-1" aria-disabled="true" @endunless>
                            <span class="hub-card-logo-wrap">
                                <img src="{{ asset('img/'.$proyecto['logo']) }}" alt="Logo {{ $proyecto['nombre'] }}"
                                    class="hub-card-logo">
                            </span>
                            <span class="hub-card-nombre">{{ $proyecto['nombre'] }}</span>
                            @unless ($proyecto['url'])
                                <span class="hub-card-badge">Próximamente</span>
                            @endunless
                        </a>
                    @else
                        <div class="hub-card hub-card--vacio" aria-hidden="true">
                            <span class="hub-card-badge">Próximamente</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            function animate() {
                if (typeof gsap === 'undefined') return;
                gsap.from('.hub-hero-anim', {
                    opacity: 0,
                    y: 18,
                    duration: 0.55,
                    stagger: 0.1,
                    ease: 'power2.out'
                });
                gsap.from('.hub-card', {
                    opacity: 0,
                    y: 18,
                    duration: 0.45,
                    stagger: 0.06,
                    delay: 0.35,
                    ease: 'power2.out'
                });
            }

            if (typeof gsap !== 'undefined') {
                animate();
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animate;
                document.head.appendChild(script);
            }
        })();
    </script>
@endsection
