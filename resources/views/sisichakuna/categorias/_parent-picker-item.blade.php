{{-- Fila recursiva del árbol de _parent-picker.blade.php --}}
<li class="bv-parent-picker__item">
    <div class="bv-parent-picker__row {{ $category->id == ($selectedId ?? null) ? 'is-selected' : '' }}"
        data-id="{{ $category->id }}" data-name="{{ $category->display_name }}"
        data-depth="{{ $category->depth }}" data-numbering="{{ $category->numbering }}">
        @if ($category->children->isNotEmpty())
            <button type="button" class="bv-parent-picker__expand"><i class="fa fa-chevron-right"></i></button>
        @else
            <span class="bv-parent-picker__expand-spacer"></span>
        @endif
        <span class="bv-parent-picker__option-label">{{ $category->display_name }}</span>
        <i class="fa fa-check bv-parent-picker__check"></i>
    </div>
    @if ($category->children->isNotEmpty())
        <ul class="bv-parent-picker__children" hidden>
            @foreach ($category->children as $child)
                @include('sisichakuna.categorias._parent-picker-item', ['category' => $child, 'selectedId' => $selectedId ?? null])
            @endforeach
        </ul>
    @endif
</li>
