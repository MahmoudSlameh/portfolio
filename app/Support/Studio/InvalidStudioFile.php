<?php

namespace App\Support\Studio;

use RuntimeException;

/**
 * An imported studio template file was rejected; the message is for the owner.
 */
final class InvalidStudioFile extends RuntimeException
{
    /**
     * @param  list<string>  $errors  Spec validation errors, when the spec itself was invalid
     */
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
