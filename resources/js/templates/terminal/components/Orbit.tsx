import { cn } from '@/lib/utils';

/** Three concentric rotating rings with two satellites, used as corner decoration. */
export function Orbit({ className }: { className?: string }) {
    return (
        <div
            aria-hidden
            className={cn(
                'pointer-events-none absolute hidden md:block',
                className,
            )}
        >
            <div className="tm-rotate relative size-[210px]">
                <div className="size-[210px] rounded-full border border-tm-mint/60" />
                <div className="absolute top-1/2 left-1/2 size-[124px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-tm-mint/60">
                    <span className="absolute top-[82%] left-[88%] size-2 rounded-full bg-tm-400" />
                </div>
                <div className="absolute top-1/2 left-1/2 size-[82px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-tm-mint/60">
                    <span className="absolute -top-1 left-1/2 size-2 -translate-x-1/2 rounded-full bg-tm-400" />
                </div>
            </div>
        </div>
    );
}
