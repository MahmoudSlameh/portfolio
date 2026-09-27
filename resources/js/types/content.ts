/**
 * Content shapes sent by Laravel (app/Http/Resources) to the templates.
 * Ported from the reference templates' src/types/content.ts — English only, images from Spatie Media Library.
 * Keep in sync with the PHP resources (guarded by tests/Feature/ResourceShapesTest.php).
 */

export interface ImageData {
    src: string;
    srcSet: string;
    width: number;
    height: number;
    alt: string;
    placeholder: string | null;
}

export type AvailabilityStatus = 'open' | 'limited' | 'closed';

export interface ProfileStat {
    id: string;
    value: string;
    label: string;
}

export interface StatusEntry {
    id: string;
    label: string;
    value: string;
    tone: 'signal' | 'neutral';
}

export interface Principle {
    id: string;
    title: string;
    body: string;
}

export interface Profile {
    name: string;
    initials: string;
    role: string;
    headline: string;
    focusAreas: string[];
    summary: string;
    location: string;
    timezone: string;
    timezoneLabel: string;
    email: string;
    phone: string | null;
    currentVersion: string;
    updatedAt: string;
    availability: {
        status: AvailabilityStatus;
        label: string;
        note: string;
    };
    latestRelease: {
        added: string[];
        changed: string[];
        removed: string[];
    };
    stats: ProfileStat[];
    status: StatusEntry[];
    story: string[];
    principles: Principle[];
    portrait: ImageData | null;
    resumeUrl: string | null;
}

export type SocialIcon =
    | 'github'
    | 'linkedin'
    | 'x'
    | 'mastodon'
    | 'rss'
    | 'read-cv'
    | 'youtube'
    | 'dribbble'
    | 'stackoverflow'
    | 'email'
    | 'website';

export interface Social {
    id: string;
    label: string;
    handle: string;
    url: string;
    icon: SocialIcon;
}

export interface SkillCategory {
    id: string;
    label: string;
    description: string;
}

export type Proficiency = 1 | 2 | 3 | 4 | 5;

export interface Skill {
    id: string;
    name: string;
    categoryId: string | null;
    proficiency: Proficiency | null;
    years: number | null;
    icon: string | null;
}

export type CompanyKind = 'employer' | 'client';

export type WordmarkStyle =
    | 'serif'
    | 'serif-italic'
    | 'mono'
    | 'sans-bold'
    | 'sans-light'
    | 'spaced';

export interface Company {
    id: string;
    name: string;
    kind: CompanyKind;
    industry: string;
    location: string;
    url: string | null;
    period: string;
    engagement: string;
    wordmark: WordmarkStyle;
    featured: boolean;
    logo: ImageData | null;
    logoDark: ImageData | null;
}

export type Branch = 'main' | 'freelance' | 'oss';

export type EmploymentType =
    | 'full-time'
    | 'part-time'
    | 'contract'
    | 'freelance'
    | 'open-source'
    | 'internship';

export type WorkMode = 'on-site' | 'remote' | 'hybrid';

export interface Experience {
    id: string;
    companyId: string | null;
    organization: string;
    role: string;
    type: EmploymentType;
    workMode: WorkMode;
    branch: Branch;
    /** YYYY-MM */
    start: string;
    /** YYYY-MM, null = current role */
    end: string | null;
    location: string;
    address: string | null;
    version: string;
    commit: string;
    message: string;
    summary: string;
    highlights: string[];
    stack: string[];
    projectIds: string[];
}

export type ProjectStatus = 'live' | 'maintained' | 'archived' | 'in-progress';

export type ProjectCategory =
    | 'platform'
    | 'product'
    | 'open-source'
    | 'design-system';

export type ArchitectureNodeKind =
    | 'client'
    | 'service'
    | 'store'
    | 'queue'
    | 'external';

export interface ArchitectureNode {
    id: string;
    label: string;
    detail: string;
    kind: ArchitectureNodeKind;
    column: number;
    row: number;
}

export interface ArchitectureEdge {
    from: string;
    to: string;
    label?: string;
}

export interface Architecture {
    caption: string;
    columns: number;
    rows: number;
    nodes: ArchitectureNode[];
    edges: ArchitectureEdge[];
}

export interface ProjectMetric {
    id: string;
    value: string;
    label: string;
    detail: string;
}

export interface TitledItem {
    title: string;
    description: string;
}

export interface ProjectLink {
    label: string;
    url: string;
    kind: 'live' | 'source' | 'writeup' | 'talk';
}

export type GalleryImage = ImageData & { caption: string };

export interface Project {
    id: string;
    slug: string;
    title: string;
    tagline: string;
    summary: string;
    year: number;
    status: ProjectStatus;
    category: ProjectCategory;
    featured: boolean;
    version: string;
    companyId: string | null;
    experienceId: string | null;
    role: string;
    team: string;
    timeline: string;
    stack: string[];
    cover: ImageData | null;
    gallery: GalleryImage[];
    overview: string[];
    problem: string[];
    approach: TitledItem[];
    architecture: Architecture | null;
    features: TitledItem[];
    challenges: TitledItem[];
    metrics: ProjectMetric[];
    links: ProjectLink[];
}

export interface Education {
    id: string;
    institution: string;
    institutionUrl: string | null;
    degree: string;
    field: string;
    grade: string | null;
    /** YYYY-MM */
    start: string;
    /** YYYY-MM, null = currently studying */
    end: string | null;
    location: string;
    description: string | null;
    notes: string[];
    logo: ImageData | null;
}

export interface Certification {
    id: string;
    name: string;
    issuer: string;
    year: number;
    credentialId: string;
    url: string;
    expired: boolean;
    badge: ImageData | null;
}

export interface Testimonial {
    id: string;
    quote: string;
    author: string;
    role: string;
    companyId: string | null;
    relation: string;
    avatar: ImageData | null;
}

/**
 * One block of an article body. Paragraph, quote, list item and callout `text` may contain the
 * inline Markdown subset `**bold**`, `*italic*`, `` `code` `` and `[text](url)`: render it with
 * `InlineText` from `@/kit` (and `plainText()` where a string is needed). Headings are plain.
 */
export type ArticleBlock =
    | { type: 'paragraph'; text: string }
    | { type: 'heading'; id: string; text: string }
    | {
          type: 'code';
          language: string;
          filename?: string;
          code: string;
      }
    | { type: 'quote'; text: string; cite?: string }
    | { type: 'list'; items: string[] }
    | { type: 'callout'; title: string; text: string }
    | { type: 'image'; image: ImageData; caption?: string };

export interface Article {
    id: string;
    slug: string;
    title: string;
    excerpt: string;
    publishedAt: string;
    tags: string[];
    projectIds: string[];
    cover: ImageData | null;
    body: ArticleBlock[];
}

export type ReadingStatus = 'reading' | 'read' | 'to-read';

export type BookCategory =
    | 'engineering'
    | 'design'
    | 'systems'
    | 'fiction'
    | 'history'
    | 'philosophy';

export type BookCoverStyle = 'band' | 'block' | 'rule' | 'circle' | 'split';

export interface Book {
    id: string;
    slug: string;
    title: string;
    author: string;
    publishedYear: number | null;
    category: BookCategory;
    status: ReadingStatus;
    /** YYYY-MM */
    finishedAt: string | null;
    rating: 1 | 2 | 3 | 4 | 5 | null;
    pages: number | null;
    note: string;
    cover: {
        background: string;
        ink: string;
        accent: string;
        style: BookCoverStyle;
    };
    coverImage: ImageData | null;
}

export interface UsesItem {
    id: string;
    name: string;
    description: string;
    url?: string;
    image: ImageData | null;
}

export interface UsesGroup {
    id: string;
    kind: 'hardware' | 'software' | 'development';
    title: string;
    items: UsesItem[];
}

export interface NowEntry {
    id: string;
    title: string;
    body: string;
}

export interface NowPage {
    updatedAt: string;
    location: string;
    focus: NowEntry[];
    learning: NowEntry[];
    readingBookIds: string[];
    availability: string;
}

export interface ContactMessage {
    name: string;
    email: string;
    topic: 'role' | 'advisory' | 'speaking' | 'hello';
    message: string;
}

/* Derived shapes (computed on the server, see app/Support/Content/PortfolioContent.php) */

export interface SkillGroup {
    category: SkillCategory;
    skills: Skill[];
}

export interface ProjectReference {
    slug: string;
    title: string;
}

export interface CareerEntry extends Experience {
    company: Company | null;
    projects: ProjectReference[];
}

export interface ProjectFacets {
    technologies: string[];
    categories: ProjectCategory[];
}

export interface ArticleSummary extends Omit<Article, 'body'> {
    readingMinutes: number;
}

export interface ProjectDetail extends Project {
    company: Company | null;
    experience: Experience | null;
    previous: ProjectReference | null;
    next: ProjectReference | null;
    relatedArticles: ArticleSummary[];
}

export interface ArticleDetail extends Article {
    readingMinutes: number;
    relatedProjects: ProjectReference[];
    previous: ArticleSummary | null;
    next: ArticleSummary | null;
}

export interface BookStats {
    total: number;
    read: number;
    reading: number;
    queued: number;
    pagesRead: number;
    averageRating: number;
    perYear: { year: number; count: number }[];
    categories: BookCategory[];
}

export interface TestimonialEntry extends Testimonial {
    company: Company | null;
}

export interface NowDetail extends NowPage {
    reading: Book[];
}

export interface SearchIndex {
    projects: ProjectReference[];
    articles: ProjectReference[];
    books: { slug: string; title: string; author: string }[];
}
