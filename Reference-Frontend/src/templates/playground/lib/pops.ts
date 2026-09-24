import type { Branch, ProjectCategory, ReadingStatus } from '@/types/content';

export type Pop = 'red' | 'blue' | 'yellow' | 'green' | 'pink' | 'purple';

const POP_ORDER: Pop[] = ['yellow', 'pink', 'blue', 'green', 'red', 'purple'];

export const popBg: Record<Pop, string> = {
  red: 'bg-pop-red',
  blue: 'bg-pop-blue',
  yellow: 'bg-pop-yellow',
  green: 'bg-pop-green',
  pink: 'bg-pop-pink',
  purple: 'bg-pop-purple',
};

export const popText: Record<Pop, string> = {
  red: 'text-pop-red',
  blue: 'text-pop-blue',
  yellow: 'text-pop-yellow',
  green: 'text-pop-green',
  pink: 'text-pop-pink',
  purple: 'text-pop-purple',
};

export const popHoverBg: Record<Pop, string> = {
  red: 'hover:bg-pop-red',
  blue: 'hover:bg-pop-blue',
  yellow: 'hover:bg-pop-yellow',
  green: 'hover:bg-pop-green',
  pink: 'hover:bg-pop-pink',
  purple: 'hover:bg-pop-purple',
};

export const popForIndex = (index: number): Pop => POP_ORDER[index % POP_ORDER.length];

export const categoryPop: Record<ProjectCategory, Pop> = {
  platform: 'blue',
  product: 'red',
  'open-source': 'green',
  'design-system': 'purple',
};

export const branchPop: Record<Branch, Pop> = {
  main: 'blue',
  freelance: 'pink',
  oss: 'green',
};

export const readingPop: Record<ReadingStatus, Pop> = {
  reading: 'yellow',
  read: 'green',
  'to-read': 'pink',
};

const TILTS = [-4, 3, -2, 5, -3, 2];

export const tiltForIndex = (index: number): number => TILTS[index % TILTS.length];
