import { Link, useTranslation } from '@/kit';

/** Rendered with a 404 status for unknown URLs (and disabled pages). Receives no content props. */
export function NotFoundPage() {
    const { t } = useTranslation();

    return (
        <div className="mn-container pt-24 text-center">
            <p className="text-sm text-ink-subtle">404</p>
            <h1 className="mt-2 text-3xl font-semibold text-ink">
                Page not found
            </h1>
            <p className="mt-6 flex justify-center gap-6">
                <Link to="/" className="mn-link">
                    {t('nav.home')}
                </Link>
                <Link to="/projects" className="mn-link">
                    {t('notFound.projects')}
                </Link>
            </p>
        </div>
    );
}
