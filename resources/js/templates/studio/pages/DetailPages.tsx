import {
    ArchitectureDiagram,
    cn,
    formatDate,
    Link,
    ResponsiveImage,
    useTranslation,
} from '@/kit';
import type {
    ArticlePageProps,
    CaseStudyPageProps,
    NowPageProps,
    UsesPageProps,
} from '@/templates/types';
import type { ProjectDetail, TitledItem } from '@/types/content';
import { ArticleBody } from '../components/ArticleBody';
import { ArticleCard } from '../components/Cards';
import { PageHeader } from '../components/Section';
import { useStudioSpec } from '../useSpec';

function Block({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="st-case-block mt-12">
            <h2 className="st-kicker mb-4">{title}</h2>
            {children}
        </section>
    );
}

function Items({ items }: { items: TitledItem[] }) {
    return (
        <dl className="grid gap-5">
            {items.map((item) => (
                <div key={item.title}>
                    <dt className="font-bold text-ink">{item.title}</dt>
                    <dd className="mt-1 text-ink-muted">{item.description}</dd>
                </div>
            ))}
        </dl>
    );
}

function CaseContent({ project }: { project: ProjectDetail }) {
    return (
        <>
            {project.overview.length > 0 && (
                <Block title="Overview">
                    <div className="grid gap-4 text-lg text-ink-muted">
                        {project.overview.map((p) => (
                            <p key={p}>{p}</p>
                        ))}
                    </div>
                </Block>
            )}
            {project.problem.length > 0 && (
                <Block title="Problem">
                    <div className="grid gap-4 text-ink-muted">
                        {project.problem.map((p) => (
                            <p key={p}>{p}</p>
                        ))}
                    </div>
                </Block>
            )}
            {project.approach.length > 0 && (
                <Block title="Approach">
                    <Items items={project.approach} />
                </Block>
            )}
            {project.architecture && (
                <Block title="Architecture">
                    <div className="st-card overflow-x-auto p-4">
                        <ArchitectureDiagram
                            architecture={project.architecture}
                        />
                    </div>
                </Block>
            )}
            {project.features.length > 0 && (
                <Block title="Features">
                    <Items items={project.features} />
                </Block>
            )}
            {project.challenges.length > 0 && (
                <Block title="Challenges">
                    <Items items={project.challenges} />
                </Block>
            )}
            {project.metrics.length > 0 && (
                <Block title="Results">
                    <dl className="grid grid-cols-2 gap-4 md:grid-cols-3">
                        {project.metrics.map((metric) => (
                            <div key={metric.id} className="st-card p-5">
                                <dd className="text-3xl font-bold text-ink">
                                    {metric.value}
                                </dd>
                                <dt className="mt-1 text-sm text-ink-muted">
                                    {metric.label}
                                </dt>
                            </div>
                        ))}
                    </dl>
                </Block>
            )}
            {project.gallery.length > 0 && (
                <Block title="Gallery">
                    <div className="grid gap-6">
                        {project.gallery.map((image) => (
                            <figure key={image.src}>
                                <ResponsiveImage
                                    image={image}
                                    sizes="(min-width: 1024px) 60vw, 100vw"
                                    className="rounded-[var(--st-radius)]"
                                />
                                <figcaption className="mt-2 text-sm text-ink-subtle">
                                    {image.caption}
                                </figcaption>
                            </figure>
                        ))}
                    </div>
                </Block>
            )}
            {project.relatedArticles.length > 0 && (
                <Block title="Related writing">
                    <div className="border-t border-line">
                        {project.relatedArticles.map((article) => (
                            <ArticleCard
                                key={article.id}
                                article={article}
                                layout="list"
                            />
                        ))}
                    </div>
                </Block>
            )}
        </>
    );
}

function Meta({ project }: { project: ProjectDetail }) {
    const rows: [string, string | null][] = [
        ['Role', project.role],
        ['Team', project.team],
        ['Timeline', project.timeline],
        ['Company', project.company?.name ?? null],
    ];

    return (
        <dl className="st-case-meta grid gap-4 text-sm">
            {rows
                .filter(([, value]) => value)
                .map(([label, value]) => (
                    <div key={label}>
                        <dt className="text-ink-subtle">{label}</dt>
                        <dd className="text-ink">{value}</dd>
                    </div>
                ))}
            {project.stack.length > 0 && (
                <div>
                    <dt className="text-ink-subtle">Stack</dt>
                    <dd className="mt-1 flex flex-wrap gap-1.5">
                        {project.stack.map((tech) => (
                            <span key={tech} className="st-chip">
                                {tech}
                            </span>
                        ))}
                    </dd>
                </div>
            )}
            {project.links.length > 0 && (
                <div className="flex flex-wrap gap-3">
                    {project.links.map((link) => (
                        <a key={link.url} href={link.url} className="st-link">
                            {link.label}
                        </a>
                    ))}
                </div>
            )}
        </dl>
    );
}

function Adjacent({
    previous,
    next,
    base,
}: {
    previous: { slug: string; title: string } | null;
    next: { slug: string; title: string } | null;
    base: '/projects/$slug' | '/writing/$slug';
}) {
    return (
        <nav
            aria-label="More"
            className="st-adjacent st-container mt-16 flex justify-between gap-4 border-t border-line pt-8"
        >
            {previous ? (
                <Link
                    to={base}
                    params={{ slug: previous.slug }}
                    className="st-link"
                >
                    ← {previous.title}
                </Link>
            ) : (
                <span />
            )}
            {next && (
                <Link
                    to={base}
                    params={{ slug: next.slug }}
                    className="st-link"
                >
                    {next.title} →
                </Link>
            )}
        </nav>
    );
}

/** /projects/{slug} — variants: longform, sidebar. */
export function CaseStudyPage({ project }: CaseStudyPageProps) {
    const { variant } = useStudioSpec().pages.caseStudy;

    return (
        <article className="st-case-study">
            <PageHeader
                kicker={`${project.year} · ${project.category}`}
                title={project.title}
                intro={project.tagline}
            />
            {project.cover && (
                <div className="st-container mt-10">
                    <ResponsiveImage
                        image={project.cover}
                        sizes="100vw"
                        priority
                        className="rounded-[var(--st-radius)] border border-line"
                    />
                </div>
            )}
            {variant === 'sidebar' ? (
                <div className="st-container mt-4 grid gap-10 lg:grid-cols-[16rem_1fr]">
                    <aside className="lg:sticky lg:top-24 lg:self-start lg:pt-12">
                        <Meta project={project} />
                    </aside>
                    <div className="min-w-0">
                        <CaseContent project={project} />
                    </div>
                </div>
            ) : (
                <div className="st-container mx-auto mt-10 max-w-3xl">
                    <Meta project={project} />
                    <CaseContent project={project} />
                </div>
            )}
            <Adjacent
                previous={project.previous}
                next={project.next}
                base="/projects/$slug"
            />
        </article>
    );
}

/** /writing/{slug} — variants: centered, wide (with a table of contents). */
export function ArticlePage({ article }: ArticlePageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.article;
    const headings = article.body.filter((block) => block.type === 'heading');

    return (
        <article className="st-article">
            <div className="st-container mx-auto max-w-3xl pt-[calc(var(--st-section-gap)/2)]">
                <Link to="/writing" className="st-link text-sm">
                    ← {t('article.back')}
                </Link>
                <h1 className="mt-6 text-[clamp(2.25rem,5vw,3.5rem)] leading-[1.05] font-bold text-ink">
                    {article.title}
                </h1>
                <p className="mt-4 font-mono text-sm text-ink-subtle">
                    <time dateTime={article.publishedAt}>
                        {formatDate(article.publishedAt)}
                    </time>{' '}
                    · {t('writing.minRead', { count: article.readingMinutes })}
                </p>
            </div>
            {article.cover && (
                <div className="st-container mt-10">
                    <ResponsiveImage
                        image={article.cover}
                        sizes="100vw"
                        priority
                        className="rounded-[var(--st-radius)]"
                    />
                </div>
            )}
            <div
                className={cn(
                    'st-container mt-10',
                    variant === 'wide' && headings.length > 0
                        ? 'grid gap-10 lg:grid-cols-[14rem_1fr]'
                        : 'mx-auto max-w-3xl',
                )}
            >
                {variant === 'wide' && headings.length > 0 && (
                    <nav
                        aria-label={t('article.toc')}
                        className="st-toc lg:sticky lg:top-24 lg:self-start"
                    >
                        <p className="st-kicker mb-3">{t('article.toc')}</p>
                        <ul className="grid gap-2 text-sm">
                            {headings.map((heading) => (
                                <li key={heading.id}>
                                    <a
                                        href={`#${heading.id}`}
                                        className="text-ink-muted hover:text-ink"
                                    >
                                        {heading.text}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </nav>
                )}
                <div className="max-w-3xl min-w-0">
                    <ArticleBody blocks={article.body} />
                    {article.relatedProjects.length > 0 && (
                        <aside className="mt-12 border-t border-line pt-6">
                            <h2 className="st-kicker">
                                {t('article.related')}
                            </h2>
                            <ul className="mt-3 flex flex-wrap gap-4">
                                {article.relatedProjects.map((project) => (
                                    <li key={project.slug}>
                                        <Link
                                            to="/projects/$slug"
                                            params={{ slug: project.slug }}
                                            className="st-link"
                                        >
                                            {project.title}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </aside>
                    )}
                </div>
            </div>
            <Adjacent
                previous={article.previous}
                next={article.next}
                base="/writing/$slug"
            />
        </article>
    );
}

/** /uses — variants: columns, list. */
export function UsesPage({ groups }: UsesPageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.uses;

    return (
        <>
            <PageHeader title={t('uses.title')} intro={t('uses.intro')} />
            <div
                className={cn(
                    'st-container st-section',
                    variant === 'columns'
                        ? 'grid gap-6 md:grid-cols-3'
                        : 'grid gap-12',
                )}
            >
                {groups.map((group) => (
                    <section
                        key={group.id}
                        aria-labelledby={`uses-${group.id}`}
                        className={cn(variant === 'columns' && 'st-card p-6')}
                    >
                        <h2 id={`uses-${group.id}`} className="st-kicker mb-4">
                            {group.title}
                        </h2>
                        <dl className="grid gap-4">
                            {group.items.map((item) => (
                                <div key={item.id}>
                                    <dt className="font-bold text-ink">
                                        {item.url ? (
                                            <a
                                                href={item.url}
                                                className="hover:text-signal"
                                            >
                                                {item.name}
                                            </a>
                                        ) : (
                                            item.name
                                        )}
                                    </dt>
                                    <dd className="text-sm text-ink-muted">
                                        {item.description}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </section>
                ))}
            </div>
        </>
    );
}

/** /now — variants: notes, timeline. */
export function NowPage({ now }: NowPageProps) {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.now;
    const entries = [
        ...now.focus.map((entry) => ({ ...entry, group: t('now.focus') })),
        ...now.learning.map((entry) => ({
            ...entry,
            group: t('now.learning'),
        })),
    ];

    return (
        <>
            <PageHeader
                kicker={`${t('now.updated')} ${formatDate(now.updatedAt)}`}
                title={t('now.title')}
                intro={t('now.intro')}
            />
            <div className="st-container st-section">
                {variant === 'timeline' ? (
                    <ol className="grid gap-8 border-s border-line ps-8">
                        {entries.map((entry) => (
                            <li key={entry.id}>
                                <p className="st-kicker">{entry.group}</p>
                                <h2 className="mt-1 text-xl font-bold text-ink">
                                    {entry.title}
                                </h2>
                                <p className="mt-1 text-ink-muted">
                                    {entry.body}
                                </p>
                            </li>
                        ))}
                    </ol>
                ) : (
                    <ul className="grid gap-4 md:grid-cols-2">
                        {entries.map((entry) => (
                            <li key={entry.id} className="st-card p-6">
                                <p className="st-kicker">{entry.group}</p>
                                <h2 className="mt-2 text-lg font-bold text-ink">
                                    {entry.title}
                                </h2>
                                <p className="mt-1 text-ink-muted">
                                    {entry.body}
                                </p>
                            </li>
                        ))}
                    </ul>
                )}
                {now.reading.length > 0 && (
                    <p className="mt-10 text-ink-muted">
                        <span className="st-kicker me-2">
                            {t('now.reading')}
                        </span>
                        {now.reading
                            .map((book) => `${book.title} (${book.author})`)
                            .join(' · ')}
                    </p>
                )}
                {now.availability && (
                    <p className="mt-4 text-ink-muted">
                        <span className="st-kicker me-2">
                            {t('now.availability')}
                        </span>
                        {now.availability}
                    </p>
                )}
            </div>
        </>
    );
}

/** 404 — variants: big-number, minimal. */
export function NotFoundPage() {
    const { t } = useTranslation();
    const { variant } = useStudioSpec().pages.notFound;

    return (
        <div className="st-not-found st-container st-section grid justify-items-center gap-6 text-center">
            {variant === 'big-number' ? (
                <p
                    aria-hidden
                    className="text-[clamp(6rem,25vw,16rem)] leading-none font-bold text-signal"
                >
                    404
                </p>
            ) : (
                <p className="st-kicker">404</p>
            )}
            <h1 className="text-3xl font-bold text-ink">Page not found</h1>
            <p className="text-ink-muted">
                This page does not exist or has moved.
            </p>
            <div className="flex flex-wrap justify-center gap-3">
                <Link to="/" className="st-button">
                    {t('nav.home')}
                </Link>
                <Link to="/projects" className="st-button st-button--ghost">
                    {t('notFound.projects')}
                </Link>
            </div>
        </div>
    );
}
