<?php

namespace App\Mcp\Tools\Articles;

use App\Enums\ArticleLanguage;
use App\Enums\ArticleStatus;
use App\Mcp\Support\Payload;
use App\Models\Article;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_articles')]
#[Title('List articles')]
#[Description('Lists the blog articles (drafts included), newest first, with filters. Use it to check whether an article already exists before creating one, and to see the parts of a series.')]
#[IsReadOnly]
class ListArticlesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', Rule::enum(ArticleLanguage::class)],
            'series' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:50'],
            'published' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $articles = Article::query()
            ->with('media')
            ->when($data['include_deleted'] ?? false, fn (Builder $query) => $query->withTrashed())
            ->when($data['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($data['language'] ?? null, fn (Builder $query, string $language) => $query->where('language', $language))
            ->when($data['series'] ?? null, fn (Builder $query, string $series) => $query->where('series', $series)->orderBy('series_order'))
            ->when($data['tag'] ?? null, fn (Builder $query, string $tag) => $query->whereJsonContains('tags', $tag))
            ->when(isset($data['published']), fn (Builder $query) => $query->where('status', $data['published'] ? ArticleStatus::Published : ArticleStatus::Draft))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(perPage: (int) ($data['per_page'] ?? 25), page: (int) ($data['page'] ?? 1));

        return Response::structured([
            'articles' => $articles->getCollection()->map(fn (Article $article): array => Payload::articleSummary($article))->values()->all(),
            'page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
            'total' => $articles->total(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Matches the title, slug or excerpt.'),
            'language' => $schema->string()->enum(ArticleLanguage::class),
            'series' => $schema->string()->description('Only the articles of this series, in series order.'),
            'tag' => $schema->string()->description('Only articles with this tag.'),
            'published' => $schema->boolean()->description('true = only published (or scheduled), false = only drafts.'),
            'include_deleted' => $schema->boolean()->description('Also list deleted articles (they can be restored with restore_article).'),
            'page' => $schema->integer()->min(1)->default(1),
            'per_page' => $schema->integer()->min(1)->max(100)->default(25),
        ];
    }
}
