import type { CareerEntry } from '@/lib/content';
import type { Branch } from '@/types/content';

export const LANE_ORDER: Branch[] = ['main', 'freelance', 'oss'];

export interface LaneSegment {
    lane: Branch;
    top: boolean;
    bottom: boolean;
    dot: boolean;
    fork: boolean;
    merge: boolean;
    open: boolean;
}

export interface GraphRow {
    entry: CareerEntry;
    isHead: boolean;
    segments: LaneSegment[];
}

const emptySegment = (lane: Branch): LaneSegment => ({
    lane,
    top: false,
    bottom: false,
    dot: false,
    fork: false,
    merge: false,
    open: false,
});

const findMergeRow = (
    entries: CareerEntry[],
    newestIndex: number,
    end: string | null,
): number => {
    if (end === null) return -1;
    for (let index = newestIndex - 1; index >= 0; index -= 1) {
        if (entries[index].start > end) return index;
    }
    return 0;
};

const buildMainLane = (entries: CareerEntry[]): LaneSegment[] =>
    entries.map((entry, index) => ({
        ...emptySegment('main'),
        top: index > 0,
        bottom: index < entries.length - 1,
        dot: entry.branch === 'main',
    }));

const buildSideLane = (entries: CareerEntry[], lane: Branch): LaneSegment[] => {
    const segments = entries.map(() => emptySegment(lane));
    const commitRows = entries.flatMap((entry, index) =>
        entry.branch === lane ? [index] : [],
    );
    if (commitRows.length === 0) return segments;

    const newestIndex = commitRows[0];
    const oldestIndex = commitRows[commitRows.length - 1];
    const mergeRow = findMergeRow(
        entries,
        newestIndex,
        entries[newestIndex].end,
    );

    for (let index = mergeRow + 1; index <= oldestIndex; index += 1) {
        const isCommit = commitRows.includes(index);
        segments[index] = {
            ...segments[index],
            top: true,
            bottom: index !== oldestIndex,
            dot: isCommit,
            fork: index === oldestIndex,
            open: mergeRow === -1 && index === 0,
        };
    }

    if (mergeRow >= 0)
        segments[mergeRow] = { ...segments[mergeRow], merge: true };

    return segments;
};

export const buildCommitGraph = (entries: CareerEntry[]): GraphRow[] => {
    const lanes = LANE_ORDER.map((lane) =>
        lane === 'main' ? buildMainLane(entries) : buildSideLane(entries, lane),
    );

    return entries.map((entry, index) => ({
        entry,
        isHead: index === 0 && entry.branch === 'main' && entry.end === null,
        segments: lanes.map((lane) => lane[index]),
    }));
};
