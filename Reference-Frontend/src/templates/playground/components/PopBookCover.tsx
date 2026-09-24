import type { CSSProperties } from 'react';
import { cn } from '@/lib/utils';
import type { Book } from '@/types/content';

function CoverShape({ book }: { book: Book }) {
  const style: CSSProperties = { backgroundColor: book.cover.accent };

  switch (book.cover.style) {
    case 'circle':
      return <span style={style} className="absolute -end-[18%] -bottom-[12%] aspect-square w-[78%] rounded-full" />;
    case 'band':
      return <span style={style} className="absolute inset-x-0 bottom-[22%] h-[16%] -skew-y-6" />;
    case 'block':
      return <span style={style} className="absolute inset-x-[10%] bottom-[10%] h-[34%] rounded-lg" />;
    case 'split':
      return <span style={style} className="absolute inset-y-0 end-0 w-[34%]" />;
    case 'rule':
      return <span style={{ borderColor: book.cover.accent }} className="absolute inset-[8%] rounded-md border-4" />;
  }
}

export function PopBookCover({ book, className }: { book: Book; className?: string }) {
  return (
    <div
      role="img"
      aria-label={`${book.title} — ${book.author}`}
      lang="en"
      dir="ltr"
      style={{ backgroundColor: book.cover.background, color: book.cover.ink }}
      className={cn(
        'relative isolate flex aspect-[2/3] w-full flex-col justify-between overflow-hidden rounded-xl border-2 border-edge p-[10%]',
        className,
      )}
    >
      <CoverShape book={book} />
      <span aria-hidden className="relative font-display text-[clamp(0.9rem,1vw+0.55rem,1.3rem)] leading-[1.02] font-extrabold [font-stretch:115%]">
        {book.title}
      </span>
      <span aria-hidden className="relative font-mono text-[0.5625rem] font-bold tracking-wide uppercase opacity-85">
        {book.author}
      </span>
    </div>
  );
}
