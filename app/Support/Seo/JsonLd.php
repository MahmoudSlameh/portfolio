<?php

namespace App\Support\Seo;

use App\Models\Article;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\Social;

/**
 * schema.org structured data for the public pages (docs/07-seo.md § 2).
 */
final class JsonLd
{
    /**
     * @return array<string, mixed>
     */
    public static function person(): array
    {
        $profile = Profile::current();
        $current = Experience::query()->visible()->whereNull('end_date')->with('company')->latest('start_date')->first();
        $portrait = $profile->getFirstMedia('portrait');

        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => Seo::url('/#person'),
            'name' => $profile->name,
            'jobTitle' => $profile->role,
            'description' => $profile->summary ?: $profile->headline,
            'url' => Seo::url('/'),
            'email' => $profile->email ? 'mailto:'.$profile->email : null,
            'image' => $portrait?->getFullUrl(),
            'address' => $profile->location ? ['@type' => 'PostalAddress', 'addressLocality' => $profile->location] : null,
            'sameAs' => Social::query()->visible()->ordered()->pluck('url')
                ->filter(fn (string $url): bool => str_starts_with($url, 'http'))
                ->values()
                ->all(),
            'worksFor' => $current !== null ? self::clean([
                '@type' => 'Organization',
                'name' => $current->organization,
                'url' => $current->company?->website_url,
            ]) : null,
            'alumniOf' => Education::query()->visible()->get()
                ->map(fn (Education $education): array => self::clean([
                    '@type' => 'EducationalOrganization',
                    'name' => $education->institution,
                    'url' => $education->institution_url,
                ]))
                ->unique('name')
                ->values()
                ->all(),
            'knowsAbout' => Skill::query()->visible()->whereNotNull('skill_category_id')->ordered()->pluck('name')->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => Seo::url('/#website'),
            'name' => SiteSetting::current()->site_name,
            'url' => Seo::url('/'),
            'inLanguage' => 'en',
            'publisher' => ['@id' => Seo::url('/#person')],
        ];
    }

    /**
     * @param  array<string, string>  $trail  name => path (the home crumb is added automatically)
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $trail): array
    {
        $items = ['Home' => '/', ...$trail];
        $position = 0;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->map(fn (string $path, string $name): array => [
                '@type' => 'ListItem',
                'position' => ++$position,
                'name' => $name,
                'item' => Seo::url($path),
            ])->values()->all(),
        ];
    }

    /**
     * @param  'CollectionPage'|'Blog'|'WebPage'  $type
     * @param  list<array{name: string, path: string}>  $items
     * @return array<string, mixed>
     */
    public static function page(string $type, string $name, string $path, array $items = []): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $name,
            'url' => Seo::url($path),
            'inLanguage' => 'en',
            'isPartOf' => ['@id' => Seo::url('/#website')],
            'mainEntity' => $items === [] ? null : [
                '@type' => 'ItemList',
                'itemListElement' => array_map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'url' => Seo::url($item['path']),
                ], $items, array_keys($items)),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function project(Project $project): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $project->title,
            'headline' => $project->tagline,
            'description' => $project->meta_description ?: $project->summary,
            'url' => Seo::url("/projects/{$project->slug}"),
            'image' => $project->getFirstMedia('cover')?->getFullUrl(),
            'dateCreated' => (string) $project->year,
            'dateModified' => $project->updated_at?->toIso8601String(),
            'keywords' => implode(', ', $project->stack),
            'creator' => ['@id' => Seo::url('/#person')],
            'sourceOrganization' => $project->company ? ['@type' => 'Organization', 'name' => $project->company->name] : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(Article $article): array
    {
        return self::clean([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'description' => $article->meta_description ?: $article->excerpt,
            'url' => Seo::url("/writing/{$article->slug}"),
            'mainEntityOfPage' => Seo::url("/writing/{$article->slug}"),
            'image' => $article->getFirstMedia('cover')?->getFullUrl(),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => ($article->updated_at ?? $article->published_at)?->toIso8601String(),
            'author' => ['@id' => Seo::url('/#person'), '@type' => 'Person', 'name' => Profile::current()->name],
            'publisher' => ['@id' => Seo::url('/#person')],
            'keywords' => implode(', ', $article->tags),
            'wordCount' => $article->wordCount(),
            'timeRequired' => 'PT'.$article->readingMinutes().'M',
            'inLanguage' => 'en',
        ]);
    }

    /**
     * Drop null / empty values so the output stays valid and compact.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function clean(array $data): array
    {
        return array_filter($data, fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }
}
