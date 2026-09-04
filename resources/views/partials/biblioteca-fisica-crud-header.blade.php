@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush
@include('partials.biblioteca-grupo-tabs', ['grupoActivo' => 'fisica'])
@php
    $active = $active ?? '';
    $tabs = [
        'categorias' => ['label' => 'Categorías', 'route' => route('biblioteca-fisica.categorias.index')],
        'libros' => ['label' => 'Libros', 'route' => route('biblioteca-fisica.libros.index')],
        'prestamos' => ['label' => 'Préstamos', 'route' => route('biblioteca-fisica.prestamos.index')],
    ];
@endphp
<nav class="bv-crud-nav mb-3" aria-label="Biblioteca física">
    <div class="bv-crud-nav-inner">
        @foreach ($tabs as $key => $t)
            <a href="{{ $t['route'] }}"
                class="bv-crud-tab {{ $active === $key ? 'is-active' : '' }}">{{ $t['label'] }}</a>
        @endforeach
    </div>
</nav>
