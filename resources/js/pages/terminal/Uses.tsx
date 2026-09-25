import { UsesPage } from '@/templates/terminal/pages/UsesPage';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { UsesPageProps } from '@/templates/types';

export default function Uses({
    seo,
    ...props
}: UsesPageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <UsesPage {...props} />
        </>
    );
}

Uses.layout = withTemplateLayout(TerminalLayout);
