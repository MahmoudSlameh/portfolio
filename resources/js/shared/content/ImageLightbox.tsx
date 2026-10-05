import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import {
    useCallback,
    useEffect,
    useRef,
    useState,
    type KeyboardEvent,
    type ReactNode,
} from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type { GalleryImage } from '@/types/content';

interface ImageLightbox {
    /** Opens the lightbox on the image at `index`. */
    open: (index: number) => void;
    /** Render once next to the gallery. */
    lightbox: ReactNode;
}

const controlClassName =
    'inline-flex size-11 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';

/**
 * Full-size viewer for a case-study gallery. Built on <dialog> so focus trapping and Escape
 * come for free; arrow keys step through the images. Templates own the thumbnail markup and
 * call `open(index)` from their own button.
 */
export function useImageLightbox(images: GalleryImage[]): ImageLightbox {
    const [activeIndex, setActiveIndex] = useState<number | null>(null);
    const close = useCallback(() => setActiveIndex(null), []);
    const open = useCallback((index: number) => setActiveIndex(index), []);

    return {
        open,
        lightbox: (
            <LightboxDialog
                images={images}
                activeIndex={activeIndex}
                onIndexChange={setActiveIndex}
                onClose={close}
            />
        ),
    };
}

function LightboxDialog({
    images,
    activeIndex,
    onIndexChange,
    onClose,
}: {
    images: GalleryImage[];
    activeIndex: number | null;
    onIndexChange: (index: number) => void;
    onClose: () => void;
}) {
    const { t } = useTranslation();
    const dialogRef = useRef<HTMLDialogElement>(null);
    const isOpen = activeIndex !== null;
    const image = activeIndex === null ? null : images[activeIndex];
    const total = images.length;

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;
        if (isOpen && !dialog.open) dialog.showModal();
        if (!isOpen && dialog.open) dialog.close();
    }, [isOpen]);

    const step = (offset: number): void => {
        if (activeIndex === null || total < 2) return;
        onIndexChange((activeIndex + offset + total) % total);
    };

    const handleKeyDown = (event: KeyboardEvent<HTMLDialogElement>): void => {
        if (event.key === 'ArrowRight') step(1);
        if (event.key === 'ArrowLeft') step(-1);
    };

    return (
        <dialog
            ref={dialogRef}
            dir="ltr"
            aria-label={image?.alt || t('case.gallery')}
            onClose={onClose}
            onKeyDown={handleKeyDown}
            onClick={(event) => {
                if (event.target === event.currentTarget) onClose();
            }}
            className="fixed inset-0 m-0 h-dvh max-h-none w-screen max-w-none bg-black/90 p-0 text-white backdrop:bg-transparent"
        >
            {image && (
                <div
                    className="flex h-full flex-col items-center justify-center gap-4 p-4 sm:p-10"
                    onClick={(event) => {
                        if (event.target === event.currentTarget) onClose();
                    }}
                >
                    <img
                        src={image.src}
                        alt={image.alt}
                        width={image.width || undefined}
                        height={image.height || undefined}
                        className="max-h-[calc(100dvh-9rem)] w-auto max-w-full object-contain"
                    />
                    {(image.caption || total > 1) && (
                        <p className="flex max-w-3xl gap-3 text-center text-sm text-white/80">
                            {total > 1 && (
                                <span className="shrink-0 font-mono text-xs text-white/50">
                                    {(activeIndex ?? 0) + 1} / {total}
                                </span>
                            )}
                            {image.caption}
                        </p>
                    )}
                </div>
            )}

            <button
                type="button"
                onClick={onClose}
                aria-label={t('lightbox.close')}
                className={`${controlClassName} absolute top-4 right-4`}
            >
                <X aria-hidden className="size-5" />
            </button>
            {total > 1 && (
                <>
                    <button
                        type="button"
                        onClick={() => step(-1)}
                        aria-label={t('lightbox.previous')}
                        className={`${controlClassName} absolute top-1/2 left-4 -translate-y-1/2`}
                    >
                        <ChevronLeft aria-hidden className="size-5" />
                    </button>
                    <button
                        type="button"
                        onClick={() => step(1)}
                        aria-label={t('lightbox.next')}
                        className={`${controlClassName} absolute top-1/2 right-4 -translate-y-1/2`}
                    >
                        <ChevronRight aria-hidden className="size-5" />
                    </button>
                </>
            )}
        </dialog>
    );
}
