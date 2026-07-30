<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sisipedia @yield('titulo')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @php
        $estilosPath = public_path('css/estilos.css');
        $estilosVersion = file_exists($estilosPath) ? filemtime($estilosPath) : null;
        $bodyClassProyecto = \App\Services\ProyectoDetector::bodyClass();
    @endphp
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    @if ($estilosVersion)
        <link rel="stylesheet" href="{{ asset('css/estilos.css') }}?v={{ $estilosVersion }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @endif
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/Icono-Puklla.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/Icono-Puklla.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap"
        rel="stylesheet">
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v22.0&appId=366341694202025"></script>
    @yield('estilos')
</head>

<body style="background: #f2f2f2" class="@yield('bodyClass', $bodyClassProyecto)">

    @php
        $proyectoLoaderLabel = match ($proyectoActual = \App\Services\ProyectoDetector::actual()) {
            'eesp' => 'Biblioteca Pukllasunchis',
            'kawsay' => 'Kawsay',
            'sisipedia' => 'Sisipedia Pukllasunchis',
            default => 'Asociación Pukllasunchis',
        };
    @endphp
    {{-- Loader de página --}}
    <div id="puklla-loader" role="status" aria-label="Cargando">
        <div class="puklla-loader__box">
            <div class="puklla-loader__track"><div class="puklla-loader__bar"></div></div>
            <p class="puklla-loader__label">{{ $proyectoLoaderLabel }}</p>
        </div>
    </div>
    <script>
    window.addEventListener('load', function () {
        var l = document.getElementById('puklla-loader');
        if (!l) return;
        setTimeout(function () {
            l.classList.add('out');
            setTimeout(function () { l.remove(); }, 500);
        }, 180);
    });
    </script>

    @livewire('menu')
    <main class="main-content">
        @yield('contenido')
    </main>
    @livewire('foot')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmia9VlWSoWzNqj9wq7QWg8p0Lp" crossorigin="anonymous"></script>

    @include('partials.libro-preview-modal')
</body>

</html>
