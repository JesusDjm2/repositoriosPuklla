-- ═══════════════════════════════════════════════════════════════════════════
-- Corrección: registra la migración de Kawsay en la tabla `migrations`.
--
-- La primera versión de migracion_produccion_kawsay_20261109.sql tenía roto el
-- INSERT final (le faltaba el nombre de la migración). Las 6 columnas SÍ se
-- crearon (el procedimiento corre antes de ese INSERT); solo faltó dejarla
-- registrada. Esto no afecta al sitio: es para que `php artisan migrate` no
-- intente crearlas otra vez algún día.
--
-- Seguro de ejecutar más de una vez.
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_09_000001_add_portada_contadores_destacado_to_kawsay', t.siguiente
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS siguiente FROM `migrations`) AS t
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_09_000001_add_portada_contadores_destacado_to_kawsay'
);

-- Verificación 1: debe devolver 1 fila
SELECT `id`, `migration`, `batch` FROM `migrations`
WHERE `migration` = '2026_11_09_000001_add_portada_contadores_destacado_to_kawsay';

-- Verificación 2: deben aparecer las 6 columnas nuevas
SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME IN ('portada', 'vistas'))
    OR (TABLE_NAME = 'kawsay_temas' AND COLUMN_NAME IN ('visitas', 'destacado'))
    OR (TABLE_NAME IN ('kawsay_libros', 'kawsay_videos') AND COLUMN_NAME = 'kawsay_tema_id'));
