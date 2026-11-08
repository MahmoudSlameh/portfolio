<?php

use App\Enums\ArticleLanguage;
use App\Enums\ArticleStatus;
use App\Mcp\Servers\PortfolioServer;
use App\Mcp\Support\ArticleImages;
use App\Mcp\Support\UploadTickets;
use App\Mcp\Tools\Articles\AddArticleImageTool;
use App\Mcp\Tools\Articles\CreateArticleTool;
use App\Mcp\Tools\Articles\DeleteArticleTool;
use App\Mcp\Tools\Articles\GetArticleTool;
use App\Mcp\Tools\Articles\ListArticlesTool;
use App\Mcp\Tools\Articles\RestoreArticleTool;
use App\Mcp\Tools\Articles\SetArticleCoverTool;
use App\Mcp\Tools\Articles\UpdateArticleTool;
use App\Models\Article;
use App\Models\Project;
use App\Support\Content\ArticleDocument;
use App\Support\Content\ArticleMarkdown;
use App\Support\Media\PublicUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    PublicUrl::resolveUsing(fn (string $host): array => ['93.184.216.34']);
});

afterEach(function () {
    PublicUrl::resolveUsing(null);
});

function articlePngBytes(int $width = 64, int $height = 36): string
{
    return UploadedFile::fake()->image('image.png', $width, $height)->get();
}

test('create_article saves an Arabic draft from Markdown with its series, sources and video', function () {
    $project = Project::factory()->create();

    PortfolioServer::tool(CreateArticleTool::class, [
        'title' => 'شو يعني Load Balancer؟',
        'slug' => 'load-balancer',
        'excerpt' => 'مقدمة بسيطة.',
        'language' => 'ar',
        'series' => 'System Design',
        'series_order' => 1,
        'tags' => ['system-design', ' networking '],
        'sources' => [['title' => 'NGINX docs', 'url' => 'https://nginx.org/en/docs/']],
        'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        'project_ids' => [$project->id],
        'body' => <<<'MD'
            مقدمة **مهمة**.

            ## الفكرة

            1. أول خطوة
            2. تاني خطوة

            | الخوارزمية | متى |
            | --- | --- |
            | Round robin | `دايماً` |

            > [!NOTE] انتبه
            > نص الملاحظة.
            MD,
    ])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('as a draft.');

    $article = Article::query()->sole();

    expect($article->slug)->toBe('load-balancer')
        ->and($article->status)->toBe(ArticleStatus::Draft)
        ->and($article->language)->toBe(ArticleLanguage::Arabic)
        ->and($article->series)->toBe('System Design')
        ->and($article->series_order)->toBe(1)
        ->and($article->tags)->toBe(['system-design', 'networking'])
        ->and($article->sources)->toBe([['title' => 'NGINX docs', 'url' => 'https://nginx.org/en/docs/']])
        ->and($article->videoPlayerUrl())->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($article->projects->pluck('id')->all())->toBe([$project->id])
        ->and(ArticleDocument::toBuilder($article->body))->toBe([
            ['type' => 'paragraph', 'data' => ['text' => 'مقدمة **مهمة**.']],
            ['type' => 'heading', 'data' => ['text' => 'الفكرة']],
            ['type' => 'list', 'data' => ['items' => ['أول خطوة', 'تاني خطوة'], 'ordered' => true]],
            ['type' => 'table', 'data' => ['header' => ['الخوارزمية', 'متى'], 'rows' => [['Round robin', '`دايماً`']]]],
            ['type' => 'callout', 'data' => ['title' => 'انتبه', 'text' => 'نص الملاحظة.']],
        ]);
});

test('create_article validates its input', function () {
    PortfolioServer::tool(CreateArticleTool::class, ['excerpt' => 'No title'])
        ->assertHasErrors(['The title field is required.']);

    PortfolioServer::tool(CreateArticleTool::class, ['title' => 'Video', 'video_url' => 'https://example.com/video.mp4'])
        ->assertHasErrors(['video_url must be a YouTube or Vimeo video link.']);

    PortfolioServer::tool(CreateArticleTool::class, ['title' => 'Bad', 'language' => 'fr', 'sources' => [['title' => 'x', 'url' => 'javascript:alert(1)']]])
        ->assertHasErrors();

    expect(Article::query()->count())->toBe(0);
});

test('placeholders in the body are filled by add_article_image', function () {
    PortfolioServer::tool(CreateArticleTool::class, [
        'title' => 'Steps',
        'body' => "Intro.\n\n![The first step](image:step-1 \"Step 1\")\n\nOutro.",
    ])->assertOk();

    $article = Article::query()->sole();

    expect(ArticleImages::placeholders($article->body))->toBe(['step-1']);

    PortfolioServer::tool(AddArticleImageTool::class, [
        'article' => $article->slug,
        'alt' => 'Requests reach the load balancer',
        'placeholder' => 'step-1',
        'image_base64' => base64_encode(articlePngBytes()),
    ])->assertOk()->assertSee('in place of image:step-1');

    $article->refresh();
    $media = $article->getFirstMedia('body_images');
    $blocks = ArticleDocument::toBuilder($article->body);

    expect(ArticleImages::placeholders($article->body))->toBe([])
        ->and($blocks[1])->toBe(['type' => 'image', 'data' => ['media_uuid' => $media->uuid, 'alt' => 'Requests reach the load balancer', 'caption' => 'Step 1']])
        ->and($blocks[2]['data']['text'])->toBe('Outro.');

    // Without a placeholder the image goes to the end.
    PortfolioServer::tool(AddArticleImageTool::class, [
        'article' => $article->id,
        'alt' => 'Summary diagram',
        'image_base64' => base64_encode(articlePngBytes()),
    ])->assertOk()->assertSee('at the end of the body');

    expect(last(ArticleDocument::toBuilder($article->refresh()->body))['data']['alt'])->toBe('Summary diagram');
});

test('get_article returns Markdown that update_article takes back, and removed images are deleted', function () {
    Http::fake(['https://images.example.com/*' => Http::response(articlePngBytes(), 200, ['Content-Type' => 'image/png'])]);

    PortfolioServer::tool(CreateArticleTool::class, [
        'title' => 'Round trip',
        'body' => "## Heading\n\nText with [a link](https://example.com).\n\n![Kept](https://images.example.com/a.png)\n\n![Dropped](https://images.example.com/b.png)",
    ])->assertOk();

    $article = Article::query()->sole();
    [$kept, $dropped] = $article->getMedia('body_images')->all();
    $markdown = ArticleMarkdown::fromDocument($article->body);

    expect($markdown)->toContain("## Heading\n\nText with [a link](https://example.com).")
        ->toContain("![Kept](media:{$kept->uuid})")
        ->toContain("![Dropped](media:{$dropped->uuid})");

    PortfolioServer::tool(GetArticleTool::class, ['article' => 'round-trip'])
        ->assertOk()
        ->assertSee("media:{$kept->uuid}");

    PortfolioServer::tool(UpdateArticleTool::class, [
        'article' => $article->id,
        'body' => str_replace("\n\n![Dropped](media:{$dropped->uuid})", '', $markdown),
    ])->assertOk()->assertHasNoErrors();

    expect($article->refresh()->getMedia('body_images')->pluck('uuid')->all())->toBe([$kept->uuid])
        ->and(ArticleMarkdown::fromDocument($article->body))->toBe(str_replace("\n\n![Dropped](media:{$dropped->uuid})", '', $markdown));
});

test('update_article warns about images it cannot use', function () {
    $article = Article::factory()->draft()->create();

    PortfolioServer::tool(UpdateArticleTool::class, [
        'article' => $article->slug,
        'body' => "Text.\n\n![Missing](media:not-an-image-of-this-article)\n\n![Local](C:/screens/a.png)",
    ])
        ->assertOk()
        ->assertSee('it is not an image of this article')
        ->assertSee('use media:<uuid>, image:<placeholder> or an http(s) URL');

    expect(ArticleDocument::toBuilder($article->refresh()->body))->toBe([['type' => 'paragraph', 'data' => ['text' => 'Text.']]]);

    PortfolioServer::tool(UpdateArticleTool::class, ['article' => $article->slug])
        ->assertHasErrors(['Nothing to update: send at least one field to change.']);
});

test('update_article publishes, and list_articles filters', function () {
    $draft = Article::factory()->draft()->create(['title' => 'Part two', 'series' => 'System Design', 'series_order' => 2, 'language' => 'ar']);
    Article::factory()->draft()->create(['title' => 'Part one', 'series' => 'System Design', 'series_order' => 1, 'language' => 'ar']);
    Article::factory()->create(['title' => 'Elsewhere']);

    PortfolioServer::tool(UpdateArticleTool::class, ['article' => $draft->slug, 'is_published' => true])
        ->assertOk();

    expect($draft->refresh()->status)->toBe(ArticleStatus::Published)
        ->and($draft->published_at)->not->toBeNull();

    PortfolioServer::tool(ListArticlesTool::class, ['series' => 'System Design'])
        ->assertOk()
        ->assertSee(['Part one', 'Part two'])
        ->assertDontSee('Elsewhere');

    PortfolioServer::tool(ListArticlesTool::class, ['language' => 'en'])
        ->assertSee('Elsewhere')
        ->assertDontSee('Part one');
});

test('articles can be deleted and restored', function () {
    $article = Article::factory()->create(['title' => 'Gone']);

    PortfolioServer::tool(DeleteArticleTool::class, ['article' => $article->slug])
        ->assertOk()
        ->assertSee('restore_article brings it back');

    expect($article->refresh()->trashed())->toBeTrue();

    PortfolioServer::tool(UpdateArticleTool::class, ['article' => $article->slug, 'title' => 'Still gone'])
        ->assertHasErrors();

    PortfolioServer::tool(RestoreArticleTool::class, ['article' => $article->slug])->assertOk();

    expect($article->refresh()->trashed())->toBeFalse();
});

test('set_article_cover stores the cover and its alt text', function () {
    $article = Article::factory()->create(['title' => 'Covered']);

    PortfolioServer::tool(SetArticleCoverTool::class, [
        'article' => $article->id,
        'alt' => 'A load balancer in front of three servers',
        'image_base64' => base64_encode(articlePngBytes(160, 90)),
    ])->assertOk()->assertSee('Set the cover of \"Covered\".');

    expect($article->refresh()->getFirstMedia('cover'))->not->toBeNull()
        ->and($article->cover_alt)->toBe('A load balancer in front of three servers');
});

test('a local file uploaded for an article_image fills its placeholder', function () {
    config()->set('portfolio.mcp.enabled', true);
    $article = Article::factory()->draft()->create(['body' => ArticleMarkdown::toDocument('![Diagram](image:diagram)')]);

    $ticket = UploadTickets::issue([
        'target' => 'article_image',
        'article_id' => $article->id,
        'mime_type' => 'image/png',
        'filename' => 'diagram.png',
        'alt' => 'The whole system',
        'placeholder' => 'diagram',
    ]);

    $this->call('PUT', route('mcp.uploads', ['token' => $ticket['token']]), server: ['CONTENT_TYPE' => 'image/png'], content: articlePngBytes())
        ->assertCreated()
        ->assertJson(['ok' => true, 'target' => 'article_image', 'placed' => 'in place of image:diagram']);

    expect(ArticleImages::placeholders($article->refresh()->body))->toBe([])
        ->and($article->getMedia('body_images'))->toHaveCount(1);
});
