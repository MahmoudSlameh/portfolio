import { usePlaygroundCopy } from '../copy';
import { PopButton } from './PopButton';

export function EmptyNote({
    onReset,
    resetLabel,
}: {
    onReset: () => void;
    resetLabel: string;
}) {
    const p = usePlaygroundCopy();

    return (
        <div
            role="status"
            className="pg-card mx-auto flex max-w-lg rotate-[-1.5deg] flex-col items-center gap-4 bg-pop-pink px-8 py-12 text-center text-on-pop"
        >
            <span aria-hidden className="pg-display text-6xl">
                ¯\_(ツ)_/¯
            </span>
            <p className="pg-display pg-keep-case text-3xl">
                {p('empty.title')}
            </p>
            <p className="text-base">{p('empty.body')}</p>
            <PopButton tone="plain" onClick={onReset}>
                {resetLabel}
            </PopButton>
        </div>
    );
}
