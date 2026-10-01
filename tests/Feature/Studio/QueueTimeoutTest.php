<?php

use App\Jobs\GenerateStudioTemplate;
use App\Models\StudioGeneration;

test('queues release jobs only after the generation job can finish', function (string $connection) {
    $timeout = (new GenerateStudioTemplate(new StudioGeneration))->timeout;

    // Otherwise a running generation is handed to a second worker and fails as "attempted too many times".
    expect(config("queue.connections.{$connection}.retry_after"))->toBeGreaterThan($timeout);
})->with(['database', 'redis', 'beanstalkd']);
