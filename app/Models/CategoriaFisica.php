<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaFisica extends Model
{
    use HasFactory;

    protected $table = 'categorias_fisicas';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
    ];

    /**
     * Tipos de material físico posibles para un libro (libros_fisicos.tipo_material).
     * Ajustar si la institución maneja otras categorías de material.
     */
    public const TIPOS_MATERIAL = [
        'libro' => 'Libro',
        'revista' => 'Revista',
        'tesis' => 'Tesis',
        'folleto' => 'Folleto',
        'separata' => 'Separata',
    ];

    private const COLORES_TIPO_MATERIAL = [
        'libro' => '#0ea5e9',
        'revista' => '#f97316',
        'tesis' => '#7c3aed',
        'folleto' => '#22c55e',
        'separata' => '#64748b',
    ];

    public static function nombreParaTipoMaterial(?string $tipo): ?string
    {
        return $tipo ? (self::TIPOS_MATERIAL[$tipo] ?? $tipo) : null;
    }

    public static function colorForTipoMaterial(?string $tipo): string
    {
        return self::COLORES_TIPO_MATERIAL[$tipo ?? ''] ?? '#6c757d';
    }

    public function librosFisicos()
    {
        return $this->belongsToMany(
            LibroFisico::class,
            'categoria_fisica_libro_fisico',
            'categoria_fisica_id',
            'libro_fisico_id'
        )->withTimestamps();
    }

    /**
     * Color de acento asociado a un código de categoría física (DEW/TEX/REF/...),
     * usado para colorear tarjetas, menús y encabezados de forma consistente.
     */
    public static function colorForCodigo(?string $codigo): string
    {
        $codigo = $codigo ?? '';

        return match (true) {
            str_starts_with($codigo, 'DEW-000') => '#6366f1',
            str_starts_with($codigo, 'DEW-100') => '#a855f7',
            str_starts_with($codigo, 'DEW-200') => '#eab308',
            str_starts_with($codigo, 'DEW-300') => '#22c55e',
            str_starts_with($codigo, 'DEW-400') => '#14b8a6',
            str_starts_with($codigo, 'DEW-500') => '#06b6d4',
            str_starts_with($codigo, 'DEW-600') => '#f97316',
            str_starts_with($codigo, 'DEW-700') => '#ef4444',
            str_starts_with($codigo, 'DEW-800') => '#ec4899',
            str_starts_with($codigo, 'DEW-900') => '#92400e',
            str_starts_with($codigo, 'TEX-PRI') => '#10b981',
            str_starts_with($codigo, 'TEX-SEC') => '#0ea5e9',
            str_starts_with($codigo, 'REF')     => '#f59e0b',
            str_starts_with($codigo, 'DOC')     => '#64748b',
            str_starts_with($codigo, 'ASO')     => '#7c3aed',
            str_starts_with($codigo, 'SW')      => '#be185d',
            default                              => '#cb9042',
        };
    }
}
