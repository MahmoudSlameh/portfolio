<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Stores the intrinsic width/height of raster images as custom properties so the
 * frontend can always render width/height attributes (no layout shift).
 */
class StoreMediaDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! str_starts_with((string) $media->mime_type, 'image/') || $media->mime_type === 'image/svg+xml') {
            return;
        }

        $contents = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());

        if ($contents === null) {
            return;
        }

        $size = @getimagesizefromstring($contents);

        if ($size === false) {
            return;
        }

        $media->setCustomProperty('width', $size[0]);
        $media->setCustomProperty('height', $size[1]);
        $media->saveQuietly();
    }
}
