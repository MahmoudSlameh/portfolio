<?php

namespace App\Support\Content;

use App\Models\Article;
use App\Support\Media\ImageData;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Converts the stored Filament Builder body (`[{type, data}]`) into the frontend `ArticleBlock[]` union.
 */
final class ArticleBody
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function toBlocks(Article $article): array
    {
        $images = $article->getMedia('body_images')->keyBy('uuid');
        $headingIds = [];
        $blocks = [];

        foreach ($article->body as $block) {
            $data = $block['data'];

            $blocks[] = match ($block['type']) {
                'paragraph' => ['type' => 'paragraph', 'text' => self::string($data, 'text')],
                'heading' => [
                    'type' => 'heading',
                    'id' => self::uniqueId(self::string($data, 'id') ?: Str::slug(self::string($data, 'text')), $headingIds),
                    'text' => self::string($data, 'text'),
                ],
                'code' => array_filter([
                    'type' => 'code',
                    'language' => self::string($data, 'language') ?: 'ts',
                    'filename' => self::string($data, 'filename') ?: null,
                    'code' => self::string($data, 'code'),
                ], fn (mixed $value): bool => $value !== null),
                'quote' => array_filter([
                    'type' => 'quote',
                    'text' => self::string($data, 'text'),
                    'cite' => self::string($data, 'cite') ?: null,
                ], fn (mixed $value): bool => $value !== null),
                'list' => ['type' => 'list', 'items' => array_values(array_map(strval(...), (array) ($data['items'] ?? [])))],
                'callout' => ['type' => 'callout', 'title' => self::string($data, 'title'), 'text' => self::string($data, 'text')],
                'image' => self::image($data, $images->get(self::string($data, 'media_uuid'))),
                default => null,
            };
        }

        return array_values(array_filter($blocks));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private static function image(array $data, ?Media $media): ?array
    {
        $image = ImageData::from($media, self::string($data, 'alt'));

        if ($image === null) {
            return null;
        }

        return array_filter([
            'type' => 'image',
            'image' => $image,
            'caption' => self::string($data, 'caption') ?: null,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param  array<string, int>  $seen
     */
    private static function uniqueId(string $id, array &$seen): string
    {
        $id = $id ?: 'section';
        $seen[$id] = ($seen[$id] ?? 0) + 1;

        return $seen[$id] > 1 ? "{$id}-{$seen[$id]}" : $id;
    }
}
