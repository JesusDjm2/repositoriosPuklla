{{--
    Selector de categoría padre en árbol desglosable, en vez de un <select>
    plano con todos los registros indentados (con cientos de registros esa
    lista se vuelve larguísima y tediosa de recorrer). Cada rama se abre solo
    al hacer click en su flecha; al cargar, se auto-expande el camino hasta
    la selección actual (si la hay) para que quede a la vista.

    Variables:
    - $parentTree (Collection<Category>) árbol anidado (ver CategoryController::buildParentTree)
    - $selectedId (int|null) parent_id ya seleccionado (old() o el de la categoría en edición)
--}}
@php
    $selectedId = $selectedId ?? null;
    $selectedCategory = null;
    if ($selectedId) {
        $find = function ($nodes) use (&$find, $selectedId) {
            foreach ($nodes as $node) {
                if ($node->id == $selectedId) {
                    return $node;
                }
                if ($found = $find($node->children)) {
                    return $found;
                }
            }
            return null;
        };
        $selectedCategory = $find($parentTree);
    }
@endphp
<div class="bv-parent-picker" id="parentPicker">
    <input type="hidden" name="parent_id" id="parent_id"
        value="{{ $selectedId }}"
        data-depth="{{ $selectedCategory->depth ?? '' }}"
        data-numbering="{{ $selectedCategory->numbering ?? '' }}">

    <button type="button" class="bv-parent-picker__toggle" id="parentPickerToggle">
        <span id="parentPickerLabel">
            {{ $selectedCategory ? $selectedCategory->display_name : 'Ninguna (Raíz)' }}
        </span>
        <i class="fa fa-chevron-down"></i>
    </button>

    <div class="bv-parent-picker__panel" id="parentPickerPanel" hidden>
        <div class="bv-parent-picker__row bv-parent-picker__row--root {{ !$selectedId ? 'is-selected' : '' }}"
            data-id="" data-name="Ninguna (Raíz)" data-depth="" data-numbering="">
            <span class="bv-parent-picker__expand-spacer"></span>
            <span class="bv-parent-picker__option-label">Ninguna (Raíz)</span>
            <i class="fa fa-check bv-parent-picker__check"></i>
        </div>
        <ul class="bv-parent-picker__tree">
            @foreach ($parentTree as $category)
                @include('sisichakuna.categorias._parent-picker-item', ['category' => $category, 'selectedId' => $selectedId])
            @endforeach
        </ul>
        @if ($parentTree->isEmpty())
            <p class="bv-parent-picker__empty">No hay otros registros todavía.</p>
        @endif
    </div>
</div>

@once
<style>
    .bv-parent-picker { position: relative; }
    .bv-parent-picker__toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .375rem .75rem;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        text-align: left;
        font-size: 1rem;
    }
    .bv-parent-picker__toggle:focus { outline: none; border-color: #80bdff; box-shadow: 0 0 0 .2rem rgba(0,123,255,.25); }
    .bv-parent-picker__panel {
        position: absolute;
        z-index: 20;
        top: calc(100% + .25rem);
        left: 0;
        right: 0;
        max-height: 360px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: .375rem;
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15);
        padding: .4rem;
    }
    .bv-parent-picker__empty { margin: .5rem; color: #6c757d; font-size: .85rem; }
    .bv-parent-picker__tree, .bv-parent-picker__tree ul {
        list-style: none;
        margin: 0;
        padding-left: 1.15rem;
    }
    .bv-parent-picker__tree { padding-left: 0; }
    .bv-parent-picker__row {
        display: flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .5rem;
        border-radius: .3rem;
        cursor: pointer;
    }
    .bv-parent-picker__row:hover { background: #f1f3f5; }
    .bv-parent-picker__row.is-selected { background: rgba(0,123,255,.1); font-weight: 600; }
    .bv-parent-picker__row--root { margin-bottom: .3rem; border-bottom: 1px solid #e9ecef; padding-bottom: .5rem; }
    .bv-parent-picker__expand, .bv-parent-picker__expand-spacer {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        color: #6c757d;
        border-radius: .25rem;
    }
    .bv-parent-picker__expand:hover { background: #e9ecef; }
    .bv-parent-picker__expand i { transition: transform .15s ease; font-size: .75rem; }
    .bv-parent-picker__expand.is-open i { transform: rotate(90deg); }
    .bv-parent-picker__option-label { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bv-parent-picker__check { display: none; color: #007bff; }
    .bv-parent-picker__row.is-selected .bv-parent-picker__check { display: inline-block; }
</style>
<script>
(function () {
    function initParentPicker(root) {
        var toggle = root.querySelector('#parentPickerToggle');
        var panel = root.querySelector('#parentPickerPanel');
        var label = root.querySelector('#parentPickerLabel');
        var input = root.querySelector('#parent_id');

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.hidden = !panel.hidden;
        });
        document.addEventListener('click', function (e) {
            if (!panel.hidden && !root.contains(e.target)) {
                panel.hidden = true;
            }
        });

        root.querySelectorAll('.bv-parent-picker__expand').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var childrenList = btn.closest('li, .bv-parent-picker').querySelector(':scope > ul, :scope > .bv-parent-picker__children');
                if (!childrenList) return;
                childrenList.hidden = !childrenList.hidden;
                btn.classList.toggle('is-open', !childrenList.hidden);
            });
        });

        root.querySelectorAll('.bv-parent-picker__row').forEach(function (row) {
            row.addEventListener('click', function () {
                root.querySelectorAll('.bv-parent-picker__row.is-selected').forEach(function (r) { r.classList.remove('is-selected'); });
                row.classList.add('is-selected');
                input.value = row.getAttribute('data-id') || '';
                input.setAttribute('data-depth', row.getAttribute('data-depth') || '');
                input.setAttribute('data-numbering', row.getAttribute('data-numbering') || '');
                label.textContent = row.getAttribute('data-name');
                input.dispatchEvent(new Event('change'));
                panel.hidden = true;
            });
        });

        // Auto-expandir el camino hasta la selección actual, si hay una.
        var selected = root.querySelector('.bv-parent-picker__row.is-selected:not(.bv-parent-picker__row--root)');
        if (selected) {
            var node = selected.closest('li');
            while (node) {
                var parentUl = node.parentElement;
                if (parentUl && parentUl.classList.contains('bv-parent-picker__children')) {
                    parentUl.hidden = false;
                    var parentLi = parentUl.closest('li');
                    var expandBtn = parentLi ? parentLi.querySelector(':scope > .bv-parent-picker__row .bv-parent-picker__expand') : null;
                    if (expandBtn) expandBtn.classList.add('is-open');
                    node = parentLi;
                } else {
                    node = null;
                }
            }
        }
    }

    document.querySelectorAll('.bv-parent-picker').forEach(initParentPicker);
})();
</script>
@endonce
