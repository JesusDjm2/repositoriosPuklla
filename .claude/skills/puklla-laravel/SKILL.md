---
name: puklla-laravel
description: Convenciones de Laravel 10 + Blade + Livewire 3 para el proyecto repositoriosPuklla (Asociación Pukllasunchis). Usar al crear o modificar controladores, modelos, rutas, migraciones, consultas Eloquent, vistas Blade, jobs o servicios en este repositorio.
---

# Laravel en repositoriosPuklla

## Stack real (no asumir otro)
- Laravel 10 (`^10.10`), PHP 8.2, Livewire 3.5, MySQL `utf8mb4_unicode_ci`.
- Hosting compartido (SiteGround) por FTP: **no hay SSH, no hay `php artisan` en producción**. Toda migración nueva necesita además un `.sql` equivalente en `database/` (`CREATE TABLE IF NOT EXISTS` + insert en `migrations`) para que el usuario lo ejecute en phpMyAdmin.
- `QUEUE_CONNECTION=database`: los jobs solo corren si existe el cron `queue:work --stop-when-empty`. Si algo depende de un job, el código debe tolerar que todavía no se haya procesado (ej. `whereNotNull('drive_id')`).
- Archivos en Google Drive (cuenta de servicio), no en `storage/`.

## Proyectos y layouts
- `App\Services\ProyectoDetector` decide el proyecto (eesp, kawsay, radio, sisipedia) por **nombre de ruta**. Toda ruta pública nueva de un proyecto se agrega ahí, o el logo/color del menú salen mal.
- Público: `layouts.padre` / `layouts.hub` → Bootstrap **5.3.3** + Font Awesome **6.0.0-beta3** + GSAP 3.12.5.
- Admin: `layouts.app` (`@section('contenido')`) → Bootstrap **4.5.2** + Font Awesome **4.7** (`fa fa-*`, `mr-1`, `badge-warning`, `text-right`). No mezclar clases de BS5 (`me-1`, `text-end`) en vistas admin ni al revés.
- FA 6 beta3 no tiene íconos recientes (`fa-folder-tree`, `fa-face-meh`…). Usar alternativas antiguas (`fa-sitemap`, `fa-binoculars`).

## Blade
- **Nunca `use` dentro de `@php`**: rompe las vistas compiladas. Usar el nombre completo o `$Clase = \App\...\Clase::class;`.
- `{{ }}` siempre; `{!! !!}` solo con HTML generado por nosotros y escapado antes (ver `BusquedaController::resaltar`).
- Parciales reutilizables en `partials/`; CSS/JS de un parcial incluido varias veces va dentro de `@once`.
- Los nombres de categorías traen prefijo de ontología ("1.3.1 Agua"): mostrar `display_name`.

## Eloquent y rendimiento
- Evitar N+1: `with()` con columnas acotadas (`'files:id,category_id,tipo'`) y `withCount()` en vez de `->count()` dentro de un `@foreach`.
- Árboles grandes (Sisipedia ~1500 nodos): cargar por niveles (lazy) y no renderizar todo.
- `LIKE` ya ignora tildes por la collation; para búsquedas de varias palabras, AND por palabra.
- Paginar siempre los listados públicos. Para colecciones en memoria, `LengthAwarePaginator` manual.
- Verificar el número de consultas con `DB::enableQueryLog()` o Debugbar cuando se toque un listado.

## Rutas y controladores
- Rutas con nombre; las públicas de búsqueda con `throttle`. Admin con `middleware(['auth','role:...'])` (Spatie).
- Validar con `$request->validate()` o un FormRequest; nunca confiar en ids del formulario sin `findOrFail` + autorización.
- Lógica reutilizable en `app/Services`, no en la vista ni duplicada entre controladores.
- Si una tabla nueva puede no existir aún en producción, envolver su uso en `Schema::hasTable()` / try-catch para no tumbar la página.

## Git en este repo
- El árbol suele tener cambios ajenos sin commit (Taqe, material didáctico). Hacer commit **solo de los archivos propios de la tarea**; si `routes/web.php` tiene cambios ajenos, preparar la versión HEAD + bloque nuevo y subir esa versión, no la del árbol de trabajo.
- Mensajes de commit en español, formato `Proyecto: qué cambió`.
