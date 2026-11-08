<?php

namespace App\Support\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;

/**
 * Markdown ⇄ the article body (a TipTap document, see ArticleDocument), for the MCP article tools.
 *
 * Supported: headings (## and ###), paragraphs, **bold**, *italic*, `code`, links, bullet and numbered
 * lists, quotes, fenced code (```php title="app/Foo.php" for a file name), GitHub tables, `---`, callouts
 * written as GitHub alerts (`> [!NOTE] Title` and the text on the next quoted lines) and images.
 * Image sources are kept as written (`media:<uuid>`, `image:<key>`, https URLs); the caller resolves them.
 * Raw HTML is kept as text, never as markup.
 */
final class ArticleMarkdown
{
    private const CALLOUT = '/^\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\][ \t]*(.*)$/iu';

    /**
     * @return array{type: 'doc', content: list<array<string, mixed>>}
     */
    public static function toDocument(string $markdown): array
    {
        $environment = new Environment(['max_nesting_level' => 50]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);

        $document = (new MarkdownParser($environment))->parse(str_replace(["\r\n", "\r"], "\n", $markdown));

        return ['type' => 'doc', 'content' => self::blocks($document)];
    }

    /**
     * @param  array<array-key, mixed>|null  $doc
     */
    public static function fromDocument(?array $doc): string
    {
        return trim(self::blocksToMarkdown(self::children($doc ?? [])))."\n";
    }

    /**
     * Calls $map for every image node in the document (depth first) and puts back what it returns.
     *
     * @param  array<array-key, mixed>  $node
     * @param  callable(array<string, mixed>): (array<string, mixed>|null)  $map  returns the new node, or null to remove it
     * @return array<array-key, mixed>
     */
    public static function mapImages(array $node, callable $map): array
    {
        if (! is_array($node['content'] ?? null)) {
            return $node;
        }

        $content = [];

        foreach ($node['content'] as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (($child['type'] ?? null) === 'image') {
                $mapped = $map($child);

                if ($mapped !== null) {
                    $content[] = $mapped;
                }

                continue;
            }

            $content[] = self::mapImages($child, $map);
        }

        $node['content'] = $content;

        return $node;
    }

    // Markdown → document

    /**
     * @return list<array<string, mixed>>
     */
    private static function blocks(Node $parent): array
    {
        $nodes = [];

        foreach ($parent->children() as $child) {
            array_push($nodes, ...self::block($child));
        }

        return $nodes;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function block(Node $node): array
    {
        return match (true) {
            $node instanceof Paragraph => self::paragraphs(self::inline($node)),
            $node instanceof Heading => self::heading($node),
            $node instanceof FencedCode => [self::code($node->getLiteral(), $node->getInfoWords())],
            $node instanceof IndentedCode => [self::code($node->getLiteral(), [])],
            $node instanceof BlockQuote => [self::callout($node) ?? ['type' => 'blockquote', 'content' => self::blocks($node)]],
            $node instanceof ListBlock => [self::list($node)],
            $node instanceof ThematicBreak => [['type' => 'horizontalRule']],
            $node instanceof Table => [self::table($node)],
            // Raw HTML blocks, link reference definitions: nothing the site shows.
            default => [],
        };
    }

    /**
     * Inline nodes → paragraphs; images inside a paragraph become blocks of their own.
     *
     * @param  list<array<string, mixed>>  $inline
     * @return list<array<string, mixed>>
     */
    private static function paragraphs(array $inline): array
    {
        $blocks = [];
        $run = [];

        $flush = function () use (&$blocks, &$run): void {
            $text = implode('', array_map(fn (array $node): string => (string) ($node['text'] ?? ''), $run));

            if (trim($text) !== '') {
                $blocks[] = ['type' => 'paragraph', 'content' => self::trimRun($run)];
            }

            $run = [];
        };

        foreach ($inline as $node) {
            if ($node['type'] === 'image') {
                $flush();
                $blocks[] = $node;
            } else {
                $run[] = $node;
            }
        }

        $flush();

        return $blocks;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function heading(Heading $heading): array
    {
        $content = array_values(array_filter(self::inline($heading), fn (array $node): bool => $node['type'] === 'text'));

        if ($content === []) {
            return [];
        }

        // The site has one heading style; ### is kept for the editor's outline.
        return [['type' => 'heading', 'attrs' => ['level' => $heading->getLevel() >= 3 ? 3 : 2], 'content' => $content]];
    }

    /**
     * @param  list<string>  $info  the fence's info words, e.g. ["php", 'title="app/Foo.php"']
     * @return array<string, mixed>
     */
    private static function code(string $code, array $info): array
    {
        $language = strtolower((string) ($info[0] ?? '')) ?: 'text';
        $code = rtrim($code, "\n");
        $filename = null;

        foreach (array_slice($info, 1) as $word) {
            if (preg_match('/^(?:title|file(?:name)?)=["\']?([^"\']+)["\']?$/i', $word, $match) === 1) {
                $filename = $match[1];
            }
        }

        if ($filename !== null) {
            return ['type' => 'customBlock', 'attrs' => ['id' => ArticleDocument::CODE_BLOCK, 'config' => ['language' => $language, 'filename' => $filename, 'code' => $code]]];
        }

        return ['type' => 'codeBlock', 'attrs' => ['language' => $language], 'content' => $code === '' ? [] : [['type' => 'text', 'text' => $code]]];
    }

    /**
     * `> [!NOTE] Title` followed by quoted text → the callout block.
     *
     * @return array<string, mixed>|null
     */
    private static function callout(BlockQuote $quote): ?array
    {
        $first = $quote->firstChild();

        if (! $first instanceof Paragraph || ! $first->firstChild() instanceof Text) {
            return null;
        }

        $firstLine = [];
        $rest = [];
        $inFirstLine = true;

        foreach ($first->children() as $inline) {
            if ($inFirstLine && $inline instanceof Newline) {
                $inFirstLine = false;

                continue;
            }

            if ($inFirstLine) {
                $firstLine[] = $inline;
            } else {
                $rest[] = $inline;
            }
        }

        $marker = implode('', array_map(fn (Node $node): string => $node instanceof Text ? $node->getLiteral() : '', $firstLine));

        if (preg_match(self::CALLOUT, trim($marker), $match) !== 1) {
            return null;
        }

        $title = trim($match[2]) !== '' ? trim($match[2]) : ucfirst(strtolower($match[1]));
        $parts = [];

        if ($rest !== []) {
            $parts[] = ArticleDocument::inlineMarkdown(['content' => self::inlineNodes($rest, [])]);
        }

        foreach ($quote->children() as $child) {
            if ($child === $first) {
                continue;
            }

            foreach (self::block($child) as $block) {
                $parts[] = self::flatText($block);
            }
        }

        return ['type' => 'customBlock', 'attrs' => ['id' => ArticleDocument::CALLOUT_BLOCK, 'config' => [
            'title' => $title,
            'text' => implode(' ', array_filter($parts, fn (string $part): bool => trim($part) !== '')),
        ]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function list(ListBlock $list): array
    {
        $data = $list->getListData();
        $ordered = $data->type === ListBlock::TYPE_ORDERED;
        $items = [];

        foreach ($list->children() as $item) {
            $content = self::blocks($item);
            $items[] = ['type' => 'listItem', 'content' => $content === [] ? [['type' => 'paragraph', 'content' => []]] : $content];
        }

        $node = ['type' => $ordered ? 'orderedList' : 'bulletList', 'content' => $items];

        if ($ordered && ($data->start ?? 1) !== 1) {
            $node['attrs'] = ['start' => $data->start];
        }

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private static function table(Table $table): array
    {
        $rows = [];

        foreach ($table->children() as $section) {
            if (! $section instanceof TableSection) {
                continue;
            }

            foreach ($section->children() as $row) {
                if (! $row instanceof TableRow) {
                    continue;
                }

                $cells = [];

                foreach ($row->children() as $cell) {
                    $isHeader = $cell instanceof TableCell && $cell->getType() === TableCell::TYPE_HEADER;
                    $content = array_values(array_filter(self::inline($cell), fn (array $node): bool => $node['type'] !== 'image'));
                    $cells[] = ['type' => $isHeader ? 'tableHeader' : 'tableCell', 'content' => [['type' => 'paragraph', 'content' => $content]]];
                }

                // A header of empty cells means "no header" (how a header-less table is written back).
                if ($section->isHead() && array_all($cells, fn (array $cell): bool => $cell['content'][0]['content'] === [])) {
                    continue;
                }

                $rows[] = ['type' => 'tableRow', 'content' => $cells];
            }
        }

        return ['type' => 'table', 'content' => $rows];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function inline(Node $parent): array
    {
        return self::inlineNodes(iterator_to_array($parent->children(), false), []);
    }

    /**
     * @param  list<Node>  $nodes
     * @param  list<array<string, mixed>>  $marks
     * @return list<array<string, mixed>>
     */
    private static function inlineNodes(array $nodes, array $marks): array
    {
        $out = [];

        foreach ($nodes as $node) {
            $children = iterator_to_array($node->children(), false);

            if ($node instanceof Text || $node instanceof HtmlInline) {
                self::pushText($out, $node->getLiteral(), $marks);
            } elseif ($node instanceof Code) {
                self::pushText($out, $node->getLiteral(), [...$marks, ['type' => 'code']]);
            } elseif ($node instanceof Newline) {
                $node->getType() === Newline::HARDBREAK ? $out[] = ['type' => 'hardBreak'] : self::pushText($out, ' ', $marks);
            } elseif ($node instanceof Image) {
                $out[] = ['type' => 'image', 'attrs' => array_filter([
                    'src' => trim($node->getUrl()),
                    'alt' => trim(self::plain($node)),
                    'title' => filled($node->getTitle()) ? (string) $node->getTitle() : null,
                ], fn (mixed $value): bool => $value !== null)];
            } else {
                $href = $node instanceof Link ? ArticleDocument::safeUrl($node->getUrl()) : null;
                $mark = match (true) {
                    $node instanceof Strong => ['type' => 'bold'],
                    $node instanceof Emphasis => ['type' => 'italic'],
                    $href !== null => ['type' => 'link', 'attrs' => ['href' => $href]],
                    default => null,
                };

                array_push($out, ...self::inlineNodes($children, $mark === null ? $marks : [...$marks, $mark]));
            }
        }

        return self::mergeText($out);
    }

    /**
     * @param  list<array<string, mixed>>  $out
     * @param  list<array<string, mixed>>  $marks
     */
    private static function pushText(array &$out, string $text, array $marks): void
    {
        if ($text !== '') {
            $out[] = $marks === [] ? ['type' => 'text', 'text' => $text] : ['type' => 'text', 'text' => $text, 'marks' => $marks];
        }
    }

    /**
     * Neighbouring text nodes with the same marks become one.
     *
     * @param  list<array<string, mixed>>  $nodes
     * @return list<array<string, mixed>>
     */
    private static function mergeText(array $nodes): array
    {
        $merged = [];

        foreach ($nodes as $node) {
            $last = array_key_last($merged);

            if ($last !== null && $node['type'] === 'text' && $merged[$last]['type'] === 'text' && ($merged[$last]['marks'] ?? []) === ($node['marks'] ?? [])) {
                $merged[$last]['text'] .= $node['text'];

                continue;
            }

            $merged[] = $node;
        }

        return $merged;
    }

    /**
     * Drops leading and trailing whitespace (and line breaks) of a paragraph.
     *
     * @param  list<array<string, mixed>>  $run
     * @return list<array<string, mixed>>
     */
    private static function trimRun(array $run): array
    {
        while ($run !== [] && ($run[0]['type'] === 'hardBreak' || trim((string) ($run[0]['text'] ?? '')) === '')) {
            array_shift($run);
        }

        while ($run !== [] && ($run[array_key_last($run)]['type'] === 'hardBreak' || trim((string) ($run[array_key_last($run)]['text'] ?? '')) === '')) {
            array_pop($run);
        }

        if ($run !== []) {
            $run[0]['text'] = ltrim((string) $run[0]['text']);
            $run[array_key_last($run)]['text'] = rtrim((string) $run[array_key_last($run)]['text']);
        }

        return $run;
    }

    private static function plain(Node $node): string
    {
        if ($node instanceof Text || $node instanceof Code) {
            return $node->getLiteral();
        }

        return implode('', array_map(fn (Node $child): string => $child instanceof Newline ? ' ' : self::plain($child), iterator_to_array($node->children(), false)));
    }

    /**
     * Inline Markdown of a converted block (for the text of a callout).
     *
     * @param  array<string, mixed>  $block
     */
    private static function flatText(array $block): string
    {
        if (in_array($block['type'], ['paragraph', 'heading'], true)) {
            return ArticleDocument::inlineMarkdown($block);
        }

        return implode(' ', array_map(fn (array $child): string => self::flatText($child), self::children($block)));
    }

    // Document → Markdown

    /**
     * @param  list<array<array-key, mixed>>  $nodes
     */
    private static function blocksToMarkdown(array $nodes): string
    {
        $parts = [];

        foreach ($nodes as $node) {
            $markdown = self::blockToMarkdown($node);

            if (trim($markdown) !== '') {
                $parts[] = $markdown;
            }
        }

        return implode("\n\n", $parts);
    }

    /**
     * @param  array<array-key, mixed>  $node
     */
    private static function blockToMarkdown(array $node): string
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];

        switch ($node['type'] ?? null) {
            case 'paragraph':
                $images = array_filter(self::children($node), fn (array $child): bool => ($child['type'] ?? null) === 'image');
                $text = self::escapeBlockStart(ArticleDocument::inlineMarkdown($node));

                return implode("\n\n", array_filter([$text, ...array_map(fn (array $image): string => self::imageToMarkdown($image), $images)], fn (string $part): bool => $part !== ''));

            case 'heading':
                return str_repeat('#', (int) ($attrs['level'] ?? 2) >= 3 ? 3 : 2).' '.ArticleDocument::inlineMarkdown($node);

            case 'bulletList':
            case 'orderedList':
                return self::listToMarkdown($node);

            case 'blockquote':
                return self::quoteLines(self::blocksToMarkdown(self::children($node)));

            case 'codeBlock':
                return self::fence(self::text($node), self::string($attrs, 'language'));

            case 'customBlock':
                $config = is_array($attrs['config'] ?? null) ? $attrs['config'] : [];

                return match (self::string($attrs, 'id')) {
                    ArticleDocument::CALLOUT_BLOCK => self::quoteLines('[!NOTE] '.self::string($config, 'title')."\n".self::string($config, 'text')),
                    ArticleDocument::CODE_BLOCK => self::fence(self::string($config, 'code'), self::string($config, 'language'), self::string($config, 'filename')),
                    default => '',
                };

            case 'image':
                return self::imageToMarkdown($node);

            case 'table':
                return self::tableToMarkdown($node);

            case 'horizontalRule':
                return '---';

            default:
                return self::blocksToMarkdown(self::children($node));
        }
    }

    /**
     * @param  array<array-key, mixed>  $list
     */
    private static function listToMarkdown(array $list): string
    {
        $ordered = ($list['type'] ?? null) === 'orderedList';
        $number = (int) (is_array($list['attrs'] ?? null) ? ($list['attrs']['start'] ?? 1) : 1);
        $lines = [];

        foreach (self::children($list) as $item) {
            $marker = $ordered ? ($number++).'. ' : '- ';
            $indent = str_repeat(' ', strlen($marker));
            $text = [];
            $nested = [];

            foreach (self::children($item) as $child) {
                if (in_array($child['type'] ?? null, ['bulletList', 'orderedList'], true)) {
                    $nested[] = self::listToMarkdown($child);
                } elseif (($child['type'] ?? null) === 'paragraph') {
                    $text[] = self::escapeBlockStart(ArticleDocument::inlineMarkdown($child));
                } else {
                    $nested[] = self::blockToMarkdown($child);
                }
            }

            $lines[] = $marker.implode(' ', array_filter($text, fn (string $part): bool => $part !== ''));

            foreach ($nested as $block) {
                $lines[] = preg_replace('/^(?=.)/m', $indent, $block);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<array-key, mixed>  $table
     */
    private static function tableToMarkdown(array $table): string
    {
        $rows = array_map(
            fn (array $row): array => [
                'header' => self::children($row) !== [] && array_all(self::children($row), fn (array $cell): bool => ($cell['type'] ?? null) === 'tableHeader'),
                'cells' => array_map(fn (array $cell): string => str_replace(['|', "\n"], ['\|', ' '], ArticleDocument::inlineMarkdown($cell)), self::children($row)),
            ],
            self::children($table),
        );

        if ($rows === []) {
            return '';
        }

        $columns = max(array_map(fn (array $row): int => count($row['cells']), $rows));
        $line = fn (array $cells): string => '| '.implode(' | ', array_pad($cells, $columns, '')).' |';
        $header = $rows[0]['header'] ? array_shift($rows)['cells'] : array_fill(0, $columns, '');

        return implode("\n", [
            $line($header),
            '|'.str_repeat(' --- |', $columns),
            ...array_map(fn (array $row): string => $line($row['cells']), $rows),
        ]);
    }

    /**
     * @param  array<array-key, mixed>  $image
     */
    private static function imageToMarkdown(array $image): string
    {
        $attrs = is_array($image['attrs'] ?? null) ? $image['attrs'] : [];
        $id = self::string($attrs, 'id');
        $src = $id !== '' ? "media:{$id}" : self::string($attrs, 'src');

        if ($src === '' || preg_match('/\s/', $src) === 1) {
            return '';
        }

        $alt = str_replace(['\\', '[', ']'], ['\\\\', '\[', '\]'], self::string($attrs, 'alt'));
        $title = self::string($attrs, 'title');

        return "![{$alt}]({$src}".($title !== '' ? ' "'.str_replace(['\\', '"'], ['\\\\', '\"'], $title).'"' : '').')';
    }

    private static function fence(string $code, string $language, string $filename = ''): string
    {
        preg_match_all('/`{3,}/', $code, $runs);
        $fence = str_repeat('`', max(3, ...array_map(fn (string $run): int => strlen($run) + 1, $runs[0] ?: ['``'])));
        $info = trim(($language !== '' && $language !== 'text' ? $language : ($filename !== '' ? 'text' : '')).($filename !== '' && preg_match('/\s/', $filename) !== 1 ? " title=\"{$filename}\"" : ''));

        return "{$fence}{$info}\n".rtrim($code, "\n")."\n{$fence}";
    }

    private static function quoteLines(string $markdown): string
    {
        return implode("\n", array_map(fn (string $line): string => $line === '' ? '>' : "> {$line}", explode("\n", trim($markdown))));
    }

    /**
     * A paragraph that starts like a heading, list, quote or rule would change meaning when read back.
     */
    private static function escapeBlockStart(string $text): string
    {
        return (string) preg_replace(['/^(#{1,6}\s|[>+]|[-=]{1,}\s*$|-\s)/', '/^(\d+)([.)]\s)/'], ['\\\\$1', '$1\\\\$2'], $text);
    }

    /**
     * @param  array<array-key, mixed>  $node
     */
    private static function text(array $node): string
    {
        if (($node['type'] ?? null) === 'text') {
            return self::string($node, 'text');
        }

        return implode('', array_map(
            fn (array $child): string => ($child['type'] ?? null) === 'hardBreak' ? "\n" : self::text($child),
            self::children($node),
        ));
    }

    /**
     * @param  array<array-key, mixed>  $node
     * @return list<array<array-key, mixed>>
     */
    private static function children(array $node): array
    {
        $content = $node['content'] ?? [];

        return is_array($content) ? array_values(array_filter($content, is_array(...))) : [];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }
}
