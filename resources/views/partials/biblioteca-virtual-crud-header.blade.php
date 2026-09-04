@push('styles')
    {{-- Antes faltaba en este partial (solo lo pedía el hermano de biblioteca
         física): sin esto, .bv-crud-nav/.bv-crud-tab quedaban sin la hoja de
         estilos correcta y cualquier regla de admin.scss con el mismo nombre
         de clase se colaba encima, dando un aspecto distinto según el grupo. --}}
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush
@include('partials.biblioteca-grupo-tabs', ['grupoActivo' => 'digital'])
@php
    $active = $active ?? '';
    $tabs = [
        'secciones' => ['label' => 'Secciones', 'route' => route('secciones.index')],
        'categorias' => ['label' => 'Categorías', 'route' => route('categorias.index')],        
        'libros' => ['label' => 'Libros', 'route' => route('libros.index')],
    ];
@endphp
<nav class="bv-crud-nav mb-3" aria-label="Biblioteca virtual">
    <div class="bv-crud-nav-inner">
        @foreach ($tabs as $key => $t)
            <a href="{{ $t['route'] }}"
                class="bv-crud-tab {{ $active === $key ? 'is-active' : '' }}">{{ $t['label'] }}</a>
        @endforeach
    </div>
</nav>
