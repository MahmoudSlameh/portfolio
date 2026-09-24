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

export function HomePage(data: HomePageProps) {
    return (
        <>
            <Hero profile={data.profile} skillGroups={data.skillGroups} />
            <Stats profile={data.profile} />
            <Cooperation
                profile={data.profile}
                companies={data.companies}
                career={data.career}
            />
            <Services profile={data.profile} groups={data.skillGroups} />
            <Experience career={data.career} />
            <Education
                education={data.education}
                certifications={data.certifications}
            />
            <ProjectsSlider
                projects={data.projects}
                companies={data.companies}
            />
            <Skills groups={data.skillGroups} />
            <Blog articles={data.articles} projects={data.projects} />
            <Contact profile={data.profile} />
        </>
    );
}
