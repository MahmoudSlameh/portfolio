import type { HomePageProps } from '@/templates/types';
import { Blog } from './Blog';
import { Contact } from './Contact';
import { Cooperation } from './Cooperation';
import { Education } from './Education';
import { Experience } from './Experience';
import { Hero } from './Hero';
import { ProjectsSlider } from './ProjectsSlider';
import { Services } from './Services';
import { Skills } from './Skills';
import { Stats } from './Stats';

/** Sections whose content lives in the panel are skipped while their list is empty. */
export function HomePage(data: HomePageProps) {
    const hasSkills = data.skillGroups.some((group) => group.skills.length > 0);

    return (
        <>
            <Hero profile={data.profile} skillGroups={data.skillGroups} />
            {data.profile.stats.length > 0 && <Stats profile={data.profile} />}
            <Cooperation
                profile={data.profile}
                companies={data.companies}
                career={data.career}
            />
            {data.services.items.length > 0 && (
                <Services profile={data.profile} services={data.services} />
            )}
            {data.career.length > 0 && <Experience career={data.career} />}
            {(data.education.length > 0 || data.certifications.length > 0) && (
                <Education
                    education={data.education}
                    certifications={data.certifications}
                />
            )}
            {data.projects.length > 0 && (
                <ProjectsSlider
                    projects={data.projects}
                    companies={data.companies}
                />
            )}
            {hasSkills && <Skills groups={data.skillGroups} />}
            {data.articles.length > 0 && (
                <Blog articles={data.articles} projects={data.projects} />
            )}
            <Contact profile={data.profile} />
        </>
    );
}
