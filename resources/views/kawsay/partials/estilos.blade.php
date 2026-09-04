{{-- Estilos compartidos por las vistas públicas de Kawsay (portada + Área/Tema/Contenido). --}}
<style>
    /* ── Portada: hero + óvalos de área montados sobre el borde inferior ──
       .fixed-image-container base ya trae min-height:60vh — de sobra para
       desktop, pero en celular deja un hueco enorme de puro fondo entre el
       buscador y los óvalos (el texto es corto, 60vh de un teléfono es
       mucho). Se acorta solo para Kawsay en mobile. */
    .kawsay-area-circles-wrap {
        position: relative;
        z-index: 3;
        margin-top: clamp(-6.5rem, -10vw, -4.5rem);
        margin-bottom: 1.5rem;
        max-width: 1600px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    @media (max-width: 767.98px) {
        .kawsay-hero {
            min-height: calc(340px + var(--nav-height));
        }
    }
    .kawsay-area-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        text-decoration: none;
        text-align: center;
        color: #2a2f36;
    }
    .kawsay-area-circle:hover { color: #2a2f36; text-decoration: none; }
    .kawsay-area-circle__ring {
        position: relative;
        width: clamp(130px, 19vw, 215px);
        height: clamp(130px, 19vw, 215px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 6px solid #fff;
        box-shadow: 0 14px 32px rgba(0, 0, 0, .22);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .kawsay-area-circle:hover .kawsay-area-circle__ring {
        transform: translateY(-6px) scale(1.04);
        box-shadow: 0 18px 34px rgba(0, 0, 0, .28);
    }
    /* Ícono de relleno mientras no hay imagen propia: grande y de fondo,
       detrás del velo y del título (ver 'imagen' en KawsayTaxonomia). */
    .kawsay-area-circle__icon {
        position: absolute;
        color: #fff;
        opacity: .3;
        font-size: clamp(3rem, 7vw, 5.5rem);
    }
    .kawsay-area-circle__scrim {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(0, 0, 0, .1) 35%, rgba(0, 0, 0, .48) 100%);
    }
    .kawsay-area-circle__title {
        position: relative;
        z-index: 2;
        color: #fff;
        font-weight: 800;
        line-height: 1.15;
        font-size: clamp(1.05rem, 2vw, 1.4rem);
        padding: 0 .9rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, .45);
    }
    .kawsay-area-circle__desc {
        display: block;
        margin-top: .85rem;
        color: #5a6169;
        font-size: .85rem;
        line-height: 1.4;
        max-width: 20rem;
    }
    @media (max-width: 575.98px) {
        .kawsay-area-circles-wrap { margin-top: -3.75rem; }
        .kawsay-area-circle__title { font-size: 1rem; }
    }
    /* En PC los óvalos son la navegación principal del proyecto (reemplazan
       los botones Libros/Videos que antes iban en el hero), así que se les
       da bastante más presencia que en mobile. */
    @media (min-width: 992px) {
        /* Hero más alto (llega casi a los pies de los óvalos) con un degradé
           abajo para que la transición a la sección blanca sea suave en vez
           de un corte seco. Al subir la altura del hero se sube en la misma
           medida el "tirón" hacia arriba de los óvalos (margin-top más
           negativo) para que se queden exactamente donde ya estaban. */
        .kawsay-hero {
            /* +10.5rem extra para que la foto (y su degradé, que es % de este
               alto) alcance a cubrir hasta donde termina el texto descriptivo
               debajo de los óvalos, no solo hasta los óvalos mismos. */
            min-height: calc(50vh + var(--nav-height) + 10.5rem);
            /* Con align-items:center (heredado de .fixed-image-container). el
               buscador se corría hacia abajo al crecer el hero y chocaba con
               los óvalos. Se fija el bloque de texto cerca del menú (mismo
               lugar que ya tenía) para que TODO el alto extra del hero se
               vaya hacia abajo, detrás de los óvalos, no hacia el centro. */
            align-items: flex-start;
        }
        .kawsay-hero .centered-content {
            /* Debe despejar el menú superior (sticky, ~5rem) + un respiro,
               si no el título queda tapado por el menú (ver medición: título
               empezaba a 58px con el menú ocupando hasta 81px). */
            padding-top: calc(var(--nav-height) + 1.5rem);
        }
        .kawsay-hero::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 45%;
            background: linear-gradient(to bottom, rgba(242, 242, 242, 0) 0%, #f2f2f2 100%);
            z-index: 0;
            pointer-events: none;
        }
        .kawsay-area-circles-wrap {
            /* Compensa el +10.5rem del hero de arriba para que los óvalos se
               queden exactamente donde ya estaban. */
            margin-top: -24.2rem;
        }
        .kawsay-area-circle__ring {
            width: clamp(220px, 19vw, 320px);
            height: clamp(220px, 19vw, 320px);
            border-width: 7px;
        }
        .kawsay-area-circle__icon {
            font-size: clamp(4rem, 6vw, 7rem);
        }
        .kawsay-area-circle__title {
            font-size: clamp(1.3rem, 1.7vw, 1.75rem);
            padding: 0 1.5rem;
        }
    }

    /* El borde de cada óvalo y el ícono de respaldo de la tarjeta de tema
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

    /* Galería del Tema, dentro del hero. 1 imagen = ancho completo; 2 a 4 =
       reparto igual; 5+ = misma fila pero con flechas/puntos para pasar de
       grupo en grupo (JS al final de este archivo). En mobile siempre es
       una imagen a la vez con swipe nativo (scroll-snap), para que no
       queden angostas. */
    .kawsay-tema-galeria {
        position: relative;
        margin-top: 1.5rem;
        border-radius: 14px;
        overflow: hidden;
    }
    .kawsay-tema-galeria__track {
        display: flex;
        gap: 6px;
    }
    .kawsay-tema-galeria__slide {
        flex: 1 0 calc(25% - 4.5px);
        aspect-ratio: 4 / 3;
    }
    .kawsay-tema-galeria__slide img {
        width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .kawsay-tema-galeria__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px; height: 36px; border-radius: 50%;
        background: rgba(255, 255, 255, .85);
        border: none;
        color: #2a2f36;
        display: flex; align-items: center; justify-content: center;
    }
    .kawsay-tema-galeria__nav--prev { left: .6rem; }
    .kawsay-tema-galeria__nav--next { right: .6rem; }
    .kawsay-tema-galeria__dots {
        display: flex; justify-content: center; gap: .4rem;
        margin-top: .6rem;
    }
    .kawsay-tema-galeria__dots span {
        width: 7px; height: 7px; border-radius: 50%;
        background: rgba(255, 255, 255, .45);
        cursor: pointer;
    }
    .kawsay-tema-galeria__dots span.is-active { background: #fff; }
    @media (max-width: 767.98px) {
        .kawsay-tema-galeria__track {
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        .kawsay-tema-galeria__slide {
            flex: 0 0 100%;
            scroll-snap-align: start;
        }
        .kawsay-tema-galeria__nav { display: none; }
    }

    /* Comentarios */
    .kawsay-comentarios { margin-top: 3rem; max-width: 720px; }
    .kawsay-comentario-form {
        display: flex; flex-direction: column; gap: .6rem;
        margin-bottom: 1.5rem;
        background: #fff; border: 1px solid #e9ecef; border-radius: 12px;
        padding: 1rem;
    }
    .kawsay-comentario-form input, .kawsay-comentario-form textarea {
        border: 1px solid #dee2e6; border-radius: 8px; padding: .5rem .7rem; font-size: .9rem;
    }
    .kawsay-comentario-form textarea { min-height: 80px; resize: vertical; }
    .kawsay-comentario-form button { align-self: flex-end; }
    .kawsay-comentario {
        border-bottom: 1px solid #e9ecef;
        padding: .8rem 0;
    }
    .kawsay-comentario p { margin: .25rem 0 0; }

    /* Portada del Tema (imagen de fondo en vez del ícono, cuando existe). */
    .kawsay-tema-card__cover {
        width: 48px; height: 48px; border-radius: 50%;
        background-size: cover; background-position: center;
        flex-shrink: 0;
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
    .kawsay-tema-card__icon {
        width: 48px; height: 48px; border-radius: 50%; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    }
    .kawsay-tema-card__title { font-weight: 600; font-size: .9rem; }
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
    .kawsay-archivo-card__title { font-weight: 600; font-size: .92rem; margin-bottom: .1rem; }
    .kawsay-archivo-card__meta { font-size: .75rem; color: #868e96; }
</style>
