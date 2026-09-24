import { ArrowUp } from 'lucide-react';
import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';
import { useTerminalCopy } from '../copy';

const PERIMETER = 152;

/** Square back-to-top button whose outline fills as the page scrolls. */
export function ScrollTop() {
  const c = useTerminalCopy();
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    let frame = 0;
    const update = (): void => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        setProgress(max > 0 ? window.scrollY / max : 0);
      });
    };
    update();
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    return () => {
      cancelAnimationFrame(frame);
      window.removeEventListener('scroll', update);
      window.removeEventListener('resize', update);
    };
  }, []);

  const visible = progress > 0.04;

  return (
    <button
      type="button"
      aria-label={c('footer.top')}
      tabIndex={visible ? 0 : -1}
      aria-hidden={!visible}
      onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
      className={cn(
        'tm-scroll-top fixed end-6 bottom-6 z-30 flex size-12 items-center justify-center rounded-[10px] bg-white text-[#62a92b] shadow-[inset_0_0_0_0.1rem_rgb(227_229_233/0.25)]',
        visible ? 'translate-y-0 opacity-100' : 'pointer-events-none translate-y-3 opacity-0',
      )}
    >
      <svg aria-hidden viewBox="0 0 40 40" className="absolute inset-0 size-full">
        <path
          d="M8 1H32C35.866 1 39 4.13401 39 8V32C39 35.866 35.866 39 32 39H8C4.13401 39 1 35.866 1 32V8C1 4.13401 4.13401 1 8 1Z"
          fill="none"
          stroke="#62a92b"
          strokeWidth="1.2"
          strokeDasharray={PERIMETER}
          strokeDashoffset={PERIMETER * (1 - progress)}
        />
      </svg>
      <ArrowUp aria-hidden className="relative size-5" />
    </button>
  );
}
