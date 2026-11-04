{{-- Alternativas cuando no hay término o no hay resultados: por formato y por área del saber. --}}
@php $Busqueda = \App\Http\Controllers\sisipedia\BusquedaController::class; @endphp
<p class="sisi-sr-explore__label">Explorar por formato</p>
<div class="sisi-sr-explore__formats">
    <a href="{{ route('public.sisi', ['tipo' => 'audio']) }}"><i class="fa fa-music" aria-hidden="true"></i>Audios</a>
    <a href="{{ route('public.sisi', ['tipo' => 'pdf']) }}"><i class="fa fa-file-pdf" aria-hidden="true"></i>PDF</a>
    <a href="{{ route('public.sisi', ['tipo' => 'imagen']) }}"><i class="fa fa-image" aria-hidden="true"></i>Imágenes</a>
    <a href="{{ route('public.sisi', ['tipo' => 'video']) }}"><i class="fa fa-video" aria-hidden="true"></i>Videos</a>
</div>

@if ($temas->isNotEmpty())
    <p class="sisi-sr-explore__label">Explorar por área</p>
    <div class="sisi-sr-explore__temas">
        @foreach ($temas as $tema)
            {{-- Mismo color que el área tiene en los resultados (las áreas van en el mismo orden) --}}
            <a href="{{ route('sisipedia.categories.show', $tema) }}"
               style="--area: {{ $Busqueda::COLORES_AREA[$loop->index % count($Busqueda::COLORES_AREA)] }}">
                <i aria-hidden="true"></i>{{ $Busqueda::nombre($tema->name, true) }}
            </a>
        @endforeach
    </div>
@endif
