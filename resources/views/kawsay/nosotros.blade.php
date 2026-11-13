@extends('layouts.padre')
@section('titulo', '- Kawsay - Nosotros')

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay') }}" class="kawsay-back-link"><i class="fa fa-arrow-left"></i> Kawsay</a>
                <h1 class="kawsay-area-header__title mt-2">Nosotros</h1>
                <p class="kawsay-area-header__desc mb-0">Quiénes somos y qué es el repositorio educativo Kawsay.</p>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-bold mb-3">Quiénes somos</h2>
                    <p class="text-muted mb-0">
                        <em>Texto de ejemplo — reemplázalo por el contenido real que quieras publicar aquí.</em>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('kawsay.partials.estilos')
@endsection
