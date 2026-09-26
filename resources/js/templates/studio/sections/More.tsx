import { Mail } from 'lucide-react';
import { Link, useTranslation, yearRange } from '@/kit';
import { ArticleCard, BookCover } from '../components/Cards';
import { ContactForm } from '../components/ContactForm';
import { Section } from '../components/Section';
import { useCopy } from '../useSpec';
import type { SectionComponentProps } from './types';

export function Education({
    data,
    variant,
}: SectionComponentProps<'education'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const { education, certifications } = data;

    if (education.length === 0 && certifications.length === 0) return null;

    const degrees = education.map((item) => (
        <li
            key={item.id}
            className={
                variant === 'cards'
                    ? 'st-card p-6'
                    : 'border-b border-line py-5'
            }
        >
            <p className="font-mono text-xs text-ink-subtle">
                {yearRange(item.start, item.end, t('career.present'))}
            </p>
            <h3 className="mt-1 text-lg font-bold text-ink">
                {[item.degree, item.field].filter(Boolean).join(', ')}
            </h3>
            <p className="text-ink-muted">{item.institution}</p>
            {item.grade && (
                <p className="mt-1 text-sm text-signal">{item.grade}</p>
            )}
            {item.description && (
                <p className="mt-2 text-ink-muted">{item.description}</p>
            )}
            {item.notes.length > 0 && (
                <ul className="mt-2 list-disc ps-5 text-sm text-ink-muted">
                    {item.notes.map((note) => (
                        <li key={note}>{note}</li>
                    ))}
                </ul>
            )}
        </li>
    ));

    return (
        <Section
            id="education"
            name="education"
            kicker={t('section.education')}
            title={copy('educationHeading', t('education.title'))}
        >
            <div className="grid gap-10 lg:grid-cols-[2fr_1fr]">
                {education.length > 0 && (
                    <ul
                        className={
                            variant === 'cards'
                                ? 'grid gap-4 md:grid-cols-2'
                                : 'border-t border-line'
                        }
                    >
                        {degrees}
                    </ul>
                )}
                {certifications.length > 0 && (
                    <div>
                        <h3 className="st-kicker mb-4">
                            {t('education.certifications')}
                        </h3>
                        <ul className="grid gap-3">
                            {certifications.map((certification) => (
                                <li key={certification.id}>
                                    <a
                                        href={certification.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="font-bold text-ink hover:text-signal"
                                    >
                                        {certification.name}
                                    </a>
                                    <p className="text-sm text-ink-muted">
                                        {certification.issuer} ·{' '}
                                        {certification.year}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </Section>
    );
}

export function Writing({
    data,
    variant,
    props,
}: SectionComponentProps<'writing'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const articles = data.articles.slice(0, props.limit ?? 3);

    if (articles.length === 0) return null;

    return (
        <Section
            id="writing"
            name="writing"
            kicker={t('section.writing')}
            title={copy('writingHeading', t('writing.title'))}
        >
            <div
                className={
                    variant === 'cards'
                        ? 'grid gap-4 md:grid-cols-3'
                        : 'border-t border-line'
                }
            >
                {articles.map((article) => (
                    <ArticleCard
                        key={article.id}
                        article={article}
                        layout={variant}
                    />
                ))}
            </div>
            <Link to="/writing" className="st-link mt-8 inline-block">
                {t('writing.viewAll')} →
            </Link>
        </Section>
    );
}

export function Books({
    data,
    variant,
    props,
}: SectionComponentProps<'books'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    // Books being read first, then the rest.
    const books = [...data.books]
        .sort(
            (a, b) =>
                Number(b.status === 'reading') - Number(a.status === 'reading'),
        )
        .slice(0, props.limit ?? 8);

    if (books.length === 0) return null;

    return (
        <Section
            id="books"
            name="books"
            kicker={t('section.books')}
            title={copy('booksHeading', t('books.title'))}
        >
            {variant === 'shelf' ? (
                <ul className="flex items-end gap-3 overflow-x-auto border-b-4 border-ink pb-1">
                    {books.map((book) => (
                        <li key={book.id} className="w-24 shrink-0 sm:w-28">
                            <Link
                                to="/books"
                                hash={`book-${book.slug}`}
                                aria-label={`${book.title} by ${book.author}`}
                                className="block transition-transform duration-[var(--st-duration)] hover:-translate-y-2"
                            >
                                <BookCover book={book} />
                            </Link>
                        </li>
                    ))}
                </ul>
            ) : (
                <ul className="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:grid-cols-6">
                    {books.map((book) => (
                        <li key={book.id}>
                            <BookCover book={book} />
                            <p className="mt-2 text-sm font-bold text-ink">
                                {book.title}
                            </p>
                            <p className="text-xs text-ink-muted">
                                {book.author}
                            </p>
                        </li>
                    ))}
                </ul>
            )}
            <Link to="/books" className="st-link mt-8 inline-block">
                {t('books.viewAll')} →
            </Link>
        </Section>
    );
}

export function Contact({ data, variant }: SectionComponentProps<'contact'>) {
    const { t } = useTranslation();
    const copy = useCopy();
    const { profile } = data;
    const heading = copy('contactHeading', t('contact.title'));
    const intro = copy('contactIntro', t('contact.intro'));
    const email = (
        <a
            href={`mailto:${profile.email}`}
            className="st-contact__email inline-flex items-center gap-2 text-lg font-bold text-ink hover:text-signal"
        >
            <Mail aria-hidden className="size-5" />
            {profile.email}
        </a>
    );

    if (variant === 'split') {
        return (
            <Section id="contact" name="contact" kicker={t('section.contact')}>
                <div className="grid gap-10 md:grid-cols-2">
                    <div className="grid content-start gap-5">
                        <h2 id="contact-title" className="st-section-title">
                            {heading}
                        </h2>
                        <p className="text-lg text-ink-muted">{intro}</p>
                        {email}
                        <p className="text-sm text-ink-muted">
                            {profile.availability.label} · {profile.location}
                        </p>
                    </div>
                    <ContactForm />
                </div>
            </Section>
        );
    }

    if (variant === 'minimal') {
        return (
            <Section
                id="contact"
                name="contact"
                kicker={t('section.contact')}
                title={heading}
            >
                <p className="max-w-2xl text-lg text-ink-muted">{intro}</p>
                <div className="mt-6">{email}</div>
                <details className="mt-8 max-w-2xl">
                    <summary className="st-link cursor-pointer">
                        {t('contact.submit')}
                    </summary>
                    <div className="mt-6">
                        <ContactForm />
                    </div>
                </details>
            </Section>
        );
    }

    return (
        <Section id="contact" name="contact" kicker={t('section.contact')}>
            <div className="st-card mx-auto grid max-w-3xl gap-6 p-6 sm:p-10">
                <h2 id="contact-title" className="st-section-title">
                    {heading}
                </h2>
                <p className="text-lg text-ink-muted">{intro}</p>
                {email}
                <ContactForm />
            </div>
        </Section>
    );
}
