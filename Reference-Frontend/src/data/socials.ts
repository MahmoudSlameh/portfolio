import type { Social } from '@/types/content';

export const socials: Social[] = [
  { id: 'github', label: 'GitHub', handle: '@adamrahman', url: 'https://github.com/adamrahman', icon: 'github' },
  {
    id: 'linkedin',
    label: 'LinkedIn',
    handle: 'in/adamrahman',
    url: 'https://www.linkedin.com/in/adamrahman',
    icon: 'linkedin',
  },
  { id: 'x', label: 'X', handle: '@adamwrites', url: 'https://x.com/adamwrites', icon: 'x' },
  {
    id: 'mastodon',
    label: 'Mastodon',
    handle: '@adam@hachyderm.io',
    url: 'https://hachyderm.io/@adam',
    icon: 'mastodon',
  },
  { id: 'rss', label: 'RSS', handle: 'Writing feed', url: 'https://adamrahman.dev/rss.xml', icon: 'rss' },
];
