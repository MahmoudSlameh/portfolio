<?php

namespace App\Mcp\Tools\Articles;

use App\Enums\ArticleStatus;
use App\Mcp\Support\ArticleFields;
use App\Mcp\Support\Payload;
use App\Models\Article;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('create_article')]
#[Title('Create an article')]
#[Description(<<<'TEXT'
Creates a blog article. Only "title" is required; send the body as Markdown. The article is a draft (not on the public site) unless is_published is true; only publish when the owner asked to. Set language "ar" for Arabic articles (right-to-left) and give them an English slug. For images, put placeholders like ![Step 1](image:step-1) in the body and fill them afterwards with add_article_image or request_image_upload; set the cover with set_article_cover. Call list_articles first so the same article is not created twice.
TEXT)]
#[IsOpenWorld]
class CreateArticleTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate(ArticleFields::rules());

        $article = new Article;
        $warnings = ArticleFields::save($article, $data);
        $article->refresh();

        return Response::structured(array_filter([
            'message' => "Created article \"{$article->title}\"".($article->status === ArticleStatus::Published ? ' (published).' : ' as a draft.'),
            'warnings' => $warnings ?: null,
            'article' => Payload::article($article),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        $fields = ArticleFields::schema($schema);
        $fields['title'] = $fields['title']->required();

        return $fields;
    }
}
