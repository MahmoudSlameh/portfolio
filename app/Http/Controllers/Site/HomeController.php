<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Support\Content\PortfolioContent;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Seo;
use App\Support\Templates\TemplateManager;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $profile = Profile::current();

        return Inertia::render($templates->page('Home'), [
            'profile' => $content->profile(),
            'socials' => $content->socials(),
            'skillGroups' => $content->skillGroups(),
            'career' => $content->career(),
            'projects' => $content->projects(['featured' => true]),
            'companies' => array_values(array_filter($content->companies(), fn (array $company): bool => $company['featured'])),
            'testimonials' => $content->testimonials(),
            'education' => $content->education(),
            'certifications' => $content->certifications(),
            'articles' => $content->articles(),
            'books' => $content->books(),
            'seo' => $seo->page(
                title: "{$profile->name} — {$profile->role}",
                description: $profile->headline ?: $profile->summary,
                path: '/',
                type: 'profile',
                image: $profile->getFirstMedia('og_image') ?? $profile->getFirstMedia('portrait'),
                imageAlt: $profile->portrait_alt ?? $profile->name,
                jsonLd: [JsonLd::person(), JsonLd::website()],
                appendSiteName: false,
            ),
        ]);
    }
}
