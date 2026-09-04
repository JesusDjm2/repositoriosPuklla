{{-- Nivel superior de navegación de Biblioteca: cambia entre el grupo digital
     y el físico. Vive por encima de las pestañas internas de cada grupo
     (Secciones/Categorías/Libros o Categorías físicas/Libros físicos/
     Préstamos, ver partials/biblioteca-virtual-crud-header y
     biblioteca-fisica-crud-header, que son quienes lo incluyen) para que
     cambiar de grupo no obligue a volver al panel o al sidebar. --}}
@php
    $grupoActivo = $grupoActivo ?? null;
@endphp
<nav class="bv-crud-nav-grupo mb-2" aria-label="Biblioteca">
    <a href="{{ route('secciones.index') }}"
        class="bv-crud-tab-grupo {{ $grupoActivo === 'digital' ? 'is-active' : '' }}">
        <i class="fa fa-laptop"></i> Biblioteca digital
    </a>
    <a href="{{ route('biblioteca-fisica.categorias.index') }}"
        class="bv-crud-tab-grupo {{ $grupoActivo === 'fisica' ? 'is-active' : '' }}">
        <i class="fa fa-archive"></i> Biblioteca física
    </a>
</nav>
