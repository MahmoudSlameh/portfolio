import { Maximize2, Minus, Plus } from 'lucide-react';
import { useEffect, useId, useRef, useState, type PointerEvent } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import type {
    Architecture,
    ArchitectureNode,
    ArchitectureNodeKind,
} from '@/types/content';

const COLUMN_WIDTH = 286;
const ROW_HEIGHT = 118;
const NODE_WIDTH = 196;
const NODE_HEIGHT = 66;
const PADDING = 16;
const MIN_SCALE = 1;
const MAX_SCALE = 3;
const ZOOM_STEP = 1.4;

interface Offset {
    x: number;
    y: number;
}

interface NodeStyle {
    fill: string;
    stroke: string;
    dash?: string;
    strokeWidth: number;
}

const nodeStyles: Record<ArchitectureNodeKind, NodeStyle> = {
    client: {
        fill: 'var(--raised)',
        stroke: 'var(--line-strong)',
        strokeWidth: 1.25,
    },
    service: { fill: 'var(--raised)', stroke: 'var(--ink)', strokeWidth: 1.5 },
    store: { fill: 'var(--surface)', stroke: 'var(--ink)', strokeWidth: 1.25 },
    queue: {
        fill: 'var(--raised)',
        stroke: 'var(--signal-ink)',
        dash: '5 4',
        strokeWidth: 1.5,
    },
    external: {
        fill: 'transparent',
        stroke: 'var(--ink-subtle)',
        dash: '2 3',
        strokeWidth: 1.25,
    },
};

const nodeOrigin = (node: ArchitectureNode): { x: number; y: number } => ({
    x: PADDING + node.column * COLUMN_WIDTH + (COLUMN_WIDTH - NODE_WIDTH) / 2,
    y: PADDING + node.row * ROW_HEIGHT + (ROW_HEIGHT - NODE_HEIGHT) / 2,
});

const edgePath = (
    from: ArchitectureNode,
    to: ArchitectureNode,
): { d: string; labelX: number; labelY: number } => {
    const start = nodeOrigin(from);
    const end = nodeOrigin(to);

    if (from.column === to.column) {
        const x = start.x + NODE_WIDTH / 2;
        const goingDown = to.row > from.row;
        const y1 = goingDown ? start.y + NODE_HEIGHT : start.y;
        const y2 = goingDown ? end.y : end.y + NODE_HEIGHT;
        return {
            d: `M ${x} ${y1} L ${x} ${y2}`,
            labelX: x,
            labelY: (y1 + y2) / 2,
        };
    }

    const goingRight = to.column > from.column;
    const x1 = goingRight ? start.x + NODE_WIDTH : start.x;
    const x2 = goingRight ? end.x : end.x + NODE_WIDTH;
    const y1 = start.y + NODE_HEIGHT / 2;
    const y2 = end.y + NODE_HEIGHT / 2;
    const midX = (x1 + x2) / 2;

    return {
        d:
            y1 === y2
                ? `M ${x1} ${y1} L ${x2} ${y2}`
                : `M ${x1} ${y1} L ${midX} ${y1} L ${midX} ${y2} L ${x2} ${y2}`,
        labelX: midX,
        labelY: y2,
    };
};

/**
 * The panel numbers cells from 1 while older seed data numbers them from 0, so the grid is
 * derived from the nodes themselves: the top-left node always lands in the first cell.
 */
const normalizeNodes = (
    nodes: ArchitectureNode[],
): { nodes: ArchitectureNode[]; columns: number; rows: number } => {
    if (nodes.length === 0) {
        return { nodes, columns: 0, rows: 0 };
    }

    const columns = nodes.map((node) => node.column);
    const rows = nodes.map((node) => node.row);
    const firstColumn = Math.min(...columns);
    const firstRow = Math.min(...rows);

    return {
        nodes: nodes.map((node) => ({
            ...node,
            column: node.column - firstColumn,
            row: node.row - firstRow,
        })),
        columns: Math.max(...columns) - firstColumn + 1,
        rows: Math.max(...rows) - firstRow + 1,
    };
};

const clamp = (value: number, min: number, max: number): number =>
    Math.min(Math.max(value, min), max);

function DiagramNode({ node }: { node: ArchitectureNode }) {
    const { x, y } = nodeOrigin(node);
    const style = nodeStyles[node.kind];

    return (
        <g>
            <rect
                x={x}
                y={y}
                width={NODE_WIDTH}
                height={NODE_HEIGHT}
                rx={node.kind === 'client' ? 10 : 3}
                fill={style.fill}
                stroke={style.stroke}
                strokeWidth={style.strokeWidth}
                strokeDasharray={style.dash}
            />
            {node.kind === 'store' && (
                <line
                    x1={x}
                    x2={x + NODE_WIDTH}
                    y1={y + 8}
                    y2={y + 8}
                    stroke="var(--ink)"
                    strokeWidth={0.75}
                />
            )}
            {node.kind === 'service' && (
                <rect
                    x={x}
                    y={y}
                    width={3}
                    height={NODE_HEIGHT}
                    fill="var(--signal)"
                />
            )}
            <text
                x={x + 14}
                y={y + 22}
                fontFamily="var(--font-mono)"
                fontSize={9.5}
                fill="var(--ink-subtle)"
                letterSpacing={0.6}
            >
                {node.kind.toUpperCase()}
            </text>
            <text
                x={x + 14}
                y={y + 40}
                fontFamily="var(--font-display)"
                fontSize={14}
                fontWeight={600}
                fill="var(--ink)"
            >
                {node.label}
            </text>
            <text
                x={x + 14}
                y={y + 56}
                fontFamily="var(--font-sans)"
                fontSize={11}
                fill="var(--ink-muted)"
            >
                {node.detail}
            </text>
        </g>
    );
}

/**
 * Pan and zoom for the diagram viewport. Dragging is clamped so the diagram never leaves the
 * frame; at 1× it fits the frame exactly and the page scrolls as usual.
 */
function usePanZoom() {
    const viewportRef = useRef<HTMLDivElement>(null);
    const dragRef = useRef<{ pointerId: number; x: number; y: number } | null>(
        null,
    );
    const [scale, setScale] = useState(MIN_SCALE);
    const [offset, setOffset] = useState<Offset>({ x: 0, y: 0 });

    const clampOffset = (next: Offset, nextScale: number): Offset => {
        const viewport = viewportRef.current;
        if (!viewport) return next;
        const { clientWidth, clientHeight } = viewport;
        return {
            x: clamp(next.x, clientWidth * (1 - nextScale), 0),
            y: clamp(next.y, clientHeight * (1 - nextScale), 0),
        };
    };

    const zoomAt = (factor: number, anchor?: Offset): void => {
        const viewport = viewportRef.current;
        if (!viewport) return;
        const point = anchor ?? {
            x: viewport.clientWidth / 2,
            y: viewport.clientHeight / 2,
        };
        const nextScale = clamp(scale * factor, MIN_SCALE, MAX_SCALE);
        const ratio = nextScale / scale;
        setScale(nextScale);
        setOffset(
            clampOffset(
                {
                    x: point.x - (point.x - offset.x) * ratio,
                    y: point.y - (point.y - offset.y) * ratio,
                },
                nextScale,
            ),
        );
    };

    const reset = (): void => {
        setScale(MIN_SCALE);
        setOffset({ x: 0, y: 0 });
    };

    // Ctrl/⌘ + wheel (and trackpad pinch) zooms at the cursor; a plain wheel keeps scrolling the page.
    const zoomAtRef = useRef(zoomAt);
    useEffect(() => {
        zoomAtRef.current = zoomAt;
    });
    useEffect(() => {
        const viewport = viewportRef.current;
        if (!viewport) return;
        const handleWheel = (event: WheelEvent): void => {
            if (!event.ctrlKey && !event.metaKey) return;
            event.preventDefault();
            const bounds = viewport.getBoundingClientRect();
            zoomAtRef.current(Math.exp(-event.deltaY * 0.01), {
                x: event.clientX - bounds.left,
                y: event.clientY - bounds.top,
            });
        };
        viewport.addEventListener('wheel', handleWheel, { passive: false });
        return () => viewport.removeEventListener('wheel', handleWheel);
    }, []);

    const endDrag = (): void => {
        dragRef.current = null;
    };

    const handlers = {
        onPointerDown: (event: PointerEvent<HTMLDivElement>): void => {
            if (scale === MIN_SCALE || event.button !== 0) return;
            event.currentTarget.setPointerCapture(event.pointerId);
            dragRef.current = {
                pointerId: event.pointerId,
                x: event.clientX - offset.x,
                y: event.clientY - offset.y,
            };
        },
        onPointerMove: (event: PointerEvent<HTMLDivElement>): void => {
            const drag = dragRef.current;
            if (!drag || drag.pointerId !== event.pointerId) return;
            setOffset(
                clampOffset(
                    { x: event.clientX - drag.x, y: event.clientY - drag.y },
                    scale,
                ),
            );
        },
        onPointerUp: endDrag,
        onPointerCancel: endDrag,
    };

    return { viewportRef, scale, offset, zoomAt, reset, handlers };
}

const zoomButtonClassName =
    'inline-flex size-8 items-center justify-center text-ink-muted transition-colors hover:bg-surface hover:text-ink disabled:pointer-events-none disabled:opacity-40';

export function ArchitectureDiagram({
    architecture,
}: {
    architecture: Architecture;
}) {
    const id = useId();
    const { t } = useTranslation();
    const { viewportRef, scale, offset, zoomAt, reset, handlers } =
        usePanZoom();
    const { nodes, columns, rows } = normalizeNodes(architecture.nodes);
    const width = columns * COLUMN_WIDTH + PADDING * 2;
    const height = rows * ROW_HEIGHT + PADDING * 2;
    const nodesById = new Map(nodes.map((node) => [node.id, node]));
    const markerId = `${id}-arrow`;
    const isZoomed = scale > MIN_SCALE;

    const edges = architecture.edges.flatMap((edge) => {
        const from = nodesById.get(edge.from);
        const to = nodesById.get(edge.to);
        return from && to ? [{ edge, from, to, ...edgePath(from, to) }] : [];
    });

    return (
        <figure dir="ltr" lang="en">
            <div className="relative overflow-hidden border border-line bg-paper [background-image:radial-gradient(var(--line)_1px,transparent_1px)] [background-size:16px_16px]">
                <div
                    ref={viewportRef}
                    {...handlers}
                    className={
                        isZoomed
                            ? 'cursor-grab touch-none select-none active:cursor-grabbing'
                            : undefined
                    }
                >
                    <svg
                        role="img"
                        aria-labelledby={`${id}-title ${id}-desc`}
                        viewBox={`0 0 ${width} ${height}`}
                        className="block h-auto w-full origin-top-left"
                        style={{
                            transform: `translate(${offset.x}px, ${offset.y}px) scale(${scale})`,
                        }}
                    >
                        <title id={`${id}-title`}>{architecture.caption}</title>
                        <desc id={`${id}-desc`}>
                            {edges
                                .map(
                                    ({ edge, from, to }) =>
                                        `${from.label} to ${to.label}${edge.label ? ` (${edge.label})` : ''}`,
                                )
                                .join('; ')}
                        </desc>
                        <defs>
                            <marker
                                id={markerId}
                                viewBox="0 0 10 10"
                                refX="9"
                                refY="5"
                                markerWidth="7"
                                markerHeight="7"
                                orient="auto-start-reverse"
                            >
                                <path
                                    d="M 0 0 L 10 5 L 0 10 z"
                                    fill="var(--ink-muted)"
                                />
                            </marker>
                        </defs>
                        {edges.map(({ edge, d }) => (
                            <path
                                key={`${edge.from}-${edge.to}`}
                                d={d}
                                fill="none"
                                stroke="var(--ink-muted)"
                                strokeWidth={1.25}
                                markerEnd={`url(#${markerId})`}
                            />
                        ))}
                        {nodes.map((node) => (
                            <DiagramNode key={node.id} node={node} />
                        ))}
                        {edges
                            .filter(({ edge }) => edge.label)
                            .map(({ edge, labelX, labelY }) => (
                                <g key={`${edge.from}-${edge.to}-label`}>
                                    <rect
                                        x={
                                            labelX -
                                            (edge.label?.length ?? 0) * 3.2 -
                                            6
                                        }
                                        y={labelY - 9}
                                        width={
                                            (edge.label?.length ?? 0) * 6.4 + 12
                                        }
                                        height={18}
                                        rx={2}
                                        fill="var(--paper)"
                                        stroke="var(--line)"
                                    />
                                    <text
                                        x={labelX}
                                        y={labelY + 3.5}
                                        textAnchor="middle"
                                        fontFamily="var(--font-mono)"
                                        fontSize={10}
                                        fill="var(--ink-muted)"
                                    >
                                        {edge.label}
                                    </text>
                                </g>
                            ))}
                    </svg>
                </div>
                <div className="absolute right-2 bottom-2 flex divide-x divide-line border border-line bg-raised">
                    <button
                        type="button"
                        onClick={() => zoomAt(1 / ZOOM_STEP)}
                        disabled={!isZoomed}
                        aria-label={t('diagram.zoomOut')}
                        className={zoomButtonClassName}
                    >
                        <Minus aria-hidden className="size-3.5" />
                    </button>
                    <button
                        type="button"
                        onClick={reset}
                        disabled={!isZoomed}
                        aria-label={t('diagram.reset')}
                        className={zoomButtonClassName}
                    >
                        <Maximize2 aria-hidden className="size-3.5" />
                    </button>
                    <button
                        type="button"
                        onClick={() => zoomAt(ZOOM_STEP)}
                        disabled={scale >= MAX_SCALE}
                        aria-label={t('diagram.zoomIn')}
                        className={zoomButtonClassName}
                    >
                        <Plus aria-hidden className="size-3.5" />
                    </button>
                </div>
            </div>
            <figcaption className="mt-3 flex gap-3 text-sm text-ink-muted">
                <span className="font-mono text-xs text-ink-subtle">Fig.</span>
                {architecture.caption}
            </figcaption>
        </figure>
    );
}
