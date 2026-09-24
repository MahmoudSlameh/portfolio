import { Children, type CSSProperties, type ReactNode } from 'react';
import { cn } from '@/lib/utils';

interface MarqueeProps {
  children: ReactNode;
  reverse?: boolean;
  duration?: number;
  className?: string;
  label?: string;
}

/** Infinite horizontal ticker. The track is rendered twice so the loop is seamless. */
export function Marquee({ children, reverse = false, duration = 30, className, label }: MarqueeProps) {
  const style = { '--tm-duration': `${duration}s`, '--tm-direction': reverse ? 'reverse' : 'normal' } as CSSProperties;
  const items = Children.toArray(children);

  return (
    <div className={cn('tm-marquee', className)} style={style}>
      <div className="tm-marquee-track" role="list" aria-label={label}>
        {items}
      </div>
      <div className="tm-marquee-track" aria-hidden>
        {items}
      </div>
    </div>
  );
}
