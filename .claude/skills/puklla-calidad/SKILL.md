---
name: puklla-calidad
description: Buenas prácticas de programación y lista de control de calidad para repositoriosPuklla — antes de dar por terminada una tarea, hacer commit o subir a producción por FTP. Usar al terminar cualquier cambio de código, al revisar código propio o ajeno, y siempre antes de un deploy.
---

# Calidad y buenas prácticas en repositoriosPuklla

## Principios
- **Cambio mínimo y enfocado**: tocar solo lo que la tarea pide; no reformatear archivos enteros ni "aprovechar" para refactorizar sin avisar.
- **Leer antes de escribir**: buscar si ya existe un parcial, servicio, variable CSS o helper que resuelva lo mismo (`partials/`, `app/Services`, `public/css/estilos.css`). Reutilizar antes que duplicar.
- **Nombres en español claros** y consistentes con el código vecino (`registros`, `aportaciones`, `sugerencias`).
- **Comentar el porqué**, no el qué; especialmente decisiones no obvias (por qué un `whereNotNull`, por qué lazy-load).
- Sin código muerto, sin `dd()`, `console.log` ni `dump()` olvidados.

## CSS
- Usar tokens existentes (`--project-accent`, `--puklla-ease`, `--puklla-nav-h`, `--puklla-z-*`) antes de crear valores nuevos.
- Controlar la especificidad: antes de usar `!important`, buscar qué regla gana y por qué. Si hace falta, documentarlo.
- Estilos de una sola vista con prefijo propio (`.sisi-*`, `.kbus-*`) para no romper otras páginas.

### Panel admin (`layouts.app`) — hoja única
- **Toda** la CSS del panel sale de `resources/sass/admin.scss` (Vite). Ver el orden y las reglas en su cabecera.
- **Prohibido** `<style>` en vistas del panel y `style="..."` con colores. Lo propio de un proyecto va en `resources/sass/admin/modules/_<proyecto>.scss`.
- Colores siempre con `var(--adm-*)` (`resources/sass/admin/_tokens.scss`). El tema oscuro se resuelve cambiando tokens, nunca con `[data-theme="dark"] .x { … !important }`.
- Un color que viene de la base (color de un área/categoría) se pasa como variable: `style="--area-color: {{ $area->color }}"` y la clase lo usa.
- Reutilizar `.adm-page-head`, `.adm-toolbar`, `.adm-chip`, `.adm-thumb`, `.adm-icon-tile` antes de crear otra cosa.
- Comprobar: `npm run revisar:estilos` (falla si hay `<style>` o colores en línea). Tras cambiar SCSS: `npm run build` y subir `public/build/` (manifest + assets nuevos).

## JavaScript
- Vanilla JS + delegación de eventos; nada de dependencias nuevas sin necesidad.
- `fetch` con `AbortController` y debounce en búsquedas; manejar error y estado de carga.
- El contenido debe funcionar sin JS (formularios GET reales, enlaces reales); JS solo mejora.

## Seguridad
- Escapar todo con `{{ }}`; validar entradas; autorizar acciones admin con middleware de rol.
- HTML que escribe un usuario (editor enriquecido) solo se muestra con `{!! \App\Services\HtmlSeguro::limpiar($x) !!}`, y se limpia también al guardar (mutador del modelo, ver `Aportacion::setDetalleAttribute`).
- No exponer ids internos ni datos personales en vistas públicas.
- Nunca subir `.env`, `storage/`, `vendor/`, `.git/`. Nunca escribir credenciales en archivos del repo.

## Lista antes de decir "listo"
1. `php -l` en cada PHP tocado; `php artisan view:clear` y cargar la vista (un Blade roto solo falla al renderizar).
2. Probar la página en local (http://localhost/repositoriosPuklla/public/...) con status 200 y sin errores en consola.
3. Revisar responsive según la habilidad `puklla-responsive-ux`. Si se tocó el panel: `npm run revisar:estilos` y revisar en tema claro **y** oscuro.
4. Si se tocó una consulta, comparar número de consultas y peso de la página antes/después.
5. `git diff` propio revisado línea por línea; commit solo de los archivos de la tarea.
6. Decir con honestidad qué se verificó de verdad y qué no se pudo probar.

## Deploy (solo cuando el usuario dice "sube" / "sube a producción")
- Ver memoria `project_deploy_ftp`. Por cada archivo: descargar el de producción al scratchpad, `diff` contra la versión anterior en git (si difiere, alguien lo cambió en el servidor: avisar antes de pisarlo), subir con `curl -T`, volver a descargar y comparar.
- Si hay tablas nuevas, recordar al usuario ejecutar el `.sql` en phpMyAdmin **antes** o junto con el código.
