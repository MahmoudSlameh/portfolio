import type { ElementType, ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface GlowCardProps {
  as?: ElementType;
  className?: string;
  innerClassName?: string;
  children: ReactNode;
  id?: string;
  'aria-labelledby'?: string;
}

/** Bordered card whose green edge slowly orbits the box (the "box-linear-animation" look). */
export function GlowCard({ as: Tag = 'div', className, innerClassName, children, ...rest }: GlowCardProps) {
  return (
    <Tag {...rest} className={cn('relative overflow-hidden rounded-lg border border-tm-border', className)}>
      <div className={cn('tm-glow relative h-full', innerClassName)}>{children}</div>
    </Tag>
  );
}
