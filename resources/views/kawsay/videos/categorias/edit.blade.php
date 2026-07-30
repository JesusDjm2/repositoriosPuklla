@extends('layouts.app')

@section('titulo', 'Editar Categoría de Video Kawsay')

@section('contenido')
    <div class="row">
        <div class="col-md-12">
            <h3>Editar Categoría
                <a href="{{ route('kawsayvideoscat.index') }}" class="btn btn-danger btn-sm float-right">Volver</a>
            </h3>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('kawsayvideoscat.update', $videoCategoria->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control rounded-pill {{ $errors->has('nombre') ? 'is-invalid' : '' }}" value="{{ old('nombre', $videoCategoria->nombre) }}" required>
                    @if ($errors->has('nombre'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('nombre') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="url">URL:</label>
                    <input type="text" name="url" id="url" class="form-control rounded-pill {{ $errors->has('url') ? 'is-invalid' : '' }}" value="{{ old('url', $videoCategoria->url) }}" readonly>
                    @if ($errors->has('url'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('url') }}</strong>
                        </span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nombreInput = document.getElementById('nombre');
            const urlInput = document.getElementById('url');

            const removeAccents = (str) => {
                const accentsMap = {
                    'á': 'a',
                    'é': 'e',
                    'í': 'i',
                    'ó': 'o',
                    'ú': 'u',
                    'Á': 'A',
                    'É': 'E',
                    'Í': 'I',
                    'Ó': 'O',
                    'Ú': 'U',
                    'ñ': 'n',
                    'Ñ': 'N'
                };
                return str.split('').map(char => accentsMap[char] || char).join('');
            };

            const toSlug = (text) => {
                return removeAccents(text)
                    .toLowerCase()
                    .replace(/&/g, 'y') // Reemplaza &
                    .replace(/[^a-z0-9]+/g, '-') // Reemplaza caracteres no alfanuméricos por "-"
                    .replace(/^-+|-+$/g, ''); // Remueve "-" al principio y al final
            };

            nombreInput.addEventListener('input', function() {
                const nombreValue = nombreInput.value.trim();
                const slugValue = nombreValue ? toSlug(nombreValue) : '';
                urlInput.value = slugValue;
            });
        });
    </script>
@endsection
