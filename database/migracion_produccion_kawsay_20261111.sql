-- ═══════════════════════════════════════════════════════════════════════════
-- Kawsay: descripción y nivel por archivo y videos de YouTube.
-- Equivale a database/migrations/2026_11_11_000001_add_descripcion_youtube_aportes_to_kawsay.php
--
-- Ejecutar en phpMyAdmin de producción ANTES (o junto) de subir el código.
-- Se puede ejecutar más de una vez. El código tolera que esto aún no exista.
-- ═══════════════════════════════════════════════════════════════════════════

DELIMITER $$
DROP PROCEDURE IF EXISTS kawsay_20261111 $$
CREATE PROCEDURE kawsay_20261111()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME = 'descripcion') THEN
        ALTER TABLE `kawsay_archivos` ADD COLUMN `descripcion` varchar(500) NULL AFTER `titulo`;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME = 'nivel') THEN
        ALTER TABLE `kawsay_archivos` ADD COLUMN `nivel` varchar(100) NULL AFTER `descripcion`;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos' AND COLUMN_NAME = 'youtube_id') THEN
        ALTER TABLE `kawsay_archivos` ADD COLUMN `youtube_id` varchar(20) NULL AFTER `drive_id`;
    END IF;
END $$
DELIMITER ;

CALL kawsay_20261111();
DROP PROCEDURE kawsay_20261111;

-- Registrar la migración (subconsulta: evita duplicado al re-ejecutar).
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_11_000001_add_descripcion_youtube_aportes_to_kawsay', t.siguiente
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS siguiente FROM `migrations`) AS t
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_11_000001_add_descripcion_youtube_aportes_to_kawsay'
);

-- Verificación: 3 columnas nuevas + 1 registro de migración
SELECT COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kawsay_archivos'
  AND COLUMN_NAME IN ('descripcion', 'nivel', 'youtube_id');
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` = '2026_11_11_000001_add_descripcion_youtube_aportes_to_kawsay';
