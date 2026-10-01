import type { ComponentType } from 'react';
import type { HomePageProps } from '@/templates/types';
import { Hero } from '../sections/Hero';
import { Books, Contact, Education, Writing } from '../sections/More';
import { About, Skills, Stats } from '../sections/Profile';
import type { SectionComponentProps } from '../sections/types';
import { Career, Clients, Projects, Testimonials } from '../sections/Work';
import type { HomeSection, HomeSectionName } from '../spec';
import { useStudioSpec } from '../useSpec';

/**
 * One component per section of the catalogue. The type is exhaustive: a section added to
 * App\Support\Studio\SpecCatalogue fails to compile here until it is implemented.
 */
const sections: {
    [S in HomeSectionName]: ComponentType<SectionComponentProps<S>>;
} = {
    hero: Hero,
    about: About,
    stats: Stats,
    skills: Skills,
    career: Career,
    projects: Projects,
    clients: Clients,
    testimonials: Testimonials,
    education: Education,
    writing: Writing,
    books: Books,
    contact: Contact,
};

function renderSection(entry: HomeSection, data: HomePageProps) {
    // The mapped type above guarantees the component matches the entry's section.
    const Component = sections[entry.section] as ComponentType<{
        data: HomePageProps;
        variant: string;
        props: object;
    }>;

    return (
        <Component
            key={entry.section}
            data={data}
            variant={entry.variant}
            props={entry.props ?? {}}
        />
    );
}

/** The home page: the spec's sections, in the spec's order. */
export function HomePage(data: HomePageProps) {
    const spec = useStudioSpec();

    return <>{spec.pages.home.map((entry) => renderSection(entry, data))}</>;
}
