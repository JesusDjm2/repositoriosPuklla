<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Pukllasunchis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Lato:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/Icono-Puklla.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/Icono-Puklla.png') }}">
    <style>
        :root {
            --accent: #6a0310;
            --accent-2: #8c0418;
            --gold: #c88e41;
            --focus: #ffffff;
            --brand-red: #cb001c;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: "Lato", system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            background: #1a1612;
        }

        .login-bg {
            position: fixed;
            inset: 0;
            background-image: url('{{ asset('img/fondo/fondo-login.jpg') }}');
            background-size: cover;
            background-position: center;
            z-index: 0;
            animation: kenburns 24s ease-in-out infinite alternate;
        }

        @keyframes kenburns {
            0% {
                transform: scale(1) translate(0, 0);
            }

            100% {
                transform: scale(1.09) translate(-1.2%, -1%);
            }
        }

        .login-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(160deg, rgba(20, 10, 10, .55) 0%, rgba(20, 10, 10, .78) 55%, rgba(10, 5, 5, .9) 100%);
            z-index: 1;
        }

        .login-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            margin: 2rem auto;
            padding: 0 1.25rem;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-logo {
            width: 150px;
            max-width: 42vw;
            margin-bottom: 1.1rem;
            opacity: 0;
            filter: drop-shadow(0 4px 14px rgba(0, 0, 0, .35));
        }

        .login-titulo {
            font-family: "EB Garamond", Garamond, "Times New Roman", serif;
            font-weight: 700;
            color: #fff;
            font-size: clamp(1.35rem, 4vw, 1.85rem);
            margin: 0 0 .4rem;
            opacity: 0;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .35);
        }

        .login-subtitulo {
            color: rgba(255, 255, 255, .72);
            font-size: .85rem;
            margin: 0;
            opacity: 0;
            letter-spacing: .01em;
        }

        .login-card {
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 1.25rem;
            padding: 2rem 1.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
            opacity: 0;
        }

        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            background: rgba(229, 72, 77, .16);
            border: 1px solid rgba(229, 72, 77, .4);
            color: #ffd7d7;
            border-radius: .7rem;
            padding: .7rem .85rem;
            font-size: .82rem;
            margin-bottom: 1.15rem;
            line-height: 1.4;
        }

        .login-alert i {
            margin-top: .15rem;
            flex-shrink: 0;
        }

        .field {
            position: relative;
            margin-bottom: 1.15rem;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 15px;
            color: var(--focus);
            font-size: .85rem;
            pointer-events: none;
            transition: color .2s ease;
        }

        .field input {
            width: 100%;
            background: rgba(255, 255, 255, .08);
            border: 1.5px solid rgba(255, 255, 255, .2);
            border-radius: .7rem;
            padding: .75rem 1rem .75rem 2.6rem;
            color: #fff;
            font-size: .92rem;
            font-family: inherit;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .field input::placeholder {
            color: rgba(255, 255, 255, .42);
        }

        /* El navegador fuerza un fondo claro en los campos autocompletados;
           lo reemplazamos por el mismo tono oscuro de la tarjeta (el
           "transition" gigante evita el parpadeo blanco antes de aplicarse). */
        .field input:-webkit-autofill,
        .field input:-webkit-autofill:hover,
        .field input:-webkit-autofill:focus,
        .field input:-webkit-autofill:active {
            -webkit-text-fill-color: #fff;
            -webkit-box-shadow: 0 0 0 1000px #241f1c inset;
            box-shadow: 0 0 0 1000px #241f1c inset;
            caret-color: #fff;
            transition: background-color 5000s ease-in-out 0s;
        }

        .field input:autofill {
            background-color: #241f1c;
            color: #fff;
        }

        .field input:focus {
            outline: none;
            border-color: var(--focus);
            background: rgba(255, 255, 255, .12);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .28);
        }

        .field.has-error input {
            border-color: #e5484d;
        }

        .field.has-error input:focus {
            box-shadow: 0 0 0 3px rgba(229, 72, 77, .25);
        }

        .field-error {
            color: #ffb4b4;
            font-size: .76rem;
            margin: .4rem 0 0 .2rem;
        }

        .field--password input {
            padding-right: 2.7rem;
        }

        .toggle-password {
            position: absolute;
            right: 6px;
            top: 6px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--focus);
            padding: .5rem .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .5rem;
            transition: color .2s ease, background .2s ease;
        }

        .toggle-password:hover {
            color: #fff;
            background: rgba(255, 255, 255, .12);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--focus);
            outline-offset: 1px;
        }

        .login-remember {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: .1rem 0 1.4rem;
            font-size: .82rem;
            color: rgba(255, 255, 255, .75);
            user-select: none;
            cursor: pointer;
        }

        .login-remember input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            border: 1.5px solid rgba(255, 255, 255, .45);
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            transition: background .2s ease, border-color .2s ease;
        }

        .login-remember input[type="checkbox"]:checked {
            background: var(--brand-red);
            border-color: var(--brand-red);
        }

        .login-remember input[type="checkbox"]:checked::after {
            content: "";
            position: absolute;
            left: 5px;
            top: 1px;
            width: 5px;
            height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .login-remember input[type="checkbox"]:focus-visible {
            outline: 2px solid var(--brand-red);
            outline-offset: 2px;
        }

        .login-btn {
            width: 100%;
            border: none;
            border-radius: .8rem;
            padding: .85rem 1rem;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-2) 100%);
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            box-shadow: 0 8px 22px rgba(106, 3, 16, .35);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
            box-shadow: 0 12px 28px rgba(106, 3, 16, .45);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        .login-btn:disabled {
            opacity: .8;
            cursor: default;
            transform: none;
        }

        .login-btn .fa-spinner {
            display: none;
        }

        .login-btn.is-loading .fa-spinner {
            display: inline-block;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .login-wrap {
                margin: 1.25rem auto;
            }

            .login-card {
                padding: 1.6rem 1.3rem;
                border-radius: 1rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-bg {
                animation: none;
            }
        }
    </style>
</head>

<body>
    <div class="login-bg" aria-hidden="true"></div>
    <div class="login-overlay" aria-hidden="true"></div>

    <div class="login-wrap">
        <div class="login-brand">
            <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Asociación Pukllasunchis" class="login-logo">
            <h1 class="login-titulo">Repositorios Asociación Pukllasunchis</h1>
            <p class="login-subtitulo">Ingresa para continuar</p>
        </div>

        <div class="login-card">
            @if ($errors->any())
                <div class="login-alert" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form" novalidate>
                @csrf

                <div class="field @error('email') has-error @enderror">
                    <i class="fas fa-envelope field-icon"></i>
                    <input id="email" type="email" name="email" placeholder="Correo electrónico"
                        value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field field--password @error('password') has-error @enderror">
                    <i class="fas fa-lock field-icon"></i>
                    <input id="password" type="password" name="password" placeholder="Contraseña" required
                        autocomplete="current-password">
                    <button type="button" class="toggle-password" data-target="password"
                        aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="login-remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Recordarme</span>
                </label>

                <button type="submit" class="login-btn">
                    <i class="fas fa-spinner"></i>
                    <span class="btn-label">Ingresar</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var revealTargets = '.login-logo, .login-titulo, .login-subtitulo, .login-card';

            function reveal() {
                document.querySelectorAll(revealTargets).forEach(function(el) {
                    el.style.opacity = 1;
                });
            }

            function animate() {
                if (typeof gsap === 'undefined') {
                    reveal();
                    return;
                }
                gsap.timeline({
                        defaults: {
                            ease: 'power3.out'
                        }
                    })
                    .fromTo('.login-logo', {
                        opacity: 0,
                        y: -14,
                        scale: .9
                    }, {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: .6
                    })
                    .fromTo('.login-titulo', {
                        opacity: 0,
                        y: 14
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: .5
                    }, '-=.3')
                    .fromTo('.login-subtitulo', {
                        opacity: 0,
                        y: 10
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: .45
                    }, '-=.3')
                    .fromTo('.login-card', {
                        opacity: 0,
                        y: 26
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: .6
                    }, '-=.2')
                    .fromTo('.field, .login-remember, .login-btn', {
                        opacity: 0,
                        y: 14
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: .4,
                        stagger: .07
                    }, '-=.35');
            }

            if (reduceMotion) {
                reveal();
            } else if (typeof gsap !== 'undefined') {
                animate();
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animate;
                script.onerror = reveal;
                document.head.appendChild(script);
            }

            document.querySelectorAll('.toggle-password').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var input = document.getElementById(btn.getAttribute('data-target'));
                    var icon = btn.querySelector('i');
                    var showing = input.type === 'text';

                    input.type = showing ? 'password' : 'text';
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                    btn.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');

                    if (typeof gsap !== 'undefined' && !reduceMotion) {
                        gsap.fromTo(icon, {
                            scale: .6,
                            opacity: .5
                        }, {
                            scale: 1,
                            opacity: 1,
                            duration: .3,
                            ease: 'back.out(2.5)'
                        });
                    }
                });
            });

            var form = document.querySelector('.login-form');
            var submitBtn = document.querySelector('.login-btn');
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    if (submitBtn.classList.contains('is-loading')) return;
                    submitBtn.classList.add('is-loading');
                    submitBtn.disabled = true;
                });
            }
        })();
    </script>
</body>

</html>
