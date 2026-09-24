import { Link } from '@/lib/router';
import { ArrowUpRight, MapPin } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { formatDate } from '@/lib/utils';
import type { NowPageProps } from '@/templates/types';
import { useTerminalCopy } from '../copy';
import { BookCover } from '../components/BookCover';
import { Kicker } from '../components/Kicker';
import { PageHeader } from '../components/PageHeader';
import { Timeline } from '../components/Timeline';

export function NowPage({ now }: NowPageProps) {
    const { t } = useTranslation();
    const c = useTerminalCopy();

    return (
        <>
            <PageHeader
                kicker={c('now.kicker')}
                title={t('now.title')}
                intro={t('now.intro')}
                aside={
                    <>
                        <span className="border-tm-border text-tm-300 border px-3 py-1">
                            {t('now.updated')} ·{' '}
                            <time dateTime={now.updatedAt}>
                                {formatDate(now.updatedAt)}
                            </time>
                        </span>
                        <span className="border-tm-border text-tm-300 inline-flex items-center gap-1.5 border px-3 py-1">
                            <MapPin
                                aria-hidden
                                className="text-tm-primary size-4"
                            />
                            {now.location}
                        </span>
                    </>
                }
            />

            <div className="tm-container flex flex-col gap-8 pt-8">
                <section
                    aria-labelledby="now-focus"
                    className="tm-box p-5 md:p-10"
                >
                    <Kicker>{t('now.focus')}</Kicker>
                    <h2
                        id="now-focus"
                        className="mt-1 mb-10 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {t('now.focus')}
                    </h2>
                    <ol className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {now.focus.map((entry, index) => (
                            <li
                                key={entry.id}
                                className="tm-box tm-hover-up rounded-md px-8 pt-12 pb-8"
                            >
                                <span className="ltr-isolate text-tm-secondary text-sm">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                <h3 className="my-3 text-[19px] font-medium">
                                    {entry.title}
                                </h3>
                                <p className="text-tm-300 mb-0 text-sm leading-relaxed">
                                    {entry.body}
                                </p>
                            </li>
                        ))}
                    </ol>
                </section>

                <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <section
                        aria-labelledby="now-learning"
                        className="tm-box p-5 md:p-10"
                    >
                        <h2
                            id="now-learning"
                            className="mb-8 text-[clamp(1.75rem,3vw,2.25rem)] font-medium"
                        >
                            {t('now.learning')}
                        </h2>
                        <Timeline
                            items={now.learning.map((entry, index) => ({
                                id: entry.id,
                                date: String(index + 1).padStart(2, '0'),
                                title: entry.title,
                                body: (
                                    <span className="text-tm-300">
                                        {entry.body}
                                    </span>
                                ),
                            }))}
                        />
                    </section>

                    <section
                        aria-labelledby="now-reading"
                        className="tm-box p-5 md:p-10"
                    >
                        <h2
                            id="now-reading"
                            className="mb-8 text-[clamp(1.75rem,3vw,2.25rem)] font-medium"
                        >
                            {t('now.reading')}
                        </h2>
                        <ul className="grid grid-cols-2 gap-5 sm:grid-cols-3">
                            {now.reading.map((book) => (
                                <li key={book.id}>
                                    <Link
                                        to="/books"
                                        hash={`book-${book.slug}`}
                                        className="tm-hover-up block rounded-md"
                                    >
                                        <BookCover book={book} />
                                    </Link>
                                    <p
                                        lang="en"
                                        className="text-ink mt-3 mb-0 text-sm"
                                    >
                                        {book.title}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>

                <section
                    aria-labelledby="now-availability"
                    className="tm-box flex flex-col items-start justify-between gap-6 p-5 md:flex-row md:items-center md:p-10"
                >
                    <div>
                        <h2
                            id="now-availability"
                            className="text-tm-400 mb-2 text-sm font-normal"
                        >
                            {t('now.availability')}
                        </h2>
                        <p className="text-ink mb-0 text-xl">
                            {now.availability}
                        </p>
                    </div>
                    <Link
                        to="/"
                        hash="contact"
                        className="text-tm-primary inline-flex items-center gap-2 font-medium hover:underline"
                    >
                        {t('hero.getInTouch')}
                        <ArrowUpRight
                            aria-hidden
                            className="size-5 rtl:-scale-x-100"
                        />
                    </Link>
                </section>
            </div>
        </>
    );
}
