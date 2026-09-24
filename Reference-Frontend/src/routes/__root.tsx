import { createRootRoute, HeadContent, Outlet } from '@tanstack/react-router';
import { MotionConfig } from 'motion/react';
import { getProfile, getSearchIndex, getSiteSettings, getSocials } from '@/lib/content';
import { buildHead, siteConfig } from '@/lib/seo';
import { activateTemplate, resolveTemplateId } from '@/templates/activation';
import { loadTemplate } from '@/templates/registry';
import { TemplateNotFound } from '@/templates/TemplateNotFound';

export const Route = createRootRoute({
  loader: async ({ location }) => {
    const settings = await getSiteSettings();
    const templateId = resolveTemplateId(settings.activeTemplate, location.searchStr);
    const [template, profile, socials, searchIndex] = await Promise.all([
      loadTemplate(templateId),
      getProfile(),
      getSocials(),
      getSearchIndex(),
    ]);
    activateTemplate(template);
    return { template, profile, socials, searchIndex };
  },
  head: () =>
    buildHead({
      title: 'This route was never deployed',
      description: `${siteConfig.author} is a staff software engineer building payments, logistics and clinical systems.`,
      path: '/404',
    }),
  component: RootLayout,
  notFoundComponent: TemplateNotFound,
});

function RootLayout() {
  const { template, profile, socials, searchIndex } = Route.useLoaderData();
  const { Layout } = template;

  return (
    <MotionConfig reducedMotion="user">
      <HeadContent />
      <Layout profile={profile} socials={socials} searchIndex={searchIndex}>
        <Outlet />
      </Layout>
    </MotionConfig>
  );
}
