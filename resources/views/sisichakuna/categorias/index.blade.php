@extends('layouts.app')
@section('titulo', '- Panel Sisichakunay')
@section('contenido')
    {{-- Encabezado (Hero) persistente de Radio & Sisipedia --}}
    @include('sisichakuna.partials.sisipedia-hero')

    @include('sisichakuna.partials.sisipedia-admin-nav', ['active' => 'categories'])
    <div class="container-fluid py-0 px-0">
        {{-- Lo más visto y descargado: ayuda a decidir qué temas ampliar --}}
        @if ($masVistos->isNotEmpty() || $masDescargados->isNotEmpty())
            <div class="row mb-2">
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 font-weight-bold"><i class="fa fa-eye text-primary mr-1"></i>Registros más visitados</h6>
                        </div>
                        <ol class="list-group list-group-flush small mb-0">
                            @forelse ($masVistos as $c)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <a href="{{ route('sisipedia.categories.show', $c) }}" target="_blank" rel="noopener" class="text-truncate mr-2">{{ $loop->iteration }}. {{ $c->display_name }}</a>
                                    <span class="badge badge-primary badge-pill">{{ \App\Services\ContadorSisipedia::formato((int) $c->visitas) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted py-3">Aún no hay visitas registradas.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 font-weight-bold"><i class="fa fa-download text-success mr-1"></i>Materiales más descargados</h6>
                        </div>
                        <ol class="list-group list-group-flush small mb-0">
                            @forelse ($masDescargados as $f)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span class="text-truncate mr-2">
                                        {{ $loop->iteration }}. <span class="text-uppercase text-muted">{{ $f->tipo }}</span>
                                        @if ($f->category)
                                            <a href="{{ route('sisipedia.categories.show', $f->category) }}" target="_blank" rel="noopener">{{ $f->category->display_name }}</a>
                                        @endif
                                        <span class="text-muted">· {{ \Illuminate\Support\Str::limit($f->nombre_original, 40) }}</span>
                                    </span>
                                    <span class="badge badge-success badge-pill">{{ \App\Services\ContadorSisipedia::formato((int) $f->descargas) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted py-3">Aún no hay descargas registradas.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="adm-page-head">
                    <div class="adm-page-head__title">
                        <h1 class="h4">
                            <i class="fa fa-sitemap mr-2 text-primary"></i>Registros Sisipedia
                        </h1>
                        <small class="text-muted">Estructura jerárquica de registros y sub-registros</small>
                    </div>
                    {{-- "Exportar CSV" vive en la barra superior (layouts/app), visible en todo Sisipedia. --}}
                    <div class="adm-page-head__actions">
                        <a href="{{ route('sisipedia.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus mr-1"></i>Nuevo registro
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <!-- Buscador + controles -->
                <div class="mb-4">
                    <div class="adm-toolbar">
                        <div class="input-group adm-toolbar__grow">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <input type="search" id="searchCategory" class="form-control"
                                placeholder="Busca un registro por nombre o slug" aria-label="Buscar registro">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch" aria-label="Limpiar búsqueda">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAll">
                            <i class="fa fa-expand mr-1"></i> Expandir todo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAll">
                            <i class="fa fa-compress mr-1"></i> Colapsar todo
                        </button>
                    </div>
                    @php
                        $totalPdf    = $fileTypeCounts['pdf']    ?? 0;
                        $totalDoc    = $fileTypeCounts['doc']    ?? 0;
                        $totalAudio  = $fileTypeCounts['audio']  ?? 0;
                        $totalVideo  = $fileTypeCounts['video']  ?? 0;
                        $totalImagen = $fileTypeCounts['imagen'] ?? 0;
                        $totalAport  = $aportCounts->sum();
                    @endphp
                    <div class="sisi-tree-summary">
                        <small class="text-muted">Contiene:</small>
                        @if ($totalPdf > 0)
                            <span class="adm-chip adm-chip--pdf"><i class="fa fa-file-pdf-o"></i>PDF <span class="adm-chip__count">{{ $totalPdf }}</span></span>
                        @endif
                        @if ($totalDoc > 0)
                            <span class="adm-chip adm-chip--doc"><i class="fa fa-file-word-o"></i>Doc <span class="adm-chip__count">{{ $totalDoc }}</span></span>
                        @endif
                        @if ($totalAudio > 0)
                            <span class="adm-chip adm-chip--audio"><i class="fa fa-music"></i>Audio <span class="adm-chip__count">{{ $totalAudio }}</span></span>
                        @endif
                        @if ($totalVideo > 0)
                            <span class="adm-chip adm-chip--video"><i class="fa fa-video-camera"></i>Video <span class="adm-chip__count">{{ $totalVideo }}</span></span>
                        @endif
                        @if ($totalImagen > 0)
                            <span class="adm-chip adm-chip--imagen"><i class="fa fa-picture-o"></i>Imágenes <span class="adm-chip__count">{{ $totalImagen }}</span></span>
                        @endif
                        @if ($totalAport > 0)
                            <span class="adm-chip adm-chip--aporte"><i class="fa fa-users"></i>Aportaciones <span class="adm-chip__count">{{ $totalAport }}</span></span>
                        @endif
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fa fa-info-circle"></i> Al buscar se muestran solo los registros que coinciden, con su jerarquía abierta.
                    </small>
                </div>
                <!-- Estructura jerárquica de categorías -->
                <div id="categoryTree">
                    @if ($tree->count())
                        <ul class="category-tree list-unstyled">
                            @foreach ($tree as $category)
                                @include('sisichakuna.categorias.tree-item', [
                                    'category' => $category,
                                    'level' => 0,
                                ])
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-folder-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No hay categorías creadas</p>
                            <a href="{{ route('sisipedia.categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Crear primera categoría
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput  = document.getElementById('searchCategory');
            const clearButton  = document.getElementById('clearSearch');
            const expandAllBtn = document.getElementById('expandAll');
            const collapseAllBtn = document.getElementById('collapseAll');
            const categoryItems = document.querySelectorAll('.category-item');
            const csrfToken = '{{ csrf_token() }}';

            // ── Expand / Collapse helpers ──────────────────────────────
            function setUlState(ul, expanded) {
                ul.style.display = expanded ? '' : 'none';
                const btn = document.querySelector(`.expand-btn[data-target="${ul.id}"]`);
                if (!btn) return;
                const icon = btn.querySelector('i');
                icon.classList.toggle('fa-chevron-down',  expanded);
                icon.classList.toggle('fa-chevron-right', !expanded);
            }

            expandAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, true));
            });
            collapseAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, false));
            });

            // Escapa texto antes de insertarlo como HTML (búsqueda y nombres).
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ── Word highlight ─────────────────────────────────────────
            // Trabaja sobre el texto escapado: el término no puede inyectar HTML.
            function highlight(el, term) {
                if (!el) return;
                if (!el.dataset.origHtml) el.dataset.origHtml = el.innerHTML;
                const esc = escapeHtml(term).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                el.innerHTML = el.dataset.origHtml.replace(new RegExp(`(${esc})`, 'gi'), '<mark>$1</mark>');
            }

            function clearHighlights() {
                document.querySelectorAll('[data-orig-html]').forEach(el => {
                    el.innerHTML = el.dataset.origHtml;
                    delete el.dataset.origHtml;
                });
            }

            function submitPostForm(action, method = null, extraFields = null) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = action;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;
                form.appendChild(csrf);

                if (method) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }

                if (extraFields && typeof extraFields === 'object') {
                    Object.entries(extraFields).forEach(([name, value]) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = name;
                        hidden.value = value;
                        form.appendChild(hidden);
                    });
                }

                document.body.appendChild(form);
                form.submit();
            }

            // ── Filter + highlight ─────────────────────────────────────
            function filterCategories(searchTerm) {
                clearHighlights();
                const existingNoResults = document.querySelector('.no-results');

                if (!searchTerm.trim()) {
                    // Restore: show all items, collapse all ULs back to default
                    categoryItems.forEach(item => {
                        item.classList.remove('has-search-match');
                        item.closest('li').style.display = '';
                    });
                    document.querySelectorAll('.category-tree ul[id]').forEach(ul => setUlState(ul, false));
                    if (existingNoResults) existingNoResults.style.display = 'none';
                    return;
                }

                const termLower = searchTerm.toLowerCase().trim();
                let hasMatches = false;

                categoryItems.forEach(item => {
                    const nameEl = item.querySelector('.cat-name-text');
                    const slugEl = item.querySelector('.category-slug');
                    const nameText = (nameEl?.textContent || '').toLowerCase();
                    const slugText = (slugEl?.textContent || '').toLowerCase();
                    const matches  = nameText.includes(termLower) || slugText.includes(termLower);

                    if (matches) {
                        hasMatches = true;
                        item.classList.add('has-search-match');
                        highlight(nameEl, searchTerm.trim());
                        highlight(slugEl, searchTerm.trim());

                        // Show this li and expand all ancestor ULs
                        let li = item.closest('li');
                        while (li) {
                            li.style.display = '';
                            const parentUl = li.parentElement;
                            if (parentUl && parentUl.id) setUlState(parentUl, true);
                            li = parentUl?.closest('li');
                        }
                    } else {
                        item.classList.remove('has-search-match');
                        item.closest('li').style.display = 'none';
                    }
                });

                // No-results message
                if (!hasMatches) {
                    if (!existingNoResults) {
                        const div = document.createElement('div');
                        div.className = 'no-results';
                        div.innerHTML = `<i class="fa fa-search fa-3x mb-3"></i>
                            <p>No hay registros con «<strong>${escapeHtml(searchTerm)}</strong>»</p>
                            <small class="text-muted">Prueba con otra palabra o revisa la ortografía.</small>`;
                        document.getElementById('categoryTree').appendChild(div);
                    } else {
                        existingNoResults.innerHTML = `<i class="fa fa-search fa-3x mb-3"></i>
                            <p>No hay registros con «<strong>${escapeHtml(searchTerm)}</strong>»</p>
                            <small class="text-muted">Prueba con otra palabra o revisa la ortografía.</small>`;
                        existingNoResults.style.display = '';
                    }
                } else if (existingNoResults) {
                    existingNoResults.style.display = 'none';
                }
            }

            // Evento de búsqueda en tiempo real
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => filterCategories(e.target.value), 300);
            });

            // Botón limpiar búsqueda
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                filterCategories('');
                searchInput.focus();
            });

            // ── Expand / collapse individual ───────────────────────────
            document.querySelectorAll('.expand-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const targetUl = document.getElementById(this.dataset.target);
                    if (targetUl) setUlState(targetUl, targetUl.style.display === 'none');
                });
            });

            document.querySelectorAll('.toggle-status-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const toggleUrl = this.dataset.toggleUrl;
                    const isActive = this.dataset.active === '1';
                    const actionText = isActive ? 'desactivar' : 'activar';

                    Swal.fire({
                        title: 'Confirmar cambio',
                        text: `¿Deseas ${actionText} este registro?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: `Sí, ${actionText}`,
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitPostForm(toggleUrl);
                        }
                    });
                });
            });

            document.querySelectorAll('.delete-category').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryName = this.dataset.name;
                    const deleteUrl = this.dataset.deleteUrl;
                    const cascade = this.dataset.cascade === '1';
                    const subtreeSize = parseInt(this.dataset.subtreeSize || '1', 10) || 1;

                    const simpleText =
                        `¿Eliminar «${categoryName}»? Esta acción no se puede deshacer.`;
                    const cascadeHtml =
                        `<p class="mb-2">Vas a eliminar <strong>«${escapeHtml(categoryName)}»</strong> y <strong>toda su ontología</strong> (sub-registros, nietos, etc.).</p>` +
                        `<p class="mb-0"><strong>${subtreeSize}</strong> registro(s) en total, con sus archivos en Drive y aportaciones vinculadas. <span class="text-danger">No se puede deshacer.</span></p>`;

                    const swalOpts = {
                        title: cascade ? 'Eliminar jerarquía completa' : 'Eliminar registro',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: cascade ? 'Sí, eliminar todo' : 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        reverseButtons: true
                    };
                    if (cascade) {
                        swalOpts.html = cascadeHtml;
                    } else {
                        swalOpts.text = simpleText;
                    }

                    Swal.fire(swalOpts).then((result) => {
                        if (result.isConfirmed) {
                            const extra = cascade ? { cascade_subtree: '1' } : null;
                            submitPostForm(deleteUrl, 'DELETE', extra);
                        }
                    });
                });
            });
        });
    </script>

@endsection
