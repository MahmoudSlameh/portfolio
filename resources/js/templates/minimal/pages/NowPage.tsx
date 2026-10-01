import { formatDate, useTranslation } from '@/kit';
import type { NowPageProps } from '@/templates/types';
import { PageHeader, Section } from '../components/Section';

/** /now. `now.reading` holds the books picked on the panel's Now page. */
export function NowPage({ now }: NowPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <PageHeader title={t('now.title')} intro={t('now.intro')}>
                <p className="mt-4 text-sm text-ink-subtle">
                    {t('now.updated')}{' '}
                    <time dateTime={now.updatedAt}>
                        {formatDate(now.updatedAt)}
                    </time>
                    {now.location && ` · ${now.location}`}
                </p>
            </PageHeader>

            {[
                { id: 'focus', title: t('now.focus'), entries: now.focus },
                {
                    id: 'learning',
                    title: t('now.learning'),
                    entries: now.learning,
                },
            ].map(
                ({ id, title, entries }) =>
                    entries.length > 0 && (
                        <Section key={id} id={id} title={title}>
                            <dl className="grid gap-4">
                                {entries.map((entry) => (
                                    <div key={entry.id}>
                                        <dt className="font-medium text-ink">
                                            {entry.title}
                                        </dt>
                                        <dd className="text-ink-muted">
                                            {entry.body}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        </Section>
                    ),
            )}

            {now.reading.length > 0 && (
                <Section id="reading" title={t('now.reading')}>
                    <ul className="grid gap-2">
                        {now.reading.map((book) => (
                            <li key={book.id} className="text-ink">
                                {book.title}{' '}
                                <span className="text-ink-subtle">
                                    by {book.author}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Section>
            )}

            {now.availability && (
                <Section id="availability" title={t('now.availability')}>
                    <p className="text-ink-muted">{now.availability}</p>
                </Section>
            )}
        </>
    );
}
