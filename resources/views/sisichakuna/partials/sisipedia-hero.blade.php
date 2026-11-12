@php
    $conteosHero = $conteosPanel ?? (isset($conteos) ? $conteos : app(\App\Services\ProyectoEstadisticasService::class)->paraRol('sisicha'));
    $iconosStats = [
        'Registros Sisipedia' => 'fa-sitemap',
        'Documentos PDF' => 'fa-file-pdf-o',
        'Audios Sisipedia' => 'fa-headphones',
        'Imágenes' => 'fa-picture-o',
        'Aportaciones pendientes' => 'fa-inbox',
        'Canciones' => 'fa-music',
        'Categorías de canciones' => 'fa-tags',
        'Videos' => 'fa-video-camera',
        'Categorías de video' => 'fa-tags',
    ];
@endphp

<x-hero-banner
    titulo="{{ $tituloHero ?? 'Panel Sisichakunay' }}"
    subtitulo="{{ $subtituloHero ?? 'Radio Pukllasunchis · registros y aportaciones de Sisipedia' }}"
    icono="fa-podcast"
    color="radio">
    <x-slot:stats>
        @foreach ($conteosHero as $etiqueta => $valor)
            <x-hero-stat :icono="$iconosStats[$etiqueta] ?? 'fa-th-list'" :valor="$valor" :etiqueta="$etiqueta" />
        @endforeach
    </x-slot:stats>
    <x-slot:actions>
        <div class="hero-banner__action-stack">
            <a href="{{ route('public.categoria.sisi') }}" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-sm text-nowrap">
                <i class="fa fa-external-link mr-1"></i> Sisipedia pública
            </a>
            <a href="{{ route('sisipedia.categories.export') }}" class="btn btn-outline-light btn-sm text-nowrap"
                title="Descargar todos los registros de Sisipedia en un archivo CSV (se abre en Excel)">
                <i class="fa fa-download mr-1"></i> Descargar registros CSV
            </a>
        </div>
    </x-slot:actions>
</x-hero-banner>
