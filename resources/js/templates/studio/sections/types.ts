import type { HomePageProps } from '@/templates/types';
import type { HomeSectionName, SectionProps, SectionVariant } from '../spec';

/** What every home section component receives: the page data, its variant and its props. */
export interface SectionComponentProps<S extends HomeSectionName> {
    data: HomePageProps;
    variant: SectionVariant<S>;
    props: SectionProps<S>;
}
