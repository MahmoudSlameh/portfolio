import type { CSSProperties, ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { popBg, type Pop } from '../lib/pops';

interface StickerProps {
  pop?: Pop;
  tilt?: number;
  className?: string;
  children: ReactNode;
}

export function Sticker({ pop = 'yellow', tilt = -4, className, children }: StickerProps) {
  const style = { '--tilt': `${tilt}deg` } as CSSProperties;

  return (
    <span style={style} className={cn('pg-sticker', popBg[pop], className)}>
      {children}
    </span>
  );
}
