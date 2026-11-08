<?php

namespace App\Mcp\Support;

use App\Models\Article;
use App\Support\Content\ArticleMarkdown;
use App\Support\Media\ImageRejected;
use App\Support\Media\MimeTypes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Images inside an article body (the `body_images` collection). In the Markdown the MCP tools exchange, an
 * image is written `![alt](src "caption")` where src is one of:
 *
 * - `media:<uuid>`: an image already stored on the article (get_article writes them this way);
 * - `image:<key>`: a placeholder, filled later by add_article_image or an upload with that placeholder;
 * - an http(s) URL: downloaded and stored on the article when the body is saved.
 *
 * Like the panel's editor, images taken out of the body are deleted.
 */
final class ArticleImages
{
    public const PLACEHOLDER_PREFIX = 'image:';

    private const MEDIA_PREFIX = 'media:';

    /**
     * Turns every image source in the document into a stored image or a placeholder.
     *
     * @param  array<array-key, mixed>  $doc
     * @return array{doc: array<array-key, mixed>, warnings: list<string>}
     */
    public static function resolve(Article $article, array $doc): array
    {
        $stored = $article->getMedia('body_images')->keyBy('uuid');
        $warnings = [];

        $doc = ArticleMarkdown::mapImages($doc, function (array $node) use ($article, $stored, &$warnings): ?array {
            $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
            $src = is_string($attrs['src'] ?? null) ? $attrs['src'] : '';
            $alt = is_string($attrs['alt'] ?? null) ? $attrs['alt'] : '';
            $caption = is_string($attrs['title'] ?? null) && $attrs['title'] !== '' ? $attrs['title'] : null;

            if (str_starts_with($src, self::PLACEHOLDER_PREFIX) && preg_match('/^image:[A-Za-z0-9_-]+$/', $src) === 1) {
                return self::node(null, $src, $alt, $caption);
            }

            if (str_starts_with($src, self::MEDIA_PREFIX)) {
                $media = $stored->get(substr($src, strlen(self::MEDIA_PREFIX)));

                if ($media === null) {
                    $warnings[] = "Removed image \"{$src}\": it is not an image of this article.";

                    return null;
                }

                return self::node($media, null, $alt, $caption);
            }

            if (preg_match('#^https?://#i', $src) === 1) {
                try {
                    $media = ImageInput::fetch(['image_url' => $src], MimeTypes::RASTER)->storeOn($article, 'body_images', $alt ?: $article->title);
                } catch (ImageRejected $exception) {
                    $warnings[] = "Removed image {$src}: {$exception->getMessage()}";

                    return null;
                }

                return self::node($media, null, $alt, $caption);
            }

            $warnings[] = 'Removed image "'.$src.'": use media:<uuid>, image:<placeholder> or an http(s) URL.';

            return null;
        });

        return ['doc' => $doc, 'warnings' => $warnings];
    }

    /**
     * Puts a stored image in the body: in place of the placeholder `image:<key>` when given (and present),
     * otherwise at the end.
     *
     * @return bool whether a placeholder was filled
     */
    public static function place(Article $article, Media $media, string $alt, ?string $caption, ?string $placeholder): bool
    {
        $doc = $article->body;
        $filled = false;

        if (filled($placeholder)) {
            $source = self::PLACEHOLDER_PREFIX.$placeholder;
            $doc = ArticleMarkdown::mapImages($doc, function (array $node) use ($source, $media, $alt, $caption, &$filled): array {
                if ($filled || ($node['attrs']['src'] ?? null) !== $source) {
                    return $node;
                }

                $filled = true;

                return self::node($media, null, $alt ?: (string) ($node['attrs']['alt'] ?? ''), $caption ?? ($node['attrs']['title'] ?? null));
            });
        }

        if (! $filled) {
            $doc['content'] = [...(is_array($doc['content'] ?? null) ? $doc['content'] : []), self::node($media, null, $alt, $caption)];
        }

        $article->forceFill(['body' => $doc])->save();

        return $filled;
    }

    /**
     * Deletes stored body images the body no longer shows.
     */
    public static function prune(Article $article): void
    {
        $used = self::storedIds($article->body);

        $article->getMedia('body_images')
            ->reject(fn (Media $media): bool => in_array($media->uuid, $used, true))
            ->each(fn (Media $media): ?bool => $media->delete());
    }

    /**
     * Keys of the placeholders still waiting for an image.
     *
     * @param  array<array-key, mixed>  $doc
     * @return list<string>
     */
    public static function placeholders(array $doc): array
    {
        $keys = [];

        ArticleMarkdown::mapImages($doc, function (array $node) use (&$keys): array {
            $src = (string) ($node['attrs']['src'] ?? '');

            if (empty($node['attrs']['id']) && str_starts_with($src, self::PLACEHOLDER_PREFIX)) {
                $keys[] = substr($src, strlen(self::PLACEHOLDER_PREFIX));
            }

            return $node;
        });

        return $keys;
    }

    /**
     * @param  array<array-key, mixed>  $doc
     * @return list<string>
     */
    private static function storedIds(array $doc): array
    {
        $ids = [];

        ArticleMarkdown::mapImages($doc, function (array $node) use (&$ids): array {
            if (is_string($node['attrs']['id'] ?? null) && $node['attrs']['id'] !== '') {
                $ids[] = $node['attrs']['id'];
            }

            return $node;
        });

        return $ids;
    }

    /**
     * An image node as the panel's editor writes it (`id` is the media uuid).
     *
     * @return array<string, mixed>
     */
    private static function node(?Media $media, ?string $placeholder, string $alt, ?string $caption): array
    {
        return ['type' => 'image', 'attrs' => array_filter([
            'id' => $media?->uuid,
            'src' => $media?->getUrl() ?? $placeholder,
            'alt' => $alt,
            'title' => $caption,
        ], fn (mixed $value): bool => $value !== null)];
    }
}
