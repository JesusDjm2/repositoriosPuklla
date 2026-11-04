{{-- Alternativas cuando no hay término o no hay resultados: por formato y por tema principal. --}}
<p class="sisi-sr-explore__label">Explorar por formato</p>
<div class="sisi-sr-explore__formats">
    <a href="{{ route('public.sisi', ['tipo' => 'audio']) }}"><i class="fa fa-music text-success" aria-hidden="true"></i>Audios</a>
    <a href="{{ route('public.sisi', ['tipo' => 'pdf']) }}"><i class="fa fa-file-pdf text-danger" aria-hidden="true"></i>PDF</a>
    <a href="{{ route('public.sisi', ['tipo' => 'imagen']) }}"><i class="fa fa-image text-info" aria-hidden="true"></i>Imágenes</a>
    <a href="{{ route('public.sisi', ['tipo' => 'video']) }}"><i class="fa fa-video text-warning" aria-hidden="true"></i>Videos</a>
</div>

@if ($temas->isNotEmpty())
    <p class="sisi-sr-explore__label">Temas principales</p>
    <div class="sisi-sr-explore__temas">
        @foreach ($temas as $tema)
            <a href="{{ route('sisipedia.categories.show', $tema) }}"><i class="fa fa-folder text-primary" aria-hidden="true"></i>{{ $tema->display_name }}</a>
        @endforeach
    </div>
@endif
