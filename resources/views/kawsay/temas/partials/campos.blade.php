{{-- Campos compartidos de crear/editar Tema. $tema es null al crear. --}}
@php
    $areaSeleccionada = (int) old('kawsay_area_id', $tema->kawsay_area_id ?? request('area'));
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="bv-form-section-title">Datos del tema</div>

        <div class="form-row">
            <div class="col-md-5 form-group">
                <label class="bv-form-label" for="tema-area">Área <span class="text-danger">*</span></label>
                <select id="tema-area" name="kawsay_area_id" class="form-control form-control-sm" required>
                    @unless ($tema)
                        <option value="">Seleccionar área</option>
                    @endunless
                    @foreach ($areas as $area)
                        <option value="{{ $area->id }}" @selected($areaSeleccionada === $area->id)>{{ $area->nombre }}</option>
                    @endforeach
                </select>
                @error('kawsay_area_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-7 form-group">
                <label class="bv-form-label" for="tema-titulo">Título <span class="text-danger">*</span></label>
                <input id="tema-titulo" type="text" name="titulo" class="form-control form-control-sm" required maxlength="255"
                    value="{{ old('titulo', $tema->titulo ?? '') }}">
                @error('titulo')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="bv-form-label" for="tema-descripcion">Descripción</label>
            <textarea id="tema-descripcion" name="descripcion" class="form-control form-control-sm" rows="6" style="height:auto;min-height:9rem;resize:vertical"
                placeholder="Cuéntale al visitante qué recursos va a encontrar para aprender, aplicar y compartir sobre este tema.">{{ old('descripcion', $tema->descripcion ?? '') }}</textarea>
            @error('descripcion')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="bv-form-section-title">Portada</div>

        <div class="kawsay-portada">
            <div class="kawsay-portada__preview" id="portadaPreview">
                @if ($tema && $tema->imagen)
                    <img src="{{ asset($tema->imagen) }}" alt="{{ $tema->titulo }}">
                @else
                    <i class="fa fa-image" aria-hidden="true"></i>
                @endif
            </div>

            <div class="kawsay-portada__campos">
                <input type="file" name="imagen" id="portadaInput" class="kawsay-portada__input" accept="image/*">
                <label for="portadaInput" class="btn btn-sm btn-outline-secondary mb-0">
                    <i class="fa fa-upload"></i> {{ $tema && $tema->imagen ? 'Cambiar imagen' : 'Elegir imagen' }}
                </label>
                <span class="kawsay-portada__nombre" id="portadaNombre"></span>
                <small class="text-muted d-block mt-2">
                    JPG, PNG o WebP, hasta 2 MB. Mejor horizontal (mín. 1200×800). Se muestra como foto principal del tema, en su tarjeta del área y al compartir el enlace.
                </small>
                @error('imagen')<div class="text-danger small">{{ $message }}</div>@enderror

                @if ($tema && $tema->imagen)
                    <label class="small mt-2 mb-0 d-block">
                        <input type="checkbox" name="remove_imagen" value="1" id="portadaQuitar"> Quitar imagen actual
                    </label>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .kawsay-portada { display: flex; flex-direction: column; gap: .9rem; }
    .kawsay-portada__preview {
        width: 100%; max-width: 320px; aspect-ratio: 4 / 3; border-radius: 12px;
        overflow: hidden; background: #f0ebe4; color: #b8a898;
        display: flex; align-items: center; justify-content: center; font-size: 2.2rem;
        border: 2px solid #e5ddd2; transition: opacity .15s ease;
    }
    .kawsay-portada__preview img { width: 100%; height: 100%; object-fit: cover; }
    .kawsay-portada__preview.is-quitada { opacity: .3; }
    .kawsay-portada__campos { min-width: 0; }
    .kawsay-portada__input { position: absolute; width: 1px; height: 1px; opacity: 0; overflow: hidden; }
    .kawsay-portada__input:focus-visible + label { outline: 2px solid #6e7d1e; outline-offset: 2px; }
    .kawsay-portada__nombre { display: block; margin-top: .35rem; font-size: .75rem; color: #6c757d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<script>
    (function () {
        var input = document.getElementById('portadaInput');
        var preview = document.getElementById('portadaPreview');
        var quitar = document.getElementById('portadaQuitar');
        var nombre = document.getElementById('portadaNombre');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            if (nombre) nombre.textContent = file.name;
            var img = preview.querySelector('img');
            if (!img) {
                preview.innerHTML = '';
                img = document.createElement('img');
                preview.appendChild(img);
            }
            img.src = URL.createObjectURL(file);
            preview.classList.remove('is-quitada');
            if (quitar) quitar.checked = false;
        });

        if (quitar) {
            quitar.addEventListener('change', function () {
                preview.classList.toggle('is-quitada', quitar.checked);
            });
        }
    })();
</script>
