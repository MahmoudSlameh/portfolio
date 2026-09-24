import type { CSSProperties } from 'react';
import { cn } from '@/lib/utils';
import type { Book } from '@/types/content';

function CoverShape({ book }: { book: Book }) {
  const style: CSSProperties = { backgroundColor: book.cover.accent };

  switch (book.cover.style) {
    case 'circle':
      return <span style={style} className="absolute -end-[18%] -bottom-[12%] aspect-square w-[78%] rounded-full" />;
    case 'band':
      return <span style={style} className="absolute inset-x-0 bottom-[22%] h-[16%]" />;
    case 'block':
      return <span style={style} className="absolute inset-x-[10%] bottom-[10%] h-[34%] rounded" />;
    case 'split':
      return <span style={style} className="absolute inset-y-0 end-0 w-[34%]" />;
    case 'rule':
      return <span style={{ borderColor: book.cover.accent }} className="absolute inset-[8%] rounded-sm border-2" />;
  }
}

export function BookCover({ book, className }: { book: Book; className?: string }) {
  return (
    <div
      role="img"
      aria-label={`${book.title} — ${book.author}`}
      lang="en"
      dir="ltr"
      style={{ backgroundColor: book.cover.background, color: book.cover.ink }}
      className={cn('relative isolate flex aspect-[2/3] w-full flex-col justify-between overflow-hidden rounded-md p-[10%]', className)}
    >
      <CoverShape book={book} />
      <span aria-hidden className="relative text-[clamp(0.85rem,0.9vw+0.5rem,1.15rem)] leading-tight font-medium">
        {book.title}
      </span>
      <span aria-hidden className="relative text-[0.625rem] tracking-wide uppercase opacity-85">
        {book.author}
      </span>
    </div>
  );
}
