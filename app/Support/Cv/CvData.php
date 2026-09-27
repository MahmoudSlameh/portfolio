<?php

namespace App\Support\Cv;

use App\Enums\SocialPlatform;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Social;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Everything a CV shows, as plain data (P11-02). It reads the panel's content with the same visibility
 * and ordering rules as the public site; the templates only format it.
 *
 * @phpstan-type CvRole array{role: string, organization: string, location: string|null, period: string, summary: string|null, highlights: list<string>, stack: list<string>}
 * @phpstan-type CvStudy array{degree: string, field: string|null, institution: string, location: string|null, period: string, grade: string|null, description: string|null, achievements: list<string>}
 * @phpstan-type CvSkillGroup array{category: string, skills: list<string>}
 * @phpstan-type CvCertification array{name: string, issuer: string, issued: string, expires: string|null, credential: string|null, url: string|null}
 * @phpstan-type CvProject array{title: string, year: int, description: string|null, stack: list<string>, url: string|null}
 */
final readonly class CvData
{
    /**
     * @param  list<array{label: string, url: string}>  $links
     * @param  list<CvRole>  $experience
     * @param  list<CvStudy>  $education
     * @param  list<CvSkillGroup>  $skills
     * @param  list<CvCertification>  $certifications
     * @param  list<CvProject>  $projects
     */
    public function __construct(
        public string $name,
        public ?string $role,
        public ?string $summary,
        public ?string $location,
        public ?string $email,
        public ?string $phone,
        public ?string $website,
        public array $links,
        public array $experience,
        public array $education,
        public array $skills,
        public array $certifications,
        public array $projects,
    ) {}

    public static function build(CvOptions $options = new CvOptions): self
    {
        $profile = Profile::current();

        return new self(
            name: $profile->name,
            role: self::text($profile->role),
            summary: self::text($profile->summary) ?? self::text($profile->headline),
            location: self::text($profile->location),
            email: self::text($profile->email),
            phone: self::text($profile->phone),
            website: self::displayUrl((string) config('app.url')),
            // Profiles a recruiter can open; the feed and the email address (already listed) are left out.
            links: array_values(Social::query()->visible()->ordered()->get()
                ->reject(fn (Social $social): bool => in_array($social->platform, [SocialPlatform::Rss, SocialPlatform::Email], true))
                ->map(fn (Social $social): array => ['label' => $social->platform->getLabel(), 'url' => self::displayUrl($social->url)])
                ->filter(fn (array $link): bool => $link['url'] !== '')
                ->all()),
            experience: self::experience($options->maxRoles),
            education: self::education(),
            skills: self::skills(),
            certifications: $options->includeCertifications ? self::certifications() : [],
            projects: $options->includeProjects ? self::projects() : [],
        );
    }

    /**
     * `MMM YYYY – MMM YYYY`, or `– Present` while ongoing (ATS parsers read this format reliably).
     */
    public static function period(CarbonInterface $start, ?CarbonInterface $end): string
    {
        return $start->format('M Y').' – '.($end?->format('M Y') ?? 'Present');
    }

    /**
     * A URL as short plain text (`github.com/jane`), readable on paper and by parsers.
     */
    public static function displayUrl(?string $url): string
    {
        return rtrim((string) preg_replace('#^(https?://)?(www\.)?#i', '', trim((string) $url)), '/');
    }

    /**
     * @return list<CvRole>
     */
    private static function experience(?int $maxRoles): array
    {
        $roles = Experience::query()->visible()->with(['company', 'skills'])->orderByDesc('start_date')->orderByDesc('id')->get();

        if ($maxRoles !== null) {
            $roles = $roles->take($maxRoles);
        }

        return array_values($roles->map(fn (Experience $experience): array => [
            'role' => $experience->role,
            'organization' => $experience->organization,
            'location' => self::text($experience->location_label) ?? $experience->work_mode->getLabel(),
            'period' => self::period($experience->start_date, $experience->end_date),
            'summary' => self::text($experience->summary),
            'highlights' => self::lines($experience->highlights),
            'stack' => self::lines($experience->stack),
        ])->all());
    }

    /**
     * @return list<CvStudy>
     */
    private static function education(): array
    {
        return array_values(Education::query()->visible()->get()
            ->sort(fn (Education $a, Education $b): int => [$b->end_date === null, $b->end_date, $b->start_date] <=> [$a->end_date === null, $a->end_date, $a->start_date])
            ->map(fn (Education $education): array => [
                'degree' => $education->degree,
                'field' => self::text($education->field_of_study),
                'institution' => $education->institution,
                'location' => self::text($education->location_label),
                'period' => self::period($education->start_date, $education->end_date),
                'grade' => self::text($education->grade),
                'description' => self::text($education->description),
                'achievements' => self::lines($education->achievements),
            ])->all());
    }

    /**
     * @return list<CvSkillGroup>
     */
    private static function skills(): array
    {
        return array_values(SkillCategory::query()->ordered()
            ->with(['skills' => fn ($query) => $query->visible()->ordered()])
            ->get()
            ->map(fn (SkillCategory $category): array => ['category' => $category->name, 'skills' => array_values($category->skills->map(fn (Skill $skill): string => $skill->name)->all())])
            ->filter(fn (array $group): bool => $group['skills'] !== [])
            ->all());
    }

    /**
     * @return list<CvCertification>
     */
    private static function certifications(): array
    {
        return array_values(Certification::query()->visible()->orderByDesc('issued_at')->orderBy('sort_order')->get()
            ->reject(fn (Certification $certification): bool => $certification->is_expired)
            ->map(fn (Certification $certification): array => [
                'name' => $certification->name,
                'issuer' => $certification->issuer,
                'issued' => $certification->issued_at->format('M Y'),
                'expires' => $certification->expires_at?->format('M Y'),
                'credential' => self::text($certification->credential_id),
                'url' => $certification->credential_url !== null ? self::displayUrl($certification->credential_url) : null,
            ])->all());
    }

    /**
     * @return list<CvProject>
     */
    private static function projects(): array
    {
        return array_values(Project::query()->published()->featured()->with('skills')->orderByDesc('year')->orderBy('sort_order')->get()
            ->map(fn (Project $project): array => [
                'title' => $project->title,
                'year' => $project->year,
                'description' => self::text($project->tagline) ?? self::text(Str::limit((string) $project->summary, 220)),
                'stack' => self::lines($project->stack),
                'url' => self::displayUrl(url("/projects/{$project->slug}")),
            ])->all());
    }

    private static function text(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }

    /**
     * @param  iterable<mixed>  $values
     * @return list<string>
     */
    private static function lines(iterable $values): array
    {
        $lines = [];

        foreach ($values as $value) {
            if (is_string($value) && ($value = self::text($value)) !== null) {
                $lines[] = $value;
            }
        }

        return $lines;
    }
}
