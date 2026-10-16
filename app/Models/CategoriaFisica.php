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
     *
     * Los códigos DEW-XXX son Dewey de 3 dígitos (ej. DEW-610, DEW-370), no solo
     * las centenas exactas (DEW-000, DEW-100...) — un str_starts_with contra la
     * centena exacta solo reconocía esas 10 y mandaba a todo el resto (la
     * inmensa mayoría de categorías reales) al color por defecto, repitiéndolo
     * en casi todas. Se agrupa por la centena real del código (610 → 600), que
     * es además el criterio estándar de las 10 clases principales Dewey.
     */
    public static function colorForCodigo(?string $codigo): string
    {
        $codigo = $codigo ?? '';

        if (preg_match('/^DEW-(\d{3})$/', $codigo, $coincidencia)) {
            $centena = intdiv((int) $coincidencia[1], 100) * 100;

            return match ($centena) {
                0 => '#6366f1',
                100 => '#a855f7',
                200 => '#eab308',
                300 => '#22c55e',
                400 => '#14b8a6',
                500 => '#06b6d4',
                600 => '#f97316',
                700 => '#ef4444',
                800 => '#ec4899',
                900 => '#92400e',
                default => '#cb9042',
            };
        }

        return match (true) {
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
