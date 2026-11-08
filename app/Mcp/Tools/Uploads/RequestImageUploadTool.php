<?php

namespace App\Mcp\Tools\Uploads;

use App\Mcp\Support\Records;
use App\Mcp\Support\UploadTickets;
use App\Mcp\Tools\Companies\SetCompanyLogoTool;
use App\Support\Media\MimeTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('request_image_upload')]
#[Title('Get a URL to upload a local image')]
#[Description(<<<'TEXT'
For image files you have locally (screenshots, mockups): returns a single-use URL on this site, valid for a few minutes, that you upload the file to with curl (`curl -X PUT --data-binary @file.png -H "Content-Type: image/png" <upload_url>`). The server checks the image and attaches it as a project cover, a new gallery image, a company logo, an article cover or an image in an article's body; the curl response says where it went. Use this instead of image_base64 for anything bigger than ~200 KB.
TEXT)]
class RequestImageUploadTool extends Tool
{
    private const TARGETS = ['cover', 'gallery', 'company_logo', 'article_cover', 'article_image'];

    public function handle(Request $request): Response|ResponseFactory
    {
        $target = $request->get('target');
        $isLogo = $target === 'company_logo';
        $isArticle = in_array($target, ['article_cover', 'article_image'], true);
        $isProject = ! $isLogo && ! $isArticle;
        $mimeTypes = $isLogo ? SetCompanyLogoTool::MIME_TYPES : MimeTypes::RASTER;

        $data = $request->validate([
            'target' => ['required', Rule::in(self::TARGETS)],
            'project' => [Rule::requiredIf($isProject), Rule::prohibitedIf(! $isProject)],
            'company' => [Rule::requiredIf($isLogo), Rule::prohibitedIf(! $isLogo)],
            'article' => [Rule::requiredIf($isArticle), Rule::prohibitedIf(! $isArticle)],
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', Rule::in($mimeTypes)],
            'alt' => [Rule::requiredIf(! $isLogo), 'nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255', Rule::prohibitedIf(! in_array($target, ['gallery', 'article_image'], true))],
            'placeholder' => ['nullable', 'string', 'alpha_dash:ascii', 'max:100', Rule::prohibitedIf($target !== 'article_image')],
            'variant' => ['nullable', 'in:light,dark', Rule::prohibitedIf(! $isLogo)],
        ], [
            'mime_type.in' => 'mime_type must be one of: '.implode(', ', $mimeTypes).($isLogo ? ' (logos are PNG or WebP; SVG logos are uploaded in the panel).' : '.'),
        ]);

        $project = $isProject ? Records::project($data['project']) : null;
        $company = $isLogo ? Records::company($data['company']) : null;
        $article = $isArticle ? Records::article($data['article']) : null;

        if ($isProject && $project === null) {
            return Records::notFound('project', $data['project'], 'list_projects');
        }

        if ($isLogo && $company === null) {
            return Records::notFound('company', $data['company'], 'list_companies');
        }

        if ($isArticle && $article === null) {
            return Records::notFound('article', $data['article'], 'list_articles');
        }

        $ticket = UploadTickets::issue([
            'target' => $data['target'],
            'project_id' => $project?->id,
            'company_id' => $company?->id,
            'article_id' => $article?->id,
            'variant' => (string) ($data['variant'] ?? 'light'),
            'mime_type' => $data['mime_type'],
            'filename' => $data['filename'],
            'alt' => (string) ($data['alt'] ?? $company?->name ?? ''),
            'caption' => $data['caption'] ?? null,
            'placeholder' => $data['placeholder'] ?? null,
            'user_id' => $request->user()?->getAuthIdentifier(),
        ]);

        $url = route('mcp.uploads', ['token' => $ticket['token']]);

        Log::info('MCP image upload requested', [
            'target' => $data['target'],
            'project_id' => $project?->id,
            'company_id' => $company?->id,
            'article_id' => $article?->id,
            'filename' => $data['filename'],
        ]);

        return Response::structured([
            'upload_url' => $url,
            'method' => 'PUT',
            'headers' => ['Content-Type' => $data['mime_type']],
            'expires_at' => $ticket['expires_at']->toIso8601String(),
            'max_bytes' => UploadTickets::maxBytes(),
            'curl_example' => 'curl -sS -X PUT --data-binary @'.escapeshellarg($data['filename']).' -H '.escapeshellarg('Content-Type: '.$data['mime_type']).' '.escapeshellarg($url),
            'note' => 'Single use; expires in '.UploadTickets::minutes().' minutes. multipart POST with a "file" field works too. The JSON response says where the image was attached.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'target' => $schema->string()->enum(self::TARGETS)->description('cover = the project\'s cover (replaces it, 16:9 works best); gallery = a new image at the end of the project\'s gallery (4:3 works best); company_logo = a company\'s logo; article_cover = an article\'s cover (replaces it, 16:9 works best); article_image = an image in an article\'s body (in place of "placeholder", otherwise at the end).')->required(),
            'project' => $schema->string()->description('The project\'s id or slug (cover and gallery).'),
            'company' => $schema->string()->description('The company\'s id or slug (company_logo).'),
            'article' => $schema->string()->description('The article\'s id or slug (article_cover and article_image).'),
            'filename' => $schema->string()->description('Path of the local file, used in the curl example, e.g. "screens/dashboard.png".')->required(),
            'mime_type' => $schema->string()->enum(MimeTypes::RASTER)->description('Type of the file: image/png, image/jpeg, image/webp or image/avif (logos: png or webp). The uploaded bytes must match it.')->required(),
            'alt' => $schema->string()->description('Alt text: what the image shows (required for every target but company_logo).'),
            'caption' => $schema->string()->description('gallery and article_image: short caption under the image.'),
            'placeholder' => $schema->string()->description('article_image only: key of the placeholder to fill, "step-1" for ![…](image:step-1) in the body.'),
            'variant' => $schema->string()->enum(['light', 'dark'])->description('company_logo only: light (default) or dark.'),
        ];
    }
}
