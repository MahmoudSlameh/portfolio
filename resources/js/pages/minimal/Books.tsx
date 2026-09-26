// Generated Inertia page (see docs/11-template-kit.md). Edit the template in resources/js/templates/minimal.
import { BooksPage } from '@/templates/minimal/pages/BooksPage';
import { MinimalLayout } from '@/templates/minimal/layout/MinimalLayout';
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

Books.layout = withTemplateLayout(MinimalLayout);
