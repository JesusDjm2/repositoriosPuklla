<section id="home-aporte-standalone" class="mt-5 pt-4 border-top sisipedia-aporte-section">
    <div class="row justify-content-center mb-4">
        <div class="col-lg-12 text-center text-lg-start">
            <span class="sisipedia-aporte-eyebrow"><i class="fa fa-hands-helping me-1"></i>Comunidad Sisichakunaq</span>
            <h2 class="titulosDos mb-2">Comparte tu experiencia</h2>
            <p class="text-muted mb-0">
                ¿Tienes fotos, audios, videos o documentos de tu comunidad, tu escuela o tu trabajo con Pukllasunchis?
                Compártelos aquí y ayúdanos a hacer crecer este archivo entre todos.
            </p>
        </div>
    </div>

    @if (session('aporte_success'))
        <div class="row justify-content-center mb-4">
            <div class="col-lg-12">
                <div class="alert alert-success border-0 shadow-sm rounded-3">
                    <i class="fa fa-check-circle me-2"></i>{{ session('aporte_success') }}
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row justify-content-center mb-4">
            <div class="col-lg-12">
                <div class="alert alert-danger border-0 shadow-sm rounded-3">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sisipedia-aporte-card">
                <div class="sisipedia-aporte-card-accent" aria-hidden="true"></div>
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                           {{--  <span class="sisipedia-aporte-icon">
                                <i class="fa fa-plus"></i>
                            </span> --}}
                            <div>
                                <h3 class="h5 mb-0">Agregar aportación</h3>
                                <small class="text-muted">Solo toma un par de minutos</small>
                            </div>
                        </div>
                        <button type="button" class="btn px-4 py-2 fw-semibold shadow-sm rounded-pill text-white sisipedia-aporte-cta mb-3"
                            id="btnHomeAbrirRoles" onclick="homeMostrarRoles()">
                            Quiero aportar <i class="fa fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <div id="homeRolSelector" class="px-4 pt-3 pb-2 d-none">
                    <p class="fw-semibold mb-3 text-center">¿Cómo deseas aportar?</p>
                    <div class="row row-cols-2 row-cols-lg-4 g-3 justify-content-center mb-3 home-rol-grid">
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#6f42c1!important;"
                                onclick="homeSeleccionarRol('Equipo Puklla')">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2"
                                    style="width:52px;height:52px;background:#6f42c1;">
                                    <i class="fa fa-users fa-lg"></i>
                                </span>
                                <div class="fw-bold small">Equipo Puklla</div>
                                <small class="text-muted" style="font-size:.7rem;">Institucional</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#0d6efd!important;"
                                onclick="homeSeleccionarRol('Docente')">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-2"
                                    style="width:52px;height:52px;">
                                    <i class="fa fa-chalkboard-teacher"></i>
                                </span>
                                <div class="fw-bold small">Docente</div>
                                <small class="text-muted" style="font-size:.7rem;">Educador</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#198754!important;"
                                onclick="homeSeleccionarRol('Líder')">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-2"
                                    style="width:52px;height:52px;">
                                    <i class="fa fa-star"></i>
                                </span>
                                <div class="fw-bold small">{{ \App\Models\sisipedia\Aportacion::ROL_LIDER_ETIQUETA }}
                                </div>
                                <small class="text-muted" style="font-size:.7rem;">Comunidad</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card border-2 rounded-4 text-center p-3 h-100 home-rol-card"
                                style="cursor:pointer;border-color:#fd7e14!important;"
                                onclick="homeSeleccionarRol('Niño/Estudiante')">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2"
                                    style="width:52px;height:52px;background:#fd7e14;">
                                    <i class="fa fa-child"></i>
                                </span>
                                <div class="fw-bold small">Niño / Estudiante</div>
                                <small class="text-muted" style="font-size:.7rem;">Estudiante</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="homeCancelarAportacion()">
                            <i class="fa fa-times me-1"></i>Cancelar
                        </button>
                    </div>
                </div>

                <div id="homeFormAportacionWrap" class="px-4 pb-4 pt-2 d-none">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge fs-6 px-3 py-2 text-white" id="homeRolBadge" style="background:#0d6efd;">
                            <i class="fa fa-user me-1"></i><span id="homeRolTexto"></span>
                        </span>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="homeVolverARoles()">
                            <i class="fa fa-arrow-left me-1"></i>Cambiar rol
                        </button>
                    </div>

                    <form action="{{ route('sisipedia.aportaciones.general.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="rol_nombre" id="homeInputRolNombre" value="{{ old('rol_nombre') }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nombre completo:<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nombre_ol" required
                                    class="form-control @error('nombre_ol') is-invalid @enderror"
                                    value="{{ old('nombre_ol') }}">
                                @error('nombre_ol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Institución</label>
                                <input type="text" name="institucion"
                                    class="form-control @error('institucion') is-invalid @enderror"
                                    value="{{ old('institucion') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <input type="text" name="ubicacion"
                                    class="form-control @error('ubicacion') is-invalid @enderror"
                                    value="{{ old('ubicacion') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Título</label>
                                <input type="text" name="titulo"
                                    class="form-control @error('titulo') is-invalid @enderror"
                                    value="{{ old('titulo') }}">
                                @error('titulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lugar de trabajo</label>
                                <input type="text" name="lugar_trabajo"
                                    class="form-control @error('lugar_trabajo') is-invalid @enderror"
                                    value="{{ old('lugar_trabajo') }}">
                                @error('lugar_trabajo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <textarea name="detalle" rows="3"
                                    class="form-control summernote-editor @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Archivos <span
                                        class="text-muted fw-normal">(puedes subir varios)</span></label>
                                <div class="sisipedia-dropzone @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                                    id="homeDropzone">
                                    <i class="fa fa-cloud-upload-alt sisipedia-dropzone-icon" aria-hidden="true"></i>
                                    <p class="mb-1 fw-semibold">Arrastra tus archivos aquí</p>
                                    <p class="mb-0 small text-muted">o haz clic para elegirlos desde tu dispositivo</p>
                                    <input type="file" name="archivos[]" multiple
                                        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp3,.wav,.ogg,.mp4,.webm,.mov,.m4v"
                                        class="sisipedia-dropzone-input" id="homeArchivosInput"
                                        aria-label="Seleccionar archivos para tu aportación">
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Límites: Imagen 10MB · PDF/Word 100MB · Audio 150MB · Video 300MB
                                </small>
                                @error('archivos')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('archivos.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div id="homeArchivosPreview" class="mt-2 d-none">
                                    <div class="d-flex flex-wrap gap-2" id="homeArchivosPreviewList"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn text-white fw-semibold sisipedia-aporte-cta"><i
                                    class="fa fa-paper-plane me-1"></i>Enviar aporte</button>
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="homeCancelarAportacion()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @isset($ultimosAportes)
        @if ($ultimosAportes->isNotEmpty())
            <div class="row justify-content-center mt-4 pt-3">
                <div class="col-lg-12">
                    <h3 class="h6 fw-bold text-secondary mb-3">
                        <i class="fa-solid fa-clock me-2 text-primary"></i>Últimas aportaciones
                    </h3>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                        @foreach ($ultimosAportes as $uap)
                            @php
                                $uColor = match ($uap->rol_nombre) {
                                    'Equipo Puklla' => 'info',
                                    'Docente' => 'primary',
                                    'Líder' => 'success',
                                    'Niño/Estudiante' => 'warning',
                                    default => 'secondary',
                                };
                                $uCat = $uap->category;
                            @endphp
                            <div class="col">
                                <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column"
                                    style="border-top: 3px solid var(--bs-{{ $uColor }}) !important;">
                                    <div class="card-body p-3 flex-grow-1">
                                        {{-- Nombre --}}
                                        <div class="fw-bold small text-dark mb-1 text-truncate"
                                            title="{{ $uap->nombre_ol }}">
                                            {{ $uap->nombre_ol }}
                                        </div>
                                        {{-- Rol --}}
                                        <span
                                            class="badge bg-{{ $uColor }} bg-opacity-10 text-{{ $uColor }} border border-{{ $uColor }} mb-2"
                                            style="font-size:.62rem;">
                                            {{ \App\Models\sisipedia\Aportacion::etiquetaRol($uap->rol_nombre) }}
                                        </span>
                                        {{-- Miga de pan del registro --}}
                                        @if ($uCat)
                                            @if ($uCat->parent)
                                                <div class="text-muted mb-1" style="font-size:.68rem;line-height:1.3;">
                                                    @if ($uCat->parent->parent)
                                                        {{ $uCat->parent->parent->display_name }} ›
                                                    @endif
                                                    {{ $uCat->parent->display_name }} ›
                                                </div>
                                            @endif
                                            <div class="fw-semibold small text-dark text-truncate mb-1"
                                                title="{{ $uCat->display_name }}">
                                                <i
                                                    class="fa fa-folder-open me-1 text-{{ $uColor }}"></i>{{ $uCat->display_name }}
                                            </div>
                                        @endif
                                        {{-- Fecha --}}
                                        <div class="text-muted mt-1" style="font-size:.68rem;">
                                            {{ $uap->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    {{-- Botón de acción --}}
                                    @if ($uCat)
                                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                                            <a href="{{ route('sisipedia.categories.show', $uCat) }}"
                                                class="btn btn-{{ $uColor }} btn-sm w-100 fw-semibold">
                                                <i class="fa fa-arrow-right me-1"></i>Ver registro
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endisset
</section>

<script>
    (function() {
        const homeRolColors = {
            'Equipo Puklla': '#6f42c1',
            'Docente': '#0d6efd',
            'Líder': '#198754',
            'Niño/Estudiante': '#fd7e14',
        };
        const homeRolTextoMostrar = {
            'Equipo Puklla': 'Equipo Puklla',
            'Docente': 'Docente',
            'Líder': @json(\App\Models\sisipedia\Aportacion::ROL_LIDER_ETIQUETA),
            'Niño/Estudiante': 'Niño/Estudiante',
        };

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function ensureGsap(callback) {
            if (typeof gsap !== 'undefined') {
                callback();
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
            script.onload = callback;
            document.head.appendChild(script);
        }

        function animateReveal(target, opts) {
            if (prefersReducedMotion) return;
            ensureGsap(function() {
                gsap.from(target, Object.assign({
                    opacity: 0,
                    y: 14,
                    duration: .45,
                    stagger: .07,
                    ease: 'power2.out'
                }, opts || {}));
            });
        }

        window.homeMostrarRoles = function() {
            document.getElementById('homeRolSelector').classList.remove('d-none');
            document.getElementById('homeFormAportacionWrap').classList.add('d-none');
            document.getElementById('btnHomeAbrirRoles').classList.add('d-none');
            animateReveal('#homeRolSelector .home-rol-grid > .col', {
                y: 18,
                stagger: .06
            });
        };

        window.homeSeleccionarRol = function(rol) {
            document.getElementById('homeInputRolNombre').value = rol;
            document.getElementById('homeRolTexto').textContent = homeRolTextoMostrar[rol] ?? rol;
            document.getElementById('homeRolBadge').style.background = homeRolColors[rol] ?? '#6c757d';
            document.getElementById('homeRolSelector').classList.add('d-none');
            document.getElementById('homeFormAportacionWrap').classList.remove('d-none');
            animateReveal('#homeFormAportacionWrap', {
                y: 10,
                duration: .4,
                stagger: 0
            });
        };

        window.homeVolverARoles = function() {
            document.getElementById('homeFormAportacionWrap').classList.add('d-none');
            document.getElementById('homeRolSelector').classList.remove('d-none');
        };

        window.homeCancelarAportacion = function() {
            document.getElementById('homeRolSelector').classList.add('d-none');
            document.getElementById('homeFormAportacionWrap').classList.add('d-none');
            const b = document.getElementById('btnHomeAbrirRoles');
            if (b) b.classList.remove('d-none');
        };

        @if ($errors->any() && old('rol_nombre'))
            document.addEventListener('DOMContentLoaded', function() {
                homeMostrarRoles();
                homeSeleccionarRol(@json(old('rol_nombre')));
            });
        @endif

        // Previsualización de archivos seleccionados
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('homeArchivosInput');
            if (input) {
                input.addEventListener('change', function() {
                    var preview = document.getElementById('homeArchivosPreview');
                    var list = document.getElementById('homeArchivosPreviewList');
                    list.innerHTML = '';
                    if (this.files.length > 0) {
                        preview.classList.remove('d-none');
                        Array.from(this.files).forEach(function(file) {
                            var icon = 'fa-file';
                            var color = 'secondary';
                            if (file.type.startsWith('image/')) {
                                icon = 'fa-image';
                                color = 'info';
                            } else if (file.type === 'application/pdf') {
                                icon = 'fa-file-pdf';
                                color = 'danger';
                            } else if (file.type.includes('word')) {
                                icon = 'fa-file-word';
                                color = 'primary';
                            } else if (file.type.startsWith('audio/')) {
                                icon = 'fa-music';
                                color = 'success';
                            } else if (file.type.startsWith('video/')) {
                                icon = 'fa-video';
                                color = 'warning';
                            }
                            var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                            var badge = document.createElement('span');
                            badge.className = 'badge bg-' + color + ' bg-opacity-10 text-' +
                                color + ' border border-' + color;
                            badge.innerHTML = '<i class="fa ' + icon + ' me-1"></i>' + file
                                .name + ' <small>(' + sizeMB + 'MB)</small>';
                            list.appendChild(badge);
                        });
                    } else {
                        preview.classList.add('d-none');
                    }
                });
            }
        });

        // Función para inicializar Summernote
        function initSummernote() {
            if (window.jQuery && $('.summernote-editor').length && !$('.summernote-editor').hasClass(
                    'summernote-initialized')) {
                $('.summernote-editor').summernote({
                    height: 120,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview']]
                    ],
                    placeholder: 'Describe los detalles de esta aportación...',
                    callbacks: {
                        onInit: function() {
                            $('.note-editable').css('font-size', '14px');
                            $('.summernote-editor').addClass('summernote-initialized');
                        }
                    }
                });
            }
        }

        // Inicializar Summernote cuando se muestra el formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Intentar inicializar por si el formulario ya está visible
            initSummernote();

            // También inicializar cuando se selecciona un rol
            const originalSeleccionarRol = window.homeSeleccionarRol;
            window.homeSeleccionarRol = function(rol) {
                originalSeleccionarRol(rol);
                setTimeout(initSummernote, 100);
            };
        });

        // Resalte visual al arrastrar archivos sobre la zona de carga
        document.addEventListener('DOMContentLoaded', function() {
            var dropzone = document.getElementById('homeDropzone');
            var input = document.getElementById('homeArchivosInput');
            if (!dropzone || !input) return;

            ['dragenter', 'dragover'].forEach(function(evt) {
                input.addEventListener(evt, function() {
                    dropzone.classList.add('is-dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function(evt) {
                input.addEventListener(evt, function() {
                    dropzone.classList.remove('is-dragover');
                });
            });
        });

        // Entrada animada de la sección completa al llegar al scroll
        document.addEventListener('DOMContentLoaded', function() {
            var section = document.getElementById('home-aporte-standalone');
            if (!section || prefersReducedMotion || !('IntersectionObserver' in window)) return;

            var targets = section.querySelectorAll(
                '.sisipedia-aporte-eyebrow, .titulosDos, .sisipedia-aporte-card');
            if (!targets.length) return;

            var observer = new IntersectionObserver(function(entries, obs) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        animateReveal(targets, {
                            y: 20,
                            stagger: .1
                        });
                        obs.disconnect();
                    }
                });
            }, {
                threshold: .15
            });
            observer.observe(section);
        });
    })();
</script>
