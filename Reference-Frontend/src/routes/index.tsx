import { createFileRoute } from '@tanstack/react-router';
import {
  getArticles,
  getBooks,
  getCareer,
  getCertifications,
  getCompanies,
  getEducation,
  getProfile,
  getProjects,
  getSkillGroups,
  getSocials,
  getTestimonials,
} from '@/lib/content';
import { buildHead, siteConfig } from '@/lib/seo';
import { useTemplatePages } from '@/templates/useTemplate';

export const Route = createFileRoute('/')({
  loader: async () => {
    const [profile, socials, skillGroups, career, projects, companies, testimonials, education, certifications, articles, books] =
      await Promise.all([
        getProfile(),
        getSocials(),
        getSkillGroups(),
        getCareer(),
        getProjects({ featured: true }),
        getCompanies(),
        getTestimonials(),
        getEducation(),
        getCertifications(),
        getArticles(),
        getBooks(),
      ]);
    return { profile, socials, skillGroups, career, projects, companies, testimonials, education, certifications, articles, books };
  },
  head: () =>
    buildHead({
      title: siteConfig.name,
      description:
        'Adam Rahman is a staff software engineer building calm, well-typed systems for payments, logistics and healthcare. Career, case studies, writing and bookshelf.',
      path: '/',
      type: 'profile',
    }),
  component: HomeRoute,
});

function HomeRoute() {
  const data = Route.useLoaderData();
  const { Home } = useTemplatePages();
  return <Home {...data} />;
}
