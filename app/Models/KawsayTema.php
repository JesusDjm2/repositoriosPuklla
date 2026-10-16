<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KawsayTema extends Model
{
    protected $fillable = ['kawsay_area_id', 'slug', 'titulo', 'descripcion', 'imagen', 'orden'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(KawsayArea::class, 'kawsay_area_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(KawsayArchivo::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(KawsayTemaComentario::class)->latest();
    }
}
