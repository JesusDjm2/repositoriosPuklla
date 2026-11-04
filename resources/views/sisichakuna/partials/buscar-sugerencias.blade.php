{{--
    Sugerencias mientras se escribe para cualquier <input data-sisi-suggest> de Sisipedia
    (hero, portada, resultados, árbol). Pide sisipedia.buscar.sugerencias con debounce y
    cancela la petición anterior; teclado: ↑ ↓ Enter Esc. Si el visitante no elige una
    sugerencia, el formulario se envía normal a la página de resultados.
--}}
@once
<style>
    .sisi-suggest-host { position: relative; }
    .sisi-suggest {
        position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 1050;
        background: #fff; border-radius: .85rem; overflow: hidden; text-align: left;
        box-shadow: 0 12px 32px rgba(16,24,40,.22); max-height: min(70vh, 420px); overflow-y: auto;
    }
    .sisi-suggest[hidden] { display: none; }
    .sisi-suggest__group { padding: .55rem .9rem .25rem; font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #868e96; }
    .sisi-suggest__opt {
        display: flex; align-items: center; gap: .65rem; padding: .55rem .9rem; color: #212529; text-decoration: none; cursor: pointer;
    }
    .sisi-suggest__opt:hover, .sisi-suggest__opt.is-active { background: #f1f5ff; color: #212529; }
    .sisi-suggest__opt i { flex-shrink: 0; width: 1.1rem; text-align: center; }
    .sisi-suggest__txt { min-width: 0; display: flex; flex-direction: column; }
    .sisi-suggest__name { font-weight: 600; font-size: .9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sisi-suggest__path { font-size: .72rem; color: #868e96; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sisi-suggest__all { display: block; padding: .65rem .9rem; border-top: 1px solid #e9ecef; font-weight: 600; font-size: .85rem; color: #0d6efd; cursor: pointer; }
    .sisi-suggest__all:hover, .sisi-suggest__all.is-active { background: #f1f5ff; }
    .sisi-suggest mark { background: #fff3bf; color: inherit; padding: 0; }
</style>
<script>
    (function () {
        var URL_SUG = @json(route('sisipedia.buscar.sugerencias'));
        var ICONOS = { pdf: 'fa-file-pdf text-danger', doc: 'fa-file-word text-primary', audio: 'fa-music text-success', video: 'fa-video text-warning', imagen: 'fa-image text-info' };

        function esc(s) {
            return String(s || '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }
        // Resalta sin importar tildes: busca en el texto normalizado y marca el tramo original.
        function marcar(texto, q) {
            var t = String(texto || ''), n = norm(t), k = norm(q).trim();
            var i = k ? n.indexOf(k) : -1;
            if (i < 0) return esc(t);
            return esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + k.length)) + '</mark>' + esc(t.slice(i + k.length));
        }

        function setup(input) {
            if (input.dataset.suggestReady) return;
            input.dataset.suggestReady = '1';
            var form = input.form;
            if (!form) return;
            form.classList.add('sisi-suggest-host');
            input.setAttribute('role', 'combobox');
            input.setAttribute('aria-autocomplete', 'list');
            input.setAttribute('aria-expanded', 'false');

            var box = document.createElement('div');
            box.className = 'sisi-suggest';
            box.id = 'sisi-suggest-' + Math.random().toString(36).slice(2, 8);
            box.setAttribute('role', 'listbox');
            box.hidden = true;
            form.appendChild(box);
            input.setAttribute('aria-controls', box.id);

            var timer = null, ctrl = null, cache = {}, activo = -1, ultimo = '';

            function opciones() { return Array.from(box.querySelectorAll('[data-opt]')); }
            function cerrar() { box.hidden = true; input.setAttribute('aria-expanded', 'false'); activo = -1; }
            function marcarActivo(i) {
                var ops = opciones();
                ops.forEach(function (o, j) { o.classList.toggle('is-active', j === i); o.setAttribute('aria-selected', j === i ? 'true' : 'false'); });
                activo = i;
                if (ops[i]) ops[i].scrollIntoView({ block: 'nearest' });
            }

            function pintar(data, q) {
                var html = '';
                if (data.registros.length) {
                    html += '<div class="sisi-suggest__group">Temas</div>';
                    data.registros.forEach(function (r) {
                        html += '<a class="sisi-suggest__opt" role="option" data-opt href="' + esc(r.url) + '">' +
                            '<i class="fa fa-folder text-primary" aria-hidden="true"></i><span class="sisi-suggest__txt">' +
                            '<span class="sisi-suggest__name">' + marcar(r.nombre, q) + '</span>' +
                            (r.ruta ? '<span class="sisi-suggest__path">' + esc(r.ruta) + '</span>' : '') + '</span></a>';
                    });
                }
                if (data.materiales.length) {
                    html += '<div class="sisi-suggest__group">Materiales</div>';
                    data.materiales.forEach(function (m) {
                        html += '<a class="sisi-suggest__opt" role="option" data-opt href="' + esc(m.url) + '">' +
                            '<i class="fa ' + (ICONOS[m.tipo] || 'fa-file') + '" aria-hidden="true"></i><span class="sisi-suggest__txt">' +
                            '<span class="sisi-suggest__name">' + marcar(m.nombre, q) + '</span>' +
                            '<span class="sisi-suggest__path">' + esc(m.ruta) + '</span></span></a>';
                    });
                }
                html += '<a class="sisi-suggest__all" role="option" data-opt data-all href="#">' +
                    '<i class="fa fa-search me-1" aria-hidden="true"></i>Ver todos los resultados para «' + esc(q) + '»</a>';
                box.innerHTML = html;
                box.hidden = false;
                input.setAttribute('aria-expanded', 'true');
                activo = -1;
            }

            function pedir() {
                var q = input.value.trim();
                if (q.length < 2) { cerrar(); return; }
                if (q === ultimo && !box.hidden) return;
                ultimo = q;
                if (cache[q]) { pintar(cache[q], q); return; }
                if (ctrl) ctrl.abort();
                ctrl = 'AbortController' in window ? new AbortController() : null;
                fetch(URL_SUG + '?q=' + encodeURIComponent(q), { signal: ctrl ? ctrl.signal : undefined, headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data || input.value.trim() !== q) return;
                        cache[q] = data;
                        pintar(data, q);
                    })
                    .catch(function () {});
            }

            input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(pedir, 220); });
            input.addEventListener('focus', function () { if (input.value.trim().length >= 2) pedir(); });
            input.addEventListener('keydown', function (e) {
                var ops = opciones();
                if (box.hidden || !ops.length) return;
                if (e.key === 'ArrowDown') { e.preventDefault(); marcarActivo((activo + 1) % ops.length); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); marcarActivo(activo <= 0 ? ops.length - 1 : activo - 1); }
                else if (e.key === 'Escape') { cerrar(); }
                else if (e.key === 'Enter' && activo >= 0) { e.preventDefault(); ops[activo].click(); }
            });
            box.addEventListener('click', function (e) {
                var all = e.target.closest('[data-all]');
                if (all) { e.preventDefault(); cerrar(); form.requestSubmit ? form.requestSubmit() : form.submit(); }
            });
            // mousedown antes del blur: que tocar una opción no la cierre primero.
            box.addEventListener('mousedown', function (e) { e.preventDefault(); });
            input.addEventListener('blur', function () { setTimeout(cerrar, 150); });
            form.addEventListener('submit', cerrar);
        }

        function init() { document.querySelectorAll('input[data-sisi-suggest]').forEach(setup); }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
</script>
@endonce
