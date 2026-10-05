<?php

namespace App\Mcp\Support;

use App\Support\Media\ImageRejected;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An image received by an MCP tool (see {@see ImageInput::fetch()}), ready to be stored.
 */
final readonly class IncomingImage
{
    public function __construct(
        public string $bytes,
        public string $mimeType,
    ) {}

    /**
     * Adds the image to the model's media collection (replacing it in single-file collections).
     *
     * @throws ImageRejected
     */
    public function storeOn(HasMedia $model, string $collection, string $name): Media
    {
        $extension = Str::after($this->mimeType, '/');
        $directory = storage_path('app/private/mcp-uploads');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.Str::ulid().'.'.$extension;
        File::put($path, $this->bytes);

        try {
            return $model->addMedia($path)
                ->usingName($name)
                ->usingFileName((Str::slug($name) ?: 'image').'.'.$extension)
                ->toMediaCollection($collection);
        } catch (FileCannotBeAdded $exception) {
            throw new ImageRejected('The image could not be stored: '.$exception->getMessage());
        } finally {
            File::delete($path);
        }
    }
}
