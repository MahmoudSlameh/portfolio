import { cn } from '@/lib/utils';

/** Green hexagon badge with a </> glyph that sits on the hero portrait. */
export function CodeHexagon({ className }: { className?: string }) {
    return (
        <svg
            aria-hidden
            width="81"
            height="73"
            viewBox="0 0 81 73"
            fill="none"
            className={cn('block', className)}
        >
            <path
                d="M55.54 0H25.46a10.4 10.4 0 0 0-9.02 5.21L1.4 31.28a10.4 10.4 0 0 0 0 10.44l15.04 26.07A10.4 10.4 0 0 0 25.46 73h30.08a10.4 10.4 0 0 0 9.02-5.21L79.6 41.72a10.4 10.4 0 0 0 0-10.44L64.57 5.21A10.4 10.4 0 0 0 55.54 0Z"
                fill="#62A92B"
            />
            <path
                d="m31 28-8.5 8.5L31 45M50 28l8.5 8.5L50 45M44 24.5l-7 24"
                stroke="#fff"
                strokeWidth="3.2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

/** The "</>" logo mark used in the navbar and footer. */
export function CodeMark({ className }: { className?: string }) {
    return (
        <svg
            aria-hidden
            viewBox="0 0 36 28"
            fill="none"
            className={cn('h-7 w-9 shrink-0', className)}
        >
            <path
                d="M10 5 1.5 14 10 23M26 5l8.5 9L26 23M21 1.5 15 26.5"
                stroke="#a8ff53"
                strokeWidth="3"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
