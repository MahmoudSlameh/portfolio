// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { ProjectArchivePage } from '@/templates/minimal/pages/ProjectArchivePage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
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

ProjectArchive.layout = withTemplateLayout(MinimalLayout);
