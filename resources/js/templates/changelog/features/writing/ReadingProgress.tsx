import { useEffect, useState, type RefObject } from 'react';
import { useTranslation } from '@/hooks/useTranslation';

export function ReadingProgress({
    targetRef,
}: {
    targetRef: RefObject<HTMLElement | null>;
}) {
    const { t } = useTranslation();
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        let frame = 0;

        const measure = (): void => {
            const element = targetRef.current;
            if (!element) return;
            const { top, height } = element.getBoundingClientRect();
            const scrollable = height - window.innerHeight;
            const ratio =
                scrollable <= 0
                    ? 1
                    : Math.min(1, Math.max(0, -top / scrollable));
            setProgress(Math.round(ratio * 100));
        };

        const handleScroll = (): void => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(measure);
        };

        measure();
        window.addEventListener('scroll', handleScroll, { passive: true });
        window.addEventListener('resize', handleScroll);
        return () => {
            cancelAnimationFrame(frame);
            window.removeEventListener('scroll', handleScroll);
            window.removeEventListener('resize', handleScroll);
        };
    }, [targetRef]);

    return (
        <div
            role="progressbar"
            aria-label={t('article.progress')}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-valuenow={progress}
            className="fixed inset-x-0 top-[var(--header-height)] z-30 h-0.5 bg-transparent"
        >
            <div
                className="h-full origin-left bg-signal transition-transform duration-150 ease-out rtl:origin-right"
                style={{ transform: `scaleX(${progress / 100})` }}
            />
        </div>
    );
}
