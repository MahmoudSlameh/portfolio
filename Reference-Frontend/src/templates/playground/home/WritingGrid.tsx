import { Link } from '@tanstack/react-router';
import { ArrowUpRight } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';
import { usePlaygroundCopy } from '../copy';
import { ArticleCard } from '../components/ArticleCard';
import { popButtonClasses } from '../components/PopButton';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/SectionHeading';

const HOME_ARTICLE_COUNT = 3;

export function WritingGrid({ articles }: { articles: ArticleSummary[] }) {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();

  return (
    <section id="writing" aria-labelledby="writing-title" className="pg-shell scroll-mt-8 py-16">
      <SectionHeading
        id="writing"
        kicker={p('writing.kicker')}
        title={p('writing.title')}
        pop="green"
        aside={
          <Link to="/writing" className={popButtonClasses({ tone: 'plain' })}>
            {t('writing.viewAll')}
            <ArrowUpRight aria-hidden className="size-4 rtl:-scale-x-100" strokeWidth={2.5} />
          </Link>
        }
      />
      <ul className="grid gap-6 md:grid-cols-3">
        {articles.slice(0, HOME_ARTICLE_COUNT).map((article, index) => (
          <Reveal as="li" key={article.id} delay={index * 90}>
            <ArticleCard article={article} index={index} />
          </Reveal>
        ))}
      </ul>
    </section>
  );
}
