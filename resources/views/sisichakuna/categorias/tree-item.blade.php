{{-- Fila recursiva del árbol de registros (admin). Estilos: admin/modules/_sisipedia.scss
     Todo lo calculado llega del controlador (CategoryController::index / arbolAdmin):
     $numeracion, $tamSubarbol, $resumenArchivos, $aportCounts. Esta vista NO debe
     disparar consultas: se repite ~1.500 veces. --}}
@php
    $hasChildren = $category->children->isNotEmpty();
    $resumen = $resumenArchivos[$category->id] ?? [];
    $aportCount = $aportCounts[$category->id] ?? 0;
    $nPdf = $resumen['pdf'] ?? 0;
    $nDoc = $resumen['doc'] ?? 0;
    $nAudio = $resumen['audio'] ?? 0;
    $nVideo = $resumen['video'] ?? 0;
@endphp
<li>
    <div class="category-item">
        <div class="category-info">
            <div class="category-identity">
                @if ($hasChildren)
                    <button type="button" class="expand-btn" data-target="children-{{ $category->id }}"
                        aria-controls="children-{{ $category->id }}" aria-label="Mostrar sub-registros de {{ $category->display_name }}">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                @else
                    <span class="expand-spacer" aria-hidden="true"></span>
                @endif
                @if ($category->image)
                    <img src="{{ asset($category->image) }}" alt="" class="category-image" loading="lazy">
                @else
                    <div class="category-image-placeholder" aria-hidden="true">
                        <i class="fa fa-folder"></i>
                    </div>
                @endif
                <div class="category-details">
                    <div class="category-name">
                        <span class="category-number">{{ $numeracion[$category->id] ?? '' }}</span>
                        <span class="cat-name-text">{{ $category->display_name }}</span>
                    </div>
                    @if ($category->slug)
                        <div class="category-slug">{{ $category->slug }}</div>
                    @endif
                    <div class="category-files">
                        @if ($nPdf > 0)
                            <span class="adm-chip adm-chip--pdf"><i class="fa fa-file-pdf-o"></i>PDF {{ $nPdf }}</span>
                        @endif
                        @if ($nDoc > 0)
                            <span class="adm-chip adm-chip--doc"><i class="fa fa-file-word-o"></i>Doc {{ $nDoc }}</span>
                        @endif
                        @if ($nAudio > 0)
                            <span class="adm-chip adm-chip--audio"><i class="fa fa-music"></i>Audio {{ $nAudio }}</span>
                        @endif
                        @if ($nVideo > 0)
                            <span class="adm-chip adm-chip--video"><i class="fa fa-video-camera"></i>Video {{ $nVideo }}</span>
                        @endif
                        @if ($aportCount > 0)
                            <span class="adm-chip adm-chip--aporte"><i class="fa fa-users"></i>{{ $aportCount }} aportacion{{ $aportCount !== 1 ? 'es' : '' }}</span>
                        @else
                            <span class="adm-chip">Sin aportaciones</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="category-meta">
                @if ($category->parent)
                    <span class="category-parent">
                        <i class="fa fa-level-up fa-rotate-90"></i>{{ $category->parent->display_name }}
                    </span>
                @endif

                {{-- Contadores (solo si la columna ya existe en esta base) --}}
                @if (array_key_exists('visitas', $category->getAttributes()))
                    @php $descMat = $resumen['descargas'] ?? 0; @endphp
                    <span class="category-order" title="Visitas a la página pública del registro">
                        <i class="fa fa-eye"></i> {{ \App\Services\ContadorSisipedia::formato((int) $category->visitas) }}
                    </span>
                    <span class="category-order" title="Descargas de los materiales de este registro">
                        <i class="fa fa-download"></i> {{ \App\Services\ContadorSisipedia::formato($descMat) }}
                    </span>
                @endif

                <span class="category-order" title="Posición en el listado">
                    <i class="fa fa-sort-numeric-asc"></i> {{ $category->order }}
                </span>
                <span class="category-status {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                    <i class="fa fa-{{ $category->is_active ? 'check-circle' : 'times-circle' }}"></i>
                    {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
        </div>

        <div class="category-actions">
            <a href="{{ $category->is_active ? route('sisipedia.categories.show', $category) : route('sisipedia.categories.admin-show', $category) }}"
                target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary btn-icon" title="Ver registro"
                aria-label="Ver {{ $category->display_name }}">
                <i class="fa fa-eye"></i>
            </a>
            <a href="{{ route('sisipedia.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary btn-icon"
                title="Editar registro" aria-label="Editar {{ $category->display_name }}">
                <i class="fa fa-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-icon toggle-status-btn"
                data-id="{{ $category->id }}" data-active="{{ $category->is_active ? '1' : '0' }}"
                data-toggle-url="{{ route('sisipedia.categories.toggle-status', $category) }}"
                title="{{ $category->is_active ? 'Desactivar' : 'Activar' }}"
                aria-label="{{ $category->is_active ? 'Desactivar' : 'Activar' }} {{ $category->display_name }}">
                <i class="fa fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
            </button>
            @php $subtreeSize = $tamSubarbol[$category->id] ?? 1; @endphp
            <button type="button" class="btn btn-sm btn-outline-danger btn-icon delete-category"
                data-id="{{ $category->id }}" data-name="{{ $category->display_name }}"
                data-delete-url="{{ route('sisipedia.categories.destroy', $category) }}"
                data-cascade="{{ $hasChildren ? '1' : '0' }}" data-subtree-size="{{ $subtreeSize }}"
                title="{{ $hasChildren ? 'Eliminar este registro y toda su jerarquía' : 'Eliminar' }}"
                aria-label="Eliminar {{ $category->display_name }}">
                <i class="fa fa-trash"></i>
                @if ($hasChildren)
                    <span class="d-none d-xl-inline small">+ jerarquía</span>
                @endif
            </button>
        </div>
    </div>

    @if ($hasChildren)
        <ul id="children-{{ $category->id }}" style="display:none;">
            @foreach ($category->children as $child)
                @include('sisichakuna.categorias.tree-item', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
