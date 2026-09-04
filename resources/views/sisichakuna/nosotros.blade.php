@extends('layouts.padre')
@section('titulo', '- Nosotros')

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @include('partials.lib-hero', [
        'heroBg' => asset('img/fondo/Fondo-sisipedia.png'),
        'heroBgAlt' => 'Fondo Sisipedia',
        'heroEyebrow' => 'Sisipedia',
        'heroTitle' => 'Nosotros',
        'heroTitleSmall' => true,
        'heroFormatButtons' => true,
    ])

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-bold mb-3"><i class="fa fa-heart text-danger me-2"></i>Quiénes somos</h2>
                        <p class="text-muted">
                            Promovemos una educación intercultural que revalora el saber de los pueblos originarios
                            del Cusco. Sisipedia nace de esa misma vocación: un repositorio abierto que reúne más de
                            20 años de investigaciones del programa de Radio Pukllasunchis, realizadas junto a
                            escuelas, docentes y líderes comunitarios de la región.
                        </p>
                        <p class="text-muted mb-0">
                            <em>Texto de ejemplo — reemplázalo por el contenido real que quieras publicar aquí.</em>
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-bold mb-3"><i class="fa fa-sitemap text-danger me-2"></i>Qué es Sisipedia</h2>
                        <p class="text-muted mb-0">
                            Sisipedia organiza audios, videos, imágenes y documentos en un árbol de registros
                            navegable, para que estudiantes, docentes e investigadores puedan encontrar y aportar
                            conocimiento sobre plantas medicinales, fiestas, tejido y otras tradiciones de los
                            pueblos originarios del Cusco.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h4 fw-bold mb-3"><i class="fa fa-users text-danger me-2"></i>Nuestro equipo</h2>
                        <p class="text-muted mb-0">
                            Este trabajo es posible gracias al equipo de la Asociación Pukllasunchis y a la
                            comunidad —docentes, líderes y estudiantes— que aporta sus conocimientos día a día.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof gsap === 'undefined') return;
            gsap.from('.card', {
                opacity: 0, y: 20, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.1,
            });
        });
    </script>
@endsection
