@extends('layouts.app')
@section('titulo', '- Comentarios Kawsay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/biblioteca-crud.css') }}?v={{ file_exists(public_path('css/biblioteca-crud.css')) ? filemtime(public_path('css/biblioteca-crud.css')) : 1 }}">
@endpush

@section('contenido')

    <x-hero-banner titulo="Comentarios Kawsay" subtitulo="Todos los comentarios públicos de los temas, en un solo lugar"
        icono="fa-comments" color="kawsay">
        <x-slot:stats>
            <x-hero-stat icono="fa-comments" :valor="$stats['total']" etiqueta="En total" />
            <x-hero-stat icono="fa-calendar-day" :valor="$stats['hoy']" etiqueta="Hoy" />
            <x-hero-stat icono="fa-calendar-week" :valor="$stats['semana']" etiqueta="Últimos 7 días" />
            <x-hero-stat icono="fa-map-signs" :valor="$stats['temas']" etiqueta="Temas con comentarios" />
        </x-slot:stats>
    </x-hero-banner>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bv-crud-toolbar form-row align-items-end mb-3">
        <div class="col-md-3 form-group mb-0">
            <label class="bv-form-label">Área</label>
            <select name="area" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" {{ (int) request('area') === $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group mb-0">
            <label class="bv-form-label">Tema</label>
            <select name="kawsay_tema_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach ($temas as $tema)
                    <option value="{{ $tema->id }}" {{ (int) request('kawsay_tema_id') === $tema->id ? 'selected' : '' }}>
                        {{ $tema->titulo }} ({{ $tema->comentarios_count }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 form-group mb-0">
            <label class="bv-form-label">Buscar</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                placeholder="En el comentario o el nombre de quien lo escribió…">
        </div>
        <div class="col-md-2 form-group mb-0 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                <i class="fa fa-search"></i> Buscar
            </button>
            @if (request()->anyFilled(['area', 'kawsay_tema_id', 'q']))
                <a href="{{ route('kawsaycomentarios.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="fa fa-times"></i>
                </a>
            @endif
        </div>
    </form>

    <form action="{{ route('kawsaycomentarios.destroyMasivo') }}" method="POST" id="kcmForm"
        data-confirm-delete="¿Eliminar los comentarios seleccionados?" data-confirm-delete-text="Esta acción no se puede deshacer.">
        @csrf
        @method('DELETE')

        <div class="kcm-barra">
            <label class="kcm-todos">
                <input type="checkbox" id="kcmTodos" {{ $comentarios->isEmpty() ? 'disabled' : '' }}>
                Seleccionar todos ({{ $comentarios->total() }})
            </label>
            <button type="submit" class="btn btn-sm btn-outline-danger" id="kcmBorrar" disabled>
                <i class="fa fa-trash"></i> Eliminar seleccionados
            </button>
        </div>

        @forelse ($comentarios as $c)
            @php $colorArea = $c->tema->area->color ?? '#6e7d1e'; @endphp
            <div class="kcm-fila">
                <input type="checkbox" name="ids[]" value="{{ $c->id }}" class="kcm-check" form="kcmForm">

                <span class="kcm-avatar">{{ mb_strtoupper(mb_substr(trim($c->nombre), 0, 1)) }}</span>

                <div class="kcm-cuerpo">
                    <div class="kcm-meta">
                        <strong class="kcm-autor">{{ $c->nombre }}</strong>
                        <time title="{{ $c->created_at->translatedFormat('d \d\e F \d\e Y, H:i') }}">{{ $c->created_at->copy()->locale('es')->diffForHumans() }}</time>
                    </div>
                    <p class="kcm-texto">{{ $c->comentario }}</p>
                    <div class="kcm-origen">
                        @if ($c->tema)
                            <span class="kcm-area" style="--kcm-color: {{ $colorArea }}">{{ $c->tema->area->nombre ?? '—' }}</span>
                            <a href="{{ route('kawsaytemas.edit', $c->tema) }}#comentarios-tema" class="kcm-tema">
                                <i class="fa fa-map-signs" aria-hidden="true"></i> {{ $c->tema->titulo }}
                            </a>
                            <a href="{{ route('kawsay.tema', [$c->tema->area->slug, $c->tema->slug]) }}#comentarios" target="_blank" rel="noopener" class="kcm-vercomo" title="Ver en el sitio">
                                <i class="fa fa-external-link"></i>
                            </a>
                        @else
                            <span class="text-muted small">Tema eliminado</span>
                        @endif
                    </div>
                </div>

                <form action="{{ route('kawsaycomentarios.destroy', $c) }}" method="POST" class="kcm-borrar"
                    data-confirm-delete="¿Eliminar este comentario?"
                    data-confirm-delete-text="Es de {{ $c->nombre }} y no se puede deshacer.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            </div>
        @empty
            <div class="bv-empty-state">
                <i class="fa fa-comments bv-empty-icon"></i>
                @if (request()->anyFilled(['area', 'kawsay_tema_id', 'q']))
                    No hay comentarios que coincidan con este filtro.
                @else
                    Todavía no hay comentarios públicos.
                @endif
            </div>
        @endforelse
    </form>

    @if ($comentarios->hasPages())
        <div class="bv-crud-pagination">
            {{ $comentarios->links() }}
        </div>
    @endif

    <style>
        .kcm-barra {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: .6rem 1rem; margin-bottom: .75rem; background: #f8f9f5; border: 1px solid #e9ecef; border-radius: .6rem;
        }
        .kcm-todos { display: flex; align-items: center; gap: .5rem; font-size: .85rem; font-weight: 600; color: #495057; margin: 0; cursor: pointer; }
        .kcm-todos input:disabled { cursor: not-allowed; }

        .kcm-fila {
            display: flex; align-items: flex-start; gap: .9rem; padding: .9rem 1rem;
            background: #fff; border: 1px solid rgba(20, 20, 40, .06); border-radius: .8rem; margin-bottom: .6rem;
            box-shadow: 0 1px 2px rgba(20, 20, 40, .03);
        }
        .kcm-check { margin-top: .55rem; width: 1.05rem; height: 1.05rem; flex-shrink: 0; }
        .kcm-avatar {
            width: 2.4rem; height: 2.4rem; border-radius: 50%; flex-shrink: 0; margin-top: .1rem;
            display: inline-flex; align-items: center; justify-content: center;
            background: #eef3dc; color: #55631a; font-weight: 800; font-size: 1rem;
        }
        .kcm-cuerpo { flex: 1; min-width: 0; }
        .kcm-meta { display: flex; align-items: baseline; gap: .6rem; flex-wrap: wrap; margin-bottom: .2rem; }
        .kcm-autor { font-size: .92rem; color: #212529; }
        .kcm-meta time { font-size: .76rem; color: #adb5bd; }
        .kcm-texto { margin: 0 0 .5rem; color: #3d4437; font-size: .92rem; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
        .kcm-origen { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
        .kcm-area {
            --kcm-color: #6e7d1e;
            font-size: .7rem; font-weight: 700; padding: .1rem .55rem; border-radius: 999px;
            background: color-mix(in srgb, var(--kcm-color) 14%, white); color: color-mix(in srgb, var(--kcm-color) 70%, black);
            border: 1px solid color-mix(in srgb, var(--kcm-color) 30%, white);
        }
        .kcm-tema { font-size: .78rem; font-weight: 600; color: #6e7d1e; text-decoration: none; }
        .kcm-tema:hover { text-decoration: underline; }
        .kcm-vercomo { color: #adb5bd; font-size: .74rem; }
        .kcm-vercomo:hover { color: #6e7d1e; }
        .kcm-borrar { flex-shrink: 0; margin-top: -.1rem; }

        @media (max-width: 575.98px) {
            .kcm-fila { flex-wrap: wrap; }
            .kcm-borrar { margin-left: auto; }
        }
    </style>

    <script>
        (function () {
            var todos = document.getElementById('kcmTodos');
            var borrar = document.getElementById('kcmBorrar');
            if (!todos) return;
            var checks = document.querySelectorAll('.kcm-check');

            function actualizar() {
                var marcados = document.querySelectorAll('.kcm-check:checked').length;
                borrar.disabled = marcados === 0;
                borrar.innerHTML = '<i class="fa fa-trash"></i> Eliminar seleccionados' + (marcados ? ' (' + marcados + ')' : '');
                todos.checked = marcados > 0 && marcados === checks.length;
                todos.indeterminate = marcados > 0 && marcados < checks.length;
            }

            todos.addEventListener('change', function () {
                checks.forEach(function (c) { c.checked = todos.checked; });
                actualizar();
            });
            checks.forEach(function (c) { c.addEventListener('change', actualizar); });
            actualizar();
        })();
    </script>

@endsection
