<?php

namespace App\Models;

use App\Models\Concerns\IsSingleton;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Site-wide settings: active template, SEO defaults and page toggles (single row).
 *
 * @property int $id
 * @property string $active_template Template id (see App\Support\Templates\TemplateRegistry)
 * @property string $site_name
 * @property string $title_separator
 * @property string|null $meta_description
 * @property string|null $twitter_handle
 * @property string|null $google_site_verification
 * @property string|null $bing_site_verification
 * @property string|null $analytics_snippet
 * @property array<string, bool> $enabled_pages
 * @property string|null $contact_recipient
 * @property bool $indexable
 * @property string|null $services_kicker
 * @property string|null $services_title
 * @property string|null $services_highlight
 * @property bool $ai_enabled
 * @property string|null $ai_provider
 * @property string|null $ai_model
 * @property string|null $ai_api_key Encrypted; hidden from arrays and JSON
 * @property string|null $ai_base_url
 * @property int|null $ai_daily_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'active_template', 'site_name', 'title_separator', 'meta_description', 'twitter_handle',
    'google_site_verification', 'bing_site_verification', 'analytics_snippet', 'enabled_pages',
    'contact_recipient', 'indexable', 'services_kicker', 'services_title', 'services_highlight',
    'ai_enabled', 'ai_provider', 'ai_model', 'ai_api_key', 'ai_base_url', 'ai_daily_limit',
])]
class SiteSetting extends Model implements HasMedia
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory, InteractsWithMedia, IsSingleton, RegistersImageConversions;

    /**
     * Secondary public pages that can be switched off from the panel.
     *
     * @var list<string>
     */
    public const TOGGLEABLE_PAGES = ['writing', 'books', 'uses', 'now'];

    /**
     * The AI key never leaves the server (docs/12-ai-templates.md §6).
     *
     * @var list<string>
     */
    protected $hidden = ['ai_api_key'];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'active_template' => 'changelog',
        'title_separator' => '—',
        'indexable' => true,
        'ai_enabled' => true,
        'enabled_pages' => '{"writing":true,"books":true,"uses":true,"now":true}',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_pages' => 'array',
            'indexable' => 'boolean',
            'ai_enabled' => 'boolean',
            'ai_api_key' => 'encrypted',
            'ai_daily_limit' => 'integer',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function singletonDefaults(): array
    {
        return [
            'active_template' => config('portfolio.templates.default'),
            'site_name' => config('app.name'),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default_og_image')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
        $this->addMediaCollection('favicon')->singleFile()->acceptsMimeTypes(['image/svg+xml', 'image/png']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['default_og_image'], og: ['default_og_image']);
    }

    /**
     * Whether a secondary page (writing, books, uses, now) is switched on. Unknown pages are always on.
     */
    public function isPageEnabled(string $page): bool
    {
        if (! in_array($page, self::TOGGLEABLE_PAGES, true)) {
            return true;
        }

        return (bool) ($this->enabled_pages[$page] ?? true);
    }
}
