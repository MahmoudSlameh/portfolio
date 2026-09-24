import { cn } from '@/lib/utils';

interface TapeProps {
    items: string[];
    className?: string;
    reverse?: boolean;
}

function Tape({ items, className, reverse = false }: TapeProps) {
    return (
        <div
            className={cn(
                'border-edge overflow-hidden border-y-2 py-3',
                className,
            )}
            dir="ltr"
        >
            <div
                className="pg-marquee"
                style={reverse ? { animationDirection: 'reverse' } : undefined}
            >
                {[0, 1].map((copyIndex) => (
                    <ul key={copyIndex} className="flex shrink-0 items-center">
                        {items.map((item) => (
                            <li
                                key={`${copyIndex}-${item}`}
                                className="font-display flex items-center gap-6 pe-6 text-2xl font-black whitespace-nowrap uppercase [font-stretch:125%] md:text-3xl"
                            >
                                {item}
                                <span aria-hidden>✺</span>
                            </li>
                        ))}
                    </ul>
                ))}
            </div>
        </div>
    );
}

export function TapeMarquee({ items }: { items: string[] }) {
    return (
        <div
            aria-hidden
            className="relative my-10 h-40 overflow-hidden md:h-44"
        >
            <Tape
                items={items}
                reverse
                className="bg-pop-pink text-on-pop absolute inset-x-[-5%] top-8 rotate-[3deg]"
            />
            <Tape
                items={items}
                className="bg-ink text-paper absolute inset-x-[-5%] top-12 rotate-[-2.5deg]"
            />
        </div>
    );
}
