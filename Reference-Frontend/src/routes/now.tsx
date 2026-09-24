import { createFileRoute } from '@tanstack/react-router';
import { getNow } from '@/lib/content';
import { buildHead } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/now')({
  loader: async () => ({ now: await getNow() }),
  head: () =>
    buildHead({
      title: 'Now',
      description:
        'What Adam Rahman is focused on right now: shipping multi-currency settlement, growing staff engineers, Quire 1.0, TLA+ and Arabic calligraphy.',
      path: '/now',
    }),
  component: NowRoute,
});

function NowRoute() {
  const { now } = Route.useLoaderData();
  const { Now } = useTemplatePages();
  return <Now now={now} />;
}
