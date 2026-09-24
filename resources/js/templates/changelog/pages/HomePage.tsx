import { AboutSection } from '@/templates/changelog/features/home/AboutSection';
import { BookshelfSection } from '@/templates/changelog/features/home/BookshelfSection';
import { CareerSection } from '@/templates/changelog/features/home/CareerSection';
import { ClientsSection } from '@/templates/changelog/features/home/ClientsSection';
import { ContactSection } from '@/templates/changelog/features/home/contact/ContactSection';
import { EducationSection } from '@/templates/changelog/features/home/EducationSection';
import { FeaturedWork } from '@/templates/changelog/features/home/FeaturedWork';
import { Hero } from '@/templates/changelog/features/home/Hero';
import { StackSection } from '@/templates/changelog/features/home/StackSection';
import { StatusStrip } from '@/templates/changelog/features/home/StatusStrip';
import { TechMarquee } from '@/templates/changelog/features/home/TechMarquee';
import { WritingSection } from '@/templates/changelog/features/home/WritingSection';
import type { HomePageProps } from '@/templates/types';

/** Sections whose content lives in the panel are skipped while their list is empty. */
export function HomePage(data: HomePageProps) {
    const hasSkills = data.skillGroups.some((group) => group.skills.length > 0);

    return (
        <>
            <Hero profile={data.profile} socials={data.socials} />
            {hasSkills && <TechMarquee groups={data.skillGroups} />}
            <StatusStrip profile={data.profile} />
            {(data.profile.story.length > 0 ||
                data.profile.principles.length > 0) && (
                <AboutSection profile={data.profile} />
            )}
            {hasSkills && <StackSection groups={data.skillGroups} />}
            {data.career.length > 0 && <CareerSection entries={data.career} />}
            {data.projects.length > 0 && (
                <FeaturedWork projects={data.projects} />
            )}
            {data.companies.length > 0 && (
                <ClientsSection
                    companies={data.companies}
                    testimonials={data.testimonials}
                />
            )}
            {(data.education.length > 0 || data.certifications.length > 0) && (
                <EducationSection
                    education={data.education}
                    certifications={data.certifications}
                />
            )}
            {data.articles.length > 0 && (
                <WritingSection articles={data.articles} />
            )}
            {data.books.length > 0 && <BookshelfSection books={data.books} />}
            <ContactSection profile={data.profile} />
        </>
    );
}
