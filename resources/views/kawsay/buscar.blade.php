@extends('layouts.padre')
@section('titulo', '- Kawsay - Buscar')

@section('meta')
    <meta name="robots" content="noindex, follow">
@endsection

@section('contenido')
@php
    // Resalta las coincidencias exactas del texto buscado (todo escapado antes de marcar).
    $hl = function (?string $texto, string $q): string {
        $texto = (string) ($texto ?? '');
        if ($texto === '' || $q === '') {
            return e($texto);
        }
        $partes = preg_split('/(' . preg_quote($q, '/') . ')/iu', $texto, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($partes === false) {
            return e($texto);
        }
        $out = '';
        foreach ($partes as $i => $parte) {
            $out .= $i % 2 === 1 ? '<mark class="kbus-hl">' . e($parte) . '</mark>' : e($parte);
        }
        return $out;
    };
    $visibles = 8;
@endphp

<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <a href="{{ route('kawsay') }}" class="kawsay-back-link"><i class="fa fa-arrow-left"></i> Kawsay</a>

        <h1 class="kawsay-area-header__title mt-2 mb-1">
            @if ($corto)
                Buscar en Kawsay
            @elseif ($total > 0)
                Resultados para <span class="kbus-consulta">“{{ $query }}”</span>
            @else
                Sin resultados para <span class="kbus-consulta">“{{ $query }}”</span>
            @endif
        </h1>
        <p class="kawsay-area-header__desc mb-3">
            @if ($corto)
                Escribe al menos 3 letras para buscar en áreas, temas, contenido, libros y videos de Kawsay.
            @elseif ($total > 0)
                {{ $total }} {{ $total === 1 ? 'resultado' : 'resultados' }} dentro de Kawsay.
            @else
                No encontramos nada en Kawsay con ese texto.
            @endif
        </p>

        <form action="{{ route('kawsay.buscar') }}" method="GET" role="search" class="kbus-form">
            <label for="kbusInput" class="visually-hidden">Buscar en Kawsay</label>
            <i class="fa fa-search" aria-hidden="true"></i>
            <input type="search" id="kbusInput" name="query" value="{{ $query }}" minlength="3" maxlength="120"
                placeholder="Buscar en Kawsay: temas, contenido, libros, videos…" autocomplete="off" required>
            <button type="submit">Buscar</button>
        </form>
    </div>
</div>

<div class="container py-4 py-md-5">
    @if ($total > 0)
        {{-- Atajos a cada grupo --}}
        <nav class="kbus-resumen" aria-label="Tipos de resultado">
            @foreach ($grupos as $g)
                <a href="#kbus-{{ $g['clave'] }}" class="kbus-chip" style="--kbus-color: {{ $g['color'] }}">
                    <i class="fa {{ $g['icono'] }}" aria-hidden="true"></i> {{ $g['titulo'] }}
                    <b>{{ $g['total'] }}</b>
                </a>
            @endforeach
        </nav>

        @foreach ($grupos as $g)
            <section class="kbus-grupo" id="kbus-{{ $g['clave'] }}" style="--kbus-color: {{ $g['color'] }}">
                <h2 class="kbus-grupo__titulo">
                    <span class="kbus-grupo__icono"><i class="fa {{ $g['icono'] }}" aria-hidden="true"></i></span>
                    {{ $g['titulo'] }}
                    <span class="kbus-grupo__count">{{ $g['total'] }}</span>
                </h2>

                <div class="kbus-lista" data-lista>
                    @foreach ($g['items'] as $item)
                        @php $tieneLink = filled($item['href'] ?? null); @endphp
                        <a href="{{ $tieneLink ? $item['href'] : 'javascript:void(0)' }}"
                            @if ($tieneLink && ($item['externo'] ?? false)) target="_blank" rel="noopener" @endif
                            class="kbus-fila {{ $loop->index >= $visibles ? 'is-oculto' : '' }} {{ $tieneLink ? '' : 'kbus-fila--sinlink' }}">
                            <span class="kbus-fila__icono"><i class="fa {{ $item['icono'] ?? $g['icono'] }}" aria-hidden="true"></i></span>
                            <span class="kbus-fila__cuerpo">
                                <span class="kbus-fila__titulo">{!! $hl($item['titulo'], $query) !!}</span>
                                @if (filled($item['meta'] ?? null))
                                    <span class="kbus-fila__meta">{!! $hl($item['meta'], $query) !!}</span>
                                @endif
                            </span>
                            @if ($tieneLink)
                                <i class="fa {{ ($item['externo'] ?? false) ? 'fa-arrow-up-right-from-square' : 'fa-chevron-right' }} kbus-fila__flecha" aria-hidden="true"></i>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if ($g['items']->count() > $visibles)
                    <button type="button" class="kbus-mas" data-mas>
                        Ver los {{ $g['items']->count() - $visibles }} restantes <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </button>
                @endif
                @if ($g['total'] > $g['items']->count())
                    <p class="kbus-nota">Mostrando los {{ $g['items']->count() }} más relevantes de {{ $g['total'] }}. Afina la búsqueda para ver otros.</p>
                @endif
            </section>
        @endforeach
    @else
        <div class="kbus-vacio">
            <span class="kbus-vacio__icono"><i class="fa {{ $corto ? 'fa-magnifying-glass' : 'fa-leaf' }}" aria-hidden="true"></i></span>
            @unless ($corto)
                <h2>No hay coincidencias</h2>
                <ul class="kbus-tips">
                    <li>Revisa que las palabras estén bien escritas.</li>
                    <li>Prueba con una sola palabra clave (por ejemplo: <em>agua</em>, <em>compostaje</em>).</li>
                    <li>Usa términos más generales.</li>
                </ul>
            @endunless
            <p class="kbus-vacio__sub">Mientras tanto, explora por área de trabajo:</p>
            <div class="kbus-areas">
                @foreach ($areasLista as $a)
                    <a href="{{ route('kawsay.area', $a->slug) }}" class="kbus-chip">{{ $a->nombre }} <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                @endforeach
            </div>
            @unless ($corto)
                <p class="kbus-global">
                    ¿Buscas en todos los repositorios de la Asociación?
                    <a href="{{ route('busqueda', ['query' => $query]) }}">Buscar “{{ $query }}” en todo el sitio <i class="fa fa-arrow-right"></i></a>
                </p>
            @endunless
        </div>
    @endif
</div>

<style>
    .kbus-consulta { opacity: .95; word-break: break-word; }
    .kbus-form {
        display: flex; align-items: center; gap: .6rem; max-width: 640px;
        background: #fff; border-radius: 999px; padding: .35rem .35rem .35rem 1.1rem;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .22);
    }
    .kbus-form > i { color: var(--project-accent); }
    .kbus-form input { flex: 1; min-width: 0; border: 0; background: transparent; font-size: .98rem; color: #2a2f26; padding: .55rem 0; }
    .kbus-form input:focus { outline: none; }
    .kbus-form button {
        border: 0; cursor: pointer; border-radius: 999px; padding: .6rem 1.4rem; font-weight: 700; font-size: .9rem;
        background: var(--project-accent); color: #fff; transition: filter .15s ease;
    }
    .kbus-form button:hover { filter: brightness(1.1); }
    .kbus-form:focus-within { box-shadow: 0 12px 30px rgba(0, 0, 0, .28), 0 0 0 4px rgba(255, 255, 255, .35); }

    .kbus-resumen { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.75rem; }
    .kbus-chip {
        --kbus-color: var(--project-accent);
        display: inline-flex; align-items: center; gap: .5rem; padding: .4rem .95rem; border-radius: 999px;
        background: #fff; border: 1px solid #e3e6db; color: #3d4437; font-size: .85rem; font-weight: 600; text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .kbus-chip i:first-child { color: var(--kbus-color); }
    .kbus-chip b { background: var(--kbus-color); color: #fff; border-radius: 999px; padding: 0 .5rem; font-size: .72rem; line-height: 1.35rem; }
    .kbus-chip:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(20, 30, 10, .1); border-color: var(--kbus-color); color: #3d4437; text-decoration: none; }

    .kbus-grupo { margin-bottom: 2.25rem; scroll-margin-top: 6rem; }
    .kbus-grupo__titulo { display: flex; align-items: center; gap: .65rem; font-size: 1.15rem; font-weight: 800; color: #2a2f26; margin: 0 0 .9rem; }
    .kbus-grupo__icono {
        width: 2.2rem; height: 2.2rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--kbus-color) 14%, white); color: var(--kbus-color); font-size: .9rem;
    }
    .kbus-grupo__count { font-size: .75rem; font-weight: 700; color: #7a8171; background: #eef0e8; border-radius: 999px; padding: .1rem .6rem; }

    .kbus-lista { display: flex; flex-direction: column; gap: .55rem; }
    .kbus-fila {
        display: flex; align-items: center; gap: .9rem; padding: .8rem 1rem; border-radius: 14px;
        background: #fff; border: 1px solid #eceee6; text-decoration: none; color: inherit;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .kbus-fila:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(20, 30, 10, .09); border-color: var(--kbus-color); color: inherit; text-decoration: none; }
    .kbus-fila--sinlink { cursor: default; }
    .kbus-fila--sinlink:hover { transform: none; box-shadow: none; border-color: #eceee6; }
    .kbus-fila.is-oculto { display: none; }
    .kbus-fila__icono {
        flex-shrink: 0; width: 2.4rem; height: 2.4rem; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--kbus-color) 13%, white); color: var(--kbus-color);
    }
    .kbus-fila__cuerpo { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .1rem; }
    .kbus-fila__titulo { font-weight: 700; font-size: .95rem; color: #2a2f26; overflow-wrap: anywhere; }
    .kbus-fila__meta { font-size: .8rem; color: #7a8171; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .kbus-fila__flecha { color: #b8bdae; font-size: .8rem; flex-shrink: 0; transition: transform .15s ease, color .15s ease; }
    .kbus-fila:hover .kbus-fila__flecha { color: var(--kbus-color); transform: translateX(3px); }
    .kbus-hl { background: color-mix(in srgb, var(--project-accent) 25%, white); color: inherit; padding: 0; border-radius: 2px; }

    .kbus-mas {
        display: flex; align-items: center; justify-content: center; gap: .4rem; width: 100%; margin-top: .8rem; padding: .65rem; cursor: pointer;
        background: transparent; color: var(--kbus-color); font-weight: 700; font-size: .88rem;
        border: 1.5px dashed color-mix(in srgb, var(--kbus-color) 50%, transparent); border-radius: 12px;
    }
    .kbus-mas:hover { background: color-mix(in srgb, var(--kbus-color) 8%, transparent); }
    .kbus-nota { margin: .6rem 0 0; font-size: .78rem; color: #8b9282; }

    .kbus-vacio { text-align: center; max-width: 640px; margin: 1rem auto; padding: 2rem 1rem; }
    .kbus-vacio__icono {
        width: 4.5rem; height: 4.5rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--project-accent) 12%, white); color: var(--project-accent); font-size: 1.8rem; margin-bottom: 1rem;
    }
    .kbus-vacio h2 { font-size: 1.3rem; font-weight: 800; color: #2a2f26; margin-bottom: .6rem; }
    .kbus-tips { list-style: none; padding: 0; margin: 0 0 1.25rem; color: #6b7264; font-size: .92rem; line-height: 1.7; }
    .kbus-vacio__sub { color: #6b7264; font-size: .9rem; margin-bottom: .7rem; }
    .kbus-areas { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
    .kbus-global { margin-top: 1.5rem; font-size: .85rem; color: #7a8171; }
    .kbus-global a { color: var(--project-accent); font-weight: 700; text-decoration: none; }
    .kbus-global a:hover { text-decoration: underline; }

    @media (max-width: 575.98px) {
        .kbus-form { padding-left: .9rem; }
        .kbus-form button { padding: .55rem 1rem; }
        .kbus-fila { padding: .7rem .8rem; gap: .7rem; }
        .kbus-fila__icono { width: 2.1rem; height: 2.1rem; }
    }
    @media (prefers-reduced-motion: reduce) { .kbus-fila, .kbus-chip { transition: none; } }
</style>

<script>
    document.querySelectorAll('[data-mas]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.kbus-grupo').querySelectorAll('.kbus-fila.is-oculto').forEach(function (f) {
                f.classList.remove('is-oculto');
            });
            btn.remove();
        });
    });
</script>

@include('kawsay.partials.estilos')
@endsection
