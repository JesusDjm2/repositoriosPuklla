#!/usr/bin/env node
// Revisa que las vistas del PANEL ADMIN respeten el sistema de estilos:
//   1. Ninguna vista lleva un bloque <style>.
//   2. Ningún style="" en línea fija colores (hex, rgb, hsl).
// Lo específico de una vista va en resources/sass/admin/modules/_<proyecto>.scss
// usando variables --adm-* (ver resources/sass/admin.scss).
//
// Uso:  npm run revisar:estilos            (falla con código 1 si hay errores)
//       npm run revisar:estilos -- --todo  (muestra también avisos de style="")
//
// Vista admin = extiende layouts.app, o es un parcial incluido solo desde el
// panel (lista PARCIALES_ADMIN). Las vistas públicas (layouts.padre/hub) no se
// revisan: tienen su propio sistema (public/css/estilos.css).
import fs from 'node:fs';
import path from 'node:path';

const RAIZ = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '..');
const VISTAS = path.join(RAIZ, 'resources', 'views');
const verTodo = process.argv.includes('--todo');

const PARCIALES_ADMIN = [
    'livewire/menu-admin.blade.php',
    'components/dash-card.blade.php',
    'components/hero-banner.blade.php',
    'components/hero-stat.blade.php',
    'components/module-card.blade.php',
    'components/project-card.blade.php',
    'components/data-table.blade.php',
    'components/form-field.blade.php',
    'components/page-header.blade.php',
    'partials/admin-breadcrumb.blade.php',
    'partials/biblioteca-fisica-crud-header.blade.php',
    'partials/biblioteca-virtual-crud-header.blade.php',
    'partials/biblioteca-grupo-tabs.blade.php',
    'partials/taqe-crud-header.blade.php',
    'sisichakuna/partials/sisipedia-admin-nav.blade.php',
    'sisichakuna/categorias/_parent-picker.blade.php',
    'sisichakuna/categorias/_parent-picker-item.blade.php',
    'sisichakuna/categorias/_file-inputs.blade.php',
    'sisichakuna/categorias/tree-item.blade.php',
    'kawsay/temas/partials/campos.blade.php',
    'kawsay/temas/partials/ficha.blade.php',
];

function* blades(dir) {
    for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
        if (e.name === '_notes' || e.name === 'vendor') continue;
        const p = path.join(dir, e.name);
        if (e.isDirectory()) yield* blades(p);
        else if (e.name.endsWith('.blade.php')) yield p;
    }
}

const COLOR = /#[0-9a-f]{3,8}\b|rgba?\(|hsla?\(/i;
const errores = [];
const avisos = [];

for (const archivo of blades(VISTAS)) {
    const rel = path.relative(VISTAS, archivo).split(path.sep).join('/');
    const texto = fs.readFileSync(archivo, 'utf8');
    const esAdmin = /@extends\(\s*['"]layouts\.app['"]/.test(texto) || PARCIALES_ADMIN.includes(rel);
    if (!esAdmin) continue;

    texto.split(/\r?\n/).forEach((linea, i) => {
        const donde = `${rel}:${i + 1}`;
        if (/<style[\s>]/i.test(linea)) {
            errores.push(`${donde}  bloque <style> en la vista → muévelo a resources/sass/admin/modules/`);
        }
        for (const m of linea.matchAll(/style="([^"]*)"/g)) {
            const css = m[1];
            // Válido: solo variables CSS con datos dinámicos (style="--area-color: {{ $area->color }}")
            // y display:none como estado inicial que luego cambia el JS.
            const soloVariables = /^\s*(--[\w-]+\s*:[^;]+;?\s*)+$/.test(css);
            const ocultoInicial = /^\s*display\s*:\s*none;?\s*$/.test(css);
            if (soloVariables || ocultoInicial) continue;
            if (COLOR.test(css)) {
                errores.push(`${donde}  color fijo en style="${css.trim()}" → usa una clase con var(--adm-*)`);
            } else {
                avisos.push(`${donde}  style="${css.trim()}"`);
            }
        }
    });
}

if (errores.length) {
    console.log(`\n✗ ${errores.length} error(es) de estilos en vistas del panel:\n`);
    errores.forEach((e) => console.log('  ' + e));
}
if (verTodo && avisos.length) {
    console.log(`\n! ${avisos.length} style="" en línea (aceptables, pero mejor como clase):\n`);
    avisos.forEach((a) => console.log('  ' + a));
} else if (avisos.length) {
    console.log(`\n(${avisos.length} style="" en línea sin colores; verlos con: npm run revisar:estilos -- --todo)`);
}
if (!errores.length) console.log('\n✓ Vistas del panel sin <style> ni colores en línea.');
process.exit(errores.length ? 1 : 0);
