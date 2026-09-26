// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { WritingArchivePage } from '@/templates/studio/pages/ArchivePages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { WritingArchivePageProps } from '@/templates/types';
import { useArchiveFilters } from '@/kit';

type Props = Omit<WritingArchivePageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['articles', 'allArticles', 'tags'];

export default function WritingArchive({ seo, ...props }: Props) {
    const onSearchChange = useArchiveFilters(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <WritingArchivePage {...props} onSearchChange={onSearchChange} />
        </>
    );
}

WritingArchive.layout = withTemplateLayout(StudioLayout);
