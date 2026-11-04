-- Sisipedia: tabla de búsquedas sin resultados (equivale a la migración
-- 2026_11_04_000001_create_sisipedia_busquedas_sin_resultados_table).
-- Ejecutar una sola vez en phpMyAdmin sobre la base de producción.

CREATE TABLE IF NOT EXISTS `sisipedia_busquedas_sin_resultados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `termino` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `veces` int unsigned NOT NULL DEFAULT '1',
  `ultima_vez` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sisipedia_busquedas_sin_resultados_termino_unique` (`termino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar la migración para que `php artisan migrate` no intente crearla de nuevo.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_04_000001_create_sisipedia_busquedas_sin_resultados_table',
       COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations`
  WHERE `migration` = '2026_11_04_000001_create_sisipedia_busquedas_sin_resultados_table'
);
