<?php

namespace App\Mcp\Support;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Company;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Models\Skill;
use App\Models\SkillCategory;
use DateTimeInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Turns models into the JSON the MCP tools return. Field names match the tool inputs, so Claude can
 * read a record, change a few fields and send them back.
 */
final class Payload
{
    /**
     * Short form for lists.
     *
     * @return array<string, mixed>
     */
    public static function projectSummary(Project $project): array
    {
        return [
            'id' => $project->id,
            'slug' => $project->slug,
            'title' => $project->title,
            'tagline' => $project->tagline,
            'year' => $project->year,
            'status' => $project->status->value,
            'category' => $project->category->value,
            'is_published' => $project->is_published,
            'is_featured' => $project->is_featured,
            'is_deleted' => $project->trashed(),
            'company' => $project->company === null ? null : ['id' => $project->company->id, 'name' => $project->company->name],
            'stack' => $project->stack,
            'has_cover' => $project->getFirstMedia('cover') !== null,
            'updated_at' => self::date($project->updated_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function project(Project $project): array
    {
        $project->loadMissing(['company', 'experience', 'skills', 'galleryItems.media', 'media']);

        return [
            'id' => $project->id,
            'slug' => $project->slug,
            'title' => $project->title,
            'tagline' => $project->tagline,
            'summary' => $project->summary,
            'year' => $project->year,
            'version' => $project->version,
            'status' => $project->status->value,
            'category' => $project->category->value,
            'is_featured' => $project->is_featured,
            'is_published' => $project->is_published,
            'published_at' => self::date($project->published_at),
            'is_deleted' => $project->trashed(),
            'company_id' => $project->company_id,
            'company' => $project->company?->name,
            'experience_id' => $project->experience_id,
            'experience' => $project->experience === null ? null : "{$project->experience->role} · {$project->experience->organization}",
            'role' => $project->role,
            'team' => $project->team,
            'timeline' => $project->timeline,
            'stack' => $project->stack,
            'overview' => $project->overview,
            'problem' => $project->problem,
            'approach' => $project->approach,
            'features' => $project->features,
            'challenges' => $project->challenges,
            'architecture' => $project->architecture,
            'metrics' => $project->metrics,
            'links' => $project->links,
            'cover' => self::image($project->getFirstMedia('cover'), $project->cover_alt),
            'cover_alt' => $project->cover_alt,
            'gallery' => $project->galleryItems->map(fn (ProjectGalleryItem $item): array => self::galleryItem($item))->values()->all(),
            'meta_title' => $project->meta_title,
            'meta_description' => $project->meta_description,
            'urls' => [
                'site' => $project->isPublished() ? url("/projects/{$project->slug}") : null,
                'admin' => ProjectResource::getUrl('edit', ['record' => $project], panel: 'admin'),
            ],
            'updated_at' => self::date($project->updated_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function galleryItem(ProjectGalleryItem $item): array
    {
        return [
            'id' => $item->id,
            'alt' => $item->alt,
            'caption' => $item->caption,
            'sort_order' => $item->sort_order,
            'image' => self::image($item->getFirstMedia('image'), $item->alt),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function skill(Skill $skill): array
    {
        return [
            'id' => $skill->id,
            'name' => $skill->name,
            'slug' => $skill->slug,
            'category' => $skill->category?->name,
            'proficiency' => $skill->proficiency,
            'years' => $skill->years,
            'icon' => $skill->icon,
            'is_visible' => $skill->is_visible,
            'sort_order' => $skill->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function skillCategory(SkillCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function company(Company $company): array
    {
        return [
            'id' => $company->id,
            'slug' => $company->slug,
            'name' => $company->name,
            'kind' => $company->kind->value,
            'website_url' => $company->website_url,
            'industry' => $company->industry,
            'city' => $company->city,
            'country_code' => $company->country_code,
            'period_label' => $company->period_label,
            'engagement' => $company->engagement,
            'wordmark_style' => $company->wordmark_style->value,
            'is_featured' => $company->is_featured,
            'is_visible' => $company->is_visible,
            'sort_order' => $company->sort_order,
            'logo' => self::image($company->getFirstMedia('logo'), $company->name),
            'logo_dark' => self::image($company->getFirstMedia('logo_dark'), $company->name),
            'urls' => ['admin' => CompanyResource::getUrl('edit', ['record' => $company], panel: 'admin')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function experience(Experience $experience): array
    {
        return [
            'id' => $experience->id,
            'company_id' => $experience->company_id,
            'organization' => $experience->organization,
            'organization_name' => $experience->organization_name,
            'role' => $experience->role,
            'employment_type' => $experience->employment_type->value,
            'work_mode' => $experience->work_mode->value,
            'country_code' => $experience->country_code,
            'city' => $experience->city,
            'start_date' => $experience->start_date->format('Y-m'),
            'end_date' => $experience->end_date?->format('Y-m'),
            'is_current' => $experience->is_current,
            'summary' => $experience->summary,
            'highlights' => $experience->highlights,
            'branch' => $experience->branch?->value,
            'version' => $experience->version,
            'stack' => $experience->stack,
            'is_visible' => $experience->is_visible,
            'sort_order' => $experience->sort_order,
            'urls' => ['admin' => ExperienceResource::getUrl('edit', ['record' => $experience], panel: 'admin')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function profile(Profile $profile): array
    {
        return [
            'name' => $profile->name,
            'initials' => $profile->initials,
            'role' => $profile->role,
            'headline' => $profile->headline,
            'summary' => $profile->summary,
            'story' => $profile->story,
            'focus_areas' => $profile->focus_areas,
            'location' => $profile->location,
            'timezone' => $profile->timezone,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'availability_status' => $profile->availability_status->value,
            'availability_label' => $profile->availability_label,
            'availability_note' => $profile->availability_note,
            'stats' => $profile->stats,
            'principles' => $profile->principles,
            'portrait' => self::image($profile->getFirstMedia('portrait'), $profile->portrait_alt),
        ];
    }

    /**
     * @return array{url: string, mime_type: string|null, width: int|null, height: int|null, alt: string|null}|null
     */
    public static function image(?Media $media, ?string $alt): ?array
    {
        if ($media === null) {
            return null;
        }

        $width = $media->getCustomProperty('width');
        $height = $media->getCustomProperty('height');

        return [
            'url' => $media->getUrl(),
            'mime_type' => $media->mime_type,
            'width' => is_numeric($width) ? (int) $width : null,
            'height' => is_numeric($height) ? (int) $height : null,
            'alt' => $alt,
        ];
    }

    public static function date(?DateTimeInterface $date): ?string
    {
        return $date?->format(DATE_ATOM);
    }
}
