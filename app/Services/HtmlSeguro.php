<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Limpia HTML que escriben los usuarios (p. ej. el "detalle" de una aportación,
 * que llega del editor Summernote desde un formulario PÚBLICO) antes de
 * mostrarlo con {!! !!}.
 *
 * Lista blanca: solo etiquetas de formato de texto y enlaces http/https/mailto.
 * Todo atributo se descarta (style, class, on*, …) salvo el href de <a>.
 * Las etiquetas peligrosas (script, iframe, form, svg…) se eliminan con su
 * contenido; las desconocidas (span, font…) se quitan conservando su texto.
 *
 * Sin dependencias: en producción no hay Composer, así que no se usa
 * HTMLPurifier. Solo DOMDocument, que viene con PHP.
 */
class HtmlSeguro
{
    private const PERMITIDAS = [
        'p', 'br', 'div', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup',
        'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'a',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    private const ELIMINAR_CON_CONTENIDO = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
        'form', 'input', 'button', 'textarea', 'select', 'option', 'svg', 'math',
        'template', 'noscript', 'link', 'meta', 'base', 'title', 'head',
    ];

    public static function limpiar(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Atajo: sin "<" ni "&" no hay marcado que limpiar, solo escapar.
        if (! str_contains($html, '<') && ! str_contains($html, '&')) {
            return e($html);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previo = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="__raiz">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previo);

        $raiz = $dom->getElementById('__raiz');
        if (! $raiz) {
            return e(strip_tags($html));
        }

        self::limpiarHijos($raiz);

        $salida = '';
        foreach ($raiz->childNodes as $hijo) {
            $salida .= $dom->saveHTML($hijo);
        }

        return trim($salida);
    }

    private static function limpiarHijos(DOMNode $nodo): void
    {
        // Copia de la lista: se modifica mientras se recorre.
        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo->nodeType === XML_COMMENT_NODE || $hijo->nodeType === XML_PI_NODE
                || $hijo->nodeType === XML_CDATA_SECTION_NODE) {
                $nodo->removeChild($hijo);

                continue;
            }

            if (! $hijo instanceof DOMElement) {
                continue; // texto: DOMDocument lo vuelve a escapar al guardar
            }

            $tag = strtolower($hijo->nodeName);

            if (in_array($tag, self::ELIMINAR_CON_CONTENIDO, true)) {
                $nodo->removeChild($hijo);

                continue;
            }

            self::limpiarHijos($hijo);

            if (! in_array($tag, self::PERMITIDAS, true)) {
                // Desenvolver: conservar el contenido, quitar la etiqueta.
                while ($hijo->firstChild) {
                    $nodo->insertBefore($hijo->firstChild, $hijo);
                }
                $nodo->removeChild($hijo);

                continue;
            }

            self::limpiarAtributos($hijo, $tag);
        }
    }

    private static function limpiarAtributos(DOMElement $el, string $tag): void
    {
        $href = $tag === 'a' ? trim($el->getAttribute('href')) : '';

        foreach (iterator_to_array($el->attributes) as $atributo) {
            $el->removeAttribute($atributo->nodeName);
        }

        if ($tag === 'a') {
            if (preg_match('#^(https?://|mailto:)#i', $href)) {
                $el->setAttribute('href', $href);
                $el->setAttribute('target', '_blank');
                $el->setAttribute('rel', 'noopener nofollow ugc');
            }
            // Sin href válido queda como <a> sin destino: texto inofensivo.
        }
    }
}
