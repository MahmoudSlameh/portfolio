<?php

namespace App\Console\Commands;

use App\Jobs\CaptureStudioScreenshot;
use App\Models\StudioTemplate;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('studio:screenshots {--missing : Only templates without a screenshot}')]
#[Description('Capture the Appearance card screenshot of every studio template (needs STUDIO_SCREENSHOT_CHROME)')]
class StudioScreenshotsCommand extends Command
{
    public function handle(): int
    {
        if (! CaptureStudioScreenshot::enabled()) {
            $this->components->error('Set STUDIO_SCREENSHOT_CHROME to a Chrome or Chromium binary first (see config/studio.php).');

            return self::FAILURE;
        }

        $templates = StudioTemplate::query()->renderable()->with('media')->get()
            ->when($this->option('missing'), fn ($templates) => $templates->filter(fn (StudioTemplate $template): bool => ! $template->hasMedia('screenshot')));

        $failed = 0;

        foreach ($templates as $template) {
            try {
                $this->components->task($template->name, fn () => (new CaptureStudioScreenshot($template))->handle());
            } catch (Throwable $exception) {
                $failed++;
                $this->components->error($exception->getMessage());
            }
        }

        if ($templates->isEmpty()) {
            $this->components->info('Nothing to capture.');
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
