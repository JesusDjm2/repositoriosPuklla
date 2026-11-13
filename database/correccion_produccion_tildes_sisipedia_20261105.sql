-- 1) Vista previa: filas afectadas (en local fueron 2: #216 y #218)
SELECT id, name, slug
FROM `categories`
WHERE name REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde';

UPDATE `categories`
SET slug = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(slug,
        'aacute', 'a'), 'eacute', 'e'), 'iacute', 'i'), 'oacute', 'o'), 'uacute', 'u'), 'ntilde', 'n')
WHERE name REGEXP '&(a|e|i|o|u|A|E|I|O|U)acute|&(n|N)tilde';

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

SELECT id, name, slug
FROM `categories`
WHERE name REGEXP '&[a-zA-Z]+' OR description REGEXP '&[a-zA-Z]+acute';
