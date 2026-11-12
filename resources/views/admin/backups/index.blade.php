@extends('layouts.app')
@section('titulo', '- Backups')


@section('contenido')

    <x-hero-banner titulo="Backups" subtitulo="Genera y descarga copias de seguridad de la base de datos" icono="fa-database">
        <x-slot:stats>
            <x-hero-stat icono="fa-database" :valor="count($backups)" etiqueta="Backups guardados" :animar="false" />
        </x-slot:stats>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="bv-form-card d-flex justify-content-between align-items-center">
        <div>
            <div class="bv-form-section-title mb-1">Generar un nuevo backup</div>
            <small class="text-muted">Crea un dump completo de la base de datos. Puede tardar unos segundos.</small>
        </div>
        <form action="{{ route('backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-plus"></i> Generar backup ahora
            </button>
        </form>
    </div>

    <div class="bv-crud-table-wrap">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Archivo</th>
                    <th>Tamaño</th>
                    <th>Fecha</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($backups as $backup)
                    <tr class="bv-table-row">
                        <td><i class="fa fa-file-text-o"></i> {{ $backup['nombre'] }}</td>
                        <td>{{ $backup['tamano'] }}</td>
                        <td>{{ $backup['fecha']->format('d/m/Y H:i') }}</td>
                        <td class="text-right bv-crud-actions">
                            <a href="{{ route('backups.download', $backup['nombre']) }}" class="btn btn-sm btn-outline-secondary" title="Descargar">
                                <i class="fa fa-download"></i>
                            </a>
                            <form action="{{ route('backups.destroy', $backup['nombre']) }}" method="POST" style="display:inline"
                                onsubmit="return confirm('¿Eliminar este backup?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="bv-empty-state">
                            <i class="fa fa-database bv-empty-icon"></i>
                            Todavía no se ha generado ningún backup.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
