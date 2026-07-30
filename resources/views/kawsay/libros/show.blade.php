@extends('layouts.padre')
@section('titulo', '- Kawsay')
@section('contenido')
    <div class="fixed-image-container">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2>LIBRO</h2>
            <h1>{{ $libro->nombre }}</h1>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row mt-4">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center">{{ $libro->nombre }}</h5>
                        <p class="text-center"><i style="font-size: 12px" class="fa fa-user"></i>
                            {{ $libro->autor }}</p>

                        @if ($libro->categorias->isNotEmpty())
                            <p class="text-center">
                                @foreach ($libro->categorias as $categoria)
                                    <a href="{{ route('kawsaycategorias.show', $categoria->url) }}"
                                        class="badge badge-info">{{ $categoria->nombre }}</a>
                                @endforeach
                            </p>
                        @endif

                        <div class="contenedorLibro">
                            <iframe loading="lazy"
                                src="https://drive.google.com/file/d/{{ $libro->identificador }}/preview"
                                width="100%" height="500"></iframe>
                        </div>

                        <div class="text-center mt-3">
                            <a href="https://drive.google.com/file/d/{{ $libro->identificador }}/view"
                                target="_blank" class="btnAbrirPDF btn btn-sm btn-primary">
                                Abrir PDF <i class="fab fa-google-drive"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
