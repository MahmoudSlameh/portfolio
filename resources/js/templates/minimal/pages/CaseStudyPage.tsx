import {
    ArchitectureDiagram,
    Link,
    ResponsiveImage,
    useTranslation,
} from '@/kit';
import type { CaseStudyPageProps } from '@/templates/types';
import { ArticleList } from '../components/Lists';
import { PageHeader, Section } from '../components/Section';

/**
 * /projects/{slug}. `project` is a ProjectDetail: the project plus company, experience,
 * previous/next project and related articles. Most case-study fields are optional in the panel,
 * so each block checks its own data.
 */
export function CaseStudyPage({ project }: CaseStudyPageProps) {
    const { t } = useTranslation();

    return (
        <article>
            <PageHeader title={project.title} intro={project.tagline}>
                <p className="mt-4 text-sm text-ink-subtle">
                    {[project.role, project.timeline, project.company?.name]
                        .filter(Boolean)
                        .join(' · ')}
                </p>
            </PageHeader>

            {project.cover && (
                <div className="mn-container mt-8">
                    <ResponsiveImage
                        image={project.cover}
                        sizes="(min-width: 768px) 672px, 100vw"
                        priority
                        className="rounded-md"
                    />
                </div>
            )}

            {project.overview.length > 0 && (
                <Section id="overview" title="Overview">
                    {project.overview.map((paragraph) => (
                        <p key={paragraph} className="mb-4 text-ink-muted">
                            {paragraph}
                        </p>
                    ))}
                </Section>
            )}

            {project.problem.length > 0 && (
                <Section id="problem" title="Problem">
                    {project.problem.map((paragraph) => (
                        <p key={paragraph} className="mb-4 text-ink-muted">
                            {paragraph}
                        </p>
                    ))}
                </Section>
            )}

            {[
                { id: 'approach', title: 'Approach', items: project.approach },
                { id: 'features', title: 'Features', items: project.features },
                {
                    id: 'challenges',
                    title: 'Challenges',
                    items: project.challenges,
                },
            ].map(
                ({ id, title, items }) =>
                    items.length > 0 && (
                        <Section key={id} id={id} title={title}>
                            <dl className="grid gap-4">
                                {items.map((item) => (
                                    <div key={item.title}>
                                        <dt className="font-medium text-ink">
                                            {item.title}
                                        </dt>
                                        <dd className="text-ink-muted">
                                            {item.description}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        </Section>
                    ),
            )}

            {project.architecture && (
                <Section id="architecture" title="Architecture">
                    <div className="overflow-x-auto">
                        <ArchitectureDiagram
                            architecture={project.architecture}
                        />
                    </div>
                </Section>
            )}

            {project.metrics.length > 0 && (
                <Section id="metrics" title="Results">
                    <dl className="grid grid-cols-2 gap-4">
                        {project.metrics.map((metric) => (
                            <div key={metric.id}>
                                <dt className="text-sm text-ink-subtle">
                                    {metric.label}
                                </dt>
                                <dd className="text-2xl font-semibold text-ink">
                                    {metric.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Section>
            )}

            {project.gallery.length > 0 && (
                <Section id="gallery" title="Gallery">
                    <div className="grid gap-6">
                        {project.gallery.map((image) => (
                            <figure key={image.src}>
                                <ResponsiveImage
                                    image={image}
                                    sizes="(min-width: 768px) 672px, 100vw"
                                    className="rounded-md"
                                />
                                <figcaption className="mt-2 text-sm text-ink-subtle">
                                    {image.caption}
                                </figcaption>
                            </figure>
                        ))}
                    </div>
                </Section>
            )}

            {project.links.length > 0 && (
                <Section id="links" title="Links">
                    <ul className="flex flex-wrap gap-4">
                        {project.links.map((link) => (
                            <li key={link.url}>
                                <a href={link.url} className="mn-link">
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </Section>
            )}

            {project.relatedArticles.length > 0 && (
                <Section id="related" title={t('section.writing')}>
                    <ArticleList articles={project.relatedArticles} />
                </Section>
            )}

            <nav
                aria-label="More projects"
                className="mn-container mt-16 flex justify-between gap-4 text-sm"
            >
                {project.previous ? (
                    <Link
                        to="/projects/$slug"
                        params={{ slug: project.previous.slug }}
                        className="mn-link"
                    >
                        ← {project.previous.title}
                    </Link>
                ) : (
                    <span />
                )}
                {project.next && (
                    <Link
                        to="/projects/$slug"
                        params={{ slug: project.next.slug }}
                        className="mn-link"
                    >
                        {project.next.title} →
                    </Link>
                )}
            </nav>
        </article>
    );
}
