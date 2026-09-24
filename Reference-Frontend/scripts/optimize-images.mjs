import { mkdir, readdir } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const sourceDir = process.argv[2] ?? path.resolve('assets-src');
const outputDir = path.resolve('public/images');

const presets = [
  { prefix: 'cover-', widths: [640, 1280], quality: 78 },
  { prefix: 'gallery-', widths: [576, 1152], quality: 78 },
  { prefix: 'portrait', widths: [432, 864], quality: 80 },
];

const findPreset = (fileName) => presets.find((preset) => fileName.startsWith(preset.prefix));

await mkdir(outputDir, { recursive: true });

const files = (await readdir(sourceDir)).filter((file) => file.endsWith('.png'));

for (const file of files) {
  const preset = findPreset(file);
  if (!preset) continue;

  const baseName = path.basename(file, '.png');
  for (const width of preset.widths) {
    const target = path.join(outputDir, `${baseName}-${width}.webp`);
    await sharp(path.join(sourceDir, file))
      .resize({ width, withoutEnlargement: true })
      .webp({ quality: preset.quality, effort: 6 })
      .toFile(target);
    console.log(`optimized ${path.relative(process.cwd(), target)}`);
  }
}
