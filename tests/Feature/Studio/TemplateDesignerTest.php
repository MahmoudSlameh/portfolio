<?php

use App\Ai\Agents\TemplateDesigner;
use App\Ai\TemplateBrief;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\SpecValidator;
use App\Support\Studio\StudioStyles;
use App\Support\Templates\TemplateRegistry;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\TextUsage;
use Laravel\Ai\Responses\StructuredAgentResponse;

/**
 * @param  array<string, mixed>  $structured
 */
function designerResponse(array $structured): AgentResponse
{
    return new StructuredAgentResponse('inv', $structured, (string) json_encode($structured), new TextUsage, new Meta('anthropic', 'claude-sonnet-5'));
}

test('the instructions describe everything the validator accepts', function () {
    $guide = TemplateDesigner::guide();

    foreach (SpecCatalogue::HOME_SECTIONS as $section => $rule) {
        expect($guide)->toContain("`{$section}`: variants ".implode(', ', array_map(fn (string $v): string => "`{$v}`", $rule['variants'])));

        foreach (array_keys($rule['props']) as $prop) {
            expect($guide)->toContain("`{$prop}`");
        }
    }

    foreach (SpecCatalogue::PAGES as $page => $variants) {
        expect($guide)->toContain("`{$page}`: ".implode(', ', array_map(fn (string $v): string => "`{$v}`", $variants)));
    }

    foreach ([...array_keys(SpecCatalogue::fonts()), ...array_keys(SpecCatalogue::COPY), ...SpecCatalogue::COLOR_ROLES] as $name) {
        expect($guide)->toContain("`{$name}`");
    }

    foreach (SpecCatalogue::CLASS_HOOKS as $hook) {
        expect($guide)->toMatch('/\.'.preg_quote($hook, '/').'\s/');
    }

    foreach ([...SpecCatalogue::LAYOUT['header'], ...SpecCatalogue::LAYOUT['footer'], ...SpecCatalogue::CONTAINERS] as $variant) {
        expect($guide)->toContain("`{$variant}`");
    }
});

test('the example in the instructions is a valid spec', function () {
    $guide = TemplateDesigner::guide();
    $json = substr($guide, (int) strpos($guide, "# Example of a valid spec\n") + strlen("# Example of a valid spec\n"));

    expect((new SpecValidator)->validateJson($json)->passes())->toBeTrue();
});

test('the css variables named in the instructions exist', function () {
    $css = StudioStyles::render(SpecCatalogue::example());

    foreach (array_keys(TemplateDesigner::CSS_VARIABLES) as $variable) {
        expect($css)->toContain("{$variable}:");
    }
});

test('the brief carries the request, images and starting point', function () {
    $plain = (new TemplateBrief('Night Shift', 'Dark, neon, playful'))->toPrompt();
    $withSpec = (new TemplateBrief('Night Shift', 'Darker please', startSpec: SpecCatalogue::example(), images: 2))->toPrompt();
    $withTemplate = (new TemplateBrief('Night Shift', '', startTemplate: app(TemplateRegistry::class)->find('terminal')))->toPrompt();

    expect($plain)->toContain('named "Night Shift"')
        ->toContain("<request>\nDark, neon, playful\n</request>")
        ->not->toContain('reference image')
        ->not->toContain('<start-spec>')
        ->and($withSpec)->toContain('2 reference images are attached')
        ->toContain('<start-spec>')
        ->toContain('"name": "Neon Brutalist"')
        ->and($withTemplate)->toContain('no written request')
        ->toContain('built-in template "Terminal"');
});

test('the spec is read from the response', function (mixed $spec, string $expected) {
    expect(TemplateDesigner::specFrom(designerResponse(['spec' => $spec, 'summary' => ' Bold. '])))->toBe($expected)
        ->and(TemplateDesigner::summaryFrom(designerResponse(['spec' => $spec, 'summary' => ' Bold. '])))->toBe('Bold.');
})->with([
    'a json string' => ['{"a":1}', '{"a":1}'],
    'a fenced string' => ["```json\n{\"a\":1}\n```", '{"a":1}'],
    'an object' => [['a' => 1], '{"a":1}'],
    'missing' => [null, ''],
]);

test('a plain text answer holding the json object is accepted', function () {
    $response = new AgentResponse('inv', '{"spec": "{\\"a\\":1}", "summary": "Calm."}', new TextUsage, new Meta('ollama', 'llama3.1'));

    expect(TemplateDesigner::specFrom($response))->toBe('{"a":1}')
        ->and(TemplateDesigner::summaryFrom($response))->toBe('Calm.');
});

test('broken output is reported by the validator', function () {
    $result = (new SpecValidator)->validateJson(TemplateDesigner::specFrom(designerResponse(['spec' => '{"$schema": "studio/v1", ', 'summary' => ''])));

    expect($result->passes())->toBeFalse()
        ->and($result->messages())->toContain(' is not valid JSON.');
});

test('the repair prompt lists the errors and removed css', function () {
    $prompt = TemplateDesigner::repairPrompt(['tokens.radius must be one of: none, sm.'], ['@import removed']);

    expect($prompt)->toContain('complete corrected spec')
        ->toContain('- tokens.radius must be one of: none, sm.')
        ->toContain('- @import removed');
});

test('a prompt with a reference image returns a valid spec', function () {
    TemplateDesigner::fake([[
        'spec' => json_encode([...SpecCatalogue::example(), 'name' => 'Night Shift']),
        'summary' => 'A loud neon design.',
    ]]);
    config()->set('ai.providers.anthropic.key', 'test-key');

    $response = (new TemplateDesigner)->prompt(
        (new TemplateBrief('Night Shift', 'Neon', images: 1))->toPrompt(),
        attachments: [Image::fromUpload(UploadedFile::fake()->image('reference.png', 40, 40))],
        provider: 'anthropic',
    );

    $result = (new SpecValidator)->validateJson(TemplateDesigner::specFrom($response));

    expect($result->passes())->toBeTrue()
        ->and(TemplateDesigner::summaryFrom($response))->toBe('A loud neon design.');

    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('Night Shift') && $prompt->attachments->count() === 1);
});

test('limits come from the config', function () {
    config()->set('studio.ai.max_output_tokens', 1234);
    config()->set('studio.ai.timeout', 99);

    expect((new TemplateDesigner)->maxTokens())->toBe(1234)
        ->and((new TemplateDesigner)->timeout())->toBe(99);
});
