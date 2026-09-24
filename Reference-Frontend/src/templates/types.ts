import type { ComponentType, ReactNode } from 'react';
import type {
  ArticleDetail,
  ArticleSummary,
  BookStats,
  CareerEntry,
  NowDetail,
  ProjectDetail,
  ProjectFacets,
  SearchIndex,
  SkillGroup,
  TestimonialEntry,
} from '@/lib/content';
import type { ArchiveSearch, LibrarySearch, WritingSearch } from '@/lib/searchSchemas';
import type {
  Book,
  Certification,
  Company,
  Education,
  Profile,
  Project,
  Social,
  UsesGroup,
} from '@/types/content';

export type TemplateId = 'changelog' | 'playground' | 'terminal';

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

export interface TemplatePages {
  Home: ComponentType<HomePageProps>;
  ProjectArchive: ComponentType<ProjectArchivePageProps>;
  CaseStudy: ComponentType<CaseStudyPageProps>;
  WritingArchive: ComponentType<WritingArchivePageProps>;
  Article: ComponentType<ArticlePageProps>;
  Books: ComponentType<BooksPageProps>;
  Uses: ComponentType<UsesPageProps>;
  Now: ComponentType<NowPageProps>;
  NotFound: ComponentType;
}

export interface TemplateDefinition {
  id: TemplateId;
  name: string;
  fontsHref: string;
  Layout: ComponentType<LayoutProps>;
  pages: TemplatePages;
}
