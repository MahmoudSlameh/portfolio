import { ArrowLeft, ArrowRight } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useState, type KeyboardEvent } from 'react';
import { IconButton } from '@/templates/changelog/components/ui/IconButton';
import { useTranslation } from '@/hooks/useTranslation';
import type { TestimonialEntry } from '@/lib/content';
import { cn } from '@/lib/utils';

export function TestimonialCarousel({
    testimonials,
}: {
    testimonials: TestimonialEntry[];
}) {
    const { t } = useTranslation();
    const [index, setIndex] = useState(0);
    const [direction, setDirection] = useState<1 | -1>(1);
    const total = testimonials.length;
    const current = testimonials[index];

    const goTo = (nextIndex: number, nextDirection: 1 | -1): void => {
        setDirection(nextDirection);
        setIndex((nextIndex + total) % total);
    };

    const handlePrevious = (): void => goTo(index - 1, -1);
    const handleNext = (): void => goTo(index + 1, 1);

    const handleKeyDown = (event: KeyboardEvent<HTMLDivElement>): void => {
        const nextKey = 'ArrowRight';
        const previousKey = 'ArrowLeft';
        if (event.key === nextKey) handleNext();
        if (event.key === previousKey) handlePrevious();
    };

    const offset = 24 * direction;

    return (
        <div
            role="region"
            aria-roledescription="carousel"
            aria-label={t('clients.testimonials')}
            onKeyDown={handleKeyDown}
            className="editorial-grid gap-y-8 border-t border-line pt-10"
        >
            <div className="col-span-4 md:col-span-9">
                <div
                    aria-live="polite"
                    aria-atomic="true"
                    className="relative min-h-[16rem] sm:min-h-[13rem] md:min-h-[12rem]"
                >
                    <AnimatePresence
                        mode="wait"
                        initial={false}
                        custom={offset}
                    >
                        <motion.figure
                            key={current.id}
                            role="group"
                            aria-roledescription="slide"
                            aria-label={t('carousel.position', {
                                current: index + 1,
                                total,
                            })}
                            initial={{ opacity: 0, x: offset }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: -offset }}
                            transition={{
                                duration: 0.4,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                        >
                            <blockquote
                                lang="en"
                                className="font-display text-[1.625rem] leading-[1.25] text-ink md:text-[2.25rem]"
                            >
                                <span aria-hidden className="text-signal-ink">
                                    “
                                </span>
                                {current.quote}
                                <span aria-hidden className="text-signal-ink">
                                    ”
                                </span>
                            </blockquote>
                            <figcaption
                                lang="en"
                                className="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1"
                            >
                                <span className="text-[0.9375rem] font-semibold text-ink">
                                    {current.author}
                                </span>
                                <span className="text-sm text-ink-muted">
                                    {current.role}, {current.company?.name}
                                </span>
                                <span className="font-mono text-[0.6875rem] text-ink-subtle">
                                    {current.relation}
                                </span>
                            </figcaption>
                        </motion.figure>
                    </AnimatePresence>
                </div>
            </div>

            <div className="col-span-4 flex items-center justify-between gap-4 md:col-span-3 md:flex-col md:items-end md:justify-start">
                <div className="flex items-center gap-2">
                    <IconButton
                        label={t('carousel.previous')}
                        onClick={handlePrevious}
                        className="border border-line-strong"
                        tooltipSide="top"
                    >
                        <ArrowLeft
                            aria-hidden
                            className="size-4 rtl:-scale-x-100"
                        />
                    </IconButton>
                    <IconButton
                        label={t('carousel.next')}
                        onClick={handleNext}
                        className="border border-line-strong"
                        tooltipSide="top"
                    >
                        <ArrowRight
                            aria-hidden
                            className="size-4 rtl:-scale-x-100"
                        />
                    </IconButton>
                </div>
                <div className="flex items-center gap-3">
                    <span className="ltr-isolate font-mono text-xs text-ink-subtle">
                        {String(index + 1).padStart(2, '0')} /{' '}
                        {String(total).padStart(2, '0')}
                    </span>
                    <div className="flex items-center gap-1">
                        {testimonials.map((testimonial, dotIndex) => (
                            <button
                                key={testimonial.id}
                                type="button"
                                aria-label={t('carousel.goTo', {
                                    index: dotIndex + 1,
                                })}
                                aria-current={
                                    dotIndex === index ? 'true' : undefined
                                }
                                onClick={() =>
                                    goTo(dotIndex, dotIndex > index ? 1 : -1)
                                }
                                className="flex size-6 items-center justify-center"
                            >
                                <span
                                    className={cn(
                                        'h-1 rounded-full transition-all duration-300',
                                        dotIndex === index
                                            ? 'w-6 bg-signal'
                                            : 'w-1.5 bg-line-strong hover:bg-electric',
                                    )}
                                />
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
