-- Sisipedia: contadores de visitas (registros) y descargas (archivos).
-- Equivale a la migración 2026_11_04_000002_add_contadores_to_sisipedia_tables.
-- Ejecutar una sola vez en phpMyAdmin sobre la base de producción.
-- Hasta ejecutarlo, el sitio funciona igual pero no cuenta (ni muestra) nada.

ALTER TABLE `categories`
  ADD COLUMN `visitas` int unsigned NOT NULL DEFAULT '0' AFTER `is_active`;

ALTER TABLE `category_files`
  ADD COLUMN `descargas` int unsigned NOT NULL DEFAULT '0' AFTER `orden`;

ALTER TABLE `aportacion_archivos`
  ADD COLUMN `descargas` int unsigned NOT NULL DEFAULT '0';

-- Registrar la migración para que `php artisan migrate` no intente aplicarla de nuevo.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_000002_add_contadores_to_sisipedia_tables',
       COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations`
  WHERE `migration` = '2026_11_04_000002_add_contadores_to_sisipedia_tables'
);
