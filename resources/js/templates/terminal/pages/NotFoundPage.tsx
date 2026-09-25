import { Link, useRouterState } from '@/lib/router';
import { useTranslation } from '@/hooks/useTranslation';
import { useTerminalCopy } from '../copy';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';
import { buttonClasses } from '../components/buttons';

export function NotFoundPage() {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const pathname = useRouterState({
        select: (state) => state.location.pathname,
    });

    return (
        <section
            aria-labelledby="not-found-title"
            className="tm-container pt-[130px]"
        >
            <GlowCard innerClassName="grid grid-cols-1 gap-10 p-6 md:p-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-center lg:p-16">
                <div
                    aria-hidden
                    className="tm-grid-bg tm-grid-fade pointer-events-none absolute inset-0"
                />
                <div
                    dir="ltr"
                    className="relative overflow-hidden rounded-lg border border-[#3b413d] bg-[#1f1f24] text-sm text-[#e9e9ea]"
                >
                    <div className="flex gap-1.5 border-b border-[#3b413d] bg-[#272730] px-4 py-3">
                        <span className="size-3 rounded-full bg-[#ec4040]" />
                        <span className="size-3 rounded-full bg-[#ffd45d]" />
                        <span className="size-3 rounded-full bg-[#a8ff53]" />
                    </div>
                    <pre className="overflow-x-auto p-5 leading-7">
                        <span className="text-[#a8ff53]">~$</span>{' '}
                        {c('notFound.cmd', { path: pathname })}
                        {'\n'}
                        <span className="text-[#f778ba]">
                            {c('notFound.out')}
                        </span>
                        {'\n'}
                        <span className="text-[#a8ff53]">~$</span> echo $?{'\n'}
                        404{'\n'}
                        <span className="text-[#a8ff53]">~$</span>{' '}
                        <span className="tm-flicker">_</span>
                    </pre>
                </div>
                <div className="relative">
                    <Kicker>{c('notFound.kicker')}</Kicker>
                    <p
                        aria-hidden
                        className="tm-text-gradient mt-2 mb-0 text-[clamp(5rem,14vw,9rem)] leading-none font-medium"
                    >
                        404
                    </p>
                    <h1
                        id="not-found-title"
                        className="mt-4 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                    >
                        {t('notFound.title')}
                    </h1>
                    <p className="text-tm-300">{t('notFound.body')}</p>
                    <div className="mt-6 flex flex-wrap gap-3">
                        <Link
                            to="/"
                            className={buttonClasses({ tone: 'primary' })}
                        >
                            {t('notFound.home')}
                        </Link>
                        <Link to="/projects" className={buttonClasses()}>
                            {t('notFound.projects')}
                        </Link>
                    </div>
                </div>
            </GlowCard>
        </section>
    );
}
