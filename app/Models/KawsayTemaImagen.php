<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KawsayTemaImagen extends Model
{
    protected $table = 'kawsay_tema_imagenes';

    protected $fillable = ['kawsay_tema_id', 'imagen', 'orden'];

    public function tema(): BelongsTo
    {
        return $this->belongsTo(KawsayTema::class, 'kawsay_tema_id');
    }
}
