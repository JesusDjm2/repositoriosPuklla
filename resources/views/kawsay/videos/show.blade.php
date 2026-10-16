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
        'heroEyebrow' => 'Video · Kawsay',
        'heroTitle' => $video->nombre,
        'heroTitleSmall' => true,
    ])
    <div class="container mt-4">
        <div class="row mt-4">
            <div class="col-lg-8 offset-lg-2">
                <div class="cardVideos">
                    @if ($video->youtube)
                        <div class="ratio ratio-16x9">
                            <iframe loading="lazy" src="https://www.youtube.com/embed/{{ $video->youtube }}"
                                title="YouTube video" allowfullscreen frameborder="0"></iframe>
                        </div>
                    @elseif ($video->drive)
                        {{-- Vista de detalle dedicada: reproductor nativo directo, sin popup
                             (play/pause/tiempo/pantalla completa vía controles del navegador). --}}
                        <div class="ratio ratio-16x9 bg-dark">
                            <video controls playsinline preload="metadata" style="object-fit:contain;"
                                   poster="{{ \App\Services\GoogleDriveService::getThumbnailUrl($video->drive, 700) }}"
                                   src="{{ route('kawsayvideos.stream', $video) }}"></video>
                        </div>
                    @endif
                    <h6 class="mt-3">{{ $video->nombre }}</h6>
                    @if ($video->descripcion)
                        <p>{{ $video->descripcion }}</p>
                    @endif
                    @if ($video->categoria)
                        <a href="{{ route('kawsayvideoscat.show', $video->categoria->url) }}" class="badge badge-info">
                            {{ $video->categoria->nombre }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
