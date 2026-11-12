ALTER TABLE `categories`
  ADD COLUMN `visitas` int unsigned NOT NULL DEFAULT '0' AFTER `is_active`;

ALTER TABLE `category_files`
  ADD COLUMN `descargas` int unsigned NOT NULL DEFAULT '0' AFTER `orden`;

ALTER TABLE `aportacion_archivos`
  ADD COLUMN `descargas` int unsigned NOT NULL DEFAULT '0';

-- Registrar la migración para que `php artisan migrate` no intente aplicarla de nuevo.
-- (Subconsulta: un SELECT con MAX() sin GROUP BY devuelve siempre una fila,
--  así que el WHERE NOT EXISTS no evitaba un duplicado al re-ejecutar.)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_000002_add_contadores_to_sisipedia_tables', t.siguiente
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS siguiente FROM `migrations`) AS t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_04_000002_add_contadores_to_sisipedia_tables');
