import { Link } from '@/lib/router';
import { BookCover } from '@/templates/changelog/components/content/BookCover';
import { buttonClasses } from '@/templates/changelog/components/ui/Button';
import { PageHeader } from '@/templates/changelog/components/ui/PageHeader';
import { StatusBadge } from '@/templates/changelog/components/ui/StatusBadge';
import { useTranslation } from '@/hooks/useTranslation';
import { formatDate } from '@/lib/utils';
import type { DictionaryKey } from '@/i18n/dictionary';
import type { NowPageProps } from '@/templates/types';
import type { NowEntry } from '@/types/content';

function EntryList({
    titleKey,
    entries,
    id,
}: {
    titleKey: DictionaryKey;
    entries: NowEntry[];
    id: string;
}) {
    const { t } = useTranslation();

    return (
        <section
            aria-labelledby={id}
            className="editorial-grid border-line gap-y-6 border-t py-12"
        >
            <h2
                id={id}
                className="eyebrow text-ink-subtle col-span-4 md:col-span-3"
            >
                {t(titleKey)}
            </h2>
            <ol className="col-span-4 flex flex-col gap-8 md:col-span-9 lg:col-span-7">
                {entries.map((entry, index) => (
                    <li
                        key={entry.id}
                        className="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-2"
                    >
                        <span className="ltr-isolate text-signal-ink pt-2 font-mono text-xs">
                            {String(index + 1).padStart(2, '0')}
                        </span>
                        <div>
                            <h3 className="font-display text-ink text-3xl leading-tight">
                                {entry.title}
                            </h3>
                            <p className="text-ink-muted mt-2 text-[1.0625rem] leading-relaxed">
                                {entry.body}
                            </p>
                        </div>
                    </li>
                ))}
            </ol>
        </section>
    );
}

export function NowPage({ now }: NowPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <PageHeader
                index="11"
                label={t('nav.now')}
                version="HEAD"
                title={t('now.title')}
                intro={t('now.intro')}
                aside={
                    <dl className="border-line flex flex-col gap-4 border-t pt-4 lg:border-t-0 lg:pt-0">
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('now.updated')}
                            </dt>
                            <dd className="text-ink mt-1 text-sm">
                                <time dateTime={now.updatedAt}>
                                    {formatDate(now.updatedAt)}
                                </time>
                            </dd>
                        </div>
                        <div>
                            <dt className="eyebrow text-ink-subtle">
                                {t('now.location')}
                            </dt>
                            <dd className="text-ink mt-1 text-sm">
                                {now.location}
                            </dd>
                        </div>
                    </dl>
                }
            />
            <div className="shell pb-16">
                <EntryList
                    id="now-focus"
                    titleKey="now.focus"
                    entries={now.focus}
                />
                <EntryList
                    id="now-learning"
                    titleKey="now.learning"
                    entries={now.learning}
                />

                <section
                    aria-labelledby="now-reading"
                    className="editorial-grid border-line gap-y-6 border-t py-12"
                >
                    <h2
                        id="now-reading"
                        className="eyebrow text-ink-subtle col-span-4 md:col-span-3"
                    >
                        {t('now.reading')}
                    </h2>
                    <ul className="col-span-4 grid gap-6 sm:grid-cols-2 md:col-span-9 lg:col-span-7">
                        {now.reading.map((book) => (
                            <li
                                key={book.id}
                                className="grid grid-cols-[5.5rem_minmax(0,1fr)] gap-5"
                            >
                                <BookCover book={book} />
                                <div lang="en" className="min-w-0">
                                    <Link
                                        to="/books"
                                        hash={`book-${book.slug}`}
                                        className="link-draw font-display text-ink text-2xl leading-tight"
                                    >
                                        {book.title}
                                    </Link>
                                    <p className="text-ink-muted mt-1 text-sm">
                                        {book.author}
                                    </p>
                                    <p className="text-ink-subtle mt-3 line-clamp-3 text-sm leading-relaxed">
                                        {book.note}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>

                <section
                    aria-labelledby="now-availability"
                    className="editorial-grid border-line gap-y-6 border-t py-12"
                >
                    <h2
                        id="now-availability"
                        className="eyebrow text-ink-subtle col-span-4 md:col-span-3"
                    >
                        {t('now.availability')}
                    </h2>
                    <div className="col-span-4 flex flex-col items-start gap-6 md:col-span-9 lg:col-span-7">
                        <StatusBadge
                            label={t('status.available')}
                            tone="signal"
                        />
                        <p className="font-display text-ink text-[1.75rem] leading-snug md:text-[2rem]">
                            {now.availability}
                        </p>
                        <Link
                            to="/"
                            hash="contact"
                            className={buttonClasses('primary', 'md')}
                        >
                            {t('nav.contact')}
                        </Link>
                    </div>
                </section>
            </div>
        </>
    );
}
