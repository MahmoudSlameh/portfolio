import type { UsesGroup } from '@/types/content';

export const usesGroups: UsesGroup[] = [
  {
    id: 'hardware',
    kind: 'hardware',
    title: { en: 'Hardware', ar: 'العتاد' },
    items: [
      { id: 'laptop', name: 'MacBook Pro 14″, M3 Max', description: '64GB RAM. Fast enough that Rust compile times became a coffee, not a lunch.' },
      { id: 'display', name: 'LG UltraFine 5K', description: 'One large, sharp screen instead of two average ones.' },
      { id: 'keyboard', name: 'HHKB Professional Hybrid', description: 'Topre switches, blank keycaps, no arrow keys, no regrets.' },
      { id: 'mouse', name: 'Logitech MX Anywhere 3S', description: 'Small enough for travel, quiet enough for calls.' },
      { id: 'audio', name: 'Shure MV7+ and Sony WH-1000XM5', description: 'A decent microphone is the most respectful purchase for remote teams.' },
      { id: 'desk', name: 'Oak standing desk', description: 'Built by a carpenter in Zamalek. Standing for design reviews, sitting for writing.' },
    ],
  },
  {
    id: 'software',
    kind: 'software',
    title: { en: 'Software', ar: 'البرمجيات' },
    items: [
      { id: 'notes', name: 'Obsidian', description: 'Plain Markdown notes, a daily log and every design doc draft.', url: 'https://obsidian.md' },
      { id: 'figma', name: 'Figma', description: 'Mostly for reading designs and leaving comments, occasionally for diagrams.', url: 'https://figma.com' },
      { id: 'raycast', name: 'Raycast', description: 'Launcher, clipboard history, snippets and window management.', url: 'https://raycast.com' },
      { id: 'things', name: 'Things 3', description: 'A calm to-do list that never sends notifications.', url: 'https://culturedcode.com/things/' },
      { id: 'arc', name: 'Firefox Developer Edition', description: 'For debugging layout and accessibility trees.', url: 'https://www.mozilla.org/firefox/developer/' },
    ],
  },
  {
    id: 'development',
    kind: 'development',
    title: { en: 'Development setup', ar: 'بيئة التطوير' },
    items: [
      { id: 'editor', name: 'Cursor', description: 'Primary editor, with a minimal theme and JetBrains Mono at 14px.', url: 'https://cursor.com' },
      { id: 'terminal', name: 'Ghostty + zsh', description: 'Fast, native terminal with a tiny prompt that only shows git state.', url: 'https://ghostty.org' },
      { id: 'git', name: 'git + lazygit', description: 'Conventional commits, small branches, interactive rebase before every review.' },
      { id: 'containers', name: 'OrbStack', description: 'Lightweight containers and a local Kubernetes cluster.', url: 'https://orbstack.dev' },
      { id: 'db', name: 'TablePlus + psql', description: 'TablePlus for browsing, psql for anything that matters.' },
      { id: 'dotfiles', name: 'Dotfiles on GitHub', description: 'Everything above, reproducible on a new machine in about twenty minutes.', url: 'https://github.com/adamrahman/dotfiles' },
    ],
  },
];
