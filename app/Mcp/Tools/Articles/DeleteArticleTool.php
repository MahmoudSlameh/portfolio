<?php

namespace App\Mcp\Tools\Articles;

use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_article')]
#[Title('Delete an article')]
#[Description('Moves an article to the trash: it disappears from the site and can be brought back with restore_article (or from the panel). Only do this when the owner asked for it.')]
#[IsDestructive]
class DeleteArticleTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['article' => ['required']])['article'];
        $article = Records::article($reference);

        if ($article === null) {
            return Records::notFound('article', $reference, 'list_articles');
        }

        $article->delete();

        return Response::structured([
            'message' => "Moved article \"{$article->title}\" to the trash. restore_article brings it back.",
            'article' => ['id' => $article->id, 'slug' => $article->slug, 'title' => $article->title],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'article' => $schema->string()->description('The article\'s id or slug.')->required(),
        ];
    }
}
