import type { HomePageProps } from '@/templates/types';
import { AboutBlock } from './AboutBlock';
import { BookNook } from './BookNook';
import { CareerTickets } from './CareerTickets';
import { ClientsWall } from './ClientsWall';
import { HeroBento } from './HeroBento';
import { PostcardContact } from './PostcardContact';
import { SchoolBadges } from './SchoolBadges';
import { StackBoard } from './StackBoard';
import { TapeMarquee } from './TapeMarquee';
import { WorkList } from './WorkList';
import { WritingGrid } from './WritingGrid';

/** Sections whose content lives in the panel are skipped while their list is empty. */
export function HomePage(data: HomePageProps) {
    const skillNames = data.skillGroups.flatMap((group) =>
        group.skills.map((skill) => skill.name),
    );

    return (
        <>
            <HeroBento profile={data.profile} socials={data.socials} />
            {skillNames.length > 0 && <TapeMarquee items={skillNames} />}
            {(data.profile.story.length > 0 ||
                data.profile.principles.length > 0) && (
                <AboutBlock profile={data.profile} />
            )}
            {skillNames.length > 0 && <StackBoard groups={data.skillGroups} />}
            {data.career.length > 0 && <CareerTickets entries={data.career} />}
            {data.projects.length > 0 && <WorkList projects={data.projects} />}
            {(data.companies.length > 0 || data.testimonials.length > 0) && (
                <ClientsWall
                    companies={data.companies}
                    testimonials={data.testimonials}
                />
            )}
            {(data.education.length > 0 || data.certifications.length > 0) && (
                <SchoolBadges
                    education={data.education}
                    certifications={data.certifications}
                />
            )}
            {data.articles.length > 0 && (
                <WritingGrid articles={data.articles} />
            )}
            {data.books.length > 0 && <BookNook books={data.books} />}
            <PostcardContact profile={data.profile} />
        </>
    );
}
