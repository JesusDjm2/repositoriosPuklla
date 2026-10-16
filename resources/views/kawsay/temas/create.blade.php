@extends('layouts.app')
@section('titulo', '- Nuevo tema Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Nuevo tema" subtitulo="Se mostrará dentro del área que elijas" icono="fa-map-signs" color="kawsay" />

    <div class="bv-form-card">
        <form action="{{ route('kawsaytemas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('kawsay.temas.partials.campos', ['tema' => null, 'areas' => $areas])

            <div class="bv-form-actions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> Guardar tema
                </button>
                <a href="{{ route('kawsaytemas.index') }}" class="btn btn-sm btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
