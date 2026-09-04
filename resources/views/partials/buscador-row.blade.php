{{--
    Fila compacta de un resultado del buscador global. Reutilizada por cada
    tipo (Sisipedia, Videos, Libros, Aportaciones, Canciones) para que todas
    las secciones sean igual de cortas: solo título + una línea de contexto,
    sin miniaturas ni reproductores incrustados.

    Variables:
    - $icon      (string) clase Font Awesome sin el prefijo "fa ", ej. 'fa-sitemap'
    - $iconColor (string, opcional) color del círculo del ícono (hex/rgb)
    - $href      (string|null) URL destino; null = fila no clicable
    - $external  (bool, opcional) añade target="_blank" si $href es externo
    - $title     (string) HTML ya escapado/resaltado
    - $meta      (string|null) HTML ya escapado/resaltado, línea secundaria
--}}
@php
    $iconColor = $iconColor ?? '#6a0310';
    $hasLink = filled($href ?? null);
@endphp
<a href="{{ $hasLink ? $href : 'javascript:void(0)' }}"
    @if ($hasLink && ($external ?? false)) target="_blank" rel="noopener" @endif
    class="buscador-row {{ $hasLink ? '' : 'buscador-row--nolink' }}">
    <span class="buscador-row-icon" style="--row-icon-color: {{ $iconColor }};"><i class="fa {{ $icon }}"></i></span>
    <span class="buscador-row-body">
        <span class="buscador-row-title">{!! $title !!}</span>
        @if (! empty($meta))
            <span class="buscador-row-meta">{!! $meta !!}</span>
        @endif
    </span>
    @if ($hasLink)
        <i class="fa fa-chevron-right buscador-row-arrow" aria-hidden="true"></i>
    @endif
</a>
