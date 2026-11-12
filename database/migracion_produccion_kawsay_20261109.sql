DELIMITER $$
DROP PROCEDURE IF EXISTS kawsay_20261109 $$
CREATE PROCEDURE kawsay_20261109()
BEGIN
    -- kawsay_archivos.portada / vistas
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME = 'portada') THEN
        ALTER TABLE `kawsay_archivos` ADD COLUMN `portada` varchar(255) NULL AFTER `peso_mb`;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME = 'vistas') THEN
        ALTER TABLE `kawsay_archivos` ADD COLUMN `vistas` int unsigned NOT NULL DEFAULT '0' AFTER `portada`;
    END IF;

    -- kawsay_temas.visitas / destacado
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_temas' AND COLUMN_NAME = 'visitas') THEN
        ALTER TABLE `kawsay_temas` ADD COLUMN `visitas` int unsigned NOT NULL DEFAULT '0' AFTER `orden`;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_temas' AND COLUMN_NAME = 'destacado') THEN
        ALTER TABLE `kawsay_temas` ADD COLUMN `destacado` tinyint(1) NOT NULL DEFAULT '0' AFTER `visitas`;
    END IF;

    -- kawsay_libros.kawsay_tema_id (libro del catálogo asignado a un tema)
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_libros' AND COLUMN_NAME = 'kawsay_tema_id') THEN
        ALTER TABLE `kawsay_libros`
            ADD COLUMN `kawsay_tema_id` bigint unsigned NULL AFTER `identificador`,
            ADD CONSTRAINT `kawsay_libros_kawsay_tema_id_foreign`
                FOREIGN KEY (`kawsay_tema_id`) REFERENCES `kawsay_temas` (`id`) ON DELETE SET NULL;
    END IF;

    -- kawsay_videos.kawsay_tema_id (video del catálogo asignado a un tema)
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_videos' AND COLUMN_NAME = 'kawsay_tema_id') THEN
        ALTER TABLE `kawsay_videos`
            ADD COLUMN `kawsay_tema_id` bigint unsigned NULL AFTER `categoria_id`,
            ADD CONSTRAINT `kawsay_videos_kawsay_tema_id_foreign`
                FOREIGN KEY (`kawsay_tema_id`) REFERENCES `kawsay_temas` (`id`) ON DELETE SET NULL;
    END IF;
END $$
DELIMITER ;

CALL kawsay_20261109();
DROP PROCEDURE kawsay_20261109;

-- Registrar la migración para que `php artisan migrate` no intente aplicarla de nuevo.
-- (Subconsulta: un SELECT con MAX() sin GROUP BY devuelve siempre una fila,
--  así que el WHERE NOT EXISTS no evitaba un duplicado al re-ejecutar.)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_09_000001_add_portada_contadores_destacado_to_kawsay', t.siguiente
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS siguiente FROM `migrations`) AS t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_09_000001_add_portada_contadores_destacado_to_kawsay');

-- Verificación: deben aparecer las 6 columnas nuevas
SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME IN ('portada', 'vistas'))
    OR (TABLE_NAME = 'kawsay_temas' AND COLUMN_NAME IN ('visitas', 'destacado'))
    OR (TABLE_NAME IN ('kawsay_libros', 'kawsay_videos') AND COLUMN_NAME = 'kawsay_tema_id'));
