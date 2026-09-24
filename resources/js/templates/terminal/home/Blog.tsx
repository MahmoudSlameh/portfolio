import { Link } from '@/lib/router';
import type { ArticleSummary } from '@/lib/content';
import type { ImageData, Project } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { Kicker } from '../components/Kicker';
import { PostCard } from '../components/PostCard';

export const coverFor = (
    article: ArticleSummary,
    projects: Project[],
): ImageData | null =>
    projects.find((project) => article.projectIds.includes(project.id))
        ?.cover ?? null;

export function Blog({
    articles,
    projects,
}: {
    articles: ArticleSummary[];
    projects: Project[];
}) {
    const c = useTerminalCopy();

    return (
        <section
            id="writing"
            aria-labelledby="writing-title"
            className="relative py-[60px]"
        >
            <div className="tm-container">
                <div className="text-center">
                    <Kicker center>{c('blog.kicker')}</Kicker>
                    <h2
                        id="writing-title"
                        className="mt-1 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {c('blog.title')}
                    </h2>
                </div>
                <ul className="mt-16 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {articles.slice(0, 3).map((article) => (
                        <li key={article.id}>
                            <PostCard
                                article={article}
                                cover={coverFor(article, projects)}
                            />
                        </li>
                    ))}
                </ul>
                <p className="text-tm-300 mt-8 mb-0 text-center">
                    <Link
                        to="/writing"
                        className="text-tm-primary hover:underline"
                    >
                        {c('blog.all')} →
                    </Link>
                </p>
            </div>
        </section>
    );
}
