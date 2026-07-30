@props(['titulo', 'subtitulo' => null])

<div class="admin-page-header">
    <div>
        <h1 class="h3 admin-page-header__title">{{ $titulo }}</h1>
        @if ($subtitulo)
            <p class="admin-page-header__subtitle mb-0">{{ $subtitulo }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="admin-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
