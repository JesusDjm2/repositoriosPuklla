{{--
    Barra de navegación Sisipedia (panel administrativo).
    $active: 'home' | 'categories' | 'aportaciones' | 'busquedas'
    Estilos: resources/sass/admin/modules/_sisipedia.scss (.sisi-admin-nav)
--}}
@php
    $active = $active ?? 'categories';
    $pendientesNav = \App\Models\sisipedia\Aportacion::where('is_approved', false)->count();
@endphp
<div class="card border-0 shadow-sm mb-4 sisipedia-admin-nav">
    <div class="card-body py-2 px-3">
        <nav class="sisi-admin-nav" aria-label="Secciones de Sisipedia">
            <div class="sisi-admin-nav__links">
                <span class="sisi-admin-nav__label">Sisipedia</span>
                <a href="{{ route('adminRadio') }}"
                    class="btn btn-sm {{ $active === 'panel' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}"
                    @if ($active === 'panel') aria-current="page" @endif>
                    <i class="fa fa-th-large mr-1"></i>Panel
                </a>
                <a href="{{ route('sisipedia.categories.index') }}"
                    class="btn btn-sm {{ $active === 'categories' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}"
                    @if ($active === 'categories') aria-current="page" @endif>
                    <i class="fa fa-sitemap mr-1"></i>Registros
                </a>
                <a href="{{ route('sisipedia.aportaciones.index') }}"
                    class="btn btn-sm {{ $active === 'aportaciones' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}"
                    @if ($active === 'aportaciones') aria-current="page" @endif>
                    <i class="fa fa-comments mr-1"></i>Aportaciones
                    @if ($pendientesNav > 0)
                        <span class="badge badge-danger ml-1">{{ $pendientesNav }}</span>
                    @endif
                </a>
                <a href="{{ route('sisipedia.busquedas.sin-resultados') }}"
                    class="btn btn-sm {{ $active === 'busquedas' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}"
                    @if ($active === 'busquedas') aria-current="page" @endif>
                    <i class="fa fa-search mr-1"></i>Búsquedas sin resultados
                </a>
            </div>
        </nav>
    </div>
</div>
