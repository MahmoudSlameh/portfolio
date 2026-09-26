<?php

use App\Console\Commands\StudioGenerateCommand;
use App\Support\Studio\InvalidSpecException;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\SpecValidator;

function validSpec(): array
{
    return json_decode((string) file_get_contents(base_path('tests/Fixtures/studio/neon-brutalist.json')), true);
}

/**
 * Validate a copy of the valid fixture changed by $mutate; returns the error lines.
 *
 * @return list<string>
 */
function specErrors(callable $mutate): array
{
    $spec = validSpec();
    $mutate($spec);

    return (new SpecValidator)->validate($spec)->messages();
}

test('the example spec is valid', function () {
    expect((new SpecValidator)->validate(validSpec())->messages())->toBe([]);
});

test('the smallest valid spec has one home section and no copy or css', function () {
    $spec = validSpec();
    $spec['pages']['home'] = [['section' => 'contact', 'variant' => 'minimal']];
    unset($spec['copy'], $spec['css']);

    expect((new SpecValidator)->validate($spec)->passes())->toBeTrue();
});

test('invalid specs are rejected with the path and reason', function (callable $mutate, string $expected) {
    expect(specErrors($mutate))->toContain($expected);
})->with([
    'wrong version' => [fn (array &$s) => $s['$schema'] = 'studio/v2', '$schema must be "studio/v1".'],
    'missing tokens' => [function (array &$s) {
        unset($s['tokens']);
    }, 'tokens is required.'],
    'unknown root key' => [fn (array &$s) => $s['script'] = 'alert(1)', 'script is not allowed; expected: $schema, name, tokens, layout, pages, copy, css.'],
    'empty name' => [fn (array &$s) => $s['name'] = '  ', 'name must be a non-empty string.'],
    'long name' => [fn (array &$s) => $s['name'] = str_repeat('a', 61), 'name may be at most 60 characters.'],
    'bad colour' => [fn (array &$s) => $s['tokens']['colors']['dark']['accent'] = 'red', 'tokens.colors.dark.accent must be a hex colour like #1a1a1a.'],
    'css colour injection' => [fn (array &$s) => $s['tokens']['colors']['light']['bg'] = '#fff;background:url(x)', 'tokens.colors.light.bg must be a hex colour like #1a1a1a.'],
    'missing colour role' => [function (array &$s) {
        unset($s['tokens']['colors']['light']['border']);
    }, 'tokens.colors.light.border is required.'],
    'font not shipped' => [fn (array &$s) => $s['tokens']['fonts']['body'] = 'comic-sans', 'tokens.fonts.body must be one of: geist, bricolage-grotesque, space-grotesk, archivo, jetbrains-mono, space-mono, dm-mono, system-sans, system-serif, system-mono.'],
    'proportional font as mono' => [fn (array &$s) => $s['tokens']['fonts']['mono'] = 'geist', 'tokens.fonts.mono must be one of: jetbrains-mono, space-mono, dm-mono, system-mono.'],
    'unknown radius' => [fn (array &$s) => $s['tokens']['radius'] = 'huge', 'tokens.radius must be one of: none, sm, md, lg, full.'],
    'unknown header' => [fn (array &$s) => $s['layout']['header']['variant'] = 'mega', 'layout.header.variant must be one of: bar-sticky, floating-pill, sidebar, minimal.'],
    'home is not a list' => [fn (array &$s) => $s['pages']['home'] = ['hero' => ['variant' => 'centered']], 'pages.home must be a non-empty list of sections.'],
    'empty home' => [fn (array &$s) => $s['pages']['home'] = [], 'pages.home must be a non-empty list of sections.'],
    'unknown section' => [fn (array &$s) => $s['pages']['home'][1]['section'] = 'pricing', 'pages.home[1].section must be one of: hero, about, stats, skills, career, projects, clients, testimonials, education, writing, books, contact.'],
    'unknown variant' => [fn (array &$s) => $s['pages']['home'][2]['variant'] = 'carousel', 'pages.home[2].variant must be one of: grid, bento, list, slider.'],
    'duplicate section' => [fn (array &$s) => $s['pages']['home'][] = ['section' => 'hero', 'variant' => 'centered'], 'pages.home[8].section "hero" is already used by pages.home[0]; each section can appear once.'],
    'unknown prop' => [fn (array &$s) => $s['pages']['home'][0]['props']['autoplay'] = true, 'pages.home[0].props.autoplay is not allowed; expected: showAvailability, showSocials.'],
    'props on a section without props' => [fn (array &$s) => $s['pages']['home'][1]['props'] = ['limit' => 2], 'pages.home[1].props is not allowed: this section has no props.'],
    'limit out of range' => [fn (array &$s) => $s['pages']['home'][2]['props']['limit'] = 99, 'pages.home[2].props.limit must be a whole number from 1 to 12.'],
    'limit not an integer' => [fn (array &$s) => $s['pages']['home'][2]['props']['limit'] = '6', 'pages.home[2].props.limit must be a whole number from 1 to 12.'],
    'boolean prop as string' => [fn (array &$s) => $s['pages']['home'][0]['props']['showAvailability'] = 'yes', 'pages.home[0].props.showAvailability must be true or false.'],
    'missing page' => [function (array &$s) {
        unset($s['pages']['now']);
    }, 'pages.now is required.'],
    'unknown page variant' => [fn (array &$s) => $s['pages']['article']['variant'] = 'fullscreen', 'pages.article.variant must be one of: centered, wide.'],
    'unknown copy key' => [fn (array &$s) => $s['copy']['bio'] = 'I am a developer', 'copy.bio is not allowed; expected: '.implode(', ', array_keys(SpecCatalogue::COPY)).'.'],
    'markup in copy' => [fn (array &$s) => $s['copy']['heroKicker'] = '<img src=x onerror=alert(1)>', 'copy.heroKicker must be plain text (no markup, line breaks or control characters).'],
    'copy too long' => [fn (array &$s) => $s['copy']['heroCta'] = str_repeat('a', 31), 'copy.heroCta may be at most 30 characters.'],
    'css too big' => [fn (array &$s) => $s['css'] = str_repeat('a', 40 * 1024 + 1), 'css may be at most 40 KB.'],
    'css not a string' => [fn (array &$s) => $s['css'] = ['a' => 'b'], 'css must be a string.'],
]);

test('every error is reported, not only the first one', function () {
    $errors = specErrors(function (array &$s) {
        $s['name'] = '';
        $s['tokens']['radius'] = 'x';
        $s['pages']['home'][0]['variant'] = 'x';
    });

    expect($errors)->toHaveCount(3);
});

test('invalid JSON and non-object input are rejected', function () {
    $validator = new SpecValidator;

    expect($validator->validateJson('{nope')->messages())->toBe([' is not valid JSON.'])
        ->and($validator->validateJson('[1, 2]')->messages())->toBe(['(root) must be an object.'])
        ->and($validator->validateJson(json_encode(validSpec()))->passes())->toBeTrue();
});

test('an invalid result can be thrown with every error in the message', function () {
    $result = (new SpecValidator)->validate(['$schema' => 'studio/v1']);

    expect(fn () => $result->throw())->toThrow(InvalidSpecException::class, 'name is required.');
});

test('the generated schema and engine catalogue are up to date', function () {
    foreach (StudioGenerateCommand::outputs() as $path => $contents) {
        expect(file_exists($path) && file_get_contents($path) === $contents)
            ->toBeTrue(str_replace(base_path().'/', '', $path).' is stale: run `php artisan studio:generate`.');
    }

    $this->artisan('studio:generate --check')->assertSuccessful();
});

test('every font in the allow-list ships with the app', function () {
    foreach (SpecCatalogue::fonts() as $id => $font) {
        if ($font['preload'] !== null) {
            expect(file_exists(base_path($font['preload'])))->toBeTrue("{$id}: {$font['preload']} does not exist");
        }
    }

    expect(SpecCatalogue::fontsFor('mono'))->not->toBeEmpty()
        ->and(SpecCatalogue::fontsFor('display'))->not->toBeEmpty();
});
