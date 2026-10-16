{{-- Sección de comentarios públicos del tema. Requiere: $tema, $areaSlug, $temaSlug. --}}
@php
    $comentarios = $tema->comentarios;
    $total = $comentarios->count();
    $visibles = 6;
    $paleta = ['#6e7d1e', '#8a5a2b', '#2f6f73', '#7a3f6d', '#a8562a', '#3b5b92'];
    $errores = $errors->getBag('comentario');
@endphp

<section id="comentarios" class="kcom" aria-labelledby="kcom-titulo">
    <header class="kcom__head">
        <div>
            <h2 id="kcom-titulo" class="kcom__title">Comentarios</h2>
            <p class="kcom__sub">Comparte una experiencia, una duda o un aporte sobre este tema.</p>
        </div>
        <span class="kcom__count" aria-label="{{ $total }} {{ $total === 1 ? 'comentario' : 'comentarios' }}">{{ $total }}</span>
    </header>

    @if (session('comentario_ok'))
        <div class="kcom__alert kcom__alert--ok" role="status">
            <i class="fa fa-circle-check" aria-hidden="true"></i> {{ session('comentario_ok') }}
        </div>
    @endif
    @if (session('comentario_error'))
        <div class="kcom__alert kcom__alert--error" role="alert">
            <i class="fa fa-triangle-exclamation" aria-hidden="true"></i> {{ session('comentario_error') }}
        </div>
    @endif

    <form action="{{ route('kawsay.tema.comentarios.store', [$areaSlug, $temaSlug]) }}" method="POST"
        class="kcom__form" id="kcomForm" novalidate>
        @csrf
        <input type="hidden" name="_t" value="{{ \App\Services\AntiSpamComentarios::token() }}">

        {{-- Trampa para bots: campo invisible que una persona nunca llena. --}}
        <div class="kcom__trampa" aria-hidden="true">
            <label>No llenes este campo
                <input type="text" name="sitio_web" tabindex="-1" autocomplete="off">
            </label>
        </div>

        <div class="kcom__field">
            <label for="kcom-nombre">Tu nombre</label>
            <input type="text" id="kcom-nombre" name="nombre" maxlength="80" required autocomplete="name"
                placeholder="Ej. María Quispe" value="{{ old('nombre') }}"
                class="{{ $errores->has('nombre') ? 'is-invalid' : '' }}">
            @if ($errores->has('nombre'))<div class="kcom__error">{{ $errores->first('nombre') }}</div>@endif
        </div>

        <div class="kcom__field">
            <label for="kcom-texto">Tu comentario</label>
            <textarea id="kcom-texto" name="comentario" rows="4" maxlength="1000" required
                placeholder="Escribe aquí tu comentario…"
                class="{{ $errores->has('comentario') ? 'is-invalid' : '' }}">{{ old('comentario') }}</textarea>
            @if ($errores->has('comentario'))<div class="kcom__error">{{ $errores->first('comentario') }}</div>@endif
        </div>

        <div class="kcom__foot">
            <p class="kcom__nota">
                <i class="fa fa-shield-halved" aria-hidden="true"></i>
                Se publica al instante. No se permiten enlaces ni correos.
                <span class="kcom__contador" id="kcomContador" aria-live="polite">0 / 1000</span>
            </p>
            <button type="submit" class="kcom__btn" id="kcomBtn">
                <i class="fa fa-paper-plane" aria-hidden="true"></i> <span>Publicar comentario</span>
            </button>
        </div>
    </form>

    @if ($total === 0)
        <div class="kcom__vacio">
            <i class="fa fa-comments" aria-hidden="true"></i>
            <p><strong>Aún no hay comentarios.</strong><br>Sé la primera persona en comentar.</p>
        </div>
    @else
        <ul class="kcom__lista" id="kcomLista">
            @foreach ($comentarios as $c)
                @php
                    $color = $paleta[crc32(mb_strtolower($c->nombre)) % count($paleta)];
                    $iniciales = mb_strtoupper(mb_substr(trim($c->nombre), 0, 1));
                @endphp
                <li class="kcom__item {{ $loop->index >= $visibles ? 'is-oculto' : '' }}">
                    <span class="kcom__avatar" style="background: {{ $color }}" aria-hidden="true">{{ $iniciales }}</span>
                    <div class="kcom__cuerpo">
                        <div class="kcom__meta">
                            <strong class="kcom__autor">{{ $c->nombre }}</strong>
                            <time datetime="{{ $c->created_at->toIso8601String() }}"
                                title="{{ $c->created_at->copy()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}">{{ $c->created_at->copy()->locale('es')->diffForHumans() }}</time>
                        </div>
                        <p class="kcom__texto">{!! nl2br(e($c->comentario)) !!}</p>
                    </div>
                </li>
            @endforeach
        </ul>

        @if ($total > $visibles)
            <button type="button" class="kcom__mas" id="kcomMas">
                Ver más comentarios <span>({{ $total - $visibles }})</span> <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button>
        @endif
    @endif
</section>

<script>
    (function () {
        var form = document.getElementById('kcomForm');
        if (!form) return;

        var texto = document.getElementById('kcom-texto');
        var contador = document.getElementById('kcomContador');
        var btn = document.getElementById('kcomBtn');

        function actualizarContador() {
            var n = texto.value.length;
            contador.textContent = n + ' / 1000';
            contador.classList.toggle('is-limite', n > 900);
        }
        texto.addEventListener('input', actualizarContador);
        actualizarContador();

        form.addEventListener('submit', function (e) {
            var nombre = form.querySelector('[name=nombre]').value.trim();
            var comentario = texto.value.trim();
            if (nombre.length < 2 || comentario.length < 5) {
                e.preventDefault();
                (nombre.length < 2 ? form.querySelector('[name=nombre]') : texto).focus();
                return;
            }
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Publicando…';
        });

        var mas = document.getElementById('kcomMas');
        if (mas) {
            mas.addEventListener('click', function () {
                document.querySelectorAll('#kcomLista .is-oculto').forEach(function (li) {
                    li.classList.remove('is-oculto');
                });
                mas.remove();
            });
        }
    })();
</script>
