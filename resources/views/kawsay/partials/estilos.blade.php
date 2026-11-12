{{-- Estilos del sitio público de Kawsay: public/css/kawsay.css (antes un <style> de
     ~500 líneas aquí mismo, repetido en cada página). @once: aunque se incluya varias
     veces, el <link> sale una sola vez. --}}
@once
    <link rel="stylesheet" href="{{ asset('css/kawsay.css') }}?v={{ @filemtime(public_path('css/kawsay.css')) }}">
@endonce
