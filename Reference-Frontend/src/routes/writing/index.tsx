import { createFileRoute } from '@tanstack/react-router';
import { getArticles, getArticleTags } from '@/lib/content';
import { writingSearchSchema, type WritingSearch } from '@/lib/searchSchemas';
import { buildHead } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/writing/')({
  validateSearch: (search) => writingSearchSchema.parse(search),
  loaderDeps: ({ search }) => ({ q: search.q, tag: search.tag }),
  loader: async ({ deps }) => {
    const [articles, tags, allArticles] = await Promise.all([
      getArticles({ search: deps.q, tag: deps.tag }),
      getArticleTags(),
      getArticles(),
    ]);
    return { articles, tags, allArticles };
  },
  head: () =>
    buildHead({
      title: 'Writing',
      description:
        'Essays by Adam Rahman on ledgers, migrations, right-to-left design systems, typed event contracts and writing design docs people read.',
      path: '/writing',
    }),
  component: WritingPage,
});

function WritingPage() {
  const { articles, tags, allArticles } = Route.useLoaderData();
  const search = Route.useSearch();
  const navigate = Route.useNavigate();
  const { WritingArchive } = useTemplatePages();

  const handleSearchChange = (patch: Partial<WritingSearch>): void => {
    void navigate({ search: (previous) => ({ ...previous, ...patch }), replace: true, resetScroll: false });
  };

  return (
    <WritingArchive
      articles={articles}
      allArticles={allArticles}
      tags={tags}
      search={search}
      onSearchChange={handleSearchChange}
    />
  );
}
