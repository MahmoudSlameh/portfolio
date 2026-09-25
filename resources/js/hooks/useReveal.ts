import { useEffect, useRef, type RefObject } from 'react';

export function useReveal<T extends HTMLElement>(): RefObject<T | null> {
    const ref = useRef<T>(null);

    useEffect(() => {
        const element = ref.current;
        if (!element) return;

        const reducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;
        if (reducedMotion || !('IntersectionObserver' in window)) {
            element.classList.add('is-visible');
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) return;
                element.classList.add('is-visible');
                observer.disconnect();
            },
            { rootMargin: '0px 0px -10% 0px', threshold: 0.05 },
        );

        observer.observe(element);
        return () => observer.disconnect();
    }, []);

    return ref;
}
