{{--
    Partial reutilizado en create y edit de categorías.
    Input único de archivos múltiples (como en aportaciones).
    En edit, recibe opcionalmente $existingFiles (colección de CategoryFile).
--}}
@php $existingFiles = $existingFiles ?? collect(); @endphp

<div class="card border rounded-3 mb-3">
    <div class="card-header bg-light py-2">
        <span class="fw-semibold"><i class="fa fa-paperclip me-1"></i> Archivos adjuntos (Google Drive)</span>
        <small class="text-muted ms-2">Puedes subir varios archivos de distintos tipos a la vez</small>
    </div>
    <div class="card-body">

        {{-- ── Archivos existentes ──────────────────────────────── --}}
        @if($existingFiles->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold text-muted small text-uppercase">Archivos actuales</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($existingFiles as $file)
                        @php
                            $color = match($file->tipo) {
                                'pdf'   => 'danger',
                                'doc'   => 'primary',
                                'audio' => 'success',
                                'video' => 'warning',
                                default => 'secondary',
                            };
                            $icon = match($file->tipo) {
                                'pdf'   => 'fa-file-pdf',
                                'doc'   => 'fa-file-word',
                                'audio' => 'fa-music',
                                'video' => 'fa-video',
                                default => 'fa-file',
                            };
                        @endphp
                        <div class="d-flex align-items-center gap-1 border rounded px-2 py-1 bg-white">
                            <i class="fa {{ $icon }} text-{{ $color }}"></i>
                            <span class="small">{{ $file->nombre_display }}</span>
                            <a href="{{ \App\Services\GoogleDriveService::getUrl($file->drive_id) }}"
                               target="_blank" class="btn btn-sm p-0 ms-1 text-{{ $color }}" title="Ver">
                                <i class="fa fa-external-link" style="font-size:.75rem;"></i>
                            </a>
                            <button type="button"
                                class="btn btn-sm p-0 text-danger ml-1 sisipedia-delete-file-btn"
                                title="Eliminar"
                                data-delete-url="{{ route('sisipedia.categories.files.destroy', [$category, $file]) }}"
                                data-csrf="{{ csrf_token() }}">
                                <i class="fa fa-times" style="font-size:.75rem;"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <hr class="my-3">
            </div>
        @endif

        {{-- ── Nuevos archivos ─────────────────────────────────── --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                <i class="fa fa-upload text-primary me-1"></i>Subir archivos
            </label>
            <input type="file" class="form-control @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                   name="archivos[]" multiple
                   accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp3,.wav,.ogg,.mp4,.webm,.mov,.m4v"
                   id="categoryArchivosInput">
            <small class="text-muted">
                <i class="fa fa-info-circle me-1"></i>
                Límites: Imagen 10MB · PDF/Word 100MB · Audio 150MB · Video 300MB
            </small>
            @error('archivos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('archivos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <div id="categoryArchivosPreview" class="mt-2 d-none">
                <div class="d-flex flex-wrap gap-2" id="categoryArchivosPreviewList"></div>
            </div>
        </div>

    </div>
</div>

@if($existingFiles->isNotEmpty())
<script>
(function () {
    document.querySelectorAll('.sisipedia-delete-file-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('¿Eliminar este archivo?')) {
                return;
            }
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = btn.getAttribute('data-delete-url');
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = btn.getAttribute('data-csrf');
            var method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        });
    });
})();
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('categoryArchivosInput');
    if (input) {
        input.addEventListener('change', function() {
            var preview = document.getElementById('categoryArchivosPreview');
            var list = document.getElementById('categoryArchivosPreviewList');
            list.innerHTML = '';
            if (this.files.length > 0) {
                preview.classList.remove('d-none');
                Array.from(this.files).forEach(function(file) {
                    var icon = 'fa-file';
                    var color = 'secondary';
                    if (file.type.startsWith('image/')) { icon = 'fa-image'; color = 'info'; }
                    else if (file.type === 'application/pdf') { icon = 'fa-file-pdf'; color = 'danger'; }
                    else if (file.type.includes('word')) { icon = 'fa-file-word'; color = 'primary'; }
                    else if (file.type.startsWith('audio/')) { icon = 'fa-music'; color = 'success'; }
                    else if (file.type.startsWith('video/')) { icon = 'fa-video'; color = 'warning'; }
                    var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                    var badge = document.createElement('span');
                    badge.className = 'badge bg-' + color + ' bg-opacity-10 text-' + color + ' border border-' + color;
                    badge.innerHTML = '<i class="fa ' + icon + ' me-1"></i>' + file.name + ' <small>(' + sizeMB + 'MB)</small>';
                    list.appendChild(badge);
                });
            } else {
                preview.classList.add('d-none');
            }
        });
    }
});
</script>
