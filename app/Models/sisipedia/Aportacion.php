<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Model;

class Aportacion extends Model
{
    protected $table = 'aportaciones';

    /** Roles en formulario general (portada) y en vista pública de cada registro */
    public const ROLES = ['Equipo Puklla', 'Docente', 'Líder', 'Niño/Estudiante'];

    /** Roles permitidos cuando el aporte está ligado a una categoría concreta (misma lista que en portada) */
    public const ROLES_CON_REGISTRO = ['Equipo Puklla', 'Docente', 'Líder', 'Niño/Estudiante'];

    /** Etiqueta visible para el rol almacenado como «Líder» */
    public const ROL_LIDER_ETIQUETA = 'Lidereza/Líder';

    protected $fillable = [
        'category_id',
        'rol_nombre',
        'nombre_ol',
        'titulo',
        'institucion',
        'ubicacion',
        'detalle',
        'imagen',
        'pdf',
        'doc',
        'audio',
        'video',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * El detalle llega del editor (Summernote) de un formulario PÚBLICO y se
     * muestra con {!! !!}: se limpia al guardar, venga de donde venga
     * (store, storeStandalone, update o cualquier código futuro).
     */
    public function setDetalleAttribute($value): void
    {
        $limpio = \App\Services\HtmlSeguro::limpiar($value);
        $this->attributes['detalle'] = $limpio === '' ? null : $limpio;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function archivos()
    {
        return $this->hasMany(AportacionArchivo::class);
    }

    public function archivosPorTipo(string $tipo)
    {
        return $this->archivos()->where('tipo', $tipo);
    }

    /** Texto para mostrar el rol (p. ej. Líder → Lidereza/Líder). */
    public static function etiquetaRol(?string $rolNombre): string
    {
        if ($rolNombre === 'Líder') {
            return self::ROL_LIDER_ETIQUETA;
        }

        return $rolNombre ?? '';
    }
}
