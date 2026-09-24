const COLUMN_GAP = 16;
const ROW_GAP = 21;

const seeded = (seed: number) => {
  let value = seed;
  return (): number => {
    value = (value * 1664525 + 1013904223) % 4294967296;
    return value / 4294967296;
  };
};

/** Builds the "binary rain" backdrop used behind the hero as an SVG data URI. */
export const binaryRain = (width = 1920, height = 640): string => {
  const random = seeded(42);
  const columns = Math.floor(width / COLUMN_GAP);
  const glyphs: string[] = [];

  for (let column = 0; column < columns; column += 1) {
    const length = 4 + Math.floor(random() * (height / ROW_GAP - 4));
    for (let row = 0; row < length; row += 1) {
      const size = random() > 0.35 ? 13 : 8;
      const opacity = (1 - row / length) * (0.55 + random() * 0.45);
      glyphs.push(
        `<text x="${column * COLUMN_GAP + 4}" y="${row * ROW_GAP + 14}" font-size="${size}" opacity="${opacity.toFixed(2)}">${random() > 0.5 ? 1 : 0}</text>`,
      );
    }
  }

  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" font-family="monospace" fill="black">${glyphs.join('')}</svg>`;
  return `url("data:image/svg+xml,${encodeURIComponent(svg)}")`;
};
