<?php

namespace App\Mcp\Tools\Articles;

use App\Mcp\Support\ArticleImages;
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

#[Name('add_article_image')]
#[Title('Add an image to an article\'s body')]
#[Description('Stores an image in an article\'s body: in place of the placeholder ![…](image:<key>) when "placeholder" is given, otherwise at the end of the body. Give the image as image_url, image_base64 or screenshot_url; for a local file use request_image_upload with target article_image instead.')]
#[IsOpenWorld]
class AddArticleImageTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'article' => ['required'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'alpha_dash:ascii', 'max:100'],
            ...ImageInput::rules(),
        ], ImageInput::messages());

        $article = Records::article($data['article']);

        if ($article === null) {
            return Records::notFound('article', $data['article'], 'list_articles');
        }

        try {
            ['media' => $media, 'filled_placeholder' => $filled] = ImageAttacher::articleImage(
                $article,
                ImageInput::fetch($data, MimeTypes::RASTER),
                $data['alt'],
                $data['caption'] ?? null,
                $data['placeholder'] ?? null,
            );
        } catch (ImageRejected $exception) {
            return Response::error($exception->getMessage());
        }

        $placeholder = $data['placeholder'] ?? null;
        $where = match (true) {
            $filled => "in place of image:{$placeholder}",
            filled($placeholder) => "at the end of the body (there is no placeholder image:{$placeholder})",
            default => 'at the end of the body',
        };

        return Response::structured([
            'message' => "Added an image to \"{$article->title}\" {$where}.",
            'image' => ['src' => "media:{$media->uuid}", ...(array) Payload::image($media, $data['alt'])],
            'pending_images' => ArticleImages::placeholders($article->refresh()->body),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'article' => $schema->string()->description('The article\'s id or slug.')->required(),
            'alt' => $schema->string()->description('Alt text: what the image shows.')->required(),
            'caption' => $schema->string()->description('Short caption shown under the image.'),
            'placeholder' => $schema->string()->description('Key of the placeholder to fill: "step-1" for ![…](image:step-1) in the body.'),
            ...ImageInput::schema($schema),
        ];
    }
}
