{{--
    Selector opcional "Tema del repositorio" para libros y videos del catálogo (panel).
    Con un tema asignado, el libro o video aparece dentro de esa página de tema.
    $temasPorArea: áreas con sus temas · $seleccionado: kawsay_tema_id actual (o null)
--}}
<div class="form-group">
    <label for="kawsay_tema_id">Tema del repositorio <span class="text-muted font-weight-normal">(opcional)</span></label>
    <select name="kawsay_tema_id" id="kawsay_tema_id" class="form-control">
        <option value="">— Ninguno: solo en el catálogo —</option>
        @foreach ($temasPorArea as $area)
            @if ($area->temas->isNotEmpty())
                <optgroup label="{{ $area->nombre }}">
                    @foreach ($area->temas as $tema)
                        <option value="{{ $tema->id }}" @selected((string) old('kawsay_tema_id', $seleccionado) === (string) $tema->id)>
                            {{ $tema->titulo }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
    </select>
    <small class="form-text text-muted">Si eliges un tema, también se mostrará dentro de esa página del repositorio.</small>
</div>
