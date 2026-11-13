<div>
    <footer class="puklla-footer" role="contentinfo">
        <div class="container">
            <div class="row puklla-footer__top gy-4">

                {{-- Marca + contacto --}}
                <div class="col-lg-4">
                    <a href="{{ route('hub') }}" class="puklla-footer__brand" aria-label="Ir al inicio">
                        <img src="{{ asset('img/Asociacion-logo-blanco.png') }}" alt="Logo blanco Asociación Pukllasunchis"
                            loading="lazy" decoding="async" width="190">
                    </a>
                    <p class="puklla-footer__tagline">
                        Promovemos una educación intercultural que revalora el saber de los pueblos originarios del
                        Cusco.
                    </p>
                    <ul class="puklla-footer__contact">
                        <li>
                            <i class="fa fa-map-marker-alt" aria-hidden="true"></i>
                            <span>Calle Siete Diablitos 222, San Blas, Cusco.</span>
                        </li>
                        <li>
                            <i class="fa fa-phone" aria-hidden="true"></i>
                            <span>
                                <a class="wasa" href="https://wa.me/51984529158" target="_blank"
                                    rel="noopener">+51 984 529 158</a>
                                <span aria-hidden="true"> · </span>
                                <a class="wasa" href="https://wa.me/51969572566" target="_blank"
                                    rel="noopener">+51 969 572 566</a>
                            </span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:eespp@pukllasunchis.org">eespp@pukllasunchis.org</a>
                        </li>
                    </ul>
                </div>

                {{-- Enlaces rápidos --}}
                <div class="col-6 col-lg-2">
                    <h3 class="puklla-footer__heading">Repositorios</h3>
                    <ul class="puklla-footer__links">
                        <li><a href="{{ route('hub') }}">Inicio</a></li>
                        @foreach ($proyectos as $proyecto)
                            <li><a href="{{ $proyecto['url'] }}">{{ $proyecto['nombre'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Institución --}}
                <div class="col-6 col-lg-3">
                    <h3 class="puklla-footer__heading">Institución</h3>
                    <ul class="puklla-footer__links puklla-footer__links--static">
                        <li>EESP Pukllasunchis</li>
                        <li>Colegio Pukllasunchis</li>
                        <li>Radio</li>
                        <li>Sipas Wayna</li>
                        <li>Kawsay</li>
                    </ul>
                </div>

                {{-- Síguenos --}}
                <div class="col-lg-3">
                    <h3 class="puklla-footer__heading">Síguenos</h3>
                    <p class="puklla-footer__tagline">Descubre más contenido y comparte con la comunidad
                        Pukllasunchis.</p>
                    <ul class="puklla-footer__social">
                        <li>
                            <a href="https://www.facebook.com/AsociacionPukllasunchis" target="_blank" rel="noopener"
                                aria-label="Facebook" class="puklla-footer__social-link puklla-footer__social-link--facebook">
                                <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.instagram.com/asociacionpuklla/" target="_blank" rel="noopener"
                                aria-label="Instagram" class="puklla-footer__social-link puklla-footer__social-link--instagram">
                                <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.youtube.com/@AsociacionPukllasunchis" target="_blank" rel="noopener"
                                aria-label="YouTube" class="puklla-footer__social-link puklla-footer__social-link--youtube">
                                <i class="fa-brands fa-youtube" aria-hidden="true"></i>
                            </a>
                        </li>
                    </ul>

                    {{-- Mismo buscador global de la portada (busca entre los registros
                         de todos los proyectos), en versión discreta: sin caja ni botón
                         de relleno, solo una línea que se ilumina con el acento del
                         proyecto activo al enfocar. --}}
                    <form action="{{ route('busqueda') }}" method="GET" role="search" class="puklla-footer__search">
                        <label for="footerBuscadorInput" class="visually-hidden">Buscar en todos los registros</label>
                        <input type="search" id="footerBuscadorInput" name="query"
                            placeholder="Buscar en todos los registros…" minlength="3" maxlength="120"
                            autocomplete="off" required>
                        <button type="submit" aria-label="Buscar">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="puklla-footer__bottom">
                <p>Asociación Pukllasunchis &copy; {{ date('Y') }}. Todos los derechos reservados.</p>
                <p>Hecho por <a href="https://www.facebook.com/DjmWebMaster" target="_blank"
                        rel="nofollow noopener">DJM2</a></p>
            </div>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    {{-- Botón volver arriba --}}
    <button id="btnTop" class="btn-top" title="Volver arriba" aria-label="Volver arriba">
        <i class="fa-solid fa-chevron-up"></i>
    </button>

    <script>
        $(document).ready(function() {
            $('.nav-link.search').click(function(e) {
                e.preventDefault();
                var searchInput = $(this).closest('.navbar-nav').find('.search-input');
                searchInput.show('slow');
                var icons = $(this).closest('.navbar-nav').find('.icon');
                icons.hide('slow');
            });

            $('.close-search').click(function(e) {
                e.preventDefault();
                var searchInput = $(this).closest('.search-input');
                searchInput.hide('slow');
                var icons = $(this).closest('.navbar-nav').find('.icon');
                icons.show('slow');
            });

            // Mostrar/ocultar botón al hacer scroll
            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 300) {
                    $('#btnTop').addClass('visible');
                } else {
                    $('#btnTop').removeClass('visible');
                }
            });

            // Scroll suave al top
            $('#btnTop').on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 500, 'swing');
            });
        });
    </script>

    {{-- Entrada animada del footer al hacer scroll (GSAP + IntersectionObserver) --}}
    <script>
        (function () {
            var footerEl = document.querySelector('.puklla-footer');
            if (!footerEl) return;

            var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function revealInstantly() {
                footerEl.querySelectorAll('.puklla-footer__top > div').forEach(function (col) {
                    col.style.opacity = 1;
                    col.style.transform = 'none';
                });
            }

            function animateFooter() {
                var cols = footerEl.querySelectorAll('.puklla-footer__top > div');
                if (!cols.length || typeof gsap === 'undefined') {
                    revealInstantly();
                    return;
                }
                gsap.from(cols, {
                    opacity: 0,
                    y: 22,
                    duration: 0.5,
                    stagger: 0.08,
                    ease: 'power2.out'
                });
                var bottom = footerEl.querySelector('.puklla-footer__bottom');
                if (bottom) {
                    gsap.from(bottom, { y: 14, duration: 0.45, delay: 0.3, ease: 'power2.out' });
                }
            }

            function ensureGsapThenAnimate() {
                if (typeof gsap !== 'undefined') {
                    animateFooter();
                    return;
                }
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animateFooter;
                script.onerror = revealInstantly;
                document.head.appendChild(script);
            }

            if (prefersReducedMotion) {
                revealInstantly();
                return;
            }

            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            ensureGsapThenAnimate();
                            obs.disconnect();
                        }
                    });
                }, { threshold: 0.15 });
                observer.observe(footerEl);
            } else {
                ensureGsapThenAnimate();
            }
        })();
    </script>
</div>
