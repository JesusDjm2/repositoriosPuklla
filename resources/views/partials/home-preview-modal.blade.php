{{--
    Modal compartido para previsualizar imágenes y PDF desde las tarjetas de
    "últimas publicaciones" de la portada. Un único modal reutilizado por todas
    las tarjetas vía window.sisipediaOpenPreview(url, nombre).
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
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('sisipediaHomePreviewModal');
        if (!modal) return;
        var iframe = document.getElementById('sisipediaHomePreviewModalIframe');
        var nameSpan = document.getElementById('sisipediaHomePreviewModalName');

        window.sisipediaOpenPreview = function(url, nombre) {
            if (typeof bootstrap === 'undefined') return;
            iframe.src = url;
            nameSpan.textContent = nombre || '';
            new bootstrap.Modal(modal).show();
        };

        modal.addEventListener('hidden.bs.modal', function() {
            iframe.src = '';
        });
    })();
</script>
