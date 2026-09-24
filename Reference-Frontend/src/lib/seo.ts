import { imageUrl } from '@/lib/utils';

export const siteConfig = {
  name: 'The Engineer’s Changelog',
  author: 'Adam Rahman',
  url: 'https://adamrahman.dev',
  twitterHandle: '@adamwrites',
  defaultImage: imageUrl('portrait', 864),
} as const;

interface SeoInput {
  title: string;
  description: string;
  path: string;
  type?: 'website' | 'article' | 'profile';
  image?: string;
  imageAlt?: string;
}

type MetaTag = { title: string } | { name: string; content: string } | { property: string; content: string };

export interface HeadConfig {
  meta: MetaTag[];
  links: { rel: string; href: string }[];
}

export const buildHead = ({
  title,
  description,
  path,
  type = 'website',
  image = siteConfig.defaultImage,
  imageAlt = `${siteConfig.author} — ${siteConfig.name}`,
}: SeoInput): HeadConfig => {
  const fullTitle = title === siteConfig.name ? title : `${title} — ${siteConfig.name}`;
  const url = `${siteConfig.url}${path}`;
  const absoluteImage = image.startsWith('http') ? image : `${siteConfig.url}${image}`;

  return {
    meta: [
      { title: fullTitle },
      { name: 'description', content: description },
      { property: 'og:title', content: fullTitle },
      { property: 'og:description', content: description },
      { property: 'og:type', content: type },
      { property: 'og:url', content: url },
      { property: 'og:site_name', content: siteConfig.name },
      { property: 'og:image', content: absoluteImage },
      { property: 'og:image:alt', content: imageAlt },
      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:site', content: siteConfig.twitterHandle },
      { name: 'twitter:title', content: fullTitle },
      { name: 'twitter:description', content: description },
      { name: 'twitter:image', content: absoluteImage },
      { name: 'twitter:image:alt', content: imageAlt },
    ],
    links: [{ rel: 'canonical', href: url }],
  };
};
