@extends('layouts.app')
@section('titulo', 'Inicio')
@section('contenido')
    <div class="row">
        <div class="col-lg-12">
            <h4 class="text-center mb-4 mt-3">Página principal para Kawsay</h4>
        </div>
        <div class="col-lg-3 p-3">
            <a href="{{ route('kawsaylibros.index') }}">
                <img src="{{ asset('img/kawsay.png') }}" width="100%" loading="lazy">
            </a>
        </div>
        <div class="col-lg-3 p-3">
            <a href="{{ route('admin.index') }}">
                <img src="{{ asset('img/usuarios.webp') }}" width="100%" loading="lazy">
            </a>
        </div>
    </div>
@endsection
