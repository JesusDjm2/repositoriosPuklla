-- 1. Material didáctico ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `db2k9t2tr5p3zg`.`categorias_material_didactico` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_material_didactico_nombre_unique` (`nombre`),
  UNIQUE KEY `categorias_material_didactico_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db2k9t2tr5p3zg`.`materiales_didacticos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_material_didactico_id` bigint(20) unsigned NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `materiales_didacticos_codigo_unique` (`codigo`),
  KEY `materiales_didacticos_categoria_material_didactico_id_foreign` (`categoria_material_didactico_id`),
  KEY `materiales_didacticos_nombre_index` (`nombre`),
  CONSTRAINT `materiales_didacticos_categoria_material_didactico_id_foreign` FOREIGN KEY (`categoria_material_didactico_id`) REFERENCES `categorias_material_didactico` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Taq'e (investigaciones y tesis EESP) ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `db2k9t2tr5p3zg`.`taqe_categorias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#c78e41',
  `icono` varchar(50) NOT NULL DEFAULT 'fa-folder',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `taqe_categorias_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db2k9t2tr5p3zg`.`taqe_trabajos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint(20) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(265) NOT NULL,
  `autores` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `formato_principal` enum('pdf','audio','video','imagen','multimedia') NOT NULL DEFAULT 'pdf',
  `idioma` enum('castellano','quechua','bilingue') NOT NULL DEFAULT 'castellano',
  `año_periodo` varchar(50) DEFAULT NULL,
  `ciclo_carrera` varchar(150) DEFAULT NULL,
  `archivo_pdf` varchar(255) DEFAULT NULL,
  `archivo_audio` varchar(255) DEFAULT NULL,
  `url_video` varchar(255) DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `vistas` int(10) unsigned NOT NULL DEFAULT 0,
  `descargas` int(10) unsigned NOT NULL DEFAULT 0,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('publicado','borrador') NOT NULL DEFAULT 'publicado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `taqe_trabajos_slug_unique` (`slug`),
  KEY `taqe_trabajos_categoria_id_foreign` (`categoria_id`),
  KEY `taqe_trabajos_formato_principal_index` (`formato_principal`),
  KEY `taqe_trabajos_idioma_index` (`idioma`),
  KEY `taqe_trabajos_estado_index` (`estado`),
  CONSTRAINT `taqe_trabajos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `taqe_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. libros_fisicos: categoría directa y códigos de ejemplares (si faltan) ──
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'libros_fisicos' AND COLUMN_NAME = 'categoria_fisica_id') = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`libros_fisicos` ADD COLUMN `categoria_fisica_id` bigint(20) unsigned DEFAULT NULL AFTER `id`', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'libros_fisicos' AND COLUMN_NAME = 'etiquetas_ejemplares') = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`libros_fisicos` ADD COLUMN `etiquetas_ejemplares` longtext DEFAULT NULL AFTER `cantidad_ejemplares`', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'libros_fisicos' AND COLUMN_NAME = 'categoria_fisica_id' AND REFERENCED_TABLE_NAME IS NOT NULL) = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`libros_fisicos` ADD CONSTRAINT `libros_fisicos_categoria_fisica_id_foreign` FOREIGN KEY (`categoria_fisica_id`) REFERENCES `categorias_fisicas` (`id`) ON DELETE SET NULL', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

-- 4. prestamos: préstamo de material didáctico, número de ejemplar y cantidad ─
-- Un préstamo de material didáctico no tiene libro: libro_fisico_id debe aceptar NULL.
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos' AND COLUMN_NAME = 'libro_fisico_id' AND IS_NULLABLE = 'NO') = 1,
  'ALTER TABLE `db2k9t2tr5p3zg`.`prestamos` MODIFY COLUMN `libro_fisico_id` bigint(20) unsigned DEFAULT NULL', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos' AND COLUMN_NAME = 'material_didactico_id') = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`prestamos` ADD COLUMN `material_didactico_id` bigint(20) unsigned DEFAULT NULL AFTER `libro_fisico_id`', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos' AND COLUMN_NAME = 'numero_ejemplar') = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`prestamos` ADD COLUMN `numero_ejemplar` varchar(50) DEFAULT NULL AFTER `material_didactico_id`', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos' AND COLUMN_NAME = 'cantidad') = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`prestamos` ADD COLUMN `cantidad` int(10) unsigned NOT NULL DEFAULT 1 AFTER `numero_ejemplar`', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @s = IF((SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos' AND COLUMN_NAME = 'material_didactico_id' AND REFERENCED_TABLE_NAME IS NOT NULL) = 0,
  'ALTER TABLE `db2k9t2tr5p3zg`.`prestamos` ADD CONSTRAINT `prestamos_material_didactico_id_foreign` FOREIGN KEY (`material_didactico_id`) REFERENCES `materiales_didacticos` (`id`) ON DELETE SET NULL', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

-- 5. Categorías iniciales (sin trabajos de ejemplo: esos se cargan desde el panel) ─
INSERT IGNORE INTO `db2k9t2tr5p3zg`.`categorias_material_didactico` (`id`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Matemática', 'MAT', NULL, NOW(), NOW()),
(2, 'Personal Social', 'PSO', NULL, NOW(), NOW()),
(3, 'Ciencia y Tecnología', 'CYT', NULL, NOW(), NOW()),
(4, 'Comunicación', 'COM', NULL, NOW(), NOW()),
(5, 'Artes', 'ART', NULL, NOW(), NOW());

INSERT IGNORE INTO `db2k9t2tr5p3zg`.`taqe_categorias` (`id`, `nombre`, `slug`, `descripcion`, `color`, `icono`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Saberes y Prácticas Ancestrales', 'saberes-ancestrales', 'Investigaciones sobre la siembra, abuelos, cosmovisión y el ciclo agrofestivo.', '#c78e41', 'fa-leaf', 1, 1, NOW(), NOW()),
(2, 'Estrategias y Materiales Pedagógicos', 'materiales-pedagogicos', 'Herramientas de planificación, programación y cartillas para el aula bilingüe.', '#2e7d32', 'fa-book-reader', 2, 1, NOW(), NOW()),
(3, 'Cuentos, Mitos y Tradición Oral', 'tradicion-oral', 'Narraciones, relatos orales y leyendas recopiladas en quechua y castellano.', '#1565c0', 'fa-commenting', 3, 1, NOW(), NOW()),
(4, 'Salud Comunitaria y Plantas Medicinales', 'plantas-medicinales', 'Saberes médicos ancestrales, curación tradicional y uso de flora medicinal.', '#6a1b9a', 'fa-heartbeat', 4, 1, NOW(), NOW());

-- Verificación: 4 tablas = 4, y las 3 columnas de prestamos = 3
SELECT COUNT(*) AS tablas_nuevas FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg'
  AND TABLE_NAME IN ('categorias_material_didactico', 'materiales_didacticos', 'taqe_categorias', 'taqe_trabajos');
SELECT COUNT(*) AS columnas_prestamos FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'db2k9t2tr5p3zg' AND TABLE_NAME = 'prestamos'
  AND COLUMN_NAME IN ('material_didactico_id', 'numero_ejemplar', 'cantidad');
