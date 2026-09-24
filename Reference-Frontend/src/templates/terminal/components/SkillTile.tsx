import { cn } from '@/lib/utils';
import { monogram, tintFor } from '../lib/monogram';

interface SkillTileProps {
  name: string;
  size?: 'md' | 'lg';
  tooltip?: boolean;
  className?: string;
}

export function SkillTile({ name, size = 'md', tooltip = false, className }: SkillTileProps) {
  return (
    <span role="listitem" className={cn('tm-has-tooltip relative shrink-0', size === 'lg' ? 'mx-[15px] mt-12' : 'mx-2.5', className)}>
      <span
        aria-hidden
        className={cn('tm-tile ltr-isolate font-medium', size === 'lg' ? 'size-20 text-xl' : 'size-[60px] text-base')}
        style={{ color: tintFor(name) }}
      >
        {monogram(name)}
      </span>
      <span lang="en" className={tooltip ? 'tm-tooltip' : 'sr-only'}>
        {name}
      </span>
    </span>
  );
}
