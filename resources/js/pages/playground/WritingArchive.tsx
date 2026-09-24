import { WritingArchivePage } from '@/templates/playground/pages/WritingArchivePage';
import { PlaygroundLayout } from '@/templates/playground/layout/PlaygroundLayout';
import { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';
import { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
import type { WritingArchivePageProps } from '@/templates/types';
import { useSearchChange } from '@/shared/inertia/useSearchChange';

type Props = Omit<WritingArchivePageProps, 'onSearchChange'> & { seo: SeoData };

const ONLY = ['articles', 'allArticles', 'tags'];

export default function WritingArchive({ seo, ...props }: Props) {
    const onSearchChange = useSearchChange(props.search, ONLY);

    return (
        <>
            <SeoHead seo={seo} />
            <WritingArchivePage {...props} onSearchChange={onSearchChange} />
        </>
    );
}

WritingArchive.layout = withTemplateLayout(PlaygroundLayout);
