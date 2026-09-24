export type Locale = 'en' | 'ar';

export type Localized<T = string> = Record<Locale, T>;

export interface ImageAsset {
  base: string;
  widths: readonly number[];
  width: number;
  height: number;
  alt: string;
}

export type AvailabilityStatus = 'open' | 'limited' | 'closed';

export interface ProfileStat {
  id: string;
  value: string;
  label: Localized;
}

export interface StatusEntry {
  id: string;
  label: Localized;
  value: Localized;
  tone: 'signal' | 'neutral';
}

export interface Principle {
  id: string;
  title: Localized;
  body: Localized;
}

export interface Profile {
  name: Localized;
  initials: string;
  role: Localized;
  headline: Localized;
  focusAreas: Localized<string[]>;
  summary: Localized;
  location: Localized;
  timezone: string;
  timezoneLabel: string;
  email: string;
  currentVersion: string;
  updatedAt: string;
  availability: {
    status: AvailabilityStatus;
    label: Localized;
    note: Localized;
  };
  latestRelease: {
    added: Localized<string[]>;
    changed: Localized<string[]>;
    removed: Localized<string[]>;
  };
  stats: ProfileStat[];
  status: StatusEntry[];
  story: Localized<string[]>;
  principles: Principle[];
  portrait: ImageAsset;
}

export type SocialIcon = 'github' | 'linkedin' | 'x' | 'mastodon' | 'rss' | 'read-cv';

export interface Social {
  id: string;
  label: string;
  handle: string;
  url: string;
  icon: SocialIcon;
}

export interface SkillCategory {
  id: string;
  label: Localized;
  description: Localized;
}

export type Proficiency = 1 | 2 | 3 | 4 | 5;

export interface Skill {
  id: string;
  name: string;
  categoryId: string;
  proficiency: Proficiency;
  years: number;
}

export type CompanyKind = 'employer' | 'client';

export type WordmarkStyle = 'serif' | 'serif-italic' | 'mono' | 'sans-bold' | 'sans-light' | 'spaced';

export interface Company {
  id: string;
  name: string;
  kind: CompanyKind;
  industry: string;
  location: string;
  url: string;
  period: string;
  engagement: string;
  wordmark: WordmarkStyle;
}

export type Branch = 'main' | 'freelance' | 'oss';

export type EmploymentType = 'full-time' | 'contract' | 'freelance' | 'open-source' | 'internship';

export interface Experience {
  id: string;
  companyId: string | null;
  organization: string;
  role: string;
  type: EmploymentType;
  branch: Branch;
  start: string;
  end: string | null;
  location: string;
  version: string;
  commit: string;
  message: string;
  summary: string;
  highlights: string[];
  stack: string[];
  projectIds: string[];
}

export type ProjectStatus = 'live' | 'maintained' | 'archived' | 'in-progress';

export type ProjectCategory = 'platform' | 'product' | 'open-source' | 'design-system';

export type ArchitectureNodeKind = 'client' | 'service' | 'store' | 'queue' | 'external';

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
  cover: ImageAsset;
  gallery: (ImageAsset & { caption: string })[];
  overview: string[];
  problem: string[];
  approach: TitledItem[];
  architecture: Architecture;
  features: TitledItem[];
  challenges: TitledItem[];
  metrics: ProjectMetric[];
  links: ProjectLink[];
}

export interface Education {
  id: string;
  institution: string;
  degree: string;
  field: string;
  start: number;
  end: number;
  location: string;
  notes: string[];
}

export interface Certification {
  id: string;
  name: string;
  issuer: string;
  year: number;
  credentialId: string;
  url: string;
}

export interface Testimonial {
  id: string;
  quote: string;
  author: string;
  role: string;
  companyId: string;
  relation: string;
}

export type ArticleBlock =
  | { type: 'paragraph'; text: string }
  | { type: 'heading'; id: string; text: string }
  | { type: 'code'; language: 'ts' | 'tsx' | 'sql' | 'bash' | 'json'; filename?: string; code: string }
  | { type: 'quote'; text: string; cite?: string }
  | { type: 'list'; items: string[] }
  | { type: 'callout'; title: string; text: string };

export interface Article {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  publishedAt: string;
  tags: string[];
  projectIds: string[];
  body: ArticleBlock[];
}

export type ReadingStatus = 'reading' | 'read' | 'to-read';

export type BookCategory = 'engineering' | 'design' | 'systems' | 'fiction' | 'history' | 'philosophy';

export type BookCoverStyle = 'band' | 'block' | 'rule' | 'circle' | 'split';

export interface Book {
  id: string;
  slug: string;
  title: string;
  author: string;
  publishedYear: number;
  category: BookCategory;
  status: ReadingStatus;
  finishedAt: string | null;
  rating: 1 | 2 | 3 | 4 | 5 | null;
  pages: number;
  note: string;
  cover: {
    background: string;
    ink: string;
    accent: string;
    style: BookCoverStyle;
  };
}

export interface UsesItem {
  id: string;
  name: string;
  description: string;
  url?: string;
}

export interface UsesGroup {
  id: string;
  kind: 'hardware' | 'software' | 'development';
  title: Localized;
  items: UsesItem[];
}

export interface NowEntry {
  id: string;
  title: Localized;
  body: Localized;
}

export interface NowPage {
  updatedAt: string;
  location: Localized;
  focus: NowEntry[];
  learning: NowEntry[];
  readingBookIds: string[];
  availability: Localized;
}

export interface SiteSettings {
  activeTemplate: string;
}

export interface ContactMessage {
  name: string;
  email: string;
  topic: 'role' | 'advisory' | 'speaking' | 'hello';
  message: string;
}
