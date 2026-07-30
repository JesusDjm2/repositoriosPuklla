<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawsayCategoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'url'];

    public function libros()
    {
        return $this->belongsToMany(KawsayLibro::class, 'kawsay_categoria_libro', 'kawsay_categoria_id', 'kawsay_libro_id');
    }
}
