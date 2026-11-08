<?php

namespace App\Mcp\Tools\Articles;

use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('restore_article')]
#[Title('Restore a deleted article')]
#[Description('Brings a deleted article back from the trash (list deleted ones with list_articles include_deleted=true).')]
#[IsIdempotent]
class RestoreArticleTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['article' => ['required']])['article'];
        $article = Records::article($reference, withTrashed: true);

        if ($article === null) {
            return Records::notFound('article', $reference, 'list_articles with include_deleted=true');
        }

        $article->restore();

        return Response::structured([
            'message' => "Restored article \"{$article->title}\".",
            'article' => Payload::article($article->refresh()),
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
