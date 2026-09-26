<?php

namespace App\Support\Studio;

/**
 * One node of a sanitised stylesheet (internal to CssSanitizer): a style rule whose body holds
 * declarations, or a group (`@media`, `@supports`, `@keyframes`) whose body holds nodes.
 */
final readonly class CssNode
{
    /**
     * @param  list<string|CssNode>  $body
     */
    public function __construct(
        public string $prelude,
        public array $body,
    ) {}
}
