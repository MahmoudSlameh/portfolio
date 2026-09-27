<?php

use App\Support\Content\ArticleDocument;

/**
 * @param  list<array<string, mixed>>  $content
 * @return list<array{type: string, data: array<string, mixed>}>
 */
function blocksOf(array $content): array
{
    return ArticleDocument::toBuilder(['type' => 'doc', 'content' => $content]);
}

/**
 * @param  list<array<string, mixed>>  $marks
 * @return array<string, mixed>
 */
function textNode(string $text, array $marks = []): array
{
    return $marks === [] ? ['type' => 'text', 'text' => $text] : ['type' => 'text', 'text' => $text, 'marks' => $marks];
}

test('builder bodies survive a round trip through the document', function () {
    $fixture = json_decode((string) file_get_contents(__DIR__.'/../Fixtures/ledgers-article.json'), true)['body'];
    $normalise = fn (array $blocks): array => array_map(function (array $block): array {
        unset($block['data']['id']); // custom heading anchors are not kept; ids come from the text

        return $block;
    }, $blocks);

    expect($normalise(ArticleDocument::toBuilder(ArticleDocument::fromBuilder($fixture))))->toBe($normalise($fixture));
});

test('marks become inline markdown and back', function (string $markdown) {
    $doc = ArticleDocument::fromBuilder([['type' => 'paragraph', 'data' => ['text' => $markdown]]]);

    expect(ArticleDocument::toBuilder($doc)[0]['data']['text'] ?? null)->toBe($markdown);
})->with([
    'bold' => 'Use **bold** here',
    'italic' => 'Use *italic* here',
    'code' => 'Call `sum(a*b)` here',
    'link' => 'See [the docs](https://laravel.com/docs) now',
    'nested' => '**bold *and italic* text**',
    'bold link' => '[**strong link**](https://example.com)',
    'escaped' => 'Literal \\* star and \\[brackets\\]',
]);

test('pasted marks are serialised once per run', function () {
    $blocks = blocksOf([['type' => 'paragraph', 'content' => [
        textNode('plain '),
        textNode('bold ', [['type' => 'bold']]),
        textNode('both', [['type' => 'bold'], ['type' => 'italic']]),
        textNode(' ', [['type' => 'bold']]),
        textNode('under', [['type' => 'underline'], ['type' => 'textStyle']]),
        textNode(' link', [['type' => 'link', 'attrs' => ['href' => 'https://en.wikipedia.org/wiki/Log_(disambiguation)']]]),
    ]]]);

    expect($blocks[0]['data']['text'])->toBe('plain **bold *both*** under [link](https://en.wikipedia.org/wiki/Log_%28disambiguation%29)');
});

test('unsafe links keep only their text', function (string $href) {
    $blocks = blocksOf([['type' => 'paragraph', 'content' => [textNode('click', [['type' => 'link', 'attrs' => ['href' => $href]]])]]]);

    expect($blocks[0]['data']['text'])->toBe('click')
        ->and(ArticleDocument::toBuilder(ArticleDocument::fromBuilder([['type' => 'paragraph', 'data' => ['text' => "[click]({$href})"]]]))[0]['data']['text'])->toBe('click');
})->with(['javascript:alert(1)', 'data:text/html,hi', 'vbscript:x']);

test('pasted structure is mapped to the site blocks', function () {
    $blocks = blocksOf([
        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [textNode('Title ', [['type' => 'bold']]), textNode('here')]],
        ['type' => 'paragraph', 'content' => []],
        ['type' => 'paragraph', 'content' => [textNode('line one'), ['type' => 'hardBreak'], textNode('line two')]],
        ['type' => 'orderedList', 'content' => [
            ['type' => 'listItem', 'content' => [
                ['type' => 'paragraph', 'content' => [textNode('first')]],
                ['type' => 'bulletList', 'content' => [
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [textNode('nested')]]]],
                ]],
            ]],
        ]],
        ['type' => 'blockquote', 'content' => [['type' => 'paragraph', 'content' => [textNode('Only a quote')]]]],
        ['type' => 'codeBlock', 'content' => [textNode("a\nb")]],
        ['type' => 'horizontalRule'],
        ['type' => 'table', 'content' => [['type' => 'tableRow', 'content' => [
            ['type' => 'tableHeader', 'content' => [['type' => 'paragraph', 'content' => [textNode('Tool')]]]],
            ['type' => 'tableCell', 'content' => [['type' => 'paragraph', 'content' => [textNode('Why', [['type' => 'bold']])]]]],
        ]]]],
        ['type' => 'image', 'attrs' => ['src' => 'https://elsewhere.test/a.png', 'alt' => 'Hotlinked']],
        ['type' => 'customBlock', 'attrs' => ['id' => 'unknown', 'config' => []]],
    ]);

    expect($blocks)->toBe([
        ['type' => 'heading', 'data' => ['text' => 'Title here']],
        ['type' => 'paragraph', 'data' => ['text' => 'line one line two']],
        ['type' => 'list', 'data' => ['items' => ['first', 'nested']]],
        ['type' => 'quote', 'data' => ['text' => 'Only a quote']],
        ['type' => 'code', 'data' => ['language' => 'text', 'code' => "a\nb"]],
        ['type' => 'paragraph', 'data' => ['text' => 'Tool · **Why**']],
    ]);
});

test('plain text drops the markers', function () {
    expect(ArticleDocument::plainText('A **b** *c* `d*e` [f](https://x.dev) \\*'))->toBe('A b c d*e f *');
});
