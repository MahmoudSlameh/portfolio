const WORD_BOUNDARY = /[\s\-_/.:]/;

export const fuzzyScore = (query: string, target: string): number => {
    const needle = query.trim().toLocaleLowerCase();
    if (!needle) return 1;

    const haystack = target.toLocaleLowerCase();
    const directIndex = haystack.indexOf(needle);
    if (directIndex !== -1) {
        const boundaryBonus =
            directIndex === 0 || WORD_BOUNDARY.test(haystack[directIndex - 1])
                ? 40
                : 0;
        return 100 + boundaryBonus - directIndex;
    }

    let score = 0;
    let searchFrom = 0;
    let previousMatch = -2;

    for (const character of needle) {
        if (character === ' ') continue;
        const matchIndex = haystack.indexOf(character, searchFrom);
        if (matchIndex === -1) return 0;

        score += 1;
        if (matchIndex === previousMatch + 1) score += 4;
        if (matchIndex === 0 || WORD_BOUNDARY.test(haystack[matchIndex - 1]))
            score += 6;

        previousMatch = matchIndex;
        searchFrom = matchIndex + 1;
    }

    return score;
};
