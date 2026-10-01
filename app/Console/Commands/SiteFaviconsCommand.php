<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Support\Media\Favicons;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('site:favicons {--force : Regenerate the icons even when they exist}')]
#[Description('Generate favicon.ico, a 192px PNG and the apple-touch-icon from the favicon uploaded in the panel')]
class SiteFaviconsCommand extends Command
{
    public function handle(): int
    {
        $settings = SiteSetting::current();

        if (! $settings->hasMedia('favicon')) {
            Favicons::sync($settings);
            $this->components->info('No favicon is uploaded (Site → SEO & settings → Advanced), so the site uses /favicon.svg.');

            return self::SUCCESS;
        }

        try {
            $generated = Favicons::sync($settings, (bool) $this->option('force'));
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach (Favicons::links($settings->refresh()) ?? [] as $url) {
            $this->components->twoColumnDetail($url, $generated ? '<fg=green>created</>' : '<fg=gray>exists</>');
        }

        $this->components->info($generated ? 'Favicons generated.' : 'The favicons are up to date (use --force to regenerate).');

        return self::SUCCESS;
    }
}
