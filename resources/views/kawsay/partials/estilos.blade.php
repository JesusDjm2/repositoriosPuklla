{{-- Estilos compartidos por las vistas públicas de Kawsay (portada + Área/Tema/Contenido). --}}
<style>
    /* ── Portada: UN solo hero (la misma .kawsay-hero de siempre, no una
       segunda sección con su propia copia de la foto) estirado en alto hasta
       pasar la última fila de tarjetas, con un degradé al final. La sección
       de Áreas se monta encima subiéndola con margin-top negativo — mismo
       mecanismo que antes usaban los óvalos, pero ahora es seguro: las
       tarjetas son superficies blancas opacas, así que nada de lo que hay
       debajo (foto, degradé, o ya blanco) puede volver el texto illegible. */
    .kawsay-hero {
        min-height: calc(var(--nav-height) + 42.3rem);
        /* Con align-items:center (heredado de .fixed-image-container) el
           título quedaba centrado dentro de este hero ya alto, en vez de
           pegado arriba — se fija arriba para que TODO el alto extra se
           vaya hacia abajo, detrás de las tarjetas, no hacia el centro. */
        align-items: flex-start;
    }
    .kawsay-hero .centered-content {
        padding: calc(var(--nav-height) + 2.5rem) 1rem 0;
    }
    .kawsay-hero::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 42%;
        background: linear-gradient(to bottom, rgba(247, 248, 242, 0) 0%, #f7f8f2 100%);
        z-index: 0;
        pointer-events: none;
    }
    /* Buscador sutil: el input base del sitio mide 350px (pensado para vivir
       junto a otro texto, no para ser el elemento más ancho de la pantalla)
       — se deja tal cual en desktop. Ya en mobile/tablet el CSS global lo
       pone al 100% de ancho cada uno por debajo de 480px y los apila; ahí sí
       se corrige para que compartan una sola línea sin ocupar tanto alto. */
    @media (max-width: 991.98px) {
        .kawsay-hero .search-container {
            flex-wrap: nowrap;
        }
        .kawsay-hero .search-input {
            width: auto;
            flex: 1 1 auto;
            min-width: 0;
        }
        .kawsay-hero .search-button {
            width: auto;
            flex: 0 0 auto;
            padding: 12px 16px;
        }
    }
    /* El alto extra del hero y el "tirón" hacia arriba de .kawsay-areas van
       siempre de la mano (más alto = margen más negativo, para que el
       degradé llegue más abajo sin mover las tarjetas de sitio) — por eso
       cada rango de ancho define los dos juntos, en un solo bloque, en vez
       de repartir .kawsay-areas suelto fuera de cualquier @media (eso fue
       justo el bug: esa regla quedaba después de los @media en la hoja y
       ganaba siempre por orden de cascada, sin importar el ancho real). */
    .kawsay-areas {
        position: relative;
        z-index: 1;
    }
    /* ≥992px: una sola fila de 4 tarjetas. */
    @media (min-width: 992px) {
        .kawsay-areas {
            margin-top: -26.3rem;
        }
    }
    /* 768px–991.98px: también una sola fila, pero tarjetas más chicas. */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .kawsay-hero {
            min-height: calc(var(--nav-height) + 43.8rem);
        }
        .kawsay-areas {
            margin-top: -28rem;
        }
    }
    /* < 768px: 2 columnas (2 filas de 4 áreas) — el hero necesita ser bastante
       más alto para alcanzar el final de la 2da fila. */
    @media (max-width: 767.98px) {
        .kawsay-hero {
            min-height: calc(var(--nav-height) + 60rem);
        }
        .kawsay-areas {
            margin-top: -45rem;
        }
    }
    .kawsay-areas__heading {
        color: #fff;
        text-shadow: 0 2px 10px rgba(0, 0, 0, .35);
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    /* El círculo ya no vive dentro de una tarjeta blanca — flota directo
       sobre el fondo de la sección, con su propia sombra para no perderse
       contra la foto. Solo la descripción va en una tarjetita chica (nada
       que envuelva también al círculo), lo justo para garantizar lectura. */
    .kawsay-area-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: .9rem;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }
    .kawsay-area-item:hover {
        color: inherit;
        text-decoration: none;
    }
    .kawsay-area-item__avatar {
        position: relative;
        width: clamp(130px, 17vw, 200px);
        height: clamp(130px, 17vw, 200px);
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: color-mix(in srgb, var(--area-color) 14%, white);
        border: 4px solid var(--area-color);
        color: var(--area-color);
        font-size: clamp(2.25rem, 4vw, 3.25rem);
        box-shadow: 0 .9rem 2rem rgba(0, 0, 0, .28);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .kawsay-area-item:hover .kawsay-area-item__avatar {
        transform: translateY(-6px) scale(1.03);
        box-shadow: 0 1.1rem 2.4rem rgba(0, 0, 0, .34);
    }
    .kawsay-area-item__avatar img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    /* Sin imagen propia, el ícono de respaldo queda grande y de fondo (baja
       opacidad), detrás del velo y del título. */
    .kawsay-area-item__avatar > i {
        position: absolute;
        opacity: .35;
    }
    /* Velo radial para que el título en blanco se lea sin importar qué foto
       tenga cada área detrás. */
    .kawsay-area-item__scrim {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(0, 0, 0, .1) 40%, rgba(0, 0, 0, .5) 100%);
    }
    .kawsay-area-item__title {
        position: relative;
        z-index: 1;
        font-weight: 800;
        font-size: clamp(1.05rem, 1.6vw, 1.3rem);
        line-height: 1.2;
        color: #fff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, .45);
        padding: 0 .6rem;
    }
    /* Sin tarjeta blanca: en desktop (≥992px) el degradé del hero ya es
       sólido en esta zona, así que el texto va directo, sin nada detrás.
       En tablet/mobile la fila de círculos todavía cae sobre la foto — ahí
       un velo suave sin bordes duros (radial, se disuelve en los extremos)
       sube el contraste; no es una "tarjeta", no tiene forma reconocible,
       solo aclara lo justo detrás de las letras. */
    .kawsay-area-item__desc {
        display: block;
        max-width: 17rem;
        font-size: .82rem;
        line-height: 1.5;
        font-weight: 600;
        color: #2f3327;
    }
    @media (max-width: 991.98px) {
        .kawsay-area-item__desc {
            margin: 0 auto;
            padding: .35rem .5rem;
            background: radial-gradient(ellipse 90% 80% at center, rgba(247, 248, 242, .8) 0%, rgba(247, 248, 242, .4) 65%, rgba(247, 248, 242, 0) 100%);
        }
    }
    @media (max-width: 575.98px) {
        .kawsay-area-item__desc { font-size: .78rem; }
    }

    /* El borde/acento del avatar y el ícono de respaldo de la tarjeta de tema
       usan el color propio del área (KawsayArea::color, hex libre elegido
       desde el admin) como estilo inline en la vista — ya no clases fijas
       por nombre, porque ahora las áreas son registros de BD y no un set
       cerrado de 4 nombres. */

    /* Header de las vistas Área/Tema/Contenido (a las que llevan los óvalos
       de la portada): mismo fondo fotográfico y mismo velo para las 3 —
       usa el color base del proyecto Kawsay (--project-accent, ya vale
       #6e7d1e en toda página de Kawsay) en vez de variar por área, para que
       las 3 vistas se sientan parte de una misma identidad. */
    .kawsay-area-header {
        color: #fff;
        position: relative;
        background-image:
            linear-gradient(135deg, color-mix(in srgb, var(--project-accent) 88%, black), color-mix(in srgb, var(--project-accent) 55%, white 20%)),
            url('{{ asset('img/kawsay/hero-secciones.jpg') }}');
        background-size: cover;
        background-position: center;
    }

    /* Buscador sutil dentro del header — mucho más chico que el buscador
       grande de la portada, alineado a la derecha del bloque de título. */
    .kawsay-header-search {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        width: 100%;
        max-width: 260px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 999px;
        padding: .3rem .3rem .3rem 1rem;
        backdrop-filter: blur(2px);
    }
    .kawsay-header-search input {
        flex: 1;
        background: transparent;
        border: none;
        color: #fff;
        font-size: .85rem;
        min-width: 0;
    }
    .kawsay-header-search input::placeholder { color: rgba(255, 255, 255, .7); }
    .kawsay-header-search input:focus { outline: none; }
    .kawsay-header-search button {
        background: rgba(255, 255, 255, .92);
        color: #2a2f36;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
    }
    @media (max-width: 767.98px) {
        .kawsay-header-search { max-width: none; }
    }

    /* Compartir: fila de botones redondos junto al título del tema. */
    .kawsay-share {
        display: flex;
        gap: .5rem;
        margin-top: 1rem;
    }
    .kawsay-share a, .kawsay-share button {
        width: 34px; height: 34px; border-radius: 50%;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .35);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem;
        transition: background .15s ease;
    }
    .kawsay-share a:hover, .kawsay-share button:hover { background: rgba(255, 255, 255, .3); color: #fff; }
    .kawsay-share button.is-copiado { background: rgba(255, 255, 255, .92); color: #2a2f36; }

    /* ── Comentarios del tema ─────────────────────────────────────────── */
    .kcom { margin-top: 3.5rem; max-width: 780px; scroll-margin-top: 6rem; }
    .kcom__head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        padding-bottom: 1rem; margin-bottom: 1.25rem;
        border-bottom: 2px solid color-mix(in srgb, var(--project-accent) 22%, transparent);
    }
    .kcom__title { font-size: 1.35rem; font-weight: 800; color: #2a2f26; margin: 0 0 .15rem; }
    .kcom__sub { margin: 0; color: #6b7264; font-size: .9rem; }
    .kcom__count {
        min-width: 2.2rem; height: 2.2rem; padding: 0 .6rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px; font-weight: 800; font-size: .9rem;
        background: var(--project-accent); color: #fff;
    }

    .kcom__alert {
        display: flex; align-items: flex-start; gap: .6rem;
        padding: .8rem 1rem; border-radius: 12px; margin-bottom: 1rem;
        font-size: .9rem; line-height: 1.4;
    }
    .kcom__alert i { margin-top: .15rem; }
    .kcom__alert--ok { background: #eaf5e4; color: #2f5a1f; border: 1px solid #cfe6c4; }
    .kcom__alert--error { background: #fdecec; color: #8a2222; border: 1px solid #f3c9c9; }

    .kcom__form {
        background: #fff; border: 1px solid #e6e9df; border-radius: 16px;
        padding: 1.25rem; margin-bottom: 2rem;
        box-shadow: 0 1px 2px rgba(20, 30, 10, .04), 0 10px 24px rgba(20, 30, 10, .05);
    }
    .kcom__field { margin-bottom: 1rem; }
    .kcom__field label { display: block; font-size: .78rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: #5a6353; margin-bottom: .35rem; }
    .kcom__field input, .kcom__field textarea {
        width: 100%; border: 1.5px solid #dfe3d6; border-radius: 10px; background: #fbfcf8;
        padding: .65rem .85rem; font-size: .95rem; color: #2a2f26; font-family: inherit;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .kcom__field textarea { min-height: 7.5rem; resize: vertical; line-height: 1.5; }
    .kcom__field input:focus, .kcom__field textarea:focus {
        outline: none; background: #fff; border-color: var(--project-accent);
        box-shadow: 0 0 0 4px color-mix(in srgb, var(--project-accent) 18%, transparent);
    }
    .kcom__field .is-invalid { border-color: #d64545; background: #fff8f8; }
    .kcom__error { color: #b42525; font-size: .8rem; margin-top: .3rem; }

    /* Campo trampa para bots: fuera de pantalla, no display:none (algunos bots lo detectan). */
    .kcom__trampa { position: absolute; left: -9999px; top: auto; width: 1px; height: 1px; overflow: hidden; }

    .kcom__foot { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .kcom__nota { margin: 0; font-size: .78rem; color: #7a8171; display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; flex: 1 1 260px; }
    .kcom__nota i { color: var(--project-accent); }
    .kcom__contador { margin-left: .25rem; font-variant-numeric: tabular-nums; color: #9aa190; }
    .kcom__contador.is-limite { color: #b42525; font-weight: 700; }
    .kcom__btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
        border: 0; cursor: pointer; padding: .7rem 1.4rem; border-radius: 999px;
        background: var(--project-accent); color: #fff; font-weight: 700; font-size: .9rem;
        box-shadow: 0 6px 16px color-mix(in srgb, var(--project-accent) 32%, transparent);
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .kcom__btn:hover { background: color-mix(in srgb, var(--project-accent) 82%, black); transform: translateY(-1px); }
    .kcom__btn:disabled { opacity: .65; cursor: wait; transform: none; }

    .kcom__lista { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .9rem; }
    .kcom__item {
        display: flex; gap: .9rem; align-items: flex-start;
        background: #fff; border: 1px solid #eceee6; border-radius: 14px; padding: 1rem 1.1rem;
        animation: kcomEntrada .35s ease both;
    }
    .kcom__item.is-oculto { display: none; }
    .kcom__avatar {
        width: 2.6rem; height: 2.6rem; flex-shrink: 0; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 1.05rem;
    }
    .kcom__cuerpo { min-width: 0; flex: 1; }
    .kcom__meta { display: flex; align-items: baseline; flex-wrap: wrap; gap: .25rem .65rem; margin-bottom: .25rem; }
    .kcom__autor { color: #2a2f26; font-size: .95rem; }
    .kcom__meta time { color: #8b9282; font-size: .78rem; }
    .kcom__texto { margin: 0; color: #3d4437; font-size: .95rem; line-height: 1.6; overflow-wrap: anywhere; }

    .kcom__mas {
        display: flex; align-items: center; justify-content: center; gap: .4rem;
        width: 100%; margin-top: 1rem; padding: .7rem; cursor: pointer;
        background: transparent; color: var(--project-accent); font-weight: 700; font-size: .9rem;
        border: 1.5px dashed color-mix(in srgb, var(--project-accent) 45%, transparent); border-radius: 12px;
        transition: background .15s ease;
    }
    .kcom__mas:hover { background: color-mix(in srgb, var(--project-accent) 8%, transparent); }

    .kcom__vacio {
        text-align: center; padding: 2rem 1rem; color: #7a8171;
        background: #fafbf6; border: 1.5px dashed #dfe3d6; border-radius: 14px;
    }
    .kcom__vacio i { font-size: 2rem; color: var(--project-accent); opacity: .45; margin-bottom: .5rem; }
    .kcom__vacio p { margin: 0; line-height: 1.5; }

    @keyframes kcomEntrada { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .kcom__item { animation: none; } .kcom__btn { transition: none; } }
    @media (max-width: 575.98px) {
        .kcom__form { padding: 1rem; }
        .kcom__btn { width: 100%; }
        .kcom__item { padding: .85rem; gap: .7rem; }
        .kcom__avatar { width: 2.2rem; height: 2.2rem; font-size: .95rem; }
    }

    /* Portada del tema: foto principal en la cabecera de la vista del tema
       (a la izquierda del texto en desktop; arriba del título en mobile). */
    /* Rejilla de la cabecera del tema: volver + contenido a la izquierda y el
       buscador en la derecha, centrado en vertical respecto a TODA la cabecera.
       En mobile se apila: volver, buscador, foto y texto. */
    .kawsay-tema-header {
        display: grid; grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas: "back search" "hero search";
        column-gap: 2rem; row-gap: 1rem;
    }
    .kawsay-tema-header__back { grid-area: back; justify-self: start; }
    .kawsay-tema-header__search { grid-area: search; align-self: center; width: 260px; }
    .kawsay-tema-header > .kawsay-tema-hero { grid-area: hero; }
    @media (max-width: 767.98px) {
        .kawsay-tema-header { grid-template-columns: minmax(0, 1fr); grid-template-areas: "back" "search" "hero"; }
        .kawsay-tema-header__search { align-self: stretch; width: auto; }
    }
    .kawsay-tema-hero { display: flex; align-items: center; gap: 2.5rem; }
    .kawsay-tema-hero__texto { flex: 1; min-width: 0; }
    .kawsay-tema-hero__foto {
        flex: 0 0 min(32%, 340px); margin: 0; aspect-ratio: 4 / 3;
        border-radius: 16px; overflow: hidden;
        border: 3px solid rgba(255, 255, 255, .35);
        box-shadow: 0 16px 36px rgba(0, 0, 0, .3);
    }
    .kawsay-tema-hero__foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
    @media (max-width: 767.98px) {
        .kawsay-tema-hero { flex-direction: column; align-items: stretch; gap: 1.1rem; }
        .kawsay-tema-hero__foto { flex: none; aspect-ratio: 16 / 9; }
    }

    .kawsay-back-link { color: rgba(255, 255, 255, .85); text-decoration: none; font-size: .85rem; font-weight: 600; }
    .kawsay-back-link:hover { color: #fff; text-decoration: underline; }

    .kawsay-area-header__icon {
        width: 56px; height: 56px; border-radius: 50%;
        background: rgba(255, 255, 255, .18);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }
    .kawsay-area-header__title { font-size: 1.6rem; font-weight: 800; margin-bottom: .1rem; }
    .kawsay-area-header__subtitle { opacity: .85; font-size: .9rem; margin-bottom: 0; }
    .kawsay-area-header__desc { max-width: 640px; margin: 1rem 0 0; opacity: .92; }

    .kawsay-tema-card {
        display: flex; flex-direction: column; align-items: center; gap: .6rem;
        text-align: center; text-decoration: none; color: #2a2f36;
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 1.4rem 1rem; height: 100%; position: relative;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .kawsay-tema-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(0, 0, 0, .1); color: #2a2f36; text-decoration: none; }
    .kawsay-tema-card__title { font-weight: 600; font-size: .9rem; }
    /* Sin foto: solo el título, con una franja del color del área arriba. */
    .kawsay-tema-card--texto {
        justify-content: center; min-height: 7rem;
        border-top: 4px solid var(--area-color, #6e7d1e);
    }
    .kawsay-tema-card--texto .kawsay-tema-card__title { font-size: 1rem; line-height: 1.3; }
    /* Variante con foto: la imagen ocupa la parte de arriba de la tarjeta. */
    .kawsay-tema-card--foto { padding: 0; gap: 0; overflow: hidden; align-items: stretch; }
    .kawsay-tema-card__foto {
        display: block; aspect-ratio: 16 / 10;
        background-size: cover; background-position: center; background-color: #f0ebe4;
        transition: transform .35s ease;
    }
    .kawsay-tema-card--foto:hover .kawsay-tema-card__foto { transform: scale(1.04); }
    .kawsay-tema-card--foto .kawsay-tema-card__title { padding: .8rem .9rem; position: relative; background: #fff; }
    .kawsay-tema-card--foto .kawsay-tema-card__badge { background: rgba(255, 255, 255, .92); box-shadow: 0 1px 4px rgba(0, 0, 0, .15); }
    .kawsay-tema-card__badge {
        position: absolute; top: .6rem; right: .6rem;
        background: #f1f3f5; color: #495057; border-radius: 999px;
        font-size: .7rem; font-weight: 700; padding: .1rem .45rem;
    }

    .kawsay-tipo-card {
        display: flex; flex-direction: column; align-items: center; gap: .6rem;
        text-align: center; text-decoration: none; color: #2a2f36;
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 1.4rem 1rem; height: 100%; position: relative;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .kawsay-tipo-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(0, 0, 0, .1); color: #2a2f36; text-decoration: none; }
    button.kawsay-tipo-card { width: 100%; font: inherit; cursor: pointer; }
    .kawsay-tipo-card--vacio { opacity: .45; cursor: default; pointer-events: none; }
    .kawsay-tipo-card__icon {
        width: 48px; height: 48px; border-radius: 50%; background: #f1f3f5; color: #495057;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .kawsay-tipo-card__title { font-weight: 600; font-size: .88rem; }
    .kawsay-tipo-card__count { font-size: .72rem; color: #868e96; }

    .kawsay-archivo-card {
        display: flex; align-items: center; gap: .9rem;
        background: #fff; border: 1px solid #e9ecef; border-radius: 12px;
        padding: .9rem 1.1rem; margin-bottom: .75rem;
    }
    .kawsay-archivo-card__icon {
        width: 42px; height: 42px; border-radius: 10px; background: #f1f3f5; color: #495057;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
    }
    /* PDF como tarjeta con portada (reusa .lib-book-card de la Escuela): en
       táctil no hay hover, así que la portada entera es el botón de vista previa. */
    .kawsay-pdf-cover { cursor: pointer; aspect-ratio: 15 / 16; }
    .kawsay-pdf-cover img { object-position: top; }
    .kawsay-pdf-cover__ph {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        font-size: 2.6rem; color: var(--project-accent); opacity: .35;
    }
    .kawsay-pdf-cover img { position: relative; }
    .kawsay-pdf-cover:focus-visible { outline: 3px solid var(--project-accent); outline-offset: -3px; }
    .kawsay-pdf-card__actions {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: auto; padding-top: .35rem; font-size: .76rem;
    }
    .kawsay-pdf-card__actions a { color: var(--project-accent); text-decoration: none; font-weight: 600; }
    .kawsay-pdf-card__actions a:hover { text-decoration: underline; }
    .kawsay-archivo-card__actions { display: flex; gap: .4rem; flex-shrink: 0; }
    @media (max-width: 575.98px) {
        .kawsay-archivo-card { flex-wrap: wrap; }
        .kawsay-archivo-card__actions { width: 100%; }
        .kawsay-archivo-card__actions .btn { flex: 1 1 0; white-space: nowrap; }
        .kawsay-archivo-card__actions .btn-primary { flex: 2 1 0; }
    }
    .kawsay-archivo-card__title { font-weight: 600; font-size: .92rem; margin-bottom: .1rem; }
    .kawsay-archivo-card__meta { font-size: .75rem; color: #868e96; }
</style>
