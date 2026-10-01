<?php

namespace App\Jobs;

use App\Models\StudioTemplate;
use App\Support\Templates\RenderSignature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Screenshots a studio template's home page for its Appearance card (P10-03) with headless Chrome,
 * when `studio.screenshots.chrome` is set. Chrome is not signed in, so it opens a signed URL that is
 * valid for a few minutes and renders that template (TemplateManager::isGalleryRender()).
 */
class CaptureStudioScreenshot implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public StudioTemplate $template) {}

    public static function enabled(): bool
    {
        return filled(config('studio.screenshots.chrome'));
    }

    /**
     * Queue a capture when screenshots are enabled.
     */
    public static function captureIfEnabled(StudioTemplate $template): void
    {
        if (self::enabled()) {
            self::dispatch($template)->afterCommit();
        }
    }

    /**
     * The URL Chrome opens: the home page with a signature valid for 5 minutes. The signature does not
     * cover the host, so the base can be an address the server reaches itself
     * (`studio.screenshots.base_url`, default APP_URL).
     */
    public static function url(StudioTemplate $template): string
    {
        $base = rtrim((string) (config('studio.screenshots.base_url') ?: config('app.url')), '/');

        return $base.'/?'.http_build_query(RenderSignature::sign($template->templateId()));
    }

    public function handle(): void
    {
        if (! self::enabled() || $this->template->active_version_id === null) {
            return;
        }

        $directory = storage_path('app/private/studio-screenshots');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.Str::ulid().'.png';

        try {
            $result = Process::timeout((int) config('studio.screenshots.timeout', 90))->run($this->command($path));

            if (! $result->successful() || ! is_file($path) || filesize($path) === 0) {
                throw new RuntimeException('Chrome did not save a screenshot: '.Str::limit(trim($result->errorOutput() ?: $result->output()), 500));
            }

            $this->template->addMedia($path)->usingFileName("{$this->template->id}.png")->toMediaCollection('screenshot');
        } finally {
            File::delete($path);
        }
    }

    /**
     * @return list<string>
     */
    private function command(string $path): array
    {
        $width = (int) config('studio.screenshots.width', 1280);
        $height = (int) config('studio.screenshots.height', 800);

        return array_values(array_filter([
            (string) config('studio.screenshots.chrome'),
            '--headless=new',
            config('studio.screenshots.no_sandbox') ? '--no-sandbox' : null,
            '--disable-gpu',
            '--hide-scrollbars',
            '--mute-audio',
            "--window-size={$width},{$height}",
            // Let the client-side app hydrate and fonts load before the capture.
            '--virtual-time-budget=8000',
            "--screenshot={$path}",
            self::url($this->template),
        ]));
    }
}
