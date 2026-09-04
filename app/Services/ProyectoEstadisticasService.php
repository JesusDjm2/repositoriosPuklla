<?php

namespace App\Services;

use App\Models\Canciones;
use App\Models\CancionesCategoria;
use App\Models\Categoria;
use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayCategoria;
use App\Models\KawsayLibro;
use App\Models\KawsayTema;
use App\Models\KawsayVideo;
use App\Models\KawsayVideoCategoria;
use App\Models\Libro;
use App\Models\LibroFisico;
use App\Models\Prestamo;
use App\Models\sisipedia\Aportacion;
use App\Models\sisipedia\Category as SisipediaCategory;
use App\Models\Video;
use App\Models\VideoCategoria;
use Closure;
use Illuminate\Support\Collection;

/**
 * Único lugar de la app que sabe qué modelos de contenido pertenecen a cada
 * proyecto (identificado por su slug). Mantiene al modelo Proyecto desacoplado
 * de los modelos de biblioteca/radio/kawsay, que no tienen nada en común entre sí.
 */
class ProyectoEstadisticasService
{
    /** @return array<string, array<string, Closure>> */
    private function definiciones(): array
    {
        return [
            'eesp-pukllasunchis' => [
                'Libros digitales' => fn () => Libro::count(),
                'Libros físicos' => fn () => LibroFisico::count(),
                'Categorías' => fn () => Categoria::count(),
                'Préstamos activos' => fn () => Prestamo::activos()->count(),
                'Préstamos atrasados' => fn () => Prestamo::atrasados()->count(),
            ],
            'radio-pukllasunchis' => [
                'Videos' => fn () => Video::count(),
                'Categorías de video' => fn () => VideoCategoria::count(),
                'Canciones' => fn () => Canciones::count(),
                'Categorías de canciones' => fn () => CancionesCategoria::count(),
                'Registros Sisipedia' => fn () => SisipediaCategory::count(),
                'Aportaciones pendientes' => fn () => Aportacion::where('is_approved', false)->count(),
            ],
            'kawsay' => [
                'Áreas' => fn () => KawsayArea::count(),
                'Temas' => fn () => KawsayTema::count(),
                'Archivos' => fn () => KawsayArchivo::count(),
                'Libros' => fn () => KawsayLibro::count(),
                'Categorías' => fn () => KawsayCategoria::count(),
                'Videos' => fn () => KawsayVideo::count(),
                'Categorías de video' => fn () => KawsayVideoCategoria::count(),
            ],
        ];
    }

    /** @return Collection<string,int> etiqueta => valor, para un proyecto por slug. */
    public function paraProyecto(string $slug): Collection
    {
        $defs = $this->definiciones()[$slug] ?? [];

        return collect($defs)->map(fn (Closure $c) => $c());
    }

    /**
     * Un proyecto puede agrupar varios roles (p. ej. "Radio Pukllasunchis" =
     * sisicha + audios + videos): esto le da a cada mini-dashboard de rol solo
     * sus propias estadísticas, no las de los otros roles del mismo proyecto.
     *
     * @return array<string, array<string, Closure>>
     */
    private function definicionesPorRol(): array
    {
        $porProyecto = $this->definiciones();

        return [
            'biblioteca' => $porProyecto['eesp-pukllasunchis'] ?? [],
            'kawsay' => $porProyecto['kawsay'] ?? [],
            'videos' => [
                'Videos' => fn () => Video::count(),
                'Categorías de video' => fn () => VideoCategoria::count(),
            ],
            'audios' => [
                'Canciones' => fn () => Canciones::count(),
                'Categorías de canciones' => fn () => CancionesCategoria::count(),
            ],
            'sisicha' => [
                'Registros Sisipedia' => fn () => SisipediaCategory::count(),
                'Aportaciones pendientes' => fn () => Aportacion::where('is_approved', false)->count(),
            ],
        ];
    }

    /** @return Collection<string,int> etiqueta => valor, para el mini-dashboard de un rol. */
    public function paraRol(string $rol): Collection
    {
        $defs = $this->definicionesPorRol()[$rol] ?? [];

        return collect($defs)->map(fn (Closure $c) => $c());
    }

    /** @return array<string, Collection<string,int>> slug => conteos, para el dashboard principal. */
    public function paraTodos(array $slugs): array
    {
        return collect($slugs)->mapWithKeys(fn ($slug) => [$slug => $this->paraProyecto($slug)])->all();
    }
}
