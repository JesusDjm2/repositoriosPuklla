<?php

namespace App\Services;

/**
 * Los 8 tipos de contenido que puede tener cada tema del repositorio educativo
 * Kawsay. Quedan fijos en código (no vienen de base de datos) porque son la
 * jerarquía de pantallas del proyecto (con sus mimes/límite de subida
 * asociados), no datos que el admin edite. Las Áreas (App\Models\KawsayArea) y
 * los Temas (App\Models\KawsayTema) de cada una SÍ viven en base de datos,
 * administrables desde Kawsay-Áreas y Kawsay-Temas.
 */
class KawsayTaxonomia
{
    /** @return array<string, array{label:string, icono:string, mimes:array<int,string>, maxKb:int}> */
    public static function tiposContenido(): array
    {
        return [
            'ficha' => ['label' => 'Fichas', 'icono' => 'fa-clipboard-list', 'mimes' => ['pdf'], 'maxKb' => 102400],
            'teoria' => ['label' => 'Teoría', 'icono' => 'fa-book-open', 'mimes' => ['pdf'], 'maxKb' => 102400],
            'video' => ['label' => 'Videos', 'icono' => 'fa-play-circle', 'mimes' => ['mp4', 'webm', 'mov', 'm4v'], 'maxKb' => 307200],
            'presentacion' => ['label' => 'Presentaciones (PPT)', 'icono' => 'fa-desktop', 'mimes' => ['ppt', 'pptx'], 'maxKb' => 102400],
            'bibliografia' => ['label' => 'Bibliografía', 'icono' => 'fa-book', 'mimes' => ['pdf', 'doc', 'docx'], 'maxKb' => 102400],
            'galeria' => ['label' => 'Galería fotográfica', 'icono' => 'fa-camera', 'mimes' => ['jpg', 'jpeg', 'png', 'webp'], 'maxKb' => 10240],
            'experiencia' => ['label' => 'Experiencias', 'icono' => 'fa-comments', 'mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'], 'maxKb' => 51200],
            'mapa' => ['label' => 'Mapas', 'icono' => 'fa-map-marker-alt', 'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'], 'maxKb' => 20480],
        ];
    }

    public static function tipoContenido(string $tipoSlug): ?array
    {
        return self::tiposContenido()[$tipoSlug] ?? null;
    }

    /**
     * El panel de administración carga Font Awesome 4, donde algunos íconos de
     * tiposContenido() (nombres de FA 5/6, que sí usa el sitio público) no
     * existen y se ven como un cuadro vacío. Equivalente visible en FA 4.
     */
    public static function iconoAdmin(string $icono): string
    {
        return [
            'fa-clipboard-list' => 'fa-list-alt',
            'fa-book-open' => 'fa-book',
            'fa-map-marker-alt' => 'fa-map-marker',
        ][$icono] ?? $icono;
    }

    /** Formato de archivo (pdf/imagen/audio/video/doc, el que usa GoogleDriveService) según la extensión. */
    public static function formatoPorExtension(string $extension): ?string
    {
        $extension = strtolower($extension);

        $mapa = [
            'pdf' => 'pdf',
            'doc' => 'doc', 'docx' => 'doc', 'ppt' => 'doc', 'pptx' => 'doc',
            'jpg' => 'imagen', 'jpeg' => 'imagen', 'png' => 'imagen', 'webp' => 'imagen', 'gif' => 'imagen',
            'mp3' => 'audio', 'wav' => 'audio', 'ogg' => 'audio',
            'mp4' => 'video', 'webm' => 'video', 'mov' => 'video', 'm4v' => 'video',
        ];

        return $mapa[$extension] ?? null;
    }
}
