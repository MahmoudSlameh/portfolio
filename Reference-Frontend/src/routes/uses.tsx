import { createFileRoute } from '@tanstack/react-router';
import { getUses } from '@/lib/content';
import { buildHead } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/uses')({
  loader: async () => ({ groups: await getUses() }),
  head: () =>
    buildHead({
      title: 'Uses',
      description:
        'The hardware, software and development setup Adam Rahman uses every day — editor, terminal, keyboard, notes and the dotfiles that tie them together.',
      path: '/uses',
    }),
  component: UsesRoute,
});

function UsesRoute() {
  const { groups } = Route.useLoaderData();
  const { Uses } = useTemplatePages();
  return <Uses groups={groups} />;
}
