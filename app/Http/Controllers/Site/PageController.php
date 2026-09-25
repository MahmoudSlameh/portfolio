<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\LibraryRequest;
use App\Models\NowPage;
use App\Support\Content\PortfolioContent;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Seo;
use App\Support\Templates\TemplateManager;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The secondary pages: books, uses and now.
 */
class PageController extends Controller
{
    public function books(LibraryRequest $request, PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $filters = $request->filters();

        return Inertia::render($templates->page('Books'), [
            'books' => $content->books(['status' => $filters['status'] ?? null, 'category' => $filters['category'] ?? null]),
            'stats' => $content->bookStats(),
            'search' => (object) $filters,
            'seo' => $seo->page(
                title: 'Bookshelf',
                description: 'Books I have read, am reading and plan to read — with notes and ratings.',
                path: '/books',
                jsonLd: [JsonLd::page('CollectionPage', 'Bookshelf', '/books'), JsonLd::breadcrumbs(['Bookshelf' => '/books'])],
            ),
        ]);
    }

    public function uses(PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        return Inertia::render($templates->page('Uses'), [
            'groups' => $content->uses(),
            'seo' => $seo->page(
                title: 'Uses',
                description: 'The hardware, software and development setup I use every day.',
                path: '/uses',
                jsonLd: [JsonLd::page('WebPage', 'Uses', '/uses'), JsonLd::breadcrumbs(['Uses' => '/uses'])],
            ),
        ]);
    }

    public function now(PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $now = NowPage::current();

        return Inertia::render($templates->page('Now'), [
            'now' => $content->now(),
            'seo' => $seo->page(
                title: 'Now',
                description: collect($now->focus)->pluck('title')->prepend('What I am focused on right now')->implode(' · '),
                path: '/now',
                modifiedAt: $now->updated_at,
                jsonLd: [JsonLd::page('WebPage', 'Now', '/now'), JsonLd::breadcrumbs(['Now' => '/now'])],
            ),
        ]);
    }
}
