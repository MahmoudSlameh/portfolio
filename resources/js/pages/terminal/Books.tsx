import { BooksPage } from '@/templates/terminal/pages/BooksPage';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { BooksPageProps } from '@/templates/types';
import { useSearchChange } from '@/shared/inertia/useSearchChange';

type Props = Omit<BooksPageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['books', 'stats'];

export default function Books({ seo, ...props }: Props) {
    const onSearchChange = useSearchChange(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <BooksPage {...props} onSearchChange={onSearchChange} />
        </>
    );
}

Books.layout = withTemplateLayout(TerminalLayout);
