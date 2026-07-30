<?php

namespace App\Livewire;

use App\Models\Proyecto;
use Livewire\Component;

class Foot extends Component
{
    public function render()
    {
        $proyectos = Proyecto::activos()->ordenados()->get()
            ->map(fn (Proyecto $p) => [
                'nombre' => $p->nombre,
                'url' => $p->urlPublica(),
            ])
            ->filter(fn (array $p) => $p['url'] !== null)
            ->values();

        return view('livewire.foot', compact('proyectos'));
    }
}
