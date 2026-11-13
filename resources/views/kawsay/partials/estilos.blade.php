{{-- Estilos y tipografías del sitio público de Kawsay (Montserrat 700 + Inter 400, 500) --}}
@once
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kawsay.css') }}?v={{ @filemtime(public_path('css/kawsay.css')) }}">
@endonce
