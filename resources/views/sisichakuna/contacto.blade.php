@extends('layouts.padre')
@section('titulo', '- Contáctanos')

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @include('partials.lib-hero', [
        'heroBg' => asset('img/fondo/Fondo-sisipedia.png'),
        'heroBgAlt' => 'Fondo Sisipedia',
        'heroEyebrow' => 'Sisipedia',
        'heroTitle' => 'Contáctanos',
        'heroTitleSmall' => true,
        'heroFormatButtons' => true,
    ])

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-bold mb-4"><i class="fa fa-envelope text-danger me-2"></i>Escríbenos</h2>

                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:44px;height:44px;">
                                    <i class="fa fa-map-marker-alt"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold">Dirección</div>
                                    <div class="text-muted">Calle Siete Diablitos 222, San Blas, Cusco.</div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:44px;height:44px;">
                                    <i class="fa fa-phone"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold">WhatsApp</div>
                                    <div class="text-muted">
                                        <a class="text-decoration-none" href="https://wa.me/51961185453" target="_blank" rel="noopener">+51 961 185 453</a>
                                    </div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-3">
                                <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:44px;height:44px;">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <div>
                                    <div class="fw-semibold">Correo</div>
                                    <a class="text-decoration-none" href="mailto:radio@pukllavirtual.edu.pe">radio@pukllavirtual.edu.pe</a>
                                </div>
                            </li>
                        </ul>

                        <hr>

                        <h3 class="h6 fw-bold mb-3">Síguenos</h3>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/Sisichakunaq" target="_blank" rel="noopener"
                                class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" aria-label="Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/sisichakunaq/" target="_blank" rel="noopener"
                                class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" aria-label="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="https://open.spotify.com/show/21uqGdVqtet2iYx3XkhAVh?si=28d6c5ac81f94329" target="_blank" rel="noopener"
                                class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" aria-label="Spotify">
                                <i class="fa-brands fa-spotify"></i>
                            </a>
                            <a href="https://www.youtube.com/@RadioPukllasunchis" target="_blank" rel="noopener"
                                class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px;" aria-label="YouTube">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof gsap === 'undefined') return;
            gsap.from('.card', {
                opacity: 0, y: 20, duration: 0.5, ease: 'power2.out', delay: 0.1,
            });
        });
    </script>
@endsection
