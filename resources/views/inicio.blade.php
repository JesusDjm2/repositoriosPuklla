@extends('layouts.hub')
@section('titulo', '- Repositorio Central')
@section('contenido')
    <div class="hub-hero">
        <div class="hub-hero-inner">
            <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Asociación Pukllasunchis"
                class="hub-logo hub-hero-anim">
            <h1 class="hub-titulo hub-hero-anim">Repositorios Pukllasunchis</h1>
            <p class="hub-intro hub-hero-anim">
                Aquí reunimos los repositorios digitales de los proyectos de la Asociación Pukllasunchis: libros,
                videos, audios e investigaciones organizados para consulta pública.
            </p>

            <form action="{{ route('busqueda') }}" method="GET" role="search"
                class="hub-hero-search mx-auto hub-hero-anim">
                <label for="hubBuscadorInput" class="visually-hidden">Buscar en todos los registros</label>
                <div class="input-group shadow">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="search" id="hubBuscadorInput" name="query" class="form-control"
                        placeholder="Buscar en todos los registros…"
                        minlength="3" maxlength="120" autocomplete="off" required>
                    <button class="btn fw-semibold px-4 text-white" type="submit">Buscar</button>
                </div>
                <p class="hub-hero-search-hint mt-4">Elige un proyecto de la Asociación para explorar su repositorio.</p>
            </form>

            <div class="hub-tree" id="hubTree">
                <svg class="hub-tree-svg" id="hubTreeSvg" aria-hidden="true"></svg>
                <div class="hub-grid" id="hubGrid">
                    @foreach ($proyectos as $proyecto)
                        @if ($proyecto['nombre'])
                            <a href="{{ $proyecto['url'] ?? '#' }}"
                                class="hub-card {{ $proyecto['url'] ? '' : 'hub-card--proximamente' }}"
                                @if (!empty($proyecto['color'])) style="--card-color: {{ $proyecto['color'] }};" @endif
                                @unless ($proyecto['url']) tabindex="-1" aria-disabled="true" @endunless>
                                <span class="hub-card-node" aria-hidden="true"></span>
                                <span class="hub-card-logo-wrap">
                                    <img src="{{ asset('img/'.$proyecto['logo']) }}" alt="Logo {{ $proyecto['nombre'] }}"
                                        class="hub-card-logo">
                                </span>
                                {{-- <span class="hub-card-nombre">{{ $proyecto['nombre'] }}</span> --}}
                                @if (!empty($proyecto['descripcion']))
                                    <span class="hub-card-desc">{{ $proyecto['descripcion'] }}</span>
                                @endif
                                @unless ($proyecto['url'])
                                    <span class="hub-card-badge">Próximamente</span>
                                @endunless
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var tree = document.getElementById('hubTree');
            var svg = document.getElementById('hubTreeSvg');
            var SVG_NS = 'http://www.w3.org/2000/svg';
            var TRUNK_LEN = 34; // largo del tronco recto antes de ramificarse

            function drawTree(opts) {
                opts = opts || {};
                if (!tree || !svg) return;
                var cards = Array.prototype.slice.call(tree.querySelectorAll('.hub-card'));
                if (!cards.length) return;

                var treeRect = tree.getBoundingClientRect();
                var w = treeRect.width;
                var h = treeRect.height;
                svg.setAttribute('width', w);
                svg.setAttribute('height', h);
                svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
                svg.innerHTML = '';

                var trunkX = w / 2;
                var trunkTopY = 0;
                var trunkBottomY = Math.min(TRUNK_LEN, h * 0.25);

                var trunk = document.createElementNS(SVG_NS, 'path');
                trunk.setAttribute('d', 'M ' + trunkX + ' ' + trunkTopY + ' L ' + trunkX + ' ' + trunkBottomY);
                trunk.setAttribute('class', 'hub-tree-trunk-path');
                svg.appendChild(trunk);

                cards.forEach(function(card, i) {
                    var cardRect = card.getBoundingClientRect();
                    var nodeX = (cardRect.left + cardRect.width / 2) - treeRect.left;
                    var nodeY = cardRect.top - treeRect.top;
                    var midY = trunkBottomY + (nodeY - trunkBottomY) * 0.55;

                    var path = document.createElementNS(SVG_NS, 'path');
                    var d = 'M ' + trunkX + ' ' + trunkBottomY +
                        ' C ' + trunkX + ' ' + midY + ', ' + nodeX + ' ' + midY + ', ' + nodeX + ' ' + nodeY;
                    path.setAttribute('d', d);
                    path.setAttribute('class', 'hub-tree-branch-path');
                    var color = card.style.getPropertyValue('--card-color') || '#8a6f52';
                    path.style.stroke = color;
                    svg.appendChild(path);

                    if (opts.animate && typeof gsap !== 'undefined') {
                        var len = path.getTotalLength();
                        path.style.strokeDasharray = len;
                        path.style.strokeDashoffset = len;
                        gsap.to(path, {
                            strokeDashoffset: 0,
                            duration: 0.6,
                            delay: i * 0.08,
                            ease: 'power2.out'
                        });
                    }
                });

                if (opts.animate && typeof gsap !== 'undefined') {
                    var tlen = trunk.getTotalLength();
                    trunk.style.strokeDasharray = tlen;
                    trunk.style.strokeDashoffset = tlen;
                    gsap.to(trunk, { strokeDashoffset: 0, duration: 0.3, ease: 'power2.out' });
                }
            }

            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() { drawTree({ animate: false }); }, 150);
            }, { passive: true });

            function animate() {
                if (typeof gsap === 'undefined') { drawTree(); return; }
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
                    ease: 'power2.out',
                    onComplete: function() { drawTree({ animate: true }); }
                });
            }

            if (reduceMotion) {
                drawTree();
            } else if (typeof gsap !== 'undefined') {
                animate();
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animate;
                script.onerror = function() { drawTree(); };
                document.head.appendChild(script);
            }

            // Corrige la posición de las ramas si las fuentes web terminan de
            // cargar después (evita que queden desalineadas por el reflow).
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() { drawTree({ animate: false }); });
            }
        })();
    </script>
@endsection
