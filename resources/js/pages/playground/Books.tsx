import { BooksPage } from '@/templates/playground/pages/BooksPage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
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

Books.layout = withTemplateLayout(PlaygroundLayout);
