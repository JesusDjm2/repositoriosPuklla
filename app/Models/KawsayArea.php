<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KawsayArea extends Model
{
    protected $fillable = ['slug', 'nombre', 'icono', 'color', 'descripcion', 'imagen', 'orden'];

    public function temas(): HasMany
    {
        return $this->hasMany(KawsayTema::class)->orderBy('orden');
    }
}
