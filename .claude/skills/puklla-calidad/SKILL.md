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

## JavaScript
- Vanilla JS + delegación de eventos; nada de dependencias nuevas sin necesidad.
- `fetch` con `AbortController` y debounce en búsquedas; manejar error y estado de carga.
- El contenido debe funcionar sin JS (formularios GET reales, enlaces reales); JS solo mejora.

## Seguridad
- Escapar todo con `{{ }}`; validar entradas; autorizar acciones admin con middleware de rol.
- No exponer ids internos ni datos personales en vistas públicas.
- Nunca subir `.env`, `storage/`, `vendor/`, `.git/`. Nunca escribir credenciales en archivos del repo.

## Lista antes de decir "listo"
1. `php -l` en cada PHP tocado; `php artisan view:clear` y cargar la vista (un Blade roto solo falla al renderizar).
2. Probar la página en local (http://localhost/repositoriosPuklla/public/...) con status 200 y sin errores en consola.
3. Revisar responsive según la habilidad `puklla-responsive-ux`.
4. Si se tocó una consulta, comparar número de consultas y peso de la página antes/después.
5. `git diff` propio revisado línea por línea; commit solo de los archivos de la tarea.
6. Decir con honestidad qué se verificó de verdad y qué no se pudo probar.

## Deploy (solo cuando el usuario dice "sube" / "sube a producción")
- Ver memoria `project_deploy_ftp`. Por cada archivo: descargar el de producción al scratchpad, `diff` contra la versión anterior en git (si difiere, alguien lo cambió en el servidor: avisar antes de pisarlo), subir con `curl -T`, volver a descargar y comparar.
- Si hay tablas nuevas, recordar al usuario ejecutar el `.sql` en phpMyAdmin **antes** o junto con el código.
