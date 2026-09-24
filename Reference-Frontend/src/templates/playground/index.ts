import type { TemplateDefinition } from '@/templates/types';
import { HomePage } from './home/HomePage';
import { PlaygroundLayout } from './layout/PlaygroundLayout';
import { ArticlePage } from './pages/ArticlePage';
import { BooksPage } from './pages/BooksPage';
import { CaseStudyPage } from './pages/CaseStudyPage';
import { NotFoundPage } from './pages/NotFoundPage';
import { NowPage } from './pages/NowPage';
import { ProjectArchivePage } from './pages/ProjectArchivePage';
import { UsesPage } from './pages/UsesPage';
import { WritingArchivePage } from './pages/WritingArchivePage';

const playgroundTemplate: TemplateDefinition = {
  id: 'playground',
  name: 'Playground',
  fontsHref:
    'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=Cairo:wght@300..900&family=Space+Grotesk:wght@300..700&family=Space+Mono:wght@400;700&display=swap',
  Layout: PlaygroundLayout,
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

export default playgroundTemplate;
