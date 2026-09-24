import { Link } from '@/lib/router';
import { ArrowRight } from 'lucide-react';
import { ArticleRow } from '@/templates/changelog/components/content/ArticleRow';
import { Section } from '@/templates/changelog/components/ui/Section';
import { sectionIndex } from '@/config/navigation';
import { useTranslation } from '@/hooks/useTranslation';
import type { ArticleSummary } from '@/lib/content';

export function WritingSection({ articles }: { articles: ArticleSummary[] }) {
    const { t } = useTranslation();

    return (
        <Section
            id="writing"
            index={sectionIndex('writing')}
            label={t('section.writing')}
            version="docs/"
            title={t('writing.title')}
            intro={t('writing.intro')}
            action={
                <Link
                    to="/writing"
                    className="group inline-flex items-center gap-2 text-sm font-medium text-ink"
                >
                    <span className="link-draw-target">
                        {t('writing.viewAll')}
                    </span>
                    <ArrowRight
                        aria-hidden
                        className="size-4 transition-transform group-hover:translate-x-0.5 rtl:-scale-x-100"
                    />
                </Link>
            }
        >
            <div className="border-t border-line">
                {articles.map((article) => (
                    <ArticleRow key={article.id} article={article} />
                ))}
            </div>
        </Section>
    );
}
