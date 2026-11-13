-- ⚠️ NO EJECUTAR: reemplazado por migracion_produccion_taqe_material_20261112.sql
--    (este usa ADD COLUMN IF NOT EXISTS, que solo existe en MariaDB, falla si una
--    llave foránea ya existe e inserta trabajos de Taq'e de ejemplo).
-- =============================================================================
-- MIGRACIÓN / SINCRONIZACIÓN DE BIBLIOTECA A PRODUCCIÓN (repositoriosPuklla)
-- FECHA: 26/10/2026
-- INSTRUCCIONES:
--   1. Realizar un backup de seguridad previo en producción antes de ejecutar.
--   2. Ejecutar este script en phpMyAdmin o consola MySQL de repositoriosPuklla.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- -----------------------------------------------------------------------------
-- 1. NUEVAS TABLAS: MÓDULO MATERIAL DIDÁCTICO
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categorias_material_didactico` (
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

CREATE TABLE IF NOT EXISTS `materiales_didacticos` (
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

-- -----------------------------------------------------------------------------
-- 2. NUEVAS TABLAS: MÓDULO REPOSITORIO TAQ'E (INVESTIGACIÓN Y TESIS)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `taqe_categorias` (
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

CREATE TABLE IF NOT EXISTS `taqe_trabajos` (
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

-- -----------------------------------------------------------------------------
-- 3. ACTUALIZACIÓN DE TABLAS EXISTENTES: libros_fisicos y prestamos
-- -----------------------------------------------------------------------------

-- 3.1. Añadir columnas a libros_fisicos (categoría directa y códigos de barra de ejemplares)
ALTER TABLE `libros_fisicos`
  ADD COLUMN IF NOT EXISTS `categoria_fisica_id` bigint(20) unsigned DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `etiquetas_ejemplares` longtext DEFAULT NULL AFTER `cantidad_ejemplares`;

-- Clave foránea en libros_fisicos (si ya existiera, MySQL la omitirá si se añade condicionalmente)
ALTER TABLE `libros_fisicos`
  ADD CONSTRAINT `libros_fisicos_categoria_fisica_id_foreign`
  FOREIGN KEY (`categoria_fisica_id`) REFERENCES `categorias_fisicas` (`id`) ON DELETE SET NULL;

-- 3.2. Modificar prestamos para soportar préstamo de material didáctico y número de ejemplar
ALTER TABLE `prestamos`
  MODIFY COLUMN `libro_fisico_id` bigint(20) unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `material_didactico_id` bigint(20) unsigned DEFAULT NULL AFTER `libro_fisico_id`,
  ADD COLUMN IF NOT EXISTS `numero_ejemplar` varchar(50) DEFAULT NULL AFTER `material_didactico_id`,
  ADD COLUMN IF NOT EXISTS `cantidad` int(10) unsigned NOT NULL DEFAULT 1 AFTER `numero_ejemplar`;

ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_material_didactico_id_foreign`
  FOREIGN KEY (`material_didactico_id`) REFERENCES `materiales_didacticos` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 4. DATOS INICIALES Y SEMILLA
-- -----------------------------------------------------------------------------

-- 4.1. Categorías de material didáctico iniciales
INSERT INTO `categorias_material_didactico` (`id`, `nombre`, `codigo`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Matemática', 'MAT', NULL, NOW(), NOW()),
(2, 'Personal Social', 'PSO', NULL, NOW(), NOW()),
(3, 'Ciencia y Tecnología', 'CYT', NULL, NOW(), NOW()),
(4, 'Comunicación', 'COM', NULL, NOW(), NOW()),
(5, 'Artes', 'ART', NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- 4.2. Categorías de repositorio Taq'e
INSERT INTO `taqe_categorias` (`id`, `nombre`, `slug`, `descripcion`, `color`, `icono`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Saberes y Prácticas Ancestrales', 'saberes-ancestrales', 'Investigaciones sobre la siembra, abuelos, cosmovisión y el ciclo agrofestivo.', '#c78e41', 'fa-leaf', 1, 1, NOW(), NOW()),
(2, 'Estrategias y Materiales Pedagógicos', 'materiales-pedagogicos', 'Herramientas de planificación, programación y cartillas para el aula bilingüe.', '#2e7d32', 'fa-book-reader', 2, 1, NOW(), NOW()),
(3, 'Cuentos, Mitos y Tradición Oral', 'tradicion-oral', 'Narraciones, relatos orales y leyendas recopiladas en quechua y castellano.', '#1565c0', 'fa-commenting', 3, 1, NOW(), NOW()),
(4, 'Salud Comunitaria y Plantas Medicinales', 'plantas-medicinales', 'Saberes médicos ancestrales, curación tradicional y uso de flora medicinal.', '#6a1b9a', 'fa-heartbeat', 4, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- 4.3. Categorías físicas añadidas recientemente (IDs 107-117)
INSERT INTO `categorias_fisicas` (`id`, `nombre`, `codigo_dewey`, `descripcion`, `codigo`, `created_at`, `updated_at`) VALUES
(107, 'Lenguaje', NULL, 'Lenguaje, enseñanza del lenguaje, lenguaje integral, sociologia del lenguaje, conceptos y procedimientos.', '400', NOW(), NOW()),
(108, 'Educación bilingue/ quechua', NULL, 'bilingue en quechua, enseñanza de las costumbres y saberes ancestrales multicultural', '370', NOW(), NOW()),
(109, 'Relatos andinos y tradicionales', NULL, 'relatos en el idioma quechua o otras lenguas originarias, relatos tradicionales en español para niños', '498', NOW(), NOW()),
(110, 'politicas educativas', NULL, 'las politicas educativas en el Perú; como la linia politica afecta a la educación', 'DEW379', NOW(), NOW()),
(111, 'saberes ancestrales, agricultura en el ande, los quechuas', NULL, 'saberes ancestrales, cosmovisión andina, ritualidad', 'DEW 572', NOW(), NOW()),
(112, 'Cultura Andina', NULL, NULL, 'DEW 985', NOW(), NOW()),
(113, 'idioma quechua', NULL, 'textos de enseñansa del quechua', '425', NOW(), NOW()),
(114, 'Educación intercultural', NULL, 'son libros que comentan experiencias de educación intercultural tomando en cuenta el territorio y costumbres.', '375', NOW(), NOW()),
(115, 'Etnohistoria', NULL, 'Historia de grupos etnicos con tradición oral', 'DEW 920', NOW(), NOW()),
(116, 'investigación cualitativa, cuantitativa, formatos', NULL, 'Pautas relacionadas a la investigación educativa y el curso en si.', '150', NOW(), NOW()),
(117, 'Antropología cultural y sociología de las instituciones/cultura', NULL, 'Por su crítica a la modernidad, el análisis de las cosmovisiones y la diversidad de saberes comunitarios y de pueblos originarios.', 'DWE-306', NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- 4.4. Trabajos iniciales del Repositorio Taq'e
INSERT INTO `taqe_trabajos` (`id`, `categoria_id`, `titulo`, `slug`, `autores`, `descripcion`, `formato_principal`, `idioma`, `año_periodo`, `ciclo_carrera`, `archivo_pdf`, `archivo_audio`, `url_video`, `portada`, `vistas`, `descargas`, `destacado`, `estado`, `created_at`, `updated_at`) VALUES
(1, 1, 'Los Apus tienen vida', 'los-apus-tienen-vida', 'Ruth Mery Quispicusi, Bill Fredy Arones', 'Investigación y animación en stopmotion que visibiliza la relación comunitaria con los Apus tutelares y el respeto a la Madre Tierra.', 'video', 'castellano', '2024', 'II Ciclo Educación Inicial EIB', NULL, NULL, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', NULL, 0, 0, 1, 'publicado', NOW(), NOW()),
(2, 2, 'Cartillas de Señas e Indicadores Naturales', 'cartillas-de-senas-e-indicadores-naturales', 'Franklin Medina', 'Cartillas pedagógicas que sistematizan las señas del clima, floración de plantas y comportamiento animal para la siembra comunitaria.', 'pdf', 'bilingue', '2024', 'IV Ciclo Educación Primaria EIB', NULL, NULL, NULL, NULL, 0, 0, 1, 'publicado', NOW(), NOW()),
(3, 1, 'Wankay de despedida a la siembra de maíz', 'wankay-de-despedida-a-la-siembra-de-maiz', 'Estudiantes de Práctica Pedagógica EIB', 'Registro sonoro y canto tradicional quechua de despedida al trabajo colectivo de siembra en las parcelas familiares.', 'audio', 'quechua', '2022', 'III Ciclo EIB', NULL, NULL, NULL, NULL, 0, 0, 1, 'publicado', NOW(), NOW()),
(4, 4, 'Curación de Huesos a través de plantas medicinales', 'curacion-de-huesos-a-traves-de-plantas-medicinales', 'Jakeline Aparicio, Luz Mery Conza', 'Documentación de prácticas de curación tradicional y aplicación de plantas medicinales para fracturas y dolores articulares.', 'pdf', 'bilingue', '2024', 'V Ciclo Educación Inicial EIB', NULL, NULL, NULL, NULL, 0, 0, 1, 'publicado', NOW(), NOW()),
(5, 3, 'Cuentos y Leyendas de Nuestras Comunidades', 'cuentos-y-leyendas-de-nuestras-comunidades', 'Judith Turpo, Maribel Coyo', 'Compilado ilustrado de relatos orales transmitidos por abuelas y abuelos en comunidades quechuahablantes del Cusco.', 'multimedia', 'bilingue', '2023', 'II Ciclo Educación Primaria EIB', NULL, NULL, NULL, NULL, 0, 0, 0, 'publicado', NOW(), NOW()),
(6, 3, 'Historia de la Laguna Auquis Anyarate', 'historia-de-la-laguna-auquis-anyarate', 'Abraham Gamarra', 'Testimonio en lengua originaria quechua sobre los misterios, memoria y encantos de la laguna Auquis.', 'video', 'quechua', '2022', 'I Ciclo Quechua', NULL, NULL, NULL, NULL, 0, 0, 0, 'publicado', NOW(), NOW()),
(7, 4, 'Narraciones sobre Plantas Medicinales Autóctonas', 'narraciones-sobre-plantas-medicinales-autoctonas', 'Felipe Huisa', 'Sistematización y diagramación gráfica de recetas ancestrales a base de hierbas medicinales de los valles andinos.', 'pdf', 'quechua', '2023', 'III Ciclo EIB', NULL, NULL, NULL, NULL, 0, 0, 0, 'publicado', NOW(), NOW()),
(8, 1, 'El Chúyay de las ovejas: Ritos de fertilidad y protección', 'el-chuyay-de-las-ovejas-ritos-de-fertilidad-y-proteccion', 'Marina Chocata, María Rosa Challhua, Verónica Qqenta', 'Registro sonoro y etnográfico de los cantos y rituales de agradecimiento al rebaño en el ciclo ganadero andino.', 'audio', 'quechua', '2024', 'IV Ciclo Educación Inicial EIB', NULL, NULL, NULL, NULL, 0, 0, 1, 'publicado', NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
