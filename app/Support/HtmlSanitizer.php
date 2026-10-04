<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist-based HTML cleaner for note bodies.
 *
 * The editor (Trix) only ever produces a small set of tags, so anything
 * outside that set is unwrapped (its text is kept, the tag dropped) and
 * every attribute except a safe http(s)/mailto href on links is removed.
 * That keeps pasted or hand-crafted HTML from smuggling in scripts,
 * styles or event handlers.
 */
class HtmlSanitizer
{
    /** Tags kept as-is. */
    private const ALLOWED = [
        'div', 'p', 'br', 'strong', 'b', 'em', 'i', 'del', 's', 'u',
        'a', 'h1', 'h2', 'h3', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li',
    ];

    /** Tags removed together with everything inside them. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'template',
        'noscript', 'svg', 'math', 'head', 'title', 'meta', 'link',
        'form', 'input', 'button', 'select', 'textarea', 'figure', 'img',
    ];

    public function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__root');
        if ($root === null) {
            return '';
        }

        $this->cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private function cleanChildren(DOMNode $node): void
    {
        // Iterate over a static copy, since we mutate the tree as we go.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                // Comments, processing instructions, CDATA…
                $node->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);

                continue;
            }

            $this->cleanChildren($child);

            if (! in_array($tag, self::ALLOWED, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            $this->cleanAttributes($child, $tag);
        }
    }

    private function cleanAttributes(DOMElement $el, string $tag): void
    {
        $href = $tag === 'a' ? trim($el->getAttribute('href')) : '';

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->nodeName);
        }

        if ($tag === 'a') {
            if (preg_match('#^(https?://|mailto:)#i', $href)) {
                $el->setAttribute('href', $href);
                $el->setAttribute('target', '_blank');
                $el->setAttribute('rel', 'noopener noreferrer nofollow');
            }
        }
    }
}
