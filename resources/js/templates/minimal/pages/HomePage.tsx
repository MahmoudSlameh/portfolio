import {
    CompanyLogo,
    formatMonth,
    Link,
    ResponsiveImage,
    useTranslation,
    yearRange,
} from '@/kit';
import type { HomePageProps } from '@/templates/types';
import { ContactForm } from '../components/ContactForm';
import { ArticleList, ProjectList } from '../components/Lists';
import { Section } from '../components/Section';

/**
 * Home page. Props come from HomeController (see HomePageProps in templates/types.ts):
 * profile, socials, skillGroups, career, projects (featured only), companies (featured only),
 * testimonials, education, certifications, articles, books.
 *
 * Rule: every list can be empty on a fresh install. Render nothing, not an empty section.
 */
export function HomePage({
    profile,
    skillGroups,
    career,
    projects,
    companies,
    testimonials,
    education,
    certifications,
    articles,
    books,
}: HomePageProps) {
    const { t } = useTranslation();
    const reading = books.filter((book) => book.status === 'reading');

    return (
        <>
            <section className="mn-container flex flex-col-reverse gap-8 pt-16 sm:flex-row sm:items-center">
                <div className="flex-1">
                    <p className="text-sm text-ink-subtle">{profile.role}</p>
                    <h1 className="mt-1 text-4xl font-semibold tracking-tight text-ink">
                        {profile.name}
                    </h1>
                    <p className="mt-4 text-lg text-ink-muted">
                        {profile.headline}
                    </p>
                    <p className="mt-4 inline-flex items-center gap-2 text-sm text-ink-muted">
                        <span
                            aria-hidden
                            className="size-2 rounded-full bg-signal"
                        />
                        {profile.availability.label}
                    </p>
                </div>
                {/* ImageData | null: ResponsiveImage renders a placeholder when nothing was uploaded. */}
                {profile.portrait && (
                    // ResponsiveImage fills its box (w-full), so size the wrapper, not the image.
                    <div className="size-32 shrink-0 overflow-hidden rounded-full sm:size-40">
                        <ResponsiveImage
                            image={profile.portrait}
                            sizes="160px"
                            priority
                            fallbackAspect="1 / 1"
                            className="size-full"
                        />
                    </div>
                )}
            </section>

            {profile.story.length > 0 && (
                <Section id="about" title={t('section.about')}>
                    <div className="grid gap-4 text-ink-muted">
                        {profile.story.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                </Section>
            )}

            {skillGroups.length > 0 && (
                <Section id="stack" title={t('section.stack')}>
                    <dl className="grid gap-4">
                        {skillGroups.map((group) => (
                            <div key={group.category.id}>
                                <dt className="font-medium text-ink">
                                    {group.category.label}
                                </dt>
                                <dd className="mt-2 flex flex-wrap gap-2">
                                    {group.skills.map((skill) => (
                                        <span
                                            key={skill.id}
                                            className="mn-chip"
                                        >
                                            {skill.name}
                                        </span>
                                    ))}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Section>
            )}

            {career.length > 0 && (
                <Section id="career" title={t('section.career')}>
                    <ol className="grid gap-8">
                        {career.map((entry) => (
                            <li key={entry.id}>
                                <p className="text-sm text-ink-subtle">
                                    {formatMonth(entry.start)} –{' '}
                                    {entry.end
                                        ? formatMonth(entry.end)
                                        : 'Present'}
                                </p>
                                <h3 className="font-medium text-ink">
                                    {entry.role} · {entry.organization}
                                </h3>
                                <p className="mt-1 text-ink-muted">
                                    {entry.summary}
                                </p>
                            </li>
                        ))}
                    </ol>
                </Section>
            )}

            {projects.length > 0 && (
                <Section id="work" title={t('section.work')}>
                    <ProjectList projects={projects} />
                    <Link to="/projects" className="mn-link mt-4 inline-block">
                        {t('nav.work')} →
                    </Link>
                </Section>
            )}

            {companies.length > 0 && (
                <Section id="clients" title={t('section.clients')}>
                    <ul className="flex flex-wrap items-center gap-x-8 gap-y-4">
                        {companies.map((company) => (
                            <li key={company.id}>
                                {/* Uploaded logo (with dark variant) or a text fallback. */}
                                <CompanyLogo
                                    company={company}
                                    fallback={
                                        <span className="font-medium text-ink-muted">
                                            {company.name}
                                        </span>
                                    }
                                />
                            </li>
                        ))}
                    </ul>
                    {testimonials.map((testimonial) => (
                        <blockquote
                            key={testimonial.id}
                            className="mt-8 border-s-2 border-line ps-4"
                        >
                            <p className="text-ink">“{testimonial.quote}”</p>
                            <footer className="mt-2 text-sm text-ink-subtle">
                                {testimonial.author}, {testimonial.role}
                            </footer>
                        </blockquote>
                    ))}
                </Section>
            )}

            {(education.length > 0 || certifications.length > 0) && (
                <Section id="education" title={t('section.education')}>
                    <ul className="grid gap-4">
                        {education.map((item) => (
                            <li key={item.id}>
                                <p className="font-medium text-ink">
                                    {item.degree}
                                    {item.field && `, ${item.field}`}
                                </p>
                                <p className="text-sm text-ink-muted">
                                    {item.institution} ·{' '}
                                    {yearRange(item.start, item.end)}
                                </p>
                            </li>
                        ))}
                        {certifications.map((certification) => (
                            <li key={certification.id}>
                                <a
                                    href={certification.url}
                                    className="font-medium text-ink hover:underline"
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
                </Section>
            )}

            {articles.length > 0 && (
                <Section id="writing" title={t('section.writing')}>
                    <ArticleList articles={articles.slice(0, 3)} />
                </Section>
            )}

            {reading.length > 0 && (
                <Section id="books" title={t('section.books')}>
                    <ul className="grid gap-2">
                        {reading.map((book) => (
                            <li key={book.id}>
                                <span className="text-ink">{book.title}</span>{' '}
                                <span className="text-ink-subtle">
                                    by {book.author}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Section>
            )}

            <Section id="contact" title={t('section.contact')}>
                <p className="mb-6 text-ink-muted">
                    {t('contact.intro')}{' '}
                    <a href={`mailto:${profile.email}`} className="mn-link">
                        {profile.email}
                    </a>
                </p>
                <ContactForm />
            </Section>
        </>
    );
}
