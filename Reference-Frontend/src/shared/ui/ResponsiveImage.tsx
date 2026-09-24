import { imageSources, cn } from '@/lib/utils';
import type { ImageAsset } from '@/types/content';

interface ResponsiveImageProps {
  image: ImageAsset;
  sizes: string;
  priority?: boolean;
  className?: string;
}

export function ResponsiveImage({ image, sizes, priority = false, className }: ResponsiveImageProps) {
  const { src, srcSet } = imageSources(image);

  return (
    <img
      src={src}
      srcSet={srcSet}
      sizes={sizes}
      width={image.width}
      height={image.height}
      alt={image.alt}
      loading={priority ? 'eager' : 'lazy'}
      decoding="async"
      fetchPriority={priority ? 'high' : 'auto'}
      className={cn('block h-auto w-full bg-surface object-cover', className)}
    />
  );
}
