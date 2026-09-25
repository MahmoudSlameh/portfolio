import { useId } from 'react';
import type {
    Architecture,
    ArchitectureNode,
    ArchitectureNodeKind,
} from '@/types/content';

const COLUMN_WIDTH = 210;
const ROW_HEIGHT = 118;
const NODE_WIDTH = 164;
const NODE_HEIGHT = 66;
const PADDING = 16;

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
        labelY: y1 === y2 ? y1 : (y1 + y2) / 2,
    };
};

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

export function ArchitectureDiagram({
    architecture,
}: {
    architecture: Architecture;
}) {
    const id = useId();
    const width = architecture.columns * COLUMN_WIDTH + PADDING * 2;
    const height = architecture.rows * ROW_HEIGHT + PADDING * 2;
    const nodesById = new Map(
        architecture.nodes.map((node) => [node.id, node]),
    );
    const markerId = `${id}-arrow`;

    const edges = architecture.edges.flatMap((edge) => {
        const from = nodesById.get(edge.from);
        const to = nodesById.get(edge.to);
        return from && to ? [{ edge, from, to, ...edgePath(from, to) }] : [];
    });

    return (
        <figure dir="ltr" lang="en">
            <div className="overflow-x-auto border border-line bg-paper [background-image:radial-gradient(var(--line)_1px,transparent_1px)] [background-size:16px_16px]">
                <svg
                    role="img"
                    aria-labelledby={`${id}-title ${id}-desc`}
                    viewBox={`0 0 ${width} ${height}`}
                    className="block h-auto w-full"
                    style={{ minWidth: Math.min(width, 640) }}
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
                    {architecture.nodes.map((node) => (
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
                                    width={(edge.label?.length ?? 0) * 6.4 + 12}
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
            <figcaption className="mt-3 flex gap-3 text-sm text-ink-muted">
                <span className="font-mono text-xs text-ink-subtle">Fig.</span>
                {architecture.caption}
            </figcaption>
        </figure>
    );
}
