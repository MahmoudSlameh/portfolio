import type { CSSProperties } from 'react';
import { cn } from '@/lib/utils';
import type { Book } from '@/types/content';

interface BookCoverProps {
    book: Book;
    className?: string;
}

function CoverOrnament({ book }: { book: Book }) {
    const { accent, style } = book.cover;

    switch (style) {
        case 'band':
            return (
                <span
                    className="absolute inset-x-0 top-[58%] h-[14%]"
                    style={{ backgroundColor: accent }}
                />
            );
        case 'block':
            return (
                <span
                    className="absolute inset-x-0 top-0 h-[42%]"
                    style={{ backgroundColor: accent }}
                />
            );
        case 'circle':
            return (
                <span
                    className="absolute top-[46%] left-1/2 aspect-square w-[46%] -translate-x-1/2 rounded-full"
                    style={{ backgroundColor: accent }}
                />
            );
        case 'split':
            return (
                <span
                    className="absolute inset-y-0 left-0 w-[22%]"
                    style={{ backgroundColor: accent }}
                />
            );
        case 'rule':
            return (
                <>
                    <span
                        className="absolute inset-x-[9%] top-[8%] h-[3px] border-y"
                        style={{ borderColor: accent }}
                    />
                    <span
                        className="absolute inset-x-[9%] bottom-[8%] h-[3px] border-y"
                        style={{ borderColor: accent }}
                    />
                </>
            );
    }
}

export function BookCover({ book, className }: BookCoverProps) {
    const style: CSSProperties = {
        backgroundColor: book.cover.background,
        color: book.cover.ink,
    };
    const textOffset = book.cover.style === 'split' ? 'pl-[30%]' : '';
    const textOnAccent = book.cover.style === 'block';

    return (
        <div
            role="img"
            aria-label={`${book.title} — ${book.author}`}
            style={style}
            className={cn(
                'relative isolate flex aspect-[2/3] w-full flex-col justify-between overflow-hidden rounded-[2px] p-[9%] shadow-[inset_4px_0_6px_-4px_rgb(0_0_0/0.35),0_1px_2px_rgb(0_0_0/0.18)]',
                className,
            )}
            lang="en"
            dir="ltr"
        >
            <CoverOrnament book={book} />
            <span
                aria-hidden
                className="pointer-events-none absolute inset-y-0 left-[5%] w-px bg-black/10"
            />
            <span
                aria-hidden
                className={cn(
                    'font-display relative text-[clamp(0.95rem,1.1vw+0.5rem,1.35rem)] leading-[1.05]',
                    textOffset,
                )}
                style={textOnAccent ? { color: book.cover.ink } : undefined}
            >
                {book.title}
            </span>
            <span
                aria-hidden
                className={cn(
                    'relative font-mono text-[0.5625rem] tracking-wide uppercase opacity-80',
                    textOffset,
                )}
            >
                {book.author}
            </span>
        </div>
    );
}

interface BookSpineProps {
    book: Book;
    index: number;
    selected: boolean;
    onSelect: (bookId: string) => void;
    label: string;
}

const SPINE_HEIGHTS = [100, 92, 97, 88, 95, 90, 99, 86, 94];

export function BookSpine({
    book,
    index,
    selected,
    onSelect,
    label,
}: BookSpineProps) {
    const width = Math.round(
        Math.min(64, Math.max(34, (book.pages ?? 300) / 11)),
    );
    const height = SPINE_HEIGHTS[index % SPINE_HEIGHTS.length];

    return (
        <button
            type="button"
            aria-pressed={selected}
            aria-label={label}
            onClick={() => onSelect(book.id)}
            style={{
                width,
                height: `${height}%`,
                backgroundColor: book.cover.background,
                color: book.cover.ink,
            }}
            className={cn(
                'relative flex shrink-0 flex-col items-center justify-between overflow-hidden rounded-t-[2px] py-3 shadow-[inset_-3px_0_4px_-2px_rgb(0_0_0/0.3),inset_2px_0_0_rgb(255_255_255/0.08)] transition-transform duration-300 ease-out hover:-translate-y-2 focus-visible:-translate-y-2',
                selected && '-translate-y-3',
            )}
            dir="ltr"
        >
            <span
                aria-hidden
                className="h-1 w-3/5"
                style={{ backgroundColor: book.cover.accent }}
            />
            <span
                aria-hidden
                lang="en"
                className="font-display max-h-[75%] overflow-hidden text-[0.9375rem] leading-none whitespace-nowrap [writing-mode:vertical-rl]"
            >
                {book.title}
            </span>
            <span
                aria-hidden
                className="font-mono text-[0.5rem] opacity-70 [writing-mode:vertical-rl]"
            >
                {book.author.split(' ').at(-1)}
            </span>
        </button>
    );
}
