<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KawsayTemaComentario extends Model
{
    protected $table = 'kawsay_tema_comentarios';

    protected $fillable = ['kawsay_tema_id', 'nombre', 'comentario'];

    public function tema(): BelongsTo
    {
        return $this->belongsTo(KawsayTema::class, 'kawsay_tema_id');
    }
}
