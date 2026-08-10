<?php

namespace App\Livewire;

use App\Models\Proyecto;
use Illuminate\Support\Collection;
use Livewire\Component;

class MenuAdmin extends Component
{
    public function render()
    {
        /** @var Collection<int, Proyecto> $proyectos */
        $proyectos = auth()->user()?->hasRole('admin')
            ? Proyecto::activos()->with('roles')->ordenados()->get()
            : collect();

        return view('livewire.menu-admin', compact('proyectos'));
    }
}
