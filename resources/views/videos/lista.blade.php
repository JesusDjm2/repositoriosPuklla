@extends('layouts.padre')
@section('titulo', '- Lista de videos')

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @include('partials.lib-hero', [
        'heroBg' => asset('img/FIlmadora-Puklla-Virtual-Fondo.webp'),
        'heroBgAlt' => 'Fondo Pukllasunchis',
        'heroEyebrow' => 'ESPP Pukllasunchis',
        'heroTitle' => 'Videos',
        'heroMeta' => [
            ['icon' => 'fa-video', 'text' => $videos->count() . ' ' . ($videos->count() === 1 ? 'video' : 'videos')],
            ['icon' => 'fa-layer-group', 'text' => $categorias->count() . ' secciones'],
        ],
    ])
    <div class="container mt-4">
        <div class="row mt-4">
            <div class="col-lg-4 mt-4">
                <div class="custom-sidebar">
                    <h4 class="custom-sidebar-title">Secciones:</h4>
                    <ul class="custom-sidebar-menu">
                        @foreach ($categorias as $seccion)
                            <li class="custom-sidebar-menu-item">
                                <a href="{{ route('videoscat.show', $seccion->url) }}" class="custom-sidebar-link">                                   
                                    <span>
                                        {{ $seccion->nombre }}
                                        <span class="video-count">
                                            ({{ $seccion->videos_count }}
                                            {{ $seccion->videos_count === 1 ? 'video' : 'videos' }})
                                        </span>
                                    </span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row">
                    <h2 class="titulo2Puklla mb-5">Últimas publicaciones</h2>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text btn btn-sm btn-primary" id="basic-addon1">Buscar</span>
                        </div>
                        <input type="text" id="search-input" placeholder="" class="form-control form-control-sm"
                            aria-label="Buscar libros por nombre o autor" aria-describedby="basic-addon1">
                    </div>
                    @foreach ($videos as $video)
                        <div class="col-lg-6 p-3">
                            <div class="cardVideos">
                                @if ($video->youtube)
                                    <div class="ratio ratio-16x9">
                                        <iframe loading="lazy" src="https://www.youtube.com/embed/{{ $video->youtube }}"
                                            title="YouTube video" allowfullscreen frameborder="0"></iframe>
                                    </div>
                                    <h6>{{ $video->nombre }}</h6>
                                    <p>{{ $video->descripcion }}</p>
                                @elseif ($video->drive)
                                    <div class="ratio ratio-16x9 bg-dark position-relative" role="button" tabindex="0"
                                         style="cursor:pointer;"
                                         aria-label="Reproducir {{ $video->nombre }}"
                                         onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('videos.stream', $video)) }}, {{ \Illuminate\Support\Js::from($video->nombre) }}, {{ \Illuminate\Support\Js::from(\App\Services\GoogleDriveService::getUrl($video->drive)) }})"
                                         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                                        <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($video->drive, 500) }}"
                                             alt="{{ $video->nombre }}" class="w-100 h-100" style="object-fit:contain;opacity:.88;"
                                             loading="lazy" onerror="this.style.display='none'">
                                        <button type="button" tabindex="-1"
                                                class="position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow"
                                                style="width:52px;height:52px;padding:0;">
                                            <i class="fa fa-play text-dark" style="margin-left:2px;"></i>
                                        </button>
                                    </div>
                                    <h6>{{ $video->nombre }}</h6>
                                    <p>{{ $video->descripcion }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script>
        //Tiperar texto en buscador:
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const texts = [
                'Videos institucionales...',
                'Metodo montessori de educación en adolescentes...',
                'El docente como educador...'
            ];
            let textIndex = 0;
            let charIndex = 0;

            function typeText() {
                if (charIndex < texts[textIndex].length) {
                    searchInput.placeholder += texts[textIndex].charAt(charIndex);
                    charIndex++;
                    setTimeout(typeText, 100); // Velocidad de tipeo
                } else {
                    setTimeout(eraseText, 2000); // Tiempo antes de borrar el texto
                }
            }

            function eraseText() {
                if (charIndex > 0) {
                    searchInput.placeholder = searchInput.placeholder.slice(0, -1);
                    charIndex--;
                    setTimeout(eraseText, 50); // Velocidad de borrado
                } else {
                    textIndex = (textIndex + 1) % texts.length;
                    setTimeout(typeText, 500); // Tiempo antes de comenzar a tipear nuevamente
                }
            }

            setTimeout(typeText, 500); // Tiempo antes de comenzar la animación
        });

        //Menu lateral secciones:
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = document.querySelectorAll('.custom-section-toggle');
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    var icon = this.querySelector('.custom-toggle-icon');
                    var submenu = this.nextElementSibling;
                    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                    icon.classList.toggle('rotate');
                });
            });
        });
    </script>

@endsection
