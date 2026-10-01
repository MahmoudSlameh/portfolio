<?php

namespace Database\Seeders;

use App\Enums\StudioSource;
use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * One ready studio template per example spec (resources/studio/examples), to see the studio engine
 * in /dev/templates and on the Appearance page: `php artisan db:seed --class=StudioDemoSeeder`.
 * Re-running replaces the demo templates; nothing is activated.
 */
class StudioDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SpecCatalogue::examples() as $slug => $spec) {
            $name = is_string($spec['name'] ?? null) ? $spec['name'] : Str::headline($slug);

            StudioTemplate::query()->where('name', $name)->where('source', StudioSource::Manual)->get()->each->delete();

            StudioTemplate::query()
                ->create(['name' => $name, 'description' => 'Demo studio template ('.$slug.').', 'source' => StudioSource::Manual])
                ->addVersion($spec);
        }
    }
}
