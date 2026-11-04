{{--
    Nodo del árbol público de registros. Recibe $category (con public_children_count,
    aportaciones_count y files:tipo ya cargados, ver CategoryController::nodosArbol) y $level.
    Si los hijos no vinieron precargados, la lista queda vacía con data-lazy-url y se pide
    al expandirla (sisipedia.registros.hijos) — así la página no carga los ~1.500 nodos.
--}}
@php
    $childCount   = (int) ($category->public_children_count ?? 0);
    $hasChildren  = $childCount > 0;
    $childrenLoaded = $category->relationLoaded('children');
    $tipos        = ($category->files ?? collect())->pluck('tipo')->unique();
    $aportCount   = (int) ($category->aportaciones_count ?? 0);

    // Sangría corta en celular: con 7 niveles, la anterior dejaba el texto en media pantalla.
    $indentClass = $level === 0 ? '' : 'ms-2 ms-md-4';
@endphp

<li class="mb-2 category-node {{ $indentClass }}">
    {{-- ── Fila principal ───────────────────────────────────────── --}}
    <div class="list-group-item border rounded-3 px-2 px-md-3 py-2" data-category-item>

        <div class="d-flex align-items-center gap-2">
            {{-- Flecha acordeón a la izquierda (antes del ícono de carpeta) --}}
            @if ($hasChildren)
                <button class="btn btn-sm p-0 border-0 toggle-btn flex-shrink-0"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#sub-{{ $category->id }}"
                        aria-expanded="false"
                        aria-controls="sub-{{ $category->id }}"
                        aria-label="Abrir o cerrar {{ $category->display_name }}"
                        style="width:28px;height:28px;background:transparent;color:#6c757d;">
                    <i class="fa fa-chevron-right toggle-icon"></i>
                </button>
            @else
                <span style="width:28px;height:28px;display:inline-block;" class="flex-shrink-0"></span>
            @endif

            {{-- Icono --}}
            <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center
                        justify-content-center flex-shrink-0 d-none d-sm-flex"
                 style="width:40px;height:40px;min-width:40px;">
                <i class="fa fa-folder{{ $hasChildren ? '-open' : '' }} fa-sm"></i>
            </div>

            {{-- Nombre + badges --}}
            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <a href="{{ route('sisipedia.categories.show', $category) }}"
                       class="fw-semibold text-dark text-decoration-none" style="word-break:break-word;">
                        {{ $category->display_name }}
                    </a>
                    @foreach (['pdf' => ['danger', 'fa-file-pdf', 'PDF'], 'doc' => ['secondary', 'fa-file-word', 'Doc'], 'audio' => ['success', 'fa-music', 'Audio'], 'video' => ['primary', 'fa-video', 'Video'], 'imagen' => ['info', 'fa-image', 'Imagen']] as $t => [$color, $icon, $label])
                        @if ($tipos->contains($t))
                            <span class="badge rounded-pill bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }}"
                                  style="font-size:.65rem;">
                                <i class="fa {{ $icon }} me-1"></i>{{ $label }}
                            </span>
                        @endif
                    @endforeach
                    @if ($aportCount > 0)
                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning"
                              style="font-size:.65rem;" title="{{ $aportCount }} aportes">
                            <i class="fa fa-users me-1"></i>{{ $aportCount }}
                        </span>
                    @endif
                    @if ($hasChildren)
                        <span class="badge rounded-pill bg-light text-secondary border"
                              style="font-size:.65rem;" title="{{ $childCount }} subtemas">
                            <i class="fa fa-folder-open me-1"></i>{{ $childCount }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Botones --}}
            <a href="{{ route('sisipedia.categories.show', $category) }}"
               class="btn btn-sm btn-outline-primary px-2 flex-shrink-0" title="Ver detalle"
               aria-label="Ver {{ $category->display_name }}">
                <i class="fa fa-eye"></i>
                <span class="d-none d-md-inline ms-1">Ver</span>
            </a>
        </div>
    </div>

    {{-- ── Hijos colapsables (cerrados por defecto) ─────────────── --}}
    @if ($hasChildren)
        @if ($childrenLoaded)
            <ul id="sub-{{ $category->id }}" class="list-unstyled mt-1 collapse" data-tree-children>
                @foreach ($category->children as $child)
                    @include('sisichakuna.tree', ['category' => $child, 'level' => $level + 1])
                @endforeach
            </ul>
        @else
            <ul id="sub-{{ $category->id }}" class="list-unstyled mt-1 collapse" data-tree-children
                data-lazy-url="{{ route('sisipedia.registros.hijos', ['category' => $category, 'level' => $level + 1]) }}">
                <li class="text-muted small ps-4 py-2" data-tree-loading>
                    <i class="fa fa-spinner fa-spin me-1"></i>Cargando…
                </li>
            </ul>
        @endif
    @endif
</li>
