import { Link } from '@tanstack/react-router';
import { Shuffle } from 'lucide-react';
import { motion } from 'motion/react';
import { useRef, useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';
import { usePlaygroundCopy } from '../copy';
import { popBg, type Pop } from '../lib/pops';
import { PopButton, popButtonClasses } from '../components/PopButton';
import { Sticker } from '../components/Sticker';

interface Digit {
  id: string;
  char: string;
  pop: Pop;
  rotate: number;
}

const DIGITS: Digit[] = [
  { id: 'first', char: '4', pop: 'yellow', rotate: -8 },
  { id: 'zero', char: '0', pop: 'pink', rotate: 6 },
  { id: 'last', char: '4', pop: 'blue', rotate: -4 },
];

const randomTilt = (): number => Math.round(Math.random() * 30 - 15);

export function NotFoundPage() {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();
  const boardRef = useRef<HTMLDivElement>(null);
  const [tilts, setTilts] = useState<number[]>(() => DIGITS.map((digit) => digit.rotate));
  const [round, setRound] = useState(0);

  const handleShuffle = (): void => {
    setTilts(DIGITS.map(randomTilt));
    setRound((value) => value + 1);
  };

  return (
    <section aria-labelledby="not-found-title" className="pg-shell py-10 md:py-16">
      <div ref={boardRef} className="pg-card relative flex min-h-[26rem] items-center justify-center gap-3 overflow-hidden bg-surface p-8 md:gap-6" dir="ltr">
        {DIGITS.map((digit, index) => (
          <motion.span
            key={`${digit.id}-${round}`}
            aria-hidden
            drag
            dragConstraints={boardRef}
            dragElastic={0.2}
            initial={{ scale: 0.4, rotate: 0, opacity: 0 }}
            animate={{ scale: 1, rotate: tilts[index], opacity: 1 }}
            whileDrag={{ scale: 1.1, cursor: 'grabbing' }}
            transition={{ type: 'spring', stiffness: 260, damping: 14, delay: index * 0.08 }}
            className={cn(
              'pg-display inline-flex h-40 w-28 cursor-grab touch-none items-center justify-center rounded-3xl border-2 border-edge text-[7rem] text-on-pop shadow-[var(--pg-shadow-lg)] select-none sm:h-56 sm:w-40 sm:text-[10rem]',
              popBg[digit.pop],
            )}
          >
            {digit.char}
          </motion.span>
        ))}
        <span className="absolute start-6 top-6">
          <Sticker pop="red" tilt={-6}>
            {p('notFound.kicker')}
          </Sticker>
        </span>
      </div>

      <div className="mt-10 flex flex-col items-center gap-5 text-center">
        <h1 id="not-found-title" className="pg-display pg-keep-case text-[clamp(2.5rem,6vw,4.5rem)] text-ink">
          {p('notFound.title')}
        </h1>
        <p className="max-w-xl text-lg text-ink-muted">{p('notFound.body')}</p>
        <div className="flex flex-wrap justify-center gap-3">
          <Link to="/" className={popButtonClasses({ tone: 'ink' })}>
            {t('notFound.home')}
          </Link>
          <Link to="/projects" className={popButtonClasses({ tone: 'green' })}>
            {t('notFound.projects')}
          </Link>
          <PopButton tone="plain" onClick={handleShuffle}>
            <Shuffle aria-hidden className="size-4" strokeWidth={2.5} />
            {p('notFound.shuffle')}
          </PopButton>
        </div>
      </div>
    </section>
  );
}
