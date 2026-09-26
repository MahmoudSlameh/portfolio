import { ProjectArchivePage } from '@/templates/terminal/pages/ProjectArchivePage';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { ProjectArchivePageProps } from '@/templates/types';
import { useArchiveFilters } from '@/kit';

type Props = Omit<ProjectArchivePageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['projects', 'facets', 'total'];

export default function ProjectArchive({ seo, ...props }: Props) {
    const onSearchChange = useArchiveFilters(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <ProjectArchivePage {...props} onSearchChange={onSearchChange} />
        </>
    );
}

ProjectArchive.layout = withTemplateLayout(TerminalLayout);
