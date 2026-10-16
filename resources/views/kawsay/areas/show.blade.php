@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $area->nombre)

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay') }}" class="kawsay-back-link"><i class="fa fa-arrow-left"></i> Kawsay</a>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <span class="kawsay-area-header__icon"><i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i></span>
                    <div>
                        <h1 class="kawsay-area-header__title">{{ $area->nombre }}</h1>
                    </div>
                </div>
                <p class="kawsay-area-header__desc">{{ $area->descripcion }}</p>
            </div>
            <form action="{{ route('kawsay.buscar') }}" method="GET" class="kawsay-header-search">
                <input type="text" name="query" placeholder="Buscar en Kawsay..." minlength="3" required aria-label="Buscar en Kawsay">
                <button type="submit" aria-label="Buscar"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>
</div>

<div class="container py-4 py-md-5">
    @if ($temas->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa {{ $area->icono ?: 'fa-leaf' }} fa-3x mb-3 d-block opacity-25"></i>
            <p class="mb-0">Próximamente vamos a publicar los temas de esta área.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach ($temas as $tema)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('kawsay.tema', [$areaSlug, $tema->slug]) }}" class="kawsay-tema-card {{ $tema->imagen ? 'kawsay-tema-card--foto' : 'kawsay-tema-card--texto' }}"
                        style="--area-color: {{ $area->color ?: '#6e7d1e' }};">
                        @if ($tema->imagen)
                            <span class="kawsay-tema-card__foto" style="background-image:url('{{ asset($tema->imagen) }}')" role="img" aria-label="{{ $tema->titulo }}"></span>
                        @endif
                        <span class="kawsay-tema-card__title">{{ $tema->titulo }}</span>
                        @if ($tema->archivos_count)
                            <span class="kawsay-tema-card__badge">{{ $tema->archivos_count }}</span>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

@include('kawsay.partials.estilos')
@endsection
