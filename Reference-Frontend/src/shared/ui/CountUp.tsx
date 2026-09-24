import { useReducedMotion } from 'motion/react';
import { useEffect, useRef, useState } from 'react';

const DURATION = 1600;

interface ParsedValue {
  prefix: string;
  number: number;
  decimals: number;
  suffix: string;
}

const parseValue = (value: string): ParsedValue | null => {
  const match = /^(\D*)(\d+(?:\.\d+)?)(.*)$/.exec(value);
  if (!match) return null;
  const [, prefix, digits, suffix] = match;
  return { prefix, number: Number(digits), decimals: digits.split('.')[1]?.length ?? 0, suffix };
};

const easeOutExpo = (progress: number): number => (progress === 1 ? 1 : 1 - 2 ** (-10 * progress));

export function CountUp({ value }: { value: string }) {
  const parsed = parseValue(value);
  const reduceMotion = useReducedMotion();
  const elementRef = useRef<HTMLSpanElement>(null);
  const [current, setCurrent] = useState(0);
  const target = parsed?.number ?? null;

  useEffect(() => {
    const element = elementRef.current;
    if (!element || target === null || reduceMotion) return;

    let frame = 0;
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry?.isIntersecting) return;
        observer.disconnect();
        const start = performance.now();
        const tick = (now: number): void => {
          const progress = Math.min(1, (now - start) / DURATION);
          setCurrent(target * easeOutExpo(progress));
          if (progress < 1) frame = requestAnimationFrame(tick);
        };
        frame = requestAnimationFrame(tick);
      },
      { threshold: 0.4 },
    );

    observer.observe(element);
    return () => {
      observer.disconnect();
      cancelAnimationFrame(frame);
    };
  }, [target, reduceMotion]);

  if (!parsed || reduceMotion) return <span>{value}</span>;

  return (
    <span ref={elementRef}>
      <span aria-hidden>
        {parsed.prefix}
        {current.toFixed(parsed.decimals)}
        {parsed.suffix}
      </span>
      <span className="sr-only">{value}</span>
    </span>
  );
}
