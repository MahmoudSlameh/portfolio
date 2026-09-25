import { Link, useRouterState } from '@/lib/router';
import { buttonClasses } from '@/templates/changelog/components/ui/Button';
import { useTranslation } from '@/hooks/useTranslation';

const deployedRoutes = [
    { commit: 'a41c9e2', to: '/', message: 'feat: publish home changelog' },
    {
        commit: '7f3b0d1',
        to: '/projects',
        message: 'feat: add project archive',
    },
    { commit: 'c2e8a55', to: '/writing', message: 'feat: add writing' },
    { commit: '3d91f07', to: '/books', message: 'feat: add bookshelf' },
    { commit: '9a7c3e4', to: '/now', message: 'docs: update now page' },
] as const;

export function NotFoundPage() {
    const { t } = useTranslation();
    const pathname = useRouterState({
        select: (state) => state.location.pathname,
    });

    return (
        <section
            aria-labelledby="not-found-title"
            className="relative overflow-hidden"
        >
            <div
                aria-hidden
                className="baseline-texture pointer-events-none absolute inset-0"
            />
            <div className="shell editorial-grid relative gap-y-12 py-20 md:py-32">
                <div className="col-span-4 md:col-span-7">
                    <p className="eyebrow ltr-isolate text-danger">
                        {t('notFound.code')}
                    </p>
                    <h1
                        id="not-found-title"
                        className="mt-6 font-display text-6xl leading-[0.95] tracking-[-0.02em] text-ink sm:text-7xl lg:text-8xl"
                    >
                        {t('notFound.title')}
                    </h1>
                    <p className="mt-8 max-w-lg text-lg leading-relaxed text-ink-muted">
                        {t('notFound.body')}
                    </p>
                    <div className="mt-10 flex flex-wrap gap-3">
                        <Link to="/" className={buttonClasses('primary')}>
                            {t('notFound.home')}
                        </Link>
                        <Link
                            to="/projects"
                            className={buttonClasses('secondary')}
                        >
                            {t('notFound.projects')}
                        </Link>
                    </div>
                </div>

                <div className="col-span-4 md:col-span-5 lg:col-span-4 lg:col-start-9">
                    <div
                        dir="ltr"
                        className="border border-line bg-raised font-mono text-[0.75rem] leading-relaxed"
                    >
                        <p className="border-b border-line px-4 py-2.5 text-ink-subtle">
                            ~/changelog — zsh
                        </p>
                        <div className="space-y-1 px-4 py-4">
                            <p className="text-ink">
                                <span className="text-signal-ink">$</span> git
                                checkout {pathname}
                            </p>
                            <p className="break-all text-danger">
                                error: pathspec &apos;{pathname}&apos; did not
                                match any file(s) known to git
                            </p>
                            <p className="pt-3 text-ink">
                                <span className="text-signal-ink">$</span>{' '}
                                {t('notFound.log')}
                            </p>
                            <ul className="space-y-1">
                                {deployedRoutes.map((route) => (
                                    <li key={route.commit}>
                                        <Link
                                            to={route.to}
                                            className="group flex gap-3 text-ink-muted hover:text-ink"
                                        >
                                            <span className="text-[#85570f] dark:text-[#f0c56b]">
                                                {route.commit}
                                            </span>
                                            <span className="link-draw-target truncate">
                                                {route.message}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
