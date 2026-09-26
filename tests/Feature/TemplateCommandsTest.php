<?php

use App\Support\Templates\TemplateRegistry;

afterEach(fn () => app(TemplateRegistry::class)->clearCache());

test('template:cache writes the manifests and template:clear removes them', function () {
    $this->artisan('template:cache')->assertSuccessful();

    expect(file_exists(base_path('bootstrap/cache/templates.php')))->toBeTrue()
        ->and(app(TemplateRegistry::class)->ids())->toBe(templateIds());

    $this->artisan('template:clear')->assertSuccessful();

    expect(file_exists(base_path('bootstrap/cache/templates.php')))->toBeFalse();
});
