import { Link } from '@tanstack/react-router';
import { ArrowUpRight } from 'lucide-react';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { formatIsoDate } from '@/lib/utils';

interface ArticleRowProps {
  article: ArticleSummary;
  headingLevel?: 'h2' | 'h3';
}

export function ArticleRow({ article, headingLevel: Heading = 'h3' }: ArticleRowProps) {
  const { t } = useTranslation();

  return (
    <article className="group relative border-b border-line py-7 transition-colors duration-300 hover:bg-raised/60 md:py-8">
      <div className="editorial-grid items-baseline gap-y-3">
        <p className="col-span-2 md:col-span-2">
          <time dateTime={article.publishedAt} className="ltr-isolate font-mono text-xs text-ink-subtle">
            {formatIsoDate(article.publishedAt)}
          </time>
        </p>
        <p className="col-span-2 text-end font-mono text-xs text-ink-subtle md:order-last md:col-span-2">
          {t('writing.minRead', { count: article.readingMinutes })}
        </p>
        <div lang="en" className="col-span-4 md:col-span-6">
          <Heading className="font-display text-[1.75rem] leading-tight text-ink md:text-[2rem]">
            <Link
              to="/writing/$slug"
              params={{ slug: article.slug }}
              className="link-draw-target after:absolute after:inset-0 after:content-['']"
            >
              {article.title}
            </Link>
          </Heading>
          <p className="mt-2 max-w-xl text-[0.9375rem] leading-relaxed text-ink-muted">{article.excerpt}</p>
        </div>
        <ul className="col-span-4 flex flex-wrap items-center gap-1.5 md:col-span-2">
          {article.tags.map((tag) => (
            <li key={tag}>
              <Tag>#{tag}</Tag>
            </li>
          ))}
          <li aria-hidden className="ms-auto hidden md:block">
            <ArrowUpRight className="size-4 text-ink-subtle opacity-0 transition-opacity duration-300 group-hover:opacity-100 rtl:-scale-x-100" />
          </li>
        </ul>
      </div>
    </article>
  );
}
