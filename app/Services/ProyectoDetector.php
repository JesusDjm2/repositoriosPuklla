<?php

namespace App\Services;

/**
 * Determina a qué proyecto público (Escuela/EESP, Kawsay, Radio o Sisipedia)
 * pertenece la petición actual, a partir del nombre de la ruta activa.
 *
 * Es la única fuente de verdad para esta decisión: tanto el menú principal
 * (resources/views/livewire/menu.blade.php) como el color de marca del body
 * (resources/views/layouts/padre.blade.php) la consultan, en vez de comparar
 * cada uno por su cuenta contra nombres de ruta sueltos. Si una ruta nueva
 * pertenece a alguno de estos proyectos, su nombre debe añadirse aquí para
 * que el menú y el color no se "rompan" al navegar fuera del inicio.
 */
class ProyectoDetector
{
    private const RUTAS_POR_PROYECTO = [
        'eesp' => [
            'eesppukllasunchis',
            'libros',
            'libros.show',
            'videos',
            'videoscat.show',
            'categorias.show',
            'detallesCat',
            'catalogo-fisico',
        ],
        'kawsay' => [
            'kawsay',
            'kawsaylibros.lista',
            'kawsaylibros.show',
            'kawsayvideos.lista',
            'kawsayvideos.show',
            'kawsaycategorias.show',
            'kawsayvideoscat.show',
        ],
        'radio' => [
            'cancionesPuklla',
        ],
        'sisipedia' => [
            'index',
            'public.sisi',
            'public.categoria.sisi',
            'sisipedia.search',
            'sisipedia.categories.show',
        ],
    ];

    private const CLASE_BODY_POR_PROYECTO = [
        'eesp' => 'is-biblioteca',
        'kawsay' => 'is-kawsay',
        'radio' => 'is-radio',
        'sisipedia' => 'is-sisipedia',
    ];

    /** Clave del proyecto activo ('eesp', 'kawsay', 'sisipedia') o null si la ruta no pertenece a ninguno. */
    public static function actual(): ?string
    {
        $nombreRuta = request()->route()?->getName();

        if ($nombreRuta === null) {
            return null;
        }

        foreach (self::RUTAS_POR_PROYECTO as $proyecto => $rutas) {
            if (in_array($nombreRuta, $rutas, true)) {
                return $proyecto;
            }
        }

        return null;
    }

    /** Clase CSS (`is-<proyecto>`) que fija el color de marca del proyecto activo en el body. */
    public static function bodyClass(): string
    {
        return self::CLASE_BODY_POR_PROYECTO[self::actual()] ?? '';
    }
}
