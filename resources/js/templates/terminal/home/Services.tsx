import {
    Bot,
    ChartColumn,
    Cloud,
    Code,
    Database,
    Gauge,
    LayoutTemplate,
    Layers,
    MessagesSquare,
    Plug,
    Server,
    ShieldCheck,
    Smartphone,
    Sparkles,
    SquareTerminal,
    Workflow,
    Wrench,
    type LucideIcon,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import type { ServicesSection } from '@/lib/content';
import type { Profile, ServiceIcon } from '@/types/content';
import { useTerminalCopy } from '../copy';
import { Tag } from '../components/Chip';
import { GlowCard } from '../components/GlowCard';
import { Kicker } from '../components/Kicker';

const ICONS: Record<ServiceIcon, LucideIcon> = {
    code: Code,
    server: Server,
    layout: LayoutTemplate,
    database: Database,
    cloud: Cloud,
    integrations: Plug,
    performance: Gauge,
    security: ShieldCheck,
    mobile: Smartphone,
    automation: Workflow,
    platform: Layers,
    ai: Bot,
    maintenance: Wrench,
    consulting: MessagesSquare,
    analytics: ChartColumn,
    terminal: SquareTerminal,
    sparkles: Sparkles,
};

export function Services({
    profile,
    services,
}: {
    profile: Profile;
    services: ServicesSection;
}) {
    const c = useTerminalCopy();
    const { heading, items } = services;

    return (
        <div className="tm-container pt-8">
            <GlowCard
                as="section"
                id="services"
                aria-labelledby="services-title"
            >
                <div
                    aria-hidden
                    className="tm-grid-bg pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_50%_45%_at_0%_0%,#000,transparent)]"
                />
                <div className="relative p-1 md:p-6 lg:p-12">
                    <div className="text-center">
                        <Kicker center>
                            {heading.kicker || c('services.kicker')}
                        </Kicker>
                        <h2
                            id="services-title"
                            className="mt-1 text-[clamp(1.5rem,3vw,2.1875rem)] font-medium"
                        >
                            {heading.title || c('services.titleA')}{' '}
                            {heading.highlight ? (
                                <span className="text-tm-300">
                                    {heading.highlight}
                                </span>
                            ) : (
                                <>
                                    <span className="text-tm-300">
                                        {c('services.titleB')}
                                    </span>
                                    <br />
                                    <span className="text-tm-300">
                                        {c('services.titleC')}
                                    </span>
                                </>
                            )}
                        </h2>
                    </div>

                    <ul
                        className={cn(
                            'mt-12 grid grid-cols-1 gap-6 px-3 md:grid-cols-2',
                            items.length % 3 === 0
                                ? 'xl:grid-cols-3'
                                : 'xl:grid-cols-4',
                        )}
                    >
                        {items.map((service) => {
                            const Icon = ICONS[service.icon] ?? Sparkles;
                            return (
                                <li
                                    key={service.id}
                                    className="tm-box tm-service tm-hover-up flex flex-col rounded-md px-8 pt-[70px] pb-8 xl:px-7"
                                >
                                    <Icon
                                        aria-hidden
                                        className="size-6 text-ink"
                                        strokeWidth={1.75}
                                    />
                                    <h3 className="my-4 text-[19px] font-medium">
                                        {service.title}
                                    </h3>
                                    <p className="mb-0 text-sm leading-relaxed text-tm-300">
                                        {service.summary}
                                    </p>
                                    {service.highlights.length > 0 && (
                                        <ul
                                            aria-label={service.title}
                                            className="mt-auto flex flex-wrap gap-2 pt-6"
                                        >
                                            {service.highlights.map(
                                                (highlight) => (
                                                    <li key={highlight}>
                                                        <Tag className="rounded-md px-2 py-0.5 text-xs text-tm-secondary">
                                                            {highlight}
                                                        </Tag>
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    )}
                                </li>
                            );
                        })}
                    </ul>

                    <p className="mt-[60px] mb-4 text-center text-tm-300">
                        {profile.availability.note}{' '}
                        <a
                            href="#contact"
                            className="text-tm-primary hover:underline"
                        >
                            {c('services.reach')}
                        </a>
                    </p>
                </div>
            </GlowCard>
        </div>
    );
}
