@extends('layouts.padre')
@section('titulo', '- Kawsay')
@section('contenido')
    <div class="fixed-image-container">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2>Video</h2>
            <h1>{{ $video->nombre }}</h1>
        </div>
    </div>
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
                        <div class="ratio ratio-16x9">
                            <iframe loading="lazy"
                                src="https://drive.google.com/file/d/{{ $video->drive }}/preview"
                                allowfullscreen frameborder="0"></iframe>
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
