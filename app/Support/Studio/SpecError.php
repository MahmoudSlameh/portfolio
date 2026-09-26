<?php

namespace App\Support\Studio;

/**
 * One validation error of a Template Spec: where it is and what is wrong.
 */
final readonly class SpecError
{
    public function __construct(
        public string $path,
        public string $message,
    ) {}

    public function __toString(): string
    {
        return "{$this->path} {$this->message}";
    }
}
