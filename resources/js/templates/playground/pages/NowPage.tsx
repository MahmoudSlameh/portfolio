import { Link } from '@/lib/router';
import { MapPin } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn, formatDate } from '@/lib/utils';
import type { NowPageProps } from '@/templates/types';
import { usePlaygroundCopy } from '../copy';
import { popBg, popForIndex, tiltForIndex } from '../lib/pops';
import { PageHero } from '../components/PageHero';
import { PopBookCover } from '../components/PopBookCover';
import { popButtonClasses } from '../components/PopButton';
import { Reveal } from '../components/Reveal';
import { Sticker } from '../components/Sticker';

export function NowPage({ now }: NowPageProps) {
    const { t } = useTranslation();
    const p = usePlaygroundCopy();

    return (
        <>
            <PageHero
                kicker={p('now.kicker')}
                title={t('now.title')}
                intro={t('now.intro')}
                pop="red"
                badge={
                    <>
                        <Sticker pop="yellow" tilt={4}>
                            {t('now.updated')} ·{' '}
                            <time dateTime={now.updatedAt}>
                                {formatDate(now.updatedAt)}
                            </time>
                        </Sticker>
                        <Sticker pop="green" tilt={-3}>
                            <MapPin
                                aria-hidden
                                className="size-3.5"
                                strokeWidth={2.5}
                            />
                            {now.location}
                        </Sticker>
                    </>
                }
            />

            <div className="pg-shell flex flex-col gap-16">
                <section aria-labelledby="now-focus">
                    <h2
                        id="now-focus"
                        className="pg-display text-ink mb-8 text-5xl"
                    >
                        {t('now.focus')}
                    </h2>
                    <ol className="grid gap-6 md:grid-cols-2">
                        {now.focus.map((entry, index) => (
                            <Reveal as="li" key={entry.id} delay={index * 80}>
                                <article className="pg-card pg-press flex h-full gap-5 p-6">
                                    <span
                                        aria-hidden
                                        className={cn(
                                            'pg-display ltr-isolate border-edge text-on-pop inline-flex size-16 shrink-0 items-center justify-center rounded-2xl border-2 text-2xl',
                                            popBg[popForIndex(index)],
                                        )}
                                    >
                                        {String(index + 1).padStart(2, '0')}
                                    </span>
                                    <div>
                                        <h3 className="text-ink text-2xl leading-tight font-bold">
                                            {entry.title}
                                        </h3>
                                        <p className="text-ink-muted mt-2 text-base leading-relaxed">
                                            {entry.body}
                                        </p>
                                    </div>
                                </article>
                            </Reveal>
                        ))}
                    </ol>
                </section>

                <section aria-labelledby="now-learning">
                    <h2
                        id="now-learning"
                        className="pg-display text-ink mb-8 text-5xl"
                    >
                        {t('now.learning')}
                    </h2>
                    <ul className="grid gap-8 md:grid-cols-3">
                        {now.learning.map((entry, index) => (
                            <Reveal as="li" key={entry.id} delay={index * 80}>
                                <article
                                    style={{
                                        rotate: `${tiltForIndex(index) * 0.7}deg`,
                                    }}
                                    className={cn(
                                        'pg-card pg-press text-on-pop relative h-full p-6 pt-9',
                                        popBg[popForIndex(index + 3)],
                                    )}
                                >
                                    <span
                                        aria-hidden
                                        className="border-edge bg-raised/80 absolute start-1/2 -top-3 h-6 w-20 -translate-x-1/2 rotate-[-4deg] rounded-sm border-2"
                                    />
                                    <h3 className="text-xl font-bold">
                                        {entry.title}
                                    </h3>
                                    <p className="mt-2 text-[0.9375rem] leading-relaxed font-medium">
                                        {entry.body}
                                    </p>
                                </article>
                            </Reveal>
                        ))}
                    </ul>
                </section>

                <div className="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                    <section
                        aria-labelledby="now-reading"
                        className="pg-card p-6"
                    >
                        <h2 id="now-reading" className="pg-label text-ink mb-6">
                            {t('now.reading')}
                        </h2>
                        <ul className="grid grid-cols-2 gap-5 sm:grid-cols-3">
                            {now.reading.map((book, index) => (
                                <li key={book.id}>
                                    <Link
                                        to="/books"
                                        hash={`book-${book.slug}`}
                                        className="pg-press block rounded-xl"
                                        style={{
                                            rotate: `${tiltForIndex(index)}deg`,
                                        }}
                                    >
                                        <PopBookCover book={book} />
                                    </Link>
                                    <p
                                        lang="en"
                                        className="text-ink mt-3 text-sm font-bold"
                                    >
                                        {book.title}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>
                    <section
                        aria-labelledby="now-availability"
                        className="pg-card bg-pop-green text-on-pop flex flex-col items-start justify-between gap-6 p-6 md:p-8"
                    >
                        <div>
                            <h2 id="now-availability" className="pg-label mb-3">
                                {t('now.availability')}
                            </h2>
                            <p className="text-2xl leading-snug font-bold">
                                {now.availability}
                            </p>
                        </div>
                        <Link
                            to="/"
                            hash="contact"
                            className={popButtonClasses({ tone: 'plain' })}
                        >
                            {t('hero.getInTouch')}
                        </Link>
                    </section>
                </div>
            </div>
        </>
    );
}
