<?php

namespace App\Support\Content;

/**
 * The article body as stored by the rich editor: a TipTap document (docs/tasks/phase-11-content-tools.md, P11-01).
 *
 * Templates never see it. `toBuilder()` flattens it into the block list the site renders
 * (paragraph, heading, code, quote, list, callout, image), with marks written as the inline
 * Markdown subset `**bold**`, `*italic*`, `` `code` `` and `[text](url)`. `fromBuilder()` is the
 * reverse, used by the migration, the factory and the demo seeder.
 */
final class ArticleDocument
{
    public const CALLOUT_BLOCK = 'callout';

    public const CODE_BLOCK = 'code';

    /**
     * @return array{type: 'doc', content: list<array<string, mixed>>}
     */
    public static function empty(): array
    {
        return ['type' => 'doc', 'content' => []];
    }

    /**
     * @param  array<array-key, mixed>|null  $doc
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    public static function toBuilder(?array $doc): array
    {
        $blocks = [];

        foreach (self::children($doc ?? []) as $node) {
            array_push($blocks, ...self::blocksFor($node));
        }

        return $blocks;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks  `[{type, data}]`
     * @return array{type: 'doc', content: list<array<string, mixed>>}
     */
    public static function fromBuilder(array $blocks): array
    {
        $content = [];

        foreach ($blocks as $block) {
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $text = fn (string $key): string => self::string($data, $key);

            $node = match ($block['type'] ?? null) {
                'paragraph' => self::paragraph($text('text')),
                'heading' => ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => self::textNodes($text('text'), withMarks: false)],
                'code' => $text('filename') !== ''
                    ? self::customBlock(self::CODE_BLOCK, ['language' => $text('language') ?: 'text', 'filename' => $text('filename'), 'code' => $text('code')])
                    : ['type' => 'codeBlock', 'attrs' => ['language' => $text('language') ?: 'text'], 'content' => self::textNodes($text('code'), withMarks: false)],
                'quote' => ['type' => 'blockquote', 'content' => array_values(array_filter([
                    self::paragraph($text('text')),
                    $text('cite') !== '' ? self::paragraph('— '.$text('cite')) : null,
                ]))],
                'list' => ['type' => 'bulletList', 'content' => array_map(
                    fn (mixed $item): array => ['type' => 'listItem', 'content' => [self::paragraph(self::scalar(is_array($item) ? ($item['item'] ?? '') : $item))]],
                    array_values((array) ($data['items'] ?? [])),
                )],
                'callout' => self::customBlock(self::CALLOUT_BLOCK, ['title' => $text('title'), 'text' => $text('text')]),
                'image' => ['type' => 'image', 'attrs' => array_filter([
                    'id' => $text('media_uuid'),
                    'alt' => $text('alt'),
                    'title' => $text('caption') ?: null,
                ], fn (mixed $value): bool => $value !== null)],
                default => null,
            };

            if ($node !== null) {
                $content[] = $node;
            }
        }

        return ['type' => 'doc', 'content' => $content];
    }

    /**
     * Blocks for one top-level (or unwrapped) node.
     *
     * @param  array<array-key, mixed>  $node
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    private static function blocksFor(array $node): array
    {
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];

        switch ($node['type'] ?? null) {
            case 'paragraph':
                $text = trim(self::inline($node));

                return $text === '' ? [] : [['type' => 'paragraph', 'data' => ['text' => $text]]];

            case 'heading':
                $text = trim(self::plain($node));

                return $text === '' ? [] : [['type' => 'heading', 'data' => ['text' => $text]]];

            case 'bulletList':
            case 'orderedList':
                $items = self::listItems($node);

                return $items === [] ? [] : [['type' => 'list', 'data' => ['items' => $items]]];

            case 'blockquote':
                return self::quote($node);

            case 'codeBlock':
                $code = self::plain($node);

                return trim($code) === '' ? [] : [['type' => 'code', 'data' => ['language' => self::string($attrs, 'language') ?: 'text', 'code' => $code]]];

            case 'customBlock':
                return self::customBlockToBlocks(self::string($attrs, 'id'), is_array($attrs['config'] ?? null) ? $attrs['config'] : []);

            case 'image':
                $uuid = self::string($attrs, 'id');

                return $uuid === '' ? [] : [['type' => 'image', 'data' => array_filter([
                    'media_uuid' => $uuid,
                    'alt' => self::string($attrs, 'alt'),
                    'caption' => self::string($attrs, 'title') ?: null,
                ], fn (mixed $value): bool => $value !== null)]];

            case 'tableRow':
                // The site has no tables: a row becomes one line, its cells separated by a dot.
                $cells = array_filter(array_map(fn (array $cell): string => trim(self::inline($cell)), self::children($node)), fn (string $cell): bool => $cell !== '');

                return $cells === [] ? [] : [['type' => 'paragraph', 'data' => ['text' => implode(' · ', $cells)]]];

            case 'horizontalRule':
            case 'hardBreak':
                return [];

            case 'text':
                $text = trim(self::inline(['content' => [$node]]));

                return $text === '' ? [] : [['type' => 'paragraph', 'data' => ['text' => $text]]];

            default:
                // Tables, details, grids, anything pasted that the site has no block for: keep the content.
                $blocks = [];

                foreach (self::children($node) as $child) {
                    array_push($blocks, ...self::blocksFor($child));
                }

                return $blocks;
        }
    }

    /**
     * @param  array<array-key, mixed>  $config
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    private static function customBlockToBlocks(string $id, array $config): array
    {
        return match ($id) {
            self::CALLOUT_BLOCK => self::string($config, 'title').self::string($config, 'text') === '' ? [] : [['type' => 'callout', 'data' => [
                'title' => self::string($config, 'title'),
                'text' => self::string($config, 'text'),
            ]]],
            self::CODE_BLOCK => trim(self::string($config, 'code')) === '' ? [] : [['type' => 'code', 'data' => array_filter([
                'language' => self::string($config, 'language') ?: 'text',
                'filename' => self::string($config, 'filename') ?: null,
                'code' => self::string($config, 'code'),
            ], fn (mixed $value): bool => $value !== null)]],
            default => [],
        };
    }

    /**
     * A quote's paragraphs; a last paragraph starting with a dash is the citation.
     *
     * @param  array<array-key, mixed>  $node
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    private static function quote(array $node): array
    {
        $lines = [];

        foreach (self::children($node) as $child) {
            $line = trim(in_array($child['type'] ?? null, ['bulletList', 'orderedList'], true)
                ? implode(' ', self::listItems($child))
                : self::inline($child));

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        $cite = null;

        if (count($lines) > 1 && preg_match('/^(?:—|–|--)\s*(.+)$/u', (string) end($lines), $match) === 1) {
            array_pop($lines);
            $cite = $match[1];
        }

        if ($lines === []) {
            return [];
        }

        return [['type' => 'quote', 'data' => array_filter(['text' => implode(' ', $lines), 'cite' => $cite], fn (mixed $value): bool => $value !== null)]];
    }

    /**
     * Each list item's text; nested lists become further items.
     *
     * @param  array<array-key, mixed>  $list
     * @return list<string>
     */
    private static function listItems(array $list): array
    {
        $items = [];

        foreach (self::children($list) as $item) {
            $text = [];
            $nested = [];

            foreach (self::children($item) as $child) {
                if (in_array($child['type'] ?? null, ['bulletList', 'orderedList'], true)) {
                    array_push($nested, ...self::listItems($child));
                } else {
                    $text[] = trim(self::inline($child));
                }
            }

            $line = trim(implode(' ', array_filter($text, fn (string $part): bool => $part !== '')));

            if ($line !== '') {
                $items[] = $line;
            }

            array_push($items, ...$nested);
        }

        return $items;
    }

    /**
     * A node's text with marks written as inline Markdown.
     *
     * @param  array<array-key, mixed>  $node
     */
    private static function inline(array $node): string
    {
        $out = '';
        $run = [];

        foreach (self::children($node) as $child) {
            if (($child['type'] ?? null) === 'text') {
                $run[] = ['text' => self::string($child, 'text'), 'marks' => self::marks($child)];

                continue;
            }

            $out .= self::serializeRun($run).(($child['type'] ?? null) === 'hardBreak' ? ' ' : self::inline($child).' ');
            $run = [];
        }

        return (string) preg_replace('/[ \t]{2,}/', ' ', $out.self::serializeRun($run));
    }

    /**
     * The marks the site keeps, keyed by type (`link` => href).
     *
     * @param  array<array-key, mixed>  $node
     * @return array<string, string>
     */
    private static function marks(array $node): array
    {
        $marks = [];

        foreach (is_array($node['marks'] ?? null) ? $node['marks'] : [] as $mark) {
            $type = is_array($mark) ? ($mark['type'] ?? null) : null;

            if ($type === 'link') {
                $href = self::safeUrl(self::string(is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [], 'href'));

                if ($href !== null) {
                    $marks['link'] = $href;
                }
            } elseif (in_array($type, ['bold', 'italic', 'code'], true)) {
                $marks[$type] = '';
            }
        }

        return $marks;
    }

    /**
     * Consecutive text nodes → inline Markdown. Neighbours sharing a mark are wrapped once, so
     * bold text with an italic word inside stays `**bold *word* text**`.
     *
     * @param  list<array{text: string, marks: array<string, string>}>  $nodes
     */
    private static function serializeRun(array $nodes): string
    {
        $out = '';
        $i = 0;
        $count = count($nodes);

        while ($i < $count) {
            $marks = $nodes[$i]['marks'];
            $outer = null;

            foreach (['link', 'bold', 'italic'] as $type) {
                if (array_key_exists($type, $marks)) {
                    $outer = $type;

                    break;
                }
            }

            if ($outer === null) {
                $text = $nodes[$i]['text'];
                // Code spans are literal; a backtick inside one cannot be written, so it is dropped.
                $out .= array_key_exists('code', $marks) ? self::wrap('`', str_replace('`', '', $text), '`') : self::escape($text);
                $i++;

                continue;
            }

            $value = $marks[$outer];
            $inner = [];

            while ($i < $count && ($nodes[$i]['marks'][$outer] ?? null) === $value) {
                $node = $nodes[$i];
                unset($node['marks'][$outer]);
                $inner[] = $node;
                $i++;
            }

            $body = self::serializeRun($inner);
            $out .= match ($outer) {
                'link' => self::wrap('[', $body, "]({$value})"),
                'bold' => self::wrap('**', $body, '**'),
                default => self::wrap('*', $body, '*'),
            };
        }

        return $out;
    }

    /**
     * Wraps text in markers, keeping surrounding spaces outside so `**word** ` stays valid.
     */
    private static function wrap(string $open, string $text, string $close): string
    {
        preg_match('/^(\s*)(.*?)(\s*)$/su', $text, $parts);
        [, $lead, $core, $trail] = $parts + ['', '', '', ''];

        return $core === '' ? $text : $lead.$open.$core.$close.$trail;
    }

    /**
     * Inline Markdown → TipTap text nodes with marks (the parser `InlineText` mirrors in the kit).
     *
     * @return list<array<string, mixed>>
     */
    public static function textNodes(string $text, bool $withMarks = true): array
    {
        if ($text === '') {
            return [];
        }

        if (! $withMarks) {
            return [['type' => 'text', 'text' => $text]];
        }

        $nodes = [];
        self::parseInline($text, [], $nodes);

        return $nodes;
    }

    /**
     * @param  list<array<string, mixed>>  $marks
     * @param  list<array<string, mixed>>  $nodes
     */
    private static function parseInline(string $text, array $marks, array &$nodes): void
    {
        $pattern = '/\\\\([\\\\`*\[\]])|`([^`]+)`|\*\*(.+?)\*\*|\*([^*\s](?:[^*]*[^*\s])?)\*|\[([^\]]+)\]\(((?:[^()\s]|\([^()\s]*\))+)\)/su';
        $offset = 0;

        while (preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL, $offset) === 1) {
            $start = $match[0][1];
            [$escaped, $code, $bold, $italic, $label, $href] = [$match[1][0], $match[2][0], $match[3][0], $match[4][0], $match[5][0], $match[6][0]];
            self::pushText(substr($text, $offset, $start - $offset), $marks, $nodes);

            if ($escaped !== null) {
                self::pushText($escaped, $marks, $nodes);
            } elseif ($code !== null) {
                self::pushText($code, [...$marks, ['type' => 'code']], $nodes);
            } elseif ($bold !== null) {
                self::parseInline($bold, [...$marks, ['type' => 'bold']], $nodes);
            } elseif ($italic !== null) {
                self::parseInline($italic, [...$marks, ['type' => 'italic']], $nodes);
            } else {
                $url = self::safeUrl((string) $href);
                self::parseInline((string) $label, $url !== null ? [...$marks, ['type' => 'link', 'attrs' => ['href' => $url]]] : $marks, $nodes);
            }

            $offset = $start + strlen((string) $match[0][0]);
        }

        self::pushText(substr($text, $offset), $marks, $nodes);
    }

    /**
     * @param  list<array<string, mixed>>  $marks
     * @param  list<array<string, mixed>>  $nodes
     */
    private static function pushText(string $text, array $marks, array &$nodes): void
    {
        if ($text === '') {
            return;
        }

        $last = array_key_last($nodes);

        // Merge with the previous node when the marks are the same (escapes split plain text).
        if ($last !== null && ($nodes[$last]['marks'] ?? []) === $marks) {
            $nodes[$last]['text'] .= $text;

            return;
        }

        $nodes[] = $marks === [] ? ['type' => 'text', 'text' => $text] : ['type' => 'text', 'text' => $text, 'marks' => $marks];
    }

    /**
     * Inline Markdown without its markers (word counts, search).
     */
    public static function plainText(string $text): string
    {
        return implode('', array_map(fn (array $node): string => self::string($node, 'text'), self::textNodes($text)));
    }

    /**
     * Only web, mail and relative links; anything else (javascript:, data:) is dropped.
     */
    public static function safeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || preg_match('/\s/', $url) === 1) {
            return null;
        }

        // Brackets are encoded so the URL always fits inside `[text](url)`.
        return preg_match('#^(https?://|mailto:|/|\#)#i', $url) === 1 ? str_replace(['(', ')'], ['%28', '%29'], $url) : null;
    }

    private static function escape(string $text): string
    {
        return (string) preg_replace('/([\\\\`*\[\]])/', '\\\\$1', $text);
    }

    /**
     * Plain text of a node and its children (no marks).
     *
     * @param  array<array-key, mixed>  $node
     */
    private static function plain(array $node): string
    {
        if (($node['type'] ?? null) === 'text') {
            return self::string($node, 'text');
        }

        return implode('', array_map(
            fn (array $child): string => ($child['type'] ?? null) === 'hardBreak' ? "\n" : self::plain($child),
            self::children($node),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private static function paragraph(string $text): array
    {
        return ['type' => 'paragraph', 'content' => self::textNodes($text)];
    }

    /**
     * @param  array<string, string>  $config
     * @return array<string, mixed>
     */
    private static function customBlock(string $id, array $config): array
    {
        return ['type' => 'customBlock', 'attrs' => ['id' => $id, 'config' => $config]];
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
        return self::scalar($data[$key] ?? '');
    }

    private static function scalar(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
