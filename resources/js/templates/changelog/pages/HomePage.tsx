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

export function HomePage(data: HomePageProps) {
    return (
        <>
            <Hero profile={data.profile} socials={data.socials} />
            <TechMarquee groups={data.skillGroups} />
            <StatusStrip profile={data.profile} />
            <AboutSection profile={data.profile} />
            <StackSection groups={data.skillGroups} />
            <CareerSection entries={data.career} />
            <FeaturedWork projects={data.projects} />
            <ClientsSection
                companies={data.companies}
                testimonials={data.testimonials}
            />
            <EducationSection
                education={data.education}
                certifications={data.certifications}
            />
            <WritingSection articles={data.articles} />
            <BookshelfSection books={data.books} />
            <ContactSection profile={data.profile} />
        </>
    );
}
