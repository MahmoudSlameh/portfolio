import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

interface ResponsiveImageProps {
    /** Null when no image was uploaded in the panel — a neutral placeholder keeps the layout. */
    image: ImageData | null;
    sizes: string;
    priority?: boolean;
    className?: string;
    /** Aspect ratio used for the placeholder (and as a fallback when the size is unknown). */
    fallbackAspect?: string;
}

export function ResponsiveImage({
    image,
    sizes,
    priority = false,
    className,
    fallbackAspect = '16 / 9',
}: ResponsiveImageProps) {
    if (!image) {
        return (
            <div
                aria-hidden
                style={{ aspectRatio: fallbackAspect }}
                className={cn('bg-surface block h-auto w-full', className)}
            />
        );
    }

    return (
        <img
            src={image.src}
            srcSet={image.srcSet || undefined}
            sizes={image.srcSet ? sizes : undefined}
            width={image.width || undefined}
            height={image.height || undefined}
            alt={image.alt}
            loading={priority ? 'eager' : 'lazy'}
            decoding="async"
            fetchPriority={priority ? 'high' : 'auto'}
            style={
                image.width && image.height
                    ? undefined
                    : { aspectRatio: fallbackAspect }
            }
            className={cn(
                'bg-surface block h-auto w-full object-cover',
                className,
            )}
        />
    );
}
