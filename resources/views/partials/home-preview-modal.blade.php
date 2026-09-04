{{--
    Modal compartido para previsualizar imágenes y PDF en cualquier vista
    pública de Sisipedia (portada, árbol/galería, detalle de registro). Un
    único modal reutilizado por todas las tarjetas vía
    window.sisipediaOpenPreview(previewUrl, nombre, driveUrl, downloadUrl).
    driveUrl/downloadUrl son opcionales: si no se pasan, esos botones se
    ocultan (p. ej. para contenido sin archivo de Drive detrás).
--}}
<div class="modal fade" id="sisipediaHomePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-truncate mb-0" id="sisipediaHomePreviewModalLabel">
                    <span id="sisipediaHomePreviewModalName"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="sisipediaHomePreviewModalIframe" src="" class="w-100 border-0" style="min-height:70vh;"
                    allow="autoplay" allowfullscreen loading="lazy"></iframe>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <a id="sisipediaHomePreviewModalDownload" href="" class="btn btn-outline-secondary btn-sm d-none">
                    <i class="fa fa-download me-1"></i>Descargar
                </a>
                <a id="sisipediaHomePreviewModalOpen" href="" target="_blank" rel="noopener"
                    class="btn btn-danger btn-sm d-none">
                    <i class="fa fa-external-link-alt me-1"></i>Ver en una hoja nueva
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('sisipediaHomePreviewModal');
        if (!modal) return;
        var iframe = document.getElementById('sisipediaHomePreviewModalIframe');
        var nameSpan = document.getElementById('sisipediaHomePreviewModalName');
        var downloadLink = document.getElementById('sisipediaHomePreviewModalDownload');
        var openLink = document.getElementById('sisipediaHomePreviewModalOpen');

        function toggleLink(link, url) {
            if (url) {
                link.href = url;
                link.classList.remove('d-none');
            } else {
                link.href = '';
                link.classList.add('d-none');
            }
        }

        window.sisipediaOpenPreview = function(previewUrl, nombre, driveUrl, downloadUrl) {
            if (typeof bootstrap === 'undefined') return;
            iframe.src = previewUrl;
            nameSpan.textContent = nombre || '';
            toggleLink(downloadLink, downloadUrl);
            toggleLink(openLink, driveUrl);
            new bootstrap.Modal(modal).show();
        };

        modal.addEventListener('hidden.bs.modal', function() {
            iframe.src = '';
        });

        // Cualquier botón con esta clase (en cualquier vista que incluya este
        // parcial) abre el popup sin necesitar su propio script — solo
        // necesita data-preview/-nombre/-drive/-download.
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.sisipedia-open-preview-modal');
            if (!btn) return;
            e.preventDefault();
            window.sisipediaOpenPreview(
                btn.getAttribute('data-preview'),
                btn.getAttribute('data-nombre'),
                btn.getAttribute('data-drive'),
                btn.getAttribute('data-download')
            );
        });
    })();
</script>
