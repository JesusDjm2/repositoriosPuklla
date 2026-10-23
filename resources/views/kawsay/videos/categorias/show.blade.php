@extends('layouts.padre')
@section('titulo', '- Kawsay')

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @include('partials.lib-hero', [
        'heroBg' => asset('img/fondo/sisi-fondo.png'),
        'heroBgAlt' => 'Fondo Kawsay',
        'heroSearchRoute' => route('kawsay.buscar'),
        'heroSearchPlaceholder' => 'Buscar en Kawsay...',
        'heroEyebrow' => 'Videos · Kawsay',
        'heroTitle' => $categoria->nombre,
        'heroTitleSmall' => true,
        'heroMeta' => [
            ['icon' => 'fa-video', 'text' => $videos->count() . ' ' . ($videos->count() === 1 ? 'video' : 'videos')],
        ],
    ])
    <div class="container mt-4">
        <div class="row mt-4">
            <div class="col-lg-4 mt-4">
                <div class="custom-sidebar">
                    <h4>Categorías:</h4>
                    <ul class="custom-sidebar-menu">
                        @foreach ($categorias as $cat)
                            <li class="custom-sidebar-menu-item">
                                <a href="{{ route('kawsayvideoscat.show', $cat->url) }}" class="custom-sidebar-link">
                                    <span>
                                        {{ $cat->nombre }}
                                        <span class="video-count">
                                            ({{ $cat->videos_count }}
                                            {{ $cat->videos_count === 1 ? 'video' : 'videos' }})
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
                    <h2 class="titulo2Puklla mb-5">Categoria: {{ $categoria->nombre }}</h2>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text btn btn-sm btn-primary" id="basic-addon1">Buscar</span>
                        </div>
                        <input type="text" id="search-input" placeholder="" class="form-control form-control-sm"
                            aria-label="Buscar videos por nombre" aria-describedby="basic-addon1">
                    </div>

                    @foreach ($videos as $video)
                        <div class="col-lg-6 p-3">
                            @if ($video->youtube)
                                <div class="cardVideos">
                                    <div class="ratio ratio-16x9">
                                        <iframe loading="lazy" src="https://www.youtube.com/embed/{{ $video->youtube }}"
                                            title="YouTube video" allowfullscreen frameborder="0"></iframe>
                                    </div>
                                    <h6>{{ $video->nombre }}</h6>
                                    <p>{{ $video->descripcion }}</p>
                                </div>
                            @elseif ($video->drive)
                                @include('kawsay.partials.video-card', [
                                    'src' => route('kawsayvideos.stream', $video),
                                    'titulo' => $video->nombre,
                                    'driveId' => $video->drive,
                                    'meta' => $video->categoria?->nombre,
                                    'descripcion' => $video->descripcion,
                                ])
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const videoCards = document.querySelectorAll('.cardVideos, .kawsay-video-card');

            // Animación de placeholder
            const texts = [
                'Buscar videos de Kawsay...',
                'Experiencias del proyecto...',
                'Metodología intercultural...'
            ];
            let textIndex = 0;
            let charIndex = 0;

            function typeText() {
                if (charIndex < texts[textIndex].length) {
                    searchInput.placeholder += texts[textIndex].charAt(charIndex);
                    charIndex++;
                    setTimeout(typeText, 100);
                } else {
                    setTimeout(eraseText, 2000);
                }
            }

            function eraseText() {
                if (charIndex > 0) {
                    searchInput.placeholder = searchInput.placeholder.slice(0, -1);
                    charIndex--;
                    setTimeout(eraseText, 50);
                } else {
                    textIndex = (textIndex + 1) % texts.length;
                    setTimeout(typeText, 500);
                }
            }

            setTimeout(typeText, 500);

            // Filtro en tiempo real por nombre del video
            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.toLowerCase();

                videoCards.forEach(function(card) {
                    const title = card.querySelector('h6')?.textContent.toLowerCase() || '';
                    if (title.includes(searchTerm)) {
                        card.closest('.col-lg-6').style.display = 'block';
                    } else {
                        card.closest('.col-lg-6').style.display = 'none';
                    }
                });
            });

            // Menú lateral
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
