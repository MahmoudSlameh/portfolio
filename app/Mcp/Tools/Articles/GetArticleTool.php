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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_article')]
#[Title('Get an article')]
#[Description('Returns one article with every field, its body as Markdown (images as media:<uuid>, the format update_article takes back), placeholders still waiting for an image, reading time and links.')]
#[IsReadOnly]
class GetArticleTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['article' => ['required']])['article'];
        $article = Records::article($reference, withTrashed: true);

        if ($article === null) {
            return Records::notFound('article', $reference, 'list_articles');
        }

        return Response::structured(['article' => Payload::article($article)]);
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
