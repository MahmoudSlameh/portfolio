<?php

namespace App\Mcp\Tools\Articles;

use App\Mcp\Support\ArticleFields;
use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('update_article')]
#[Title('Update an article')]
#[Description('Changes an article. Send only the fields to change. "body" replaces the whole body (start from get_article\'s Markdown and keep the media:<uuid> images you want to keep); list fields (tags, sources, project_ids) replace the stored list.')]
#[IsOpenWorld]
class UpdateArticleTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['article' => ['required']])['article'];
        $article = Records::article($reference);

        if ($article === null) {
            return Records::notFound('article', $reference, 'list_articles');
        }

        $data = Arr::except($request->validate(ArticleFields::rules($article)), ['article']);

        if ($data === []) {
            return Response::error('Nothing to update: send at least one field to change.');
        }

        $warnings = ArticleFields::save($article, $data);
        $article->refresh();

        return Response::structured(array_filter([
            'message' => "Updated article \"{$article->title}\": ".implode(', ', array_keys($data)).'.',
            'warnings' => $warnings ?: null,
            'article' => Payload::article($article),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'article' => $schema->string()->description('The article\'s id or slug.')->required(),
            ...ArticleFields::schema($schema),
        ];
    }
}
