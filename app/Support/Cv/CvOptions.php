<?php

namespace App\Support\Cv;

use App\Enums\CvTemplate;

/**
 * How a CV is put together: template, paper and which optional sections to include.
 */
final readonly class CvOptions
{
    /**
     * @param  'a4'|'letter'  $paper
     * @param  int|null  $maxRoles  Keep only the most recent roles (null: all)
     */
    public function __construct(
        public CvTemplate $template = CvTemplate::Classic,
        public string $paper = 'a4',
        public bool $includeProjects = true,
        public bool $includeCertifications = true,
        public ?int $maxRoles = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Form state (template, paper, include_projects, include_certifications, max_roles)
     */
    public static function fromArray(array $data): self
    {
        $template = $data['template'] ?? null;
        $maxRoles = filter_var($data['max_roles'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return new self(
            // Form state may hold the enum itself (Filament casts enum options).
            template: $template instanceof CvTemplate ? $template : (CvTemplate::tryFrom(is_string($template) ? $template : '') ?? CvTemplate::Classic),
            paper: ($data['paper'] ?? 'a4') === 'letter' ? 'letter' : 'a4',
            includeProjects: (bool) ($data['include_projects'] ?? true),
            includeCertifications: (bool) ($data['include_certifications'] ?? true),
            maxRoles: $maxRoles === false ? null : $maxRoles,
        );
    }
}
