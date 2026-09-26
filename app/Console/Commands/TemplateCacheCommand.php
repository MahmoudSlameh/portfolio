<?php

namespace App\Console\Commands;

use App\Support\Templates\TemplateRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('template:cache')]
#[Description('Cache the template manifests (runs with `php artisan optimize`)')]
class TemplateCacheCommand extends Command
{
    public function handle(TemplateRegistry $registry): int
    {
        $registry->cache();

        $this->components->info('Template manifests cached successfully ('.count($registry->all()).' templates).');

        return self::SUCCESS;
    }
}
