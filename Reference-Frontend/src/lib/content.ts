import { articles } from '@/data/articles';
import { books } from '@/data/books';
import { certifications } from '@/data/certifications';
import { companies } from '@/data/companies';
import { education } from '@/data/education';
import { experiences } from '@/data/experiences';
import { nowPage } from '@/data/now';
import { profile } from '@/data/profile';
import { projects } from '@/data/projects';
import { siteSettings } from '@/data/settings';
import { skillCategories, skills } from '@/data/skills';
import { socials } from '@/data/socials';
import { testimonials } from '@/data/testimonials';
import { usesGroups } from '@/data/uses';
import type {
  Article,
  Book,
  BookCategory,
  Company,
  ContactMessage,
  Experience,
  NowPage,
  Project,
  ProjectCategory,
  ReadingStatus,
  SiteSettings,
  Skill,
  SkillCategory,
  Testimonial,
} from '@/types/content';

const respond = <T>(value: T): Promise<T> => Promise.resolve(value);

const normalize = (value: string): string => value.toLocaleLowerCase().normalize('NFKD');

const matchesSearch = (search: string | undefined, fields: string[]): boolean => {
  if (!search?.trim()) return true;
  const needle = normalize(search.trim());
  return fields.some((field) => normalize(field).includes(needle));
};

const findCompany = (id: string | null): Company | null => companies.find((company) => company.id === id) ?? null;

export const getSiteSettings = (): Promise<SiteSettings> => respond(siteSettings);

export const getProfile = () => respond(profile);

export const getSocials = () => respond(socials);

export interface SkillGroup {
  category: SkillCategory;
  skills: Skill[];
}

export const getSkillGroups = (): Promise<SkillGroup[]> =>
  respond(
    skillCategories.map((category) => ({
      category,
      skills: skills.filter((skill) => skill.categoryId === category.id),
    })),
  );

export const getCompanies = () => respond(companies);

export interface ProjectReference {
  slug: string;
  title: string;
}

const toReference = (project: Project): ProjectReference => ({ slug: project.slug, title: project.title });

export interface CareerEntry extends Experience {
  company: Company | null;
  projects: ProjectReference[];
}

export const getCareer = (): Promise<CareerEntry[]> =>
  respond(
    [...experiences]
      .sort((a, b) => b.start.localeCompare(a.start))
      .map((experience) => ({
        ...experience,
        company: findCompany(experience.companyId),
        projects: projects.filter((project) => experience.projectIds.includes(project.id)).map(toReference),
      })),
  );

export type ProjectSort = 'newest' | 'oldest';

export interface ProjectQuery {
  featured?: boolean;
  search?: string;
  category?: ProjectCategory;
  tech?: string;
  sort?: ProjectSort;
}

export const getProjects = (query: ProjectQuery = {}): Promise<Project[]> => {
  const { featured, search, category, tech, sort = 'newest' } = query;
  const direction = sort === 'newest' ? -1 : 1;

  return respond(
    projects
      .filter((project) => featured === undefined || project.featured === featured)
      .filter((project) => !category || project.category === category)
      .filter((project) => !tech || project.stack.includes(tech))
      .filter((project) =>
        matchesSearch(search, [project.title, project.tagline, project.summary, project.role, ...project.stack]),
      )
      .sort((a, b) => (a.year - b.year) * direction || a.title.localeCompare(b.title)),
  );
};

export interface ProjectFacets {
  technologies: string[];
  categories: ProjectCategory[];
}

export const getProjectFacets = (): Promise<ProjectFacets> =>
  respond({
    technologies: [...new Set(projects.flatMap((project) => project.stack))].sort((a, b) => a.localeCompare(b)),
    categories: [...new Set(projects.map((project) => project.category))],
  });

export interface ProjectDetail extends Project {
  company: Company | null;
  experience: Experience | null;
  previous: ProjectReference | null;
  next: ProjectReference | null;
  relatedArticles: ArticleSummary[];
}

export const getProjectBySlug = (slug: string): Promise<ProjectDetail | null> => {
  const ordered = [...projects].sort((a, b) => b.year - a.year);
  const index = ordered.findIndex((project) => project.slug === slug);
  if (index === -1) return respond(null);

  const project = ordered[index];
  const previous = ordered[index - 1];
  const next = ordered[index + 1];

  return respond({
    ...project,
    company: findCompany(project.companyId),
    experience: experiences.find((experience) => experience.id === project.experienceId) ?? null,
    previous: previous ? toReference(previous) : null,
    next: next ? toReference(next) : null,
    relatedArticles: articles.filter((article) => article.projectIds.includes(project.id)).map(toArticleSummary),
  });
};

const WORDS_PER_MINUTE = 220;

const countWords = (article: Article): number =>
  article.body.reduce((total, block) => {
    const text =
      block.type === 'list'
        ? block.items.join(' ')
        : block.type === 'code'
          ? block.code
          : block.type === 'callout'
            ? `${block.title} ${block.text}`
            : block.text;
    return total + text.split(/\s+/).filter(Boolean).length;
  }, 0);

export interface ArticleSummary extends Omit<Article, 'body'> {
  readingMinutes: number;
}

const toArticleSummary = ({ body, ...article }: Article): ArticleSummary => ({
  ...article,
  readingMinutes: Math.max(1, Math.round(countWords({ ...article, body }) / WORDS_PER_MINUTE)),
});

export interface ArticleQuery {
  search?: string;
  tag?: string;
}

const sortedArticles = (): Article[] => [...articles].sort((a, b) => b.publishedAt.localeCompare(a.publishedAt));

export const getArticles = (query: ArticleQuery = {}): Promise<ArticleSummary[]> =>
  respond(
    sortedArticles()
      .filter((article) => !query.tag || article.tags.includes(query.tag))
      .filter((article) => matchesSearch(query.search, [article.title, article.excerpt, ...article.tags]))
      .map(toArticleSummary),
  );

export const getArticleTags = (): Promise<string[]> =>
  respond([...new Set(articles.flatMap((article) => article.tags))].sort((a, b) => a.localeCompare(b)));

export interface ArticleDetail extends Article {
  readingMinutes: number;
  relatedProjects: ProjectReference[];
  previous: ArticleSummary | null;
  next: ArticleSummary | null;
}

export const getArticleBySlug = (slug: string): Promise<ArticleDetail | null> => {
  const ordered = sortedArticles();
  const index = ordered.findIndex((article) => article.slug === slug);
  if (index === -1) return respond(null);

  const article = ordered[index];
  const newer = ordered[index - 1];
  const older = ordered[index + 1];

  return respond({
    ...article,
    readingMinutes: toArticleSummary(article).readingMinutes,
    relatedProjects: projects.filter((project) => article.projectIds.includes(project.id)).map(toReference),
    previous: older ? toArticleSummary(older) : null,
    next: newer ? toArticleSummary(newer) : null,
  });
};

export interface BookQuery {
  status?: ReadingStatus;
  category?: BookCategory;
}

const statusOrder: Record<ReadingStatus, number> = { reading: 0, read: 1, 'to-read': 2 };

export const getBooks = (query: BookQuery = {}): Promise<Book[]> =>
  respond(
    books
      .filter((book) => !query.status || book.status === query.status)
      .filter((book) => !query.category || book.category === query.category)
      .sort(
        (a, b) =>
          statusOrder[a.status] - statusOrder[b.status] || (b.finishedAt ?? '').localeCompare(a.finishedAt ?? ''),
      ),
  );

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

export const getBookStats = (): Promise<BookStats> => {
  const finished = books.filter((book) => book.status === 'read');
  const rated = finished.filter((book) => book.rating !== null);
  const years = finished.map((book) => Number(book.finishedAt?.slice(0, 4)));
  const firstYear = Math.min(...years);
  const lastYear = Math.max(...years);

  return respond({
    total: books.length,
    read: finished.length,
    reading: books.filter((book) => book.status === 'reading').length,
    queued: books.filter((book) => book.status === 'to-read').length,
    pagesRead: finished.reduce((total, book) => total + book.pages, 0),
    averageRating: rated.reduce((total, book) => total + (book.rating ?? 0), 0) / Math.max(rated.length, 1),
    perYear: Array.from({ length: lastYear - firstYear + 1 }, (_, offset) => {
      const year = firstYear + offset;
      return { year, count: years.filter((finishedYear) => finishedYear === year).length };
    }),
    categories: [...new Set(books.map((book) => book.category))],
  });
};

export interface TestimonialEntry extends Testimonial {
  company: Company | null;
}

export const getTestimonials = (): Promise<TestimonialEntry[]> =>
  respond(testimonials.map((testimonial) => ({ ...testimonial, company: findCompany(testimonial.companyId) })));

export const getEducation = () => respond([...education].sort((a, b) => b.end - a.end));

export const getCertifications = () => respond([...certifications].sort((a, b) => b.year - a.year));

export const getUses = () => respond(usesGroups);

export interface NowDetail extends NowPage {
  reading: Book[];
}

export const getNow = (): Promise<NowDetail> =>
  respond({ ...nowPage, reading: books.filter((book) => nowPage.readingBookIds.includes(book.id)) });

export interface SearchIndex {
  projects: ProjectReference[];
  articles: ProjectReference[];
  books: { slug: string; title: string; author: string }[];
}

export const getSearchIndex = (): Promise<SearchIndex> =>
  respond({
    projects: projects.map(toReference),
    articles: sortedArticles().map((article) => ({ slug: article.slug, title: article.title })),
    books: books.map((book) => ({ slug: book.slug, title: book.title, author: book.author })),
  });

const SIMULATED_LATENCY_MS = 900;

export const submitContactMessage = (message: ContactMessage): Promise<{ id: string; receivedAt: string }> =>
  new Promise((resolve) => {
    window.setTimeout(() => {
      resolve({ id: `msg_${message.email.length}${Date.now().toString(36)}`, receivedAt: new Date().toISOString() });
    }, SIMULATED_LATENCY_MS);
  });
