import { createFileRoute } from '@tanstack/react-router';
import { archiveSearchSchema, type ArchiveSearch } from '@/lib/searchSchemas';
import { getProjectFacets, getProjects } from '@/lib/content';
import { buildHead } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/projects/')({
  validateSearch: (search) => archiveSearchSchema.parse(search),
  loaderDeps: ({ search }) => ({ q: search.q, tech: search.tech, category: search.category, sort: search.sort }),
  loader: async ({ deps }) => {
    const [projects, facets, allProjects] = await Promise.all([
      getProjects({ search: deps.q, tech: deps.tech, category: deps.category, sort: deps.sort }),
      getProjectFacets(),
      getProjects(),
    ]);
    return { projects, facets, total: allProjects.length };
  },
  head: () =>
    buildHead({
      title: 'Project archive',
      description:
        'Every project Adam Rahman has written up — payment ledgers, logistics platforms, clinical dashboards, design systems and open source. Search, filter by technology and sort by year.',
      path: '/projects',
    }),
  component: ProjectsPage,
});

function ProjectsPage() {
  const { projects, facets, total } = Route.useLoaderData();
  const search = Route.useSearch();
  const navigate = Route.useNavigate();
  const { ProjectArchive } = useTemplatePages();

  const handleSearchChange = (patch: Partial<ArchiveSearch>): void => {
    void navigate({ search: (previous) => ({ ...previous, ...patch }), replace: true, resetScroll: false });
  };

  return (
    <ProjectArchive
      projects={projects}
      facets={facets}
      total={total}
      search={search}
      onSearchChange={handleSearchChange}
    />
  );
}
