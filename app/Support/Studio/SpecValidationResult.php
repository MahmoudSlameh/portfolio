<?php

namespace App\Support\Studio;

/**
 * The outcome of validating a Template Spec.
 */
final readonly class SpecValidationResult
{
    /**
     * @param  list<SpecError>  $errors
     */
    public function __construct(public array $errors) {}

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * One line per error, e.g. `pages.home[2].variant must be one of: grid, bento, list, slider.`
     *
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(fn (SpecError $error): string => (string) $error, $this->errors);
    }

    /**
     * @throws InvalidSpecException
     */
    public function throw(): void
    {
        if (! $this->passes()) {
            throw new InvalidSpecException($this);
        }
    }
}
