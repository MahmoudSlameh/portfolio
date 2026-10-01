<?php

namespace App\Support\Studio;

use InvalidArgumentException;

final class InvalidSpecException extends InvalidArgumentException
{
    public function __construct(public readonly SpecValidationResult $result)
    {
        parent::__construct("The template spec is invalid:\n- ".implode("\n- ", $result->messages()));
    }
}
