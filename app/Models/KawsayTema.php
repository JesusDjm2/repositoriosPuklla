<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KawsayTema extends Model
{
    protected $fillable = ['kawsay_area_id', 'slug', 'titulo', 'descripcion', 'imagen', 'orden', 'destacado'];

    protected $casts = ['destacado' => 'boolean', 'visitas' => 'integer'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(KawsayArea::class, 'kawsay_area_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(KawsayArchivo::class);
    }

    /** Libros y videos del catálogo antiguo asignados a este tema. */
    public function libros(): HasMany
    {
        return $this->hasMany(KawsayLibro::class);
    }

    public function videosCatalogo(): HasMany
    {
        return $this->hasMany(KawsayVideo::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(KawsayTemaComentario::class)->latest();
    }
}
