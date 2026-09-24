import { Link } from '@/lib/router';
import { Plus } from 'lucide-react';
import { useState, type CSSProperties } from 'react';
import { Tag } from '@/templates/changelog/components/ui/Tag';
import { useTranslation } from '@/hooks/useTranslation';
import type { CareerEntry } from '@/lib/content';
import { cn, durationInMonths, formatMonth } from '@/lib/utils';
import type { Branch } from '@/types/content';
import {
    buildCommitGraph,
    LANE_ORDER,
    type GraphRow,
    type LaneSegment,
} from './buildCommitGraph';

const LANE_START = 12;
const LANE_GAP = 22;
const GUTTER_WIDTH = LANE_START * 2 + LANE_GAP * (LANE_ORDER.length - 1);
const DOT_Y = 34;

const laneX = (lane: Branch): number =>
    LANE_START + LANE_ORDER.indexOf(lane) * LANE_GAP;

const laneColor: Record<Branch, string> = {
    main: 'var(--electric)',
    freelance: 'var(--coral)',
    oss: 'var(--aqua)',
};

function LaneLines({ segment }: { segment: LaneSegment }) {
    const x = laneX(segment.lane);
    const base: CSSProperties = {
        insetInlineStart: x - 0.75,
        width: 1.5,
        backgroundColor: laneColor[segment.lane],
    };

    return (
        <>
            {segment.top && (
                <span
                    className={cn(
                        'absolute top-0',
                        segment.open &&
                            '[mask-image:linear-gradient(to_bottom,transparent,black_70%)]',
                    )}
                    style={{ ...base, height: DOT_Y }}
                />
            )}
            {segment.bottom && (
                <span
                    className="absolute bottom-0"
                    style={{ ...base, top: DOT_Y }}
                />
            )}
        </>
    );
}

function LaneCurves({ segments }: { segments: LaneSegment[] }) {
    const mainX = laneX('main');
    const curves = segments.filter((segment) => segment.fork || segment.merge);
    if (curves.length === 0) return null;

    return (
        <svg
            aria-hidden
            className="absolute rtl:-scale-x-100"
            style={{
                insetInlineStart: 0,
                top: DOT_Y,
                bottom: 0,
                width: GUTTER_WIDTH,
                height: `calc(100% - ${DOT_Y}px)`,
            }}
            viewBox={`0 0 ${GUTTER_WIDTH} 100`}
            preserveAspectRatio="none"
            fill="none"
        >
            {curves.map((segment) => {
                const x = laneX(segment.lane);
                const path = segment.fork
                    ? `M ${mainX} 100 C ${mainX} 35, ${x} 55, ${x} 0`
                    : `M ${x} 100 C ${x} 55, ${mainX} 35, ${mainX} 0`;
                return (
                    <path
                        key={`${segment.lane}-${segment.fork ? 'fork' : 'merge'}`}
                        d={path}
                        stroke={laneColor[segment.lane]}
                        strokeWidth={1.5}
                        vectorEffect="non-scaling-stroke"
                    />
                );
            })}
        </svg>
    );
}

function CommitDot({
    segment,
    isHead,
}: {
    segment: LaneSegment;
    isHead: boolean;
}) {
    const x = laneX(segment.lane);

    return (
        <span
            className={cn(
                'absolute size-[11px] rounded-full border-2',
                isHead
                    ? 'animate-pulse-dot border-signal bg-signal'
                    : 'bg-paper',
            )}
            style={{
                insetInlineStart: x - 5.5,
                top: DOT_Y - 5.5,
                borderColor: isHead ? undefined : laneColor[segment.lane],
            }}
        />
    );
}

function GraphGutter({ row }: { row: GraphRow }) {
    return (
        <div
            aria-hidden
            className="relative shrink-0"
            style={{ width: GUTTER_WIDTH }}
        >
            {row.segments.map((segment) => (
                <LaneLines key={segment.lane} segment={segment} />
            ))}
            <LaneCurves segments={row.segments} />
            {row.segments
                .filter((segment) => segment.dot)
                .map((segment) => (
                    <CommitDot
                        key={segment.lane}
                        segment={segment}
                        isHead={row.isHead}
                    />
                ))}
        </div>
    );
}

const formatDuration = (months: number): string => {
    const years = Math.floor(months / 12);
    const remainder = months % 12;
    return [years > 0 ? `${years}y` : '', remainder > 0 ? `${remainder}m` : '']
        .filter(Boolean)
        .join(' ');
};

function CommitRow({ row }: { row: GraphRow }) {
    const { t } = useTranslation();
    const [isExpanded, setIsExpanded] = useState(row.isHead);
    const { entry } = row;
    const detailsId = `commit-${entry.id}`;
    const range = `${formatMonth(entry.start)} — ${entry.end ? formatMonth(entry.end) : t('career.present')}`;

    return (
        <li className="flex">
            <GraphGutter row={row} />
            <div className="min-w-0 flex-1 border-b border-line py-6 ps-3 md:ps-6">
                <div className="grid gap-x-8 gap-y-3 md:grid-cols-[12rem_minmax(0,1fr)]">
                    <div className="flex flex-wrap items-center gap-2 md:flex-col md:items-start md:gap-2.5">
                        <span className="flex items-center gap-2">
                            <code className="ltr-isolate font-mono text-xs text-[#85570f] dark:text-[#f0c56b]">
                                {entry.commit}
                            </code>
                            <span className="version-label">
                                {entry.version}
                            </span>
                        </span>
                        {row.isHead && (
                            <span className="ltr-isolate rounded-[3px] bg-signal-soft px-1.5 py-0.5 font-mono text-[0.625rem] font-semibold text-signal-ink">
                                {t('career.head')} → main
                            </span>
                        )}
                        {entry.branch !== 'main' && (
                            <span className="ltr-isolate font-mono text-[0.6875rem] text-ink-subtle">
                                ⎇ {t(`career.branch.${entry.branch}`)}
                            </span>
                        )}
                        <span className="text-[0.8125rem] text-ink-muted">
                            <time dateTime={entry.start}>{range}</time>
                            <span className="ltr-isolate ms-2 font-mono text-[0.6875rem] text-ink-subtle">
                                {formatDuration(
                                    durationInMonths(entry.start, entry.end),
                                )}
                            </span>
                        </span>
                    </div>

                    <div lang="en">
                        <h3 className="font-display text-[1.625rem] leading-tight text-ink md:text-[2rem]">
                            {entry.role}
                            <span className="text-ink-subtle">
                                {' '}
                                @ {entry.organization}
                            </span>
                        </h3>
                        <p className="ltr-isolate mt-1.5 font-mono text-xs text-ink-subtle">
                            {entry.message}
                        </p>
                        <p className="mt-3 max-w-2xl text-[0.9375rem] leading-relaxed text-ink-muted">
                            {entry.summary}
                        </p>

                        <button
                            type="button"
                            aria-expanded={isExpanded}
                            aria-controls={detailsId}
                            onClick={() => setIsExpanded((current) => !current)}
                            className="mt-4 inline-flex items-center gap-1.5 text-[0.8125rem] font-medium text-ink hover:text-signal-ink"
                        >
                            <Plus
                                aria-hidden
                                className={cn(
                                    'size-3.5 transition-transform duration-300',
                                    isExpanded && 'rotate-45',
                                )}
                                strokeWidth={2.25}
                            />
                            {isExpanded
                                ? t('career.hideDetails')
                                : t('career.showDetails')}
                        </button>

                        <div
                            id={detailsId}
                            inert={!isExpanded}
                            className={cn(
                                'grid transition-[grid-template-rows,opacity] duration-500 ease-out',
                                isExpanded
                                    ? 'grid-rows-[1fr] opacity-100'
                                    : 'grid-rows-[0fr] opacity-0',
                            )}
                        >
                            <div className="overflow-hidden">
                                <div className="grid gap-6 pt-5 lg:grid-cols-[minmax(0,1fr)_16rem]">
                                    <div>
                                        <h4 className="eyebrow mb-3 text-ink-subtle">
                                            {t('career.highlights')}
                                        </h4>
                                        <ul className="flex flex-col gap-2.5">
                                            {entry.highlights.map(
                                                (highlight) => (
                                                    <li
                                                        key={highlight}
                                                        className="flex gap-3 text-[0.9375rem] leading-relaxed text-ink"
                                                    >
                                                        <span
                                                            aria-hidden
                                                            className="font-mono text-signal-ink"
                                                        >
                                                            +
                                                        </span>
                                                        {highlight}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </div>
                                    <div className="flex flex-col gap-5">
                                        <div>
                                            <h4 className="eyebrow mb-3 text-ink-subtle">
                                                {t('career.stack')}
                                            </h4>
                                            <ul className="flex flex-wrap gap-1.5">
                                                {entry.stack.map(
                                                    (technology) => (
                                                        <li key={technology}>
                                                            <Tag>
                                                                {technology}
                                                            </Tag>
                                                        </li>
                                                    ),
                                                )}
                                            </ul>
                                        </div>
                                        {entry.projects.length > 0 && (
                                            <div>
                                                <h4 className="eyebrow mb-2 text-ink-subtle">
                                                    {t('career.projects')}
                                                </h4>
                                                <ul className="flex flex-col gap-1">
                                                    {entry.projects.map(
                                                        (project) => (
                                                            <li
                                                                key={
                                                                    project.slug
                                                                }
                                                            >
                                                                <Link
                                                                    to="/projects/$slug"
                                                                    params={{
                                                                        slug: project.slug,
                                                                    }}
                                                                    className="link-draw text-[0.9375rem] font-medium text-ink"
                                                                >
                                                                    {
                                                                        project.title
                                                                    }{' '}
                                                                    →
                                                                </Link>
                                                            </li>
                                                        ),
                                                    )}
                                                </ul>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    );
}

function GraphLegend() {
    const { t } = useTranslation();

    return (
        <ul
            aria-hidden
            className="mb-6 flex flex-wrap gap-5 font-mono text-[0.6875rem] text-ink-subtle"
        >
            {LANE_ORDER.map((lane) => (
                <li key={lane} className="ltr-isolate flex items-center gap-2">
                    <span
                        className="h-px w-5"
                        style={{
                            backgroundColor: laneColor[lane],
                            height: 1.5,
                        }}
                    />
                    {t(`career.branch.${lane}`)}
                </li>
            ))}
        </ul>
    );
}

export function CareerGraph({ entries }: { entries: CareerEntry[] }) {
    const { t } = useTranslation();
    const rows = buildCommitGraph(entries);

    return (
        <div>
            <GraphLegend />
            <ol
                aria-label={t('career.graphLabel')}
                className="border-t border-line"
            >
                {rows.map((row) => (
                    <CommitRow key={row.entry.id} row={row} />
                ))}
            </ol>
        </div>
    );
}
