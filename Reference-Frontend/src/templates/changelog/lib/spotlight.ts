import type { PointerEvent } from 'react';

export const handleSpotlightMove = (event: PointerEvent<HTMLElement>): void => {
  const target = event.currentTarget;
  const rect = target.getBoundingClientRect();
  target.style.setProperty('--spot-x', `${event.clientX - rect.left}px`);
  target.style.setProperty('--spot-y', `${event.clientY - rect.top}px`);
};
