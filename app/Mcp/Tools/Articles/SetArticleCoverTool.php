<?php

namespace App\Mcp\Tools\Articles;

use App\Mcp\Support\ImageAttacher;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use App\Support\Media\ImageRejected;
use App\Support\Media\MimeTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('set_article_cover')]
#[Title('Set an article\'s cover image')]
#[Description('Sets (replaces) the cover image of an article: 16:9 works best, it is shown on cards, at the top of the article and in social previews. Give the image as image_url or image_base64; for a local file use request_image_upload with target article_cover.')]
#[IsOpenWorld]
class SetArticleCoverTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'article' => ['required'],
            'alt' => ['required', 'string', 'max:255'],
            ...ImageInput::rules(),
        ], ImageInput::messages());

        $article = Records::article($data['article']);

        if ($article === null) {
            return Records::notFound('article', $data['article'], 'list_articles');
        }

        try {
            ImageAttacher::articleCover($article, ImageInput::fetch($data, MimeTypes::RASTER), $data['alt']);
        } catch (ImageRejected $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::structured([
            'message' => "Set the cover of \"{$article->title}\".",
            'cover' => Payload::image($article->refresh()->getFirstMedia('cover'), $article->cover_alt),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'article' => $schema->string()->description('The article\'s id or slug.')->required(),
            'alt' => $schema->string()->description('Alt text: what the image shows, for screen readers and search engines.')->required(),
            ...ImageInput::schema($schema),
        ];
    }
}
