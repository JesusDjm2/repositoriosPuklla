<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawsayLibro extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'autor', 'identificador'];

    public function categorias()
    {
        return $this->belongsToMany(KawsayCategoria::class, 'kawsay_categoria_libro', 'kawsay_libro_id', 'kawsay_categoria_id');
    }
}
