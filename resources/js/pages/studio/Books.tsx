// Inertia page of the studio engine: renders any studio template from its spec (docs/12-ai-templates.md §3).
import { BooksPage } from '@/templates/studio/pages/ArchivePages';
import { StudioLayout } from '@/templates/studio/layout/StudioLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { BooksPageProps } from '@/templates/types';
import { useArchiveFilters } from '@/kit';

type Props = Omit<BooksPageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['books', 'stats'];

export default function Books({ seo, ...props }: Props) {
    const onSearchChange = useArchiveFilters(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <BooksPage {...props} onSearchChange={onSearchChange} />
        </>
    );
}

Books.layout = withTemplateLayout(StudioLayout);
