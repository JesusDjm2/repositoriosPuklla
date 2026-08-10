@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'options' => [],
    'selected' => null,
    'rows' => 3,
    'required' => false,
    'placeholder' => null,
])

@php
    $clases = 'form-control' . ($errors->has($name) ? ' is-invalid' : '');
@endphp

<div class="admin-form-field">
    <label for="{{ $name }}">{{ $label }}{{ $required ? ' *' : '' }}</label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => $clases]) }} @required($required)>{{ $value }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => $clases]) }}
            @required($required)>
            <option value="">Selecciona...</option>
            @foreach ($options as $valorOpcion => $etiquetaOpcion)
                <option value="{{ $valorOpcion }}" @selected((string) $selected === (string) $valorOpcion)>
                    {{ $etiquetaOpcion }}
                </option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
            placeholder="{{ $placeholder }}" {{ $attributes->merge(['class' => $clases]) }} @required($required)>
    @endif

    @error($name)
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>
