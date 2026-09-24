import type { TemplateDefinition } from '@/templates/types';
import { HomePage } from './home/HomePage';
import { TerminalLayout } from './layout/TerminalLayout';
import { ArticlePage } from './pages/ArticlePage';
import { BooksPage } from './pages/BooksPage';
import { CaseStudyPage } from './pages/CaseStudyPage';
import { NotFoundPage } from './pages/NotFoundPage';
import { NowPage } from './pages/NowPage';
import { ProjectArchivePage } from './pages/ProjectArchivePage';
import { UsesPage } from './pages/UsesPage';
import { WritingArchivePage } from './pages/WritingArchivePage';

const terminalTemplate: TemplateDefinition = {
  id: 'terminal',
  name: 'Terminal',
  fontsHref:
    'https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600&display=swap',
  Layout: TerminalLayout,
  pages: {
    Home: HomePage,
    ProjectArchive: ProjectArchivePage,
    CaseStudy: CaseStudyPage,
    WritingArchive: WritingArchivePage,
    Article: ArticlePage,
    Books: BooksPage,
    Uses: UsesPage,
    Now: NowPage,
    NotFound: NotFoundPage,
  },
};

export default terminalTemplate;
