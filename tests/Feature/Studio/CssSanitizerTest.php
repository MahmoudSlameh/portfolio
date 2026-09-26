<?php

use App\Support\Studio\CssSanitizer;
use App\Support\Studio\CssSanitizeResult;

function sanitizeCss(string $css): CssSanitizeResult
{
    return (new CssSanitizer)->sanitize($css);
}

test('safe css is kept and scoped to the studio template', function () {
    $result = sanitizeCss('.st-hero h1 { letter-spacing: -0.04em; color: var(--st-accent) !important } a:hover, .card > p { --gap: 2rem; margin: calc(var(--gap) * 2) }');

    expect($result->css)->toBe(<<<'CSS'
        [data-template="studio"] .st-hero h1 {
          letter-spacing: -0.04em;
          color: var(--st-accent) !important;
        }
        [data-template="studio"] a:hover, [data-template="studio"] .card > p {
          --gap: 2rem;
          margin: calc(var(--gap) * 2);
        }

        CSS)
        ->and($result->removed)->toBe([]);
});

test('the root, html, body and theme selectors attach to the scope', function () {
    $css = sanitizeCss(':root { --x: 1px } html[data-theme="dark"] .a { color: red } [data-theme="dark"] .b { color: red } body { margin: 0 } [data-template="studio"] .c { color: red }')->css;

    expect($css)
        ->toContain("[data-template=\"studio\"] {\n  --x: 1px;")
        ->toContain('[data-template="studio"][data-theme="dark"] .a {')
        ->toContain('[data-template="studio"][data-theme="dark"] .b {')
        ->toContain('[data-template="studio"] body {')
        ->toContain('[data-template="studio"] .c {')
        ->not->toContain('[data-template="studio"] [data-template="studio"]');
});

test('media, supports and keyframes are kept; their rules are scoped', function () {
    $css = sanitizeCss('@media (min-width: 768px) and (prefers-color-scheme: dark) { .a { margin: 0 } } @supports (display: grid) { .b { display: grid } } @keyframes pop { from { opacity: 0 } 50%, to { opacity: 1 } }')->css;

    expect($css)
        ->toContain("@media (min-width: 768px) and (prefers-color-scheme: dark) {\n  [data-template=\"studio\"] .a {")
        ->toContain("@supports (display: grid) {\n  [data-template=\"studio\"] .b {")
        ->toContain("@keyframes pop {\n  from {\n    opacity: 0;\n  }\n  50%, to {");
});

test('inline data images are allowed, anything that loads a resource is not', function () {
    $result = sanitizeCss('.a { background: url("data:image/png;base64,iVBORw0KGgo=") } .b { background: url(https://evil.test/x.png) } .c { background: url(//evil.test/x) } .d { background-image: image-set("https://evil.test/x.png" 1x) } .e { background: url("data:text/html,<script>alert(1)</script>") }');

    expect($result->css)->toContain('url("data:image/png;base64,iVBORw0KGgo=")')
        ->not->toContain('evil')
        ->not->toContain('text/html')
        ->and($result->removed)->toContain('background: url() may only hold an inline data: image')
        ->toContain('background-image: image-set() is not allowed');
});

test('malicious input is dropped', function (string $css, string $reason) {
    $result = sanitizeCss($css.' .safe { color: red }');

    expect($result->removed)->toContain($reason)
        // The rest of the stylesheet survives, and nothing can break out of the <style> tag.
        ->and($result->css)->toContain('[data-template="studio"] .safe {')
        ->not->toContain('<')
        ->not->toContain('evil')
        ->not->toContain('alert');
})->with([
    '@import' => ['@import url("https://evil.test/x.css");', '@import'],
    '@import without url' => ['@import "https://evil.test/x.css";', '@import'],
    '@font-face' => ['@font-face { font-family: X; src: url(https://evil.test/f.woff2) }', '@font-face'],
    '@charset' => ['@charset "utf-8";', '@charset'],
    '@namespace' => ['@namespace svg url(http://evil.test);', '@namespace'],
    'style tag in a value' => ['.x { content: "</style><script>alert(1)</script>" }', 'content: escapes or markup characters'],
    'expression()' => ['.x { width: expression(alert(1)) }', 'width: script'],
    'javascript: url' => ['.x { background: url(javascript:alert(1)) }', 'background: script'],
    'behavior' => ['.x { behavior: url(evil.htc) }', 'the behavior property'],
    '-moz-binding' => ['.x { -moz-binding: url(evil.xml#x) }', 'the -moz-binding property'],
    'escaped url' => ['.x { background: u\72 l(https://evil.test) }', 'background: escapes or markup characters'],
    'escaped selector' => ['.x\3c script { color: red }', 'an invalid selector'],
    'unbalanced brace to escape the scope' => ['.x { color: red }} body { display: none }', 'an unmatched "}"'],
    'nested rule' => ['.x { .evil { background: url(https://evil.test) } color: red }', 'nested rules'],
    'html comment' => ['<!-- .x { color: red } -->', 'HTML comment markers'],
    'unknown at-rule block' => ['@page { margin: 0; background: url(https://evil.test) }', '@page'],
    'invalid media condition' => ['@media screen and (url(https://evil.test)) { .x { color: red } }', '@media with an unsupported condition'],
]);

test('markup that tries to close the style tag is dropped with the rule it merges into', function () {
    $result = sanitizeCss('.ok { color: red } </style><script>alert(1)</script> .x { color: red }');

    expect($result->css)->toBe("[data-template=\"studio\"] .ok {\n  color: red;\n}\n")
        ->and($result->removed)->toBe(['an invalid selector']);
});

test('an unscoped escape attempt only ever matches inside the studio template', function () {
    $css = sanitizeCss('body, * , [data-template="terminal"] .x { display: none }')->css;

    expect($css)->toBe("[data-template=\"studio\"] body, [data-template=\"studio\"] *, [data-template=\"studio\"] [data-template=\"terminal\"] .x {\n  display: none;\n}\n");
});

test('comments are removed, including unterminated ones', function () {
    expect(sanitizeCss('.a { color: /* hidden */ red } /* .b { color: blue }')->css)
        ->toBe("[data-template=\"studio\"] .a {\n  color: red;\n}\n");
});

test('css over the size limit is rejected entirely', function () {
    $result = sanitizeCss(str_repeat('.a { color: red }', 5000));

    expect($result->css)->toBe('')
        ->and($result->removed)->toBe(['the stylesheet is larger than 40 KB; nothing was kept']);
});

test('sanitising is stable', function () {
    $once = sanitizeCss('.a{color:red} @media (max-width: 600px){.b,.c{margin:0 auto!important}} :root{--st:1} @keyframes k{from{opacity:0}to{opacity:1}} .d{background:url("data:image/svg+xml;utf8,%3Csvg%3E%3C/svg%3E")}')->css;

    expect(sanitizeCss($once)->css)->toBe($once)
        ->and(sanitizeCss($once)->removed)->toBe([]);
});

test('the example spec css passes unchanged apart from formatting', function () {
    $spec = json_decode((string) file_get_contents(base_path('tests/Fixtures/studio/neon-brutalist.json')), true);
    $result = sanitizeCss($spec['css']);

    expect($result->removed)->toBe([])
        ->and($result->css)->toContain('[data-template="studio"] .st-hero h1 {');
});
