const TINTS = [
    '#62a92b',
    '#33a381',
    '#f778ba',
    '#6e4ef2',
    '#e0a42b',
    '#3aa3d8',
] as const;

/** Short, stable label for a technology tile: TypeScript → TS, AWS → AWS, Go → Go. */
export const monogram = (name: string): string => {
    const word = name.split(/[\s&]+/)[0] ?? name;
    if (/^[A-Z0-9+]{2,4}$/.test(word)) return word;
    const capitals = name.match(/[A-Z]/g) ?? [];
    if (capitals.length >= 2) return capitals.slice(0, 2).join('');
    const clean = name.replace(/[^A-Za-z0-9]/g, '');
    return clean.charAt(0).toUpperCase() + clean.charAt(1).toLowerCase();
};

export const tintFor = (name: string): string => {
    let hash = 0;
    for (const char of name) hash = (hash * 31 + char.charCodeAt(0)) >>> 0;
    return TINTS[hash % TINTS.length];
};
