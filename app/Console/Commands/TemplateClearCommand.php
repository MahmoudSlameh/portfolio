<?php

namespace App\Console\Commands;

use App\Support\Templates\TemplateRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('template:clear')]
#[Description('Remove the template manifest cache file')]
class TemplateClearCommand extends Command
{
    public function handle(TemplateRegistry $registry): int
    {
        $registry->clearCache();

        $this->components->info('Template manifest cache cleared successfully.');

        return self::SUCCESS;
    }
}
