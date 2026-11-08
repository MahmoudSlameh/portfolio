<?php

use App\Support\Content\ArticleDocument;
use App\Support\Content\ArticleMarkdown;
use App\Support\Content\VideoEmbed;

test('markdown becomes the site blocks', function () {
    $doc = ArticleMarkdown::toDocument(<<<'MD'
        # Title becomes a section heading

        A paragraph with **bold**, *italic*, `code`, [a link](https://example.com) and [a bad link](javascript:alert(1)).
        A soft break joins the line.

        ### Smaller heading

        - one
        - two
          1. nested

        > Quoted text.
        >
        > — Someone

        ```php title="app/Foo.php"
        echo 1;
        ```

            indented code

        ---

        <div>raw html block</div>

        Generic <T> types stay text.
        MD);

    expect(ArticleDocument::toBuilder($doc))->toBe([
        ['type' => 'heading', 'data' => ['text' => 'Title becomes a section heading']],
        ['type' => 'paragraph', 'data' => ['text' => 'A paragraph with **bold**, *italic*, `code`, [a link](https://example.com) and a bad link. A soft break joins the line.']],
        ['type' => 'heading', 'data' => ['text' => 'Smaller heading']],
        ['type' => 'list', 'data' => ['items' => ['one', 'two', 'nested']]],
        ['type' => 'quote', 'data' => ['text' => 'Quoted text.', 'cite' => 'Someone']],
        ['type' => 'code', 'data' => ['language' => 'php', 'filename' => 'app/Foo.php', 'code' => 'echo 1;']],
        ['type' => 'code', 'data' => ['language' => 'text', 'code' => 'indented code']],
        ['type' => 'paragraph', 'data' => ['text' => 'Generic <T> types stay text.']],
    ])
        ->and($doc['content'][0]['attrs']['level'])->toBe(2)
        ->and($doc['content'][2]['attrs']['level'])->toBe(3);
});

test('images become blocks of their own and keep their source for the caller', function () {
    $doc = ArticleMarkdown::toDocument('Before ![A *diagram*](image:step-1 "Step 1") after');

    expect($doc['content'])->toBe([
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Before']]],
        ['type' => 'image', 'attrs' => ['src' => 'image:step-1', 'alt' => 'A diagram', 'title' => 'Step 1']],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'after']]],
    ]);
});

test('tables without a header and callouts without a title', function () {
    $doc = ArticleMarkdown::toDocument("|  |  |\n| --- | --- |\n| a | b \\| c |\n\n> [!TIP]\n> Use an index.");

    expect(ArticleDocument::toBuilder($doc))->toBe([
        ['type' => 'table', 'data' => ['header' => null, 'rows' => [['a', 'b | c']]]],
        ['type' => 'callout', 'data' => ['title' => 'Tip', 'text' => 'Use an index.']],
    ]);
});

test('a document survives the round trip through markdown', function () {
    $markdown = <<<'MD'
        ## Heading

        Text with **bold** and a [link](https://example.com).

        1. First
        2. Second
           - nested

        > A quote.

        > [!NOTE] Title
        > Callout text.

        | Name | Why |
        | --- | --- |
        | Index | Faster *reads* |

        ```php title="app/Foo.php"
        echo 1;
        ```

        ```
        no language
        ```

        ![Alt](media:1b4e28ba-2fa1-11d2-883f-0016d3cca427 "Caption")

        ![Pending](image:step-2)

        ---

        \# not a heading
        MD;

    expect(ArticleMarkdown::fromDocument(ArticleMarkdown::toDocument($markdown)))->toBe($markdown."\n");
});

test('code containing fences gets a longer fence', function () {
    $doc = ['type' => 'doc', 'content' => [['type' => 'codeBlock', 'attrs' => ['language' => 'md'], 'content' => [['type' => 'text', 'text' => "```\nx\n```"]]]]];

    expect(ArticleMarkdown::fromDocument($doc))->toBe("````md\n```\nx\n```\n````\n")
        ->and(ArticleDocument::toBuilder(ArticleMarkdown::toDocument(ArticleMarkdown::fromDocument($doc)))[0]['data']['code'])->toBe("```\nx\n```");
});

test('video links become player urls', function (string $url, ?string $player) {
    expect(VideoEmbed::playerUrl($url))->toBe($player);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://m.youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
    ['https://example.com/watch?v=dQw4w9WgXcQ', null],
    ['javascript:alert(1)', null],
    ['https://www.youtube.com/watch?v=short', null],
]);
