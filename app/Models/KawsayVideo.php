<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawsayVideo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'youtube', 'drive', 'categoria_id', 'kawsay_tema_id'];

    /** Tema del repositorio al que pertenece (opcional). */
    public function tema()
    {
        return $this->belongsTo(KawsayTema::class, 'kawsay_tema_id');
    }

    public function categoria()
    {
        return $this->belongsTo(KawsayVideoCategoria::class, 'categoria_id');
    }
}
