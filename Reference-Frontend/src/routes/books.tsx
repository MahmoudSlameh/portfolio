import { createFileRoute } from '@tanstack/react-router';
import { getBooks, getBookStats } from '@/lib/content';
import { librarySearchSchema, type LibrarySearch } from '@/lib/searchSchemas';
import { buildHead } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/books')({
  validateSearch: (search) => librarySearchSchema.parse(search),
  loader: async () => {
    const [books, stats] = await Promise.all([getBooks(), getBookStats()]);
    return { books, stats };
  },
  head: () =>
    buildHead({
      title: 'Bookshelf',
      description:
        'The books Adam Rahman has read, is reading and plans to read — engineering, systems, design, history and fiction, with notes and ratings.',
      path: '/books',
    }),
  component: BooksPage,
});

function BooksPage() {
  const { books, stats } = Route.useLoaderData();
  const search = Route.useSearch();
  const navigate = Route.useNavigate();
  const { Books } = useTemplatePages();

  const handleSearchChange = (patch: Partial<LibrarySearch>): void => {
    void navigate({ search: (previous) => ({ ...previous, ...patch }), replace: true, resetScroll: false });
  };

  return <Books books={books} stats={stats} search={search} onSearchChange={handleSearchChange} />;
}
