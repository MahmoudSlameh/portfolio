import { ProjectArchive as ProjectArchiveView } from '@/templates/changelog/features/projects/ProjectArchive';
import { ChangelogLayout } from '@/templates/changelog/layout/ChangelogLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { ProjectArchivePageProps } from '@/templates/types';
import { useSearchChange } from '@/shared/inertia/useSearchChange';

type Props = Omit<ProjectArchivePageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['projects', 'facets', 'total'];

export default function ProjectArchive({ seo, ...props }: Props) {
    const onSearchChange = useSearchChange(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <ProjectArchiveView {...props} onSearchChange={onSearchChange} />
        </>
    );
}

ProjectArchive.layout = withTemplateLayout(ChangelogLayout);
