<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    public function sanitize(string $html): string
    {
        if (! class_exists(DOMDocument::class)) {
            return strip_tags($html);
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML('<?xml encoding="utf-8" ?><!DOCTYPE html><html><body><div id="page-content">'.str_replace("\0", '', $html).'</div></body></html>');
            $container = $document->getElementById('page-content');
            if (! $container instanceof DOMElement) {
                return '';
            }

            $allowed = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'strong', 'em', 'b', 'i', 'u', 's', 'br', 'hr', 'a', 'pre', 'code', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];
            $elements = [];
            foreach ($container->getElementsByTagName('*') as $element) {
                $elements[] = $element;
            }
            foreach (array_reverse($elements) as $element) {
                if (! in_array(strtolower($element->tagName), $allowed, true)) {
                    if (in_array(strtolower($element->tagName), ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form'], true)) {
                        $element->parentNode?->removeChild($element);
                    } else {
                        $this->unwrap($element);
                    }

                    continue;
                }

                $permittedAttributes = match (strtolower($element->tagName)) {
                    'a' => ['href', 'title', 'target'],
                    'th', 'td' => ['colspan', 'rowspan'],
                    default => [],
                };
                foreach (iterator_to_array($element->attributes) as $attribute) {
                    if (! in_array(strtolower($attribute->name), $permittedAttributes, true)) {
                        $element->removeAttribute($attribute->name);
                    }
                }
                if ($element->tagName === 'a') {
                    $href = trim($element->getAttribute('href'));
                    if ($href !== '' && ! preg_match('/^(https?:|mailto:|tel:|\/|#)/i', $href)) {
                        $element->removeAttribute('href');
                    }
                    if ($element->getAttribute('target') === '_blank') {
                        $element->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }

            $clean = '';
            foreach (iterator_to_array($container->childNodes) as $child) {
                $clean .= $document->saveHTML($child);
            }

            return $clean;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function unwrap(DOMNode $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }
        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }
}
