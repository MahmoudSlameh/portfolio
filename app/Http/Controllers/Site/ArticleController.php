<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\WritingArchiveRequest;
use App\Models\Article;
use App\Support\Content\PortfolioContent;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Seo;
use App\Support\Templates\TemplateManager;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(WritingArchiveRequest $request, PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $filters = $request->filters();
        $allArticles = $content->articles();

        return Inertia::render($templates->page('WritingArchive'), [
            'articles' => $content->articles(['search' => $filters['q'] ?? null, 'tag' => $filters['tag'] ?? null]),
            'allArticles' => $allArticles,
            'tags' => $content->articleTags(),
            'search' => (object) $filters,
            'seo' => $seo->page(
                title: 'Writing',
                description: 'Essays and notes on software engineering — architecture, migrations, tooling and the craft of shipping calm software.',
                path: '/writing',
                jsonLd: [
                    JsonLd::page('Blog', 'Writing', '/writing', array_map(
                        fn (array $article): array => ['name' => $article['title'], 'path' => "/writing/{$article['slug']}"],
                        $allArticles,
                    )),
                    JsonLd::breadcrumbs(['Writing' => '/writing']),
                ],
            ),
        ]);
    }

    public function show(string $slug, PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $article = Article::query()->published()->where('slug', $slug)->with('media')->firstOrFail();
        $detail = $content->articleBySlug($slug);
        abort_if($detail === null, 404);

        return Inertia::render($templates->page('Article'), [
            'article' => $detail,
            'seo' => $seo->page(
                title: $article->meta_title ?: $article->title,
                description: $article->meta_description ?: $article->excerpt,
                path: "/writing/{$article->slug}",
                type: 'article',
                image: $article->getFirstMedia('cover'),
                imageAlt: $article->cover_alt ?? $article->title,
                publishedAt: $article->published_at,
                modifiedAt: $article->updated_at,
                tags: $article->tags,
                jsonLd: [
                    JsonLd::article($article),
                    JsonLd::breadcrumbs(['Writing' => '/writing', $article->title => "/writing/{$article->slug}"]),
                ],
            ),
        ]);
    }
}
