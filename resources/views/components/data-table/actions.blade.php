@props([
    'editHref' => null,
    'deleteAction' => null,
    'deleteConfirmTitle' => '¿Eliminar este registro?',
    'deleteConfirmText' => 'Esta acción no se puede deshacer.',
])

<div class="admin-table__actions">
    {{ $slot ?? '' }}

    @if ($editHref)
        <a href="{{ $editHref }}" class="admin-table__icon-btn" title="Editar" aria-label="Editar">
            <i class="fa fa-pencil"></i>
        </a>
    @endif

    @if ($deleteAction)
        <form action="{{ $deleteAction }}" method="POST" class="admin-table__delete-form"
            data-confirm-delete="{{ $deleteConfirmTitle }}" data-confirm-delete-text="{{ $deleteConfirmText }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-table__icon-btn admin-table__icon-btn--danger" title="Eliminar"
                aria-label="Eliminar">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    @endif
</div>
