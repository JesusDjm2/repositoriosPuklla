@extends('layouts.app')
@section('titulo', '- Áreas Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Áreas Kawsay" subtitulo="Las 4 áreas que organizan todo el repositorio educativo — el punto de partida del que cuelgan los temas"
        icono="fa-th-large" color="kawsay">
        <x-slot:stats>
            <x-hero-stat icono="fa-th-large" :valor="$areas->count()" etiqueta="Áreas" />
            <x-hero-stat icono="fa-map-signs" :valor="$areas->sum('temas_count')" etiqueta="Temas en total" />
        </x-slot:stats>
        <x-slot:actions>
            <a href="{{ route('kawsayareas.create') }}" class="btn btn-sm btn-light">
                <i class="fa fa-plus"></i> Nueva área
            </a>
        </x-slot:actions>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @error('area')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    @forelse ($areas as $area)
        <div class="kawsay-area-card">
            <div class="kawsay-area-card__head">
                <span class="kawsay-area-card__circle" style="border-color: {{ $area->color ?: '#6e7d1e' }};">
                    @if ($area->imagen)
                        <img src="{{ asset($area->imagen) }}" alt="{{ $area->nombre }}">
                    @else
                        <i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i>
                    @endif
                </span>
                <div class="kawsay-area-card__info">
                    <div class="kawsay-area-card__title">
                        {{ $area->nombre }}
                        <span class="kawsay-area-card__orden" title="Orden">#{{ $area->orden }}</span>
                    </div>
                    @if ($area->descripcion)
                        <p class="kawsay-area-card__desc">{{ $area->descripcion }}</p>
                    @endif
                </div>
                <div class="kawsay-area-card__actions">
                    <a href="{{ route('kawsay.area', $area->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver en el sitio">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('kawsayareas.edit', $area) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <form action="{{ route('kawsayareas.destroy', $area) }}" method="POST" style="display:inline"
                        data-confirm-delete="¿Eliminar &quot;{{ $area->nombre }}&quot;?"
                        data-confirm-delete-text="Solo se puede si no tiene temas asociados.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="kawsay-area-card__temas">
                @forelse ($area->temas as $tema)
                    <a href="{{ route('kawsaytemas.edit', $tema) }}" class="kawsay-area-card__tema">
                        <i class="fa {{ $tema->icono ?: 'fa-map-signs' }}"></i> {{ $tema->titulo }}
                    </a>
                @empty
                    <span class="kawsay-area-card__vacio">Todavía no tiene temas.</span>
                @endforelse
                <a href="{{ route('kawsaytemas.create', ['area' => $area->id]) }}" class="kawsay-area-card__tema kawsay-area-card__tema--nuevo">
                    <i class="fa fa-plus"></i> Agregar tema
                </a>
            </div>
        </div>
    @empty
        <div class="bv-crud-table-wrap">
            <div class="bv-empty-state">
                <i class="fa fa-th-large bv-empty-icon"></i>
                Todavía no hay áreas creadas.
            </div>
        </div>
    @endforelse

    <style>
        .kawsay-area-card {
            background: #fff;
            border: 1px solid rgba(20, 20, 40, 0.06);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(20, 20, 40, 0.04), 0 0.5rem 1.25rem rgba(20, 20, 40, 0.06);
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.25rem;
        }
        .kawsay-area-card__head {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .kawsay-area-card__circle {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 3px solid;
            overflow: hidden;
            background: #f1f3f5;
            color: #6c757d;
            font-size: 1.3rem;
        }
        .kawsay-area-card__circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .kawsay-area-card__info {
            flex: 1 1 auto;
            min-width: 0;
        }
        .kawsay-area-card__title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .kawsay-area-card__orden {
            font-size: 0.72rem;
            font-weight: 600;
            color: #adb5bd;
        }
        .kawsay-area-card__desc {
            margin: 0.2rem 0 0;
            color: #6c757d;
            font-size: 0.85rem;
        }
        .kawsay-area-card__actions {
            flex-shrink: 0;
            display: flex;
            gap: 0.35rem;
        }
        .kawsay-area-card__temas {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #e9ecef;
        }
        .kawsay-area-card__tema {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: #f1f3f5;
            color: #495057;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }
        .kawsay-area-card__tema:hover {
            background: #e9ecef;
            color: #212529;
            transform: translateY(-1px);
            text-decoration: none;
        }
        .kawsay-area-card__tema--nuevo {
            background: transparent;
            border: 1px dashed #ced4da;
            color: #868e96;
        }
        .kawsay-area-card__tema--nuevo:hover {
            border-color: #6e7d1e;
            color: #6e7d1e;
            background: rgba(110, 125, 30, 0.06);
        }
        .kawsay-area-card__vacio {
            color: #adb5bd;
            font-size: 0.85rem;
            font-style: italic;
            align-self: center;
        }
    </style>

@endsection
