-- 1) Vista previa: filas afectadas (en local fueron 2: #216 y #218)
SELECT id, name, slug
FROM `categories`
WHERE name REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde';

-- 2) Slug: se corrige ANTES que el nombre, usando el nombre dañado como filtro.
--    "oacute" → "o", etc. (en un slug la tilde ya se escribe sin acento).
UPDATE `categories`
SET slug = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(slug,
        'aacute', 'a'), 'eacute', 'e'), 'iacute', 'i'), 'oacute', 'o'), 'uacute', 'u'), 'ntilde', 'n')
WHERE name REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde';

-- 3) Nombre y descripción: primero la forma correcta (con ;) y luego la incompleta.
UPDATE `categories`
SET name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,
           '&aacute;', 'á'), '&eacute;', 'é'), '&iacute;', 'í'), '&oacute;', 'ó'), '&uacute;', 'ú'), '&ntilde;', 'ñ'),
           '&aacute', 'á'), '&eacute', 'é'), '&iacute', 'í'), '&oacute', 'ó'), '&uacute', 'ú'), '&ntilde', 'ñ'),
    description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
           REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,
           '&aacute;', 'á'), '&eacute;', 'é'), '&iacute;', 'í'), '&oacute;', 'ó'), '&uacute;', 'ú'), '&ntilde;', 'ñ'),
           '&aacute', 'á'), '&eacute', 'é'), '&iacute', 'í'), '&oacute', 'ó'), '&uacute', 'ú'), '&ntilde', 'ñ')
WHERE name REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde'
   OR description REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde';

-- 4) Verificación: debe devolver 0 filas
SELECT id, name, slug
FROM `categories`
WHERE name REGEXP '&[a-zA-Z]+' OR description REGEXP '&[a-zA-Z]+acute';
