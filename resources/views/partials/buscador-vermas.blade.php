{{--
    Enlace "ver más" al final de una sección compacta del buscador, cuando
    hay más coincidencias de las que se muestran (evita listas larguísimas).

    Variables:
    - $restantes (int)    cuántos resultados no se muestran
    - $href      (string) a dónde ir a verlos todos (vista propia del proyecto)
    - $label     (string) nombre del proyecto
--}}
@if ($restantes > 0)
    <a href="{{ $href }}" class="buscador-vermas">
        Ver {{ $restantes }} {{ $restantes === 1 ? 'resultado más' : 'resultados más' }} en {{ $label }}
        <i class="fa fa-arrow-right"></i>
    </a>
@endif
