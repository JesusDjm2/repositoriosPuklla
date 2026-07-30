<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroFisico extends Model
{
    use HasFactory;

    protected $table = 'libros_fisicos';

    protected $fillable = [
        'nombre',
        'autor',
        'editorial',
        'anio_publicacion',
        'numero_paginas',
        'codigo',
        'cantidad_ejemplares',
    ];

    protected $casts = [
        'anio_publicacion' => 'integer',
        'numero_paginas' => 'integer',
        'cantidad_ejemplares' => 'integer',
    ];

    public function categoriasFisicas()
    {
        return $this->belongsToMany(
            CategoriaFisica::class,
            'categoria_fisica_libro_fisico',
            'libro_fisico_id',
            'categoria_fisica_id'
        )->withTimestamps();
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'libro_fisico_id');
    }

    public function prestamosActivos()
    {
        return $this->hasMany(Prestamo::class, 'libro_fisico_id')->activos();
    }

    /**
     * Usa el conteo precargado por withCount() si está disponible (evita N+1 en listados).
     */
    public function getEjemplaresPrestadosAttribute(): int
    {
        return $this->attributes['ejemplares_prestados_count'] ?? $this->prestamosActivos()->count();
    }

    public function getEjemplaresDisponiblesAttribute(): int
    {
        return max(0, $this->cantidad_ejemplares - $this->ejemplares_prestados);
    }
}
