import { createFileRoute, notFound } from '@tanstack/react-router';
import { getProjectBySlug } from '@/lib/content';
import { buildHead } from '@/lib/seo';
import { imageUrl } from '@/lib/utils';
import { TemplateNotFound } from '@/templates/TemplateNotFound';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/projects/$slug')({
  loader: async ({ params }) => {
    const project = await getProjectBySlug(params.slug);
    if (!project) throw notFound();
    return { project };
  },
  head: ({ loaderData, params }) => {
    if (!loaderData) {
      return buildHead({
        title: 'This route was never deployed',
        description: `No case study exists at /projects/${params.slug}.`,
        path: `/projects/${params.slug}`,
      });
    }
    const { project } = loaderData;
    return buildHead({
      title: `${project.title} — case study`,
      description: project.summary,
      path: `/projects/${project.slug}`,
      type: 'article',
      image: imageUrl(project.cover.base, project.cover.width),
      imageAlt: project.cover.alt,
    });
  },
  component: CaseStudyPage,
  notFoundComponent: TemplateNotFound,
});

function CaseStudyPage() {
  const { project } = Route.useLoaderData();
  const { CaseStudy } = useTemplatePages();
  return <CaseStudy key={project.slug} project={project} />;
}
