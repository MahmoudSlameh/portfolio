<?php

namespace App\Mcp\Tools\Companies;

use App\Mcp\Support\ImageAttacher;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use App\Support\Media\ImageRejected;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('set_company_logo')]
#[Title('Set a company\'s logo')]
#[Description('Sets (replaces) a company\'s logo, as a PNG or WebP with a transparent background. variant "dark" is an optional version for dark backgrounds. SVG logos have to be uploaded in the panel.')]
#[IsOpenWorld]
class SetCompanyLogoTool extends Tool
{
    /**
     * Raster only: SVGs from the internet could carry scripts, so they are left to the panel.
     */
    public const MIME_TYPES = ['image/png', 'image/webp'];

    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'company' => ['required'],
            'variant' => ['nullable', 'in:light,dark'],
            ...ImageInput::rules(),
            'screenshot_url' => ['prohibited'],
        ], [...ImageInput::messages(), 'screenshot_url.prohibited' => 'A logo cannot be a screenshot: send image_url or image_base64.']);

        $company = Records::company($data['company']);

        if ($company === null) {
            return Records::notFound('company', $data['company'], 'list_companies');
        }

        $collection = ($data['variant'] ?? 'light') === 'dark' ? 'logo_dark' : 'logo';

        try {
            ImageAttacher::logo($company, ImageInput::fetch($data, self::MIME_TYPES), $collection === 'logo_dark' ? 'dark' : 'light');
        } catch (ImageRejected $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::structured([
            'message' => 'Set the '.($collection === 'logo_dark' ? 'dark ' : '')."logo of \"{$company->name}\".",
            'logo' => Payload::image($company->refresh()->getFirstMedia($collection), $company->name),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        $image = ImageInput::schema($schema);
        unset($image['screenshot_url'], $image['screenshot_viewport']);

        return [
            'company' => $schema->string()->description('The company\'s id or slug.')->required(),
            'variant' => $schema->string()->enum(['light', 'dark'])->description('light (default) or dark: the version for dark backgrounds.')->default('light'),
            ...$image,
        ];
    }
}
