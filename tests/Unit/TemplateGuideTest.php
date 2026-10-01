<?php

/*
 * docs/templates/building-a-template.md documents the props of every page of the template contract.
 * This keeps it in sync with resources/js/templates/types.ts: adding a prop without documenting it
 * fails here.
 */

$root = dirname(__DIR__, 2);

test('the template guide documents every prop of the template contract', function () use ($root) {
    $types = (string) file_get_contents("{$root}/resources/js/templates/types.ts");
    $guide = (string) file_get_contents("{$root}/docs/templates/building-a-template.md");

    preg_match_all('/export interface (\w+Props) \{\n(.*?)\n\}/s', $types, $interfaces, PREG_SET_ORDER);

    expect($interfaces)->toHaveCount(9);

    foreach ($interfaces as [, $interface, $body]) {
        preg_match_all('/^    (\w+)\??:/m', $body, $props);

        expect($props[1])->not->toBeEmpty();

        foreach ($props[1] as $prop) {
            expect(str_contains($guide, "| `{$prop}`"))->toBeTrue("{$interface}.{$prop} is not documented in the template guide");
        }

        // Page components are named after their props interface (HomePageProps → HomePage).
        if ($interface !== 'LayoutProps') {
            expect($guide)->toContain('`'.substr($interface, 0, -5).'`');
        }
    }
});
