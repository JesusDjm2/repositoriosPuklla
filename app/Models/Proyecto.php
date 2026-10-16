<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

class Proyecto extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'logo',
        'color',
        'descripcion',
        'orden',
        'activo',
    ];

    /**
     * Nombre de la ruta de Laravel que lleva a la página pública de cada
     * proyecto, por slug. No es un dato que el admin configure: cada entrada
     * se añade acá cuando un desarrollador construye la página pública de un
     * proyecto nuevo (igual que el ícono del menú en menu-admin.blade.php).
     */
    private const RUTAS_PUBLICAS = [
        'eesp-pukllasunchis' => 'eesppukllasunchis',
        // Sisipedia es la marca pública del repositorio de Radio Pukllasunchis
        // (no un proyecto aparte) — su página de aterrizaje es esta, no
        // 'cancionesPuklla' (que además requiere sesión iniciada).
        'radio-pukllasunchis' => 'index',
        'kawsay' => 'kawsay',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'proyecto_role');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden');
    }

    /** Consulta (no relación Eloquent) de los usuarios que administran este proyecto. */
    public function usuariosQuery(): Builder
    {
        $roleIds = $this->roles()->pluck('roles.id');

        return User::whereHas('roles', fn (Builder $q) => $q->whereIn('roles.id', $roleIds));
    }

    public function logoUrl(): string
    {
        return $this->logo ? asset('img/' . $this->logo) : asset('img/logoiesp.ico');
    }

    public function urlPublica(): ?string
    {
        $rutaNombre = self::RUTAS_PUBLICAS[$this->slug] ?? null;

        if (! $rutaNombre || ! Route::has($rutaNombre)) {
            return null;
        }

        return route($rutaNombre);
    }
}
