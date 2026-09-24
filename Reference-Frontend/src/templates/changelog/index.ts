import { BooksLibrary } from '@/templates/changelog/features/books/BooksLibrary';
import { NotFoundPage } from '@/templates/changelog/features/not-found/NotFoundPage';
import { CaseStudy } from '@/templates/changelog/features/projects/CaseStudy';
import { ProjectArchive } from '@/templates/changelog/features/projects/ProjectArchive';
import { ArticlePage } from '@/templates/changelog/features/writing/ArticlePage';
import { WritingArchive } from '@/templates/changelog/features/writing/WritingArchive';
import type { TemplateDefinition } from '@/templates/types';
import { ChangelogLayout } from './layout/ChangelogLayout';
import { HomePage } from './pages/HomePage';
import { NowPage } from './pages/NowPage';
import { UsesPage } from './pages/UsesPage';

const changelogTemplate: TemplateDefinition = {
  id: 'changelog',
  name: 'The Engineer’s Changelog',
  fontsHref:
    'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Geist:wght@300..700&family=JetBrains+Mono:wght@400;500;600&family=Readex+Pro:wght@300..700&display=swap',
  Layout: ChangelogLayout,
  pages: {
    Home: HomePage,
    ProjectArchive,
    CaseStudy,
    WritingArchive,
    Article: ArticlePage,
    Books: BooksLibrary,
    Uses: UsesPage,
    Now: NowPage,
    NotFound: NotFoundPage,
  },
};

export default changelogTemplate;
