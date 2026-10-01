import type { ReactNode } from 'react';
import type {
    ArticleDetail,
    ArticleSummary,
    Book,
    BookStats,
    CareerEntry,
    Certification,
    Company,
    Education,
    NowDetail,
    Profile,
    Project,
    ProjectDetail,
    ProjectFacets,
    SearchIndex,
    ServicesSection,
    SkillGroup,
    Social,
    TestimonialEntry,
    UsesGroup,
} from '@/types/content';
import type {
    ArchiveSearch,
    LibrarySearch,
    WritingSearch,
} from '@/lib/searchSchemas';

export type { TemplateId } from '@/types/shared';

/**
 * The template contract: every template renders these pages with exactly these props
 * (produced by app/Http/Controllers/Site/*).
 */
export interface LayoutProps {
    profile: Profile;
    socials: Social[];
    searchIndex: SearchIndex;
    children: ReactNode;
}

export interface HomePageProps {
    profile: Profile;
    socials: Social[];
    skillGroups: SkillGroup[];
    services: ServicesSection;
    career: CareerEntry[];
    projects: Project[];
    companies: Company[];
    testimonials: TestimonialEntry[];
    education: Education[];
    certifications: Certification[];
    articles: ArticleSummary[];
    books: Book[];
}

export interface ProjectArchivePageProps {
    projects: Project[];
    facets: ProjectFacets;
    total: number;
    search: ArchiveSearch;
    onSearchChange: (patch: Partial<ArchiveSearch>) => void;
}

export interface CaseStudyPageProps {
    project: ProjectDetail;
}

export interface WritingArchivePageProps {
    articles: ArticleSummary[];
    allArticles: ArticleSummary[];
    tags: string[];
    search: WritingSearch;
    onSearchChange: (patch: Partial<WritingSearch>) => void;
}

export interface ArticlePageProps {
    article: ArticleDetail;
}

export interface BooksPageProps {
    books: Book[];
    stats: BookStats;
    search: LibrarySearch;
    onSearchChange: (patch: Partial<LibrarySearch>) => void;
}

export interface UsesPageProps {
    groups: UsesGroup[];
}

export interface NowPageProps {
    now: NowDetail;
}
