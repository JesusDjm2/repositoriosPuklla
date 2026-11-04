{{-- Fragmento HTML: hijos de un nodo del árbol, pedido al expandirlo (CategoryController::hijos). --}}
@foreach ($hijos as $child)
    @include('sisichakuna.tree', ['category' => $child, 'level' => $level])
@endforeach
