import { WritingArchive as WritingArchiveView } from '@/templates/changelog/features/writing/WritingArchive';
import { ChangelogLayout } from '@/templates/changelog/layout/ChangelogLayout';
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
            <WritingArchiveView {...props} onSearchChange={onSearchChange} />
        </>
    );
}

WritingArchive.layout = withTemplateLayout(ChangelogLayout);
