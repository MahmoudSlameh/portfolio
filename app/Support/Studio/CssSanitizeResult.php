<?php

namespace App\Support\Studio;

/**
 * Sanitised CSS and a human-readable list of what was dropped (for the owner and the AI).
 */
final readonly class CssSanitizeResult
{
    /**
     * @param  list<string>  $removed
     */
    public function __construct(
        public string $css,
        public array $removed,
    ) {}

    public function changed(): bool
    {
        return $this->removed !== [];
    }
}
