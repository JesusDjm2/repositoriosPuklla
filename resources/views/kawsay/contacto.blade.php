@extends('layouts.padre')
@section('titulo', '- Kawsay - Contáctanos')

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay') }}" class="kawsay-back-link"><i class="fa fa-arrow-left"></i> Kawsay</a>
                <h1 class="kawsay-area-header__title mt-2">Contáctanos</h1>
                <p class="kawsay-area-header__desc mb-0">Comunícate con el equipo del repositorio educativo Kawsay.</p>
            </div>
            <form action="{{ route('kawsay.buscar') }}" method="GET" role="search" class="kawsay-header-search">
                <input type="text" name="query" placeholder="Buscar en Kawsay..." minlength="3" required aria-label="Buscar en Kawsay">
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 fw-bold mb-4"><i class="fa fa-envelope me-2" style="color: var(--project-accent, #6e7d1e);"></i>Escríbenos</h2>

                    <ul class="list-unstyled mb-4">
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:44px;height:44px; background: rgba(110, 125, 30, 0.12); color: #6e7d1e;">
                                <i class="fa fa-map-marker-alt"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">Dirección</div>
                                <div class="text-muted">Calle Siete Diablitos 222, San Blas, Cusco.</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:44px;height:44px; background: rgba(110, 125, 30, 0.12); color: #6e7d1e;">
                                <i class="fa fa-phone"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">WhatsApp</div>
                                <div class="text-muted">
                                    <a class="text-decoration-none" href="https://wa.me/51984529158" target="_blank" rel="noopener">+51 984 529 158</a>
                                    <span class="mx-1">·</span>
                                    <a class="text-decoration-none" href="https://wa.me/51969572566" target="_blank" rel="noopener">+51 969 572 566</a>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-3">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:44px;height:44px; background: rgba(110, 125, 30, 0.12); color: #6e7d1e;">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">Correo Electrónico</div>
                                <a class="text-decoration-none" href="mailto:eespp@pukllasunchis.org">eespp@pukllasunchis.org</a>
                            </div>
                        </li>
                    </ul>

                    <hr>

                    <h3 class="h6 fw-bold mb-3">Síguenos</h3>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com" target="_blank" rel="noopener"
                            class="btn rounded-circle text-white d-inline-flex align-items-center justify-content-center"
                            style="width:42px;height:42px; background: #6e7d1e;" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com" target="_blank" rel="noopener"
                            class="btn rounded-circle text-white d-inline-flex align-items-center justify-content-center"
                            style="width:42px;height:42px; background: #6e7d1e;" aria-label="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com" target="_blank" rel="noopener"
                            class="btn rounded-circle text-white d-inline-flex align-items-center justify-content-center"
                            style="width:42px;height:42px; background: #6e7d1e;" aria-label="YouTube">
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('kawsay.partials.estilos')
@endsection
