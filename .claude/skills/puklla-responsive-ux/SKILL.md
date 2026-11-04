---
name: puklla-responsive-ux
description: Diseño responsive y UX/UI para las páginas públicas de Pukllasunchis (Escuela/EESP, Kawsay, Radio, Sisipedia), pensadas para docentes rurales del Perú con poca experiencia digital y celulares de gama baja. Usar al diseñar o corregir vistas, menús, botones, formularios, estados hover/foco, animaciones o cualquier cambio visual, y para revisar cómo se ve en celular.
---

# Responsive y UX/UI en Pukllasunchis

## Para quién diseñamos
Docentes de zonas rurales: celular Android modesto, datos móviles lentos, poca costumbre con interfaces. Cada pantalla debe responder sin esfuerzo a "¿qué es esto y qué hago aquí?".
- Lenguaje simple y directo, en segunda persona ("Escucha", "Descarga", "Busca un tema"). Nada de jerga técnica (no "registro", "categoría padre", "upload").
- Una acción principal por bloque, con verbo claro. Íconos siempre acompañados de texto, salvo los universales (redes sociales, cerrar) y entonces con `aria-label`.
- Pasos numerados solo si de verdad hay una secuencia ("Paso 1 de 2").
- Estados vacíos y errores que digan qué hacer después, nunca solo "No hay resultados".

## Responsive (mobile-first)
- Diseñar primero a **360–390px** y luego ampliar. Breakpoints de Bootstrap 5: 576 / 768 / 992 / 1200.
- Nada puede desbordar horizontalmente: textos largos con `overflow-wrap:anywhere`, imágenes `max-width:100%; height:auto`, tablas dentro de `.table-responsive`, migas de pan con scroll horizontal y `flex-shrink:0` en sus ítems.
- Cuidado con badges de Bootstrap (`white-space:nowrap`): no usarlos para textos de usuario.
- Objetivos táctiles de **mínimo 44×44px** y separación entre ellos. Inputs con `font-size ≥ 16px` (evita el zoom de iOS).
- Listas largas: mostrar 6–8 y "Mostrar los X restantes"; filtros por tipo; búsqueda solo si hay más de 8.
- Imágenes con `loading="lazy"` y tamaños razonables; cada KB pesa en datos móviles.

## Estados de interacción (siempre los cuatro)
`:hover`, `:focus-visible`, `:active` y deshabilitado deben ser **distinguibles sobre cualquier fondo** en el que aparezca el elemento.
- Si el contenedor puede ser transparente (menú en `top:0` sobre una foto), el hover **no puede depender de transparencias** (`--puklla-accent-soft`, `rgba(...)` bajos): usar relleno sólido (`var(--project-accent)`) con contraste ≥ 4.5:1 en el ícono/texto.
- Diseñar el estado sobre los **dos** fondos: menú transparente sobre hero y menú sólido con scroll (`.is-scrolled`).
- Feedback que se sienta, no solo color: un pequeño `transform: translateY(-1px)` o `scale(1.06)` + sombra corta, transición 150–200ms con `var(--puklla-ease)`.
- `:hover` solo dentro de `@media (hover:hover)` para que en celular no quede "pegado" tras tocar; en táctil usar `:active`.
- Foco visible con `outline` de 2px y `outline-offset:2px`; nunca `outline:none` sin reemplazo.

## Color y marca
- Cada proyecto tiene su acento en `--project-accent` (clase `is-<proyecto>` en el body). Usar la variable, no hex sueltos.
- Contraste WCAG AA: 4.5:1 texto normal, 3:1 íconos y texto grande. Verificar el acento de cada proyecto.

## Movimiento (GSAP 3.12.5 está cargado en público)
- Ver habilidades `gsap-core` y `gsap-performance`. Animar solo `transform` y `opacity`.
- Respetar `prefers-reduced-motion` (`gsap.matchMedia()`); el contenido debe ser visible aunque el JS falle (no dejar `opacity:0` en CSS esperando a GSAP).
- Un momento orquestado por página (entrada del hero) vale más que animar cada tarjeta.

## Cómo verificar (obligatorio antes de decir "listo")
1. Revisar en 390px: el Chrome headless no baja de ~500px, así que cargar la página dentro de un `<iframe width=390>` en el scratchpad y medir `document.documentElement.scrollWidth > clientWidth` y elementos cuyo `getBoundingClientRect().right` exceda el ancho.
2. Revisar también a 768 y 1280px.
3. Para hover/foco: si no se puede simular, forzar el estado con una clase temporal o capturar con `:hover` → `.force-hover` en el probe, y decir con honestidad qué se verificó y qué solo se revisó en CSS.
4. Comprobar los estados sobre fondo transparente y sobre fondo sólido.
