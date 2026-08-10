{{-- Recibe $secciones (lista de ['titulo' => ?string, 'icono' => ?string, 'items' => [['ruta','label']]])
     y $idPrefix (para IDs de collapse únicos). Cuando 'titulo' es null los items
     se listan directo; cuando tiene valor, se anidan bajo un sub-toggle propio. --}}
@foreach ($secciones as $indice => $seccion)
    @if ($seccion['titulo'])
        <li>
            <a href="#{{ $idPrefix }}-{{ $indice }}" data-toggle="collapse" aria-expanded="false"
                class="dropdown-toggle">
                @if ($seccion['icono'])
                    <i class="fa {{ $seccion['icono'] }}"></i>
                @endif
                {{ $seccion['titulo'] }}
            </a>
            <ul class="collapse list-unstyled" id="{{ $idPrefix }}-{{ $indice }}">
                @foreach ($seccion['items'] as $item)
                    @if (\Illuminate\Support\Facades\Route::has($item['ruta']))
                        <li>
                            <a href="{{ route($item['ruta']) }}"><span class="flecha-admin">→</span> {{ $item['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </li>
    @else
        @foreach ($seccion['items'] as $item)
            @if (\Illuminate\Support\Facades\Route::has($item['ruta']))
                <li>
                    <a href="{{ route($item['ruta']) }}"><span class="flecha-admin">→</span> {{ $item['label'] }}</a>
                </li>
            @endif
        @endforeach
    @endif
@endforeach
