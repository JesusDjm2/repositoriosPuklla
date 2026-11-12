-- ═══════════════════════════════════════════════════════════════════════════
-- Kawsay: se retira "Comparte tu experiencia" (envíos de visitantes).
-- Equivale a database/migrations/2026_11_11_000002_drop_kawsay_aportes_table.php
-- Se puede ejecutar más de una vez.
--
-- Cada sentencia nombra la base de la web (db2k9t2tr5p3zg) para no depender de la
-- base seleccionada en phpMyAdmin (si no, se ejecuta en information_schema: error #1044).
-- ═══════════════════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS `db2k9t2tr5p3zg`.`kawsay_aportes`;

INSERT INTO `db2k9t2tr5p3zg`.`migrations` (`migration`, `batch`)
SELECT '2026_11_11_000002_drop_kawsay_aportes_table', t.siguiente
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS siguiente FROM `db2k9t2tr5p3zg`.`migrations`) AS t
WHERE NOT EXISTS (
    SELECT 1 FROM `db2k9t2tr5p3zg`.`migrations` WHERE `migration` = '2026_11_11_000002_drop_kawsay_aportes_table'
);

-- Verificación: debe dar 0
SELECT COUNT(*) AS tabla_kawsay_aportes FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'kawsay_aportes';
