import { Link } from '@tanstack/react-router';
import { ArrowUpRight } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { cn, formatDate } from '@/lib/utils';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex } from '../lib/pops';

export function ArticleCard({ article, index }: { article: ArticleSummary; index: number }) {
  const { locale } = useTranslation();
  const p = usePlaygroundCopy();
  const pop = popForIndex(index);

  return (
    <Link
      to="/writing/$slug"
      params={{ slug: article.slug }}
      className="pg-card pg-press group flex h-full flex-col overflow-hidden"
    >
      <span className={cn('flex items-center justify-between gap-3 border-b-2 border-edge px-5 py-3 text-on-pop', popBg[pop])}>
        <time dateTime={article.publishedAt} className="font-mono text-xs font-bold">
          {formatDate(article.publishedAt, locale)}
        </time>
        <span className="rounded-full border-2 border-edge bg-raised px-2 font-mono text-[0.6875rem] font-bold text-ink">
          {p('article.stickerRead', { count: article.readingMinutes })}
        </span>
      </span>
      <span className="flex flex-1 flex-col gap-3 p-5">
        <span lang="en" className="font-display text-2xl leading-tight font-extrabold text-ink [font-stretch:110%]">
          {article.title}
        </span>
        <span lang="en" className="text-[0.9375rem] leading-relaxed text-ink-muted">
          {article.excerpt}
        </span>
        <span className="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2">
          <span className="flex flex-wrap gap-1.5">
            {article.tags.map((tag) => (
              <span key={tag} lang="en" className="pg-tag text-ink">
                #{tag}
              </span>
            ))}
          </span>
          <span
            aria-hidden
            className="inline-flex size-9 items-center justify-center rounded-full border-2 border-edge bg-pop-yellow text-on-pop transition-transform duration-300 group-hover:rotate-45"
          >
            <ArrowUpRight className="size-4 rtl:-scale-x-100" strokeWidth={2.5} />
          </span>
        </span>
      </span>
    </Link>
  );
}
