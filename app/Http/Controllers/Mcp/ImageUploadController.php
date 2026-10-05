<?php

namespace App\Http\Controllers\Mcp;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Mcp\Support\ImageAttacher;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\IncomingImage;
use App\Mcp\Support\Payload;
use App\Mcp\Support\UploadTickets;
use App\Mcp\Tools\Companies\SetCompanyLogoTool;
use App\Models\Company;
use App\Models\Project;
use App\Support\Media\ImageRejected;
use App\Support\Media\MimeTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receives a file for a ticket issued by the request_image_upload MCP tool (docs/13-mcp-connector.md):
 * the raw image as the body of a PUT (or a multipart POST with a "file" field). The signed, single-use
 * token in the URL is the only credential, so an agent that only has curl can upload local files.
 */
class ImageUploadController
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        if (! config('portfolio.mcp.enabled')) {
            return $this->error('The connector is switched off.', Response::HTTP_NOT_FOUND);
        }

        $lock = UploadTickets::lock($token);

        if (! $lock->get()) {
            return $this->error('This upload URL is being used by another request.', Response::HTTP_CONFLICT);
        }

        try {
            $ticket = UploadTickets::find($token);

            if ($ticket === null) {
                return $this->error('This upload URL has expired or was already used. Call request_image_upload again.', Response::HTTP_GONE);
            }

            $bytes = $this->body($request);

            if ($bytes instanceof JsonResponse) {
                return $bytes;
            }

            $mimeTypes = $ticket['target'] === 'company_logo' ? SetCompanyLogoTool::MIME_TYPES : MimeTypes::RASTER;

            try {
                $image = ImageInput::check($bytes, $mimeTypes, 'uploaded file');
            } catch (ImageRejected $exception) {
                return $this->error($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($image->mimeType !== $ticket['mime_type']) {
                return $this->error("The file is {$image->mimeType}, but the upload URL was requested for {$ticket['mime_type']}. Request a new URL with the right mime_type.", Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            try {
                $result = $this->attach($ticket, $image);
            } catch (ImageRejected $exception) {
                return $this->error($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($result === null) {
                UploadTickets::consume($token);

                return $this->error('The project or company of this upload no longer exists.', Response::HTTP_GONE);
            }

            UploadTickets::consume($token);

            Log::info('MCP image uploaded', [
                'target' => $ticket['target'],
                'project_id' => $ticket['project_id'],
                'company_id' => $ticket['company_id'],
                'bytes' => strlen($bytes),
                'mime_type' => $image->mimeType,
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => true, ...$result], Response::HTTP_CREATED);
        } finally {
            $lock->release();
        }
    }

    /**
     * The uploaded bytes: a multipart "file" field, or the raw request body.
     */
    private function body(Request $request): string|JsonResponse
    {
        $maxBytes = UploadTickets::maxBytes();
        $tooLarge = fn (): JsonResponse => $this->error('The file is larger than '.round($maxBytes / 1024 / 1024, 1).' MB.', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);

        if ((int) $request->headers->get('Content-Length') > $maxBytes) {
            return $tooLarge();
        }

        $file = $request->file('file');

        if ($file instanceof UploadedFile) {
            if (! $file->isValid()) {
                return $this->error('The file did not upload completely: '.$file->getErrorMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $bytes = (string) $file->get();
        } else {
            $bytes = (string) $request->getContent();
        }

        if ($bytes === '') {
            return $this->error('The request has no file. Send the image as the body of a PUT (curl --data-binary @file.png) or as a multipart "file" field.', Response::HTTP_BAD_REQUEST);
        }

        return strlen($bytes) > $maxBytes ? $tooLarge() : $bytes;
    }

    /**
     * @param  array{target: string, project_id: int|null, company_id: int|null, variant: string, alt: string, caption: string|null}  $ticket
     * @return array<string, mixed>|null null when the record is gone
     *
     * @throws ImageRejected
     */
    private function attach(array $ticket, IncomingImage $image): ?array
    {
        if ($ticket['target'] === 'company_logo') {
            $company = Company::query()->find((int) $ticket['company_id']);

            if ($company === null) {
                return null;
            }

            $media = ImageAttacher::logo($company, $image, $ticket['variant'] === 'dark' ? 'dark' : 'light');

            return [
                'target' => 'company_logo',
                'image' => ['id' => $media->id, ...(array) Payload::image($media, $company->name)],
                'company_admin_url' => CompanyResource::getUrl('edit', ['record' => $company], panel: 'admin'),
            ];
        }

        $project = Project::query()->find((int) $ticket['project_id']);

        if ($project === null) {
            return null;
        }

        if ($ticket['target'] === 'gallery') {
            $item = ImageAttacher::gallery($project, $image, $ticket['alt'], $ticket['caption']);
            $stored = Payload::galleryItem($item->refresh());
        } else {
            $media = ImageAttacher::cover($project, $image, $ticket['alt']);
            $stored = ['id' => $media->id, ...(array) Payload::image($media, $ticket['alt'])];
        }

        return [
            'target' => $ticket['target'],
            'image' => $stored,
            'project_admin_url' => ProjectResource::getUrl('edit', ['record' => $project], panel: 'admin'),
        ];
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $message], $status);
    }
}
