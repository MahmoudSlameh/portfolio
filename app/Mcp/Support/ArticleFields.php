<?php

namespace App\Mcp\Support;

use App\Enums\ArticleLanguage;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Support\Content\ArticleMarkdown;
use App\Support\Content\VideoEmbed;
use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Input schema, validation and saving shared by the create_article and update_article tools. The fields
 * mirror the panel's article form (app/Filament/Resources/Articles/Schemas/ArticleForm.php); the body is
 * Markdown (see ArticleMarkdown and ArticleImages).
 */
final class ArticleFields
{
    /**
     * Columns written as given (lists replace the stored list).
     */
    private const COLUMNS = [
        'title', 'slug', 'excerpt', 'language', 'tags', 'series', 'series_order', 'sources', 'video_url',
        'published_at', 'cover_alt', 'meta_title', 'meta_description',
    ];

    /**
     * Text columns where an empty string means "clear it".
     */
    private const NULLABLE_TEXT = ['slug', 'excerpt', 'series', 'video_url', 'cover_alt', 'meta_title', 'meta_description'];

    /**
     * @return array<string, mixed>
     */
    public static function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('The article\'s title.'),
            'slug' => $schema->string()->description('URL slug (/writing/<slug>): latin letters, numbers and dashes. Always send an English slug for Arabic articles (e.g. "database-indexes"); otherwise it is generated from the title.'),
            'excerpt' => $schema->string()->description('One or two sentences for cards, search results and the default meta description, max 400 characters.'),
            'language' => $schema->string()->enum(ArticleLanguage::class)->description('en (default) or ar. Sets the language and the reading direction (ar = right to left) of the article on the site.'),
            'body' => $schema->string()->description(<<<'TEXT'
The article in Markdown: ## and ### headings, paragraphs, **bold**, *italic*, `code`, [links](https://…), - and 1. lists, > quotes, ``` fenced code (```php title="app/Foo.php" adds a file name), GitHub tables, --- rules. Callout: "> [!NOTE] Title" with the text on the next "> " lines. Images on their own line: ![alt](src "caption") where src is media:<uuid> (an image of this article, as get_article returns it), image:<key> (a placeholder that add_article_image or request_image_upload fills later) or an https URL (downloaded now). Raw HTML is shown as text. Replaces the whole body; images left out of it are deleted.
TEXT),
            'tags' => $schema->array()->items($schema->string())->description('Topic tags, e.g. ["system-design", "databases"]. Replaces the list.'),
            'series' => $schema->string()->description('Name of the series the article belongs to, e.g. "System Design". Empty clears it.'),
            'series_order' => $schema->integer()->min(1)->max(999)->description('Position in the series (1, 2, 3…); shown as "System Design · 01".'),
            'sources' => $schema->array()->items($schema->object([
                'title' => $schema->string()->description('What the source is, e.g. "Designing Data-Intensive Applications, ch. 5".')->required(),
                'url' => $schema->string()->description('http(s) link to it.')->required(),
            ]))->description('Sources listed at the end of the article. Replaces the list.'),
            'video_url' => $schema->string()->description('A YouTube or Vimeo link, embedded under the cover. Empty removes it.'),
            'project_ids' => $schema->array()->items($schema->integer())->description('Related projects (ids from list_projects). Replaces the list.'),
            'is_published' => $schema->boolean()->description('Visible on the public site. New articles are drafts unless the owner asked to publish.'),
            'published_at' => $schema->string()->description('ISO 8601 date-time; a future date schedules the article. Empty = now, when is_published is true.'),
            'cover_alt' => $schema->string()->description('Alt text of the cover image.'),
            'meta_title' => $schema->string()->description('SEO title override, ≤ 60 characters.'),
            'meta_description' => $schema->string()->description('SEO description override, ≤ 160 characters.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(?Article $article = null): array
    {
        $text = fn (int $max): array => ['sometimes', 'nullable', 'string', "max:{$max}"];

        return [
            'title' => [$article === null ? 'required' : 'sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'alpha_dash:ascii', 'max:255', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'excerpt' => $text(400),
            'language' => ['sometimes', Rule::enum(ArticleLanguage::class)],
            'body' => ['sometimes', 'nullable', 'string', 'max:300000'],
            'tags' => ['sometimes', 'array', 'max:20'],
            'tags.*' => ['string', 'distinct', 'max:50'],
            'series' => $text(255),
            'series_order' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'sources' => ['sometimes', 'array', 'max:50'],
            'sources.*.title' => ['required', 'string', 'max:255'],
            'sources.*.url' => ['required', 'string', 'url:http,https', 'max:2048'],
            'video_url' => ['sometimes', 'nullable', 'string', 'url:http,https', 'max:2048', self::videoRule()],
            'project_ids' => ['sometimes', 'array', 'max:20'],
            'project_ids.*' => ['integer', 'distinct', Rule::exists('projects', 'id')],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'cover_alt' => $text(255),
            'meta_title' => $text(70),
            'meta_description' => $text(255),
        ];
    }

    /**
     * Saves the validated input on the article.
     *
     * @param  array<string, mixed>  $data  validated input
     * @return list<string> warnings about the body (images that could not be used)
     */
    public static function save(Article $article, array $data): array
    {
        $attributes = Arr::only($data, self::COLUMNS);

        foreach (self::NULLABLE_TEXT as $column) {
            if (array_key_exists($column, $attributes) && is_string($attributes[$column])) {
                $attributes[$column] = trim($attributes[$column]) === '' ? null : trim($attributes[$column]);
            }
        }

        if (array_key_exists('sources', $attributes)) {
            $attributes['sources'] = array_values(array_map(
                fn (array $source): array => ['title' => trim((string) $source['title']), 'url' => trim((string) $source['url'])],
                (array) $attributes['sources'],
            ));
        }

        if (array_key_exists('tags', $attributes)) {
            $attributes['tags'] = array_values(array_filter(array_map(fn (mixed $tag): string => trim((string) $tag), (array) $attributes['tags']), fn (string $tag): bool => $tag !== ''));
        }

        if (array_key_exists('is_published', $data)) {
            $attributes['status'] = $data['is_published'] ? ArticleStatus::Published : ArticleStatus::Draft;
        }

        // The article must exist before images can be stored on it.
        DB::transaction(function () use ($article, $attributes, $data): void {
            $article->fill($attributes)->save();

            if (array_key_exists('project_ids', $data)) {
                $article->projects()->sync(array_values((array) $data['project_ids']));
            }
        });

        if (! array_key_exists('body', $data)) {
            return [];
        }

        ['doc' => $doc, 'warnings' => $warnings] = ArticleImages::resolve($article, ArticleMarkdown::toDocument((string) $data['body']));

        $article->forceFill(['body' => $doc])->save();
        ArticleImages::prune($article);

        return $warnings;
    }

    /**
     * Only links the site can embed.
     */
    private static function videoRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && trim($value) !== '' && VideoEmbed::playerUrl($value) === null) {
                $fail('video_url must be a YouTube or Vimeo video link.');
            }
        };
    }
}
