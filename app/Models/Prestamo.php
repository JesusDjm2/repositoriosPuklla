<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = [
        'libro_fisico_id',
        'nombre_prestatario',
        'dni_prestatario',
        'contacto_prestatario',
        'fecha_prestamo',
        'fecha_limite',
        'fecha_devolucion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_prestamo' => 'date',
        'fecha_limite' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function libroFisico()
    {
        return $this->belongsTo(LibroFisico::class, 'libro_fisico_id');
    }

    /**
     * El estado no se guarda en columna: se calcula siempre a partir de
     * fecha_devolucion y fecha_limite para evitar inconsistencias.
     */
    public function getEstadoAttribute(): string
    {
        if ($this->fecha_devolucion !== null) {
            return 'devuelto';
        }

        return $this->fecha_limite->lt(today()) ? 'atrasado' : 'prestado';
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'atrasado' => 'Atrasado',
            'devuelto' => 'Devuelto',
            default => 'Prestado',
        };
    }

    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'atrasado' => 'badge-danger',
            'devuelto' => 'badge-success',
            default => 'badge-warning',
        };
    }

    public function scopeActivos($query)
    {
        return $query->whereNull('fecha_devolucion');
    }

    public function scopeDevueltos($query)
    {
        return $query->whereNotNull('fecha_devolucion');
    }

    public function scopeAtrasados($query)
    {
        return $query->whereNull('fecha_devolucion')
            ->whereDate('fecha_limite', '<', today());
    }
}
