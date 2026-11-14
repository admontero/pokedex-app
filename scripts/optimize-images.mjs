/**
 * Optimize the local illustrations and generate modern formats.
 *
 * Run with `npm run images`. The generated AVIF/WebP files are committed, so
 * the site keeps working without running this script on deploy.
 *
 * Each source gets a 1x and a 2x variant; the Blade <x-picture> component
 * wires them through srcset with density descriptors.
 */
import { rename, stat } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const DIR = 'public/vendor/images';

// Sources and the widths used by the views (1x and 2x).
const assets = [
    { file: 'logo.png', widths: [250, 500] },
    { file: '404.png', widths: [300, 600] },
    { file: '500.png', widths: [300, 600] },
    { file: 'pikachu.jpg', widths: [200, 400] },
    { file: 'rocket.png', widths: [200, 400] },
];

const kb = (bytes) => `${(bytes / 1024).toFixed(1)} KB`;

const optimizeFallback = async (file, transform) => {
    const source = path.join(DIR, file);
    const temporary = `${source}.tmp`;
    const before = (await stat(source)).size;

    await transform(sharp(source)).toFile(temporary);
    await rename(temporary, source);

    const after = (await stat(source)).size;

    console.log(`${file}: ${kb(before)} -> ${kb(after)}`);
};

// Keep the fallbacks small too: older browsers only download these.
await optimizeFallback('logo.png', (image) =>
    image.png({ compressionLevel: 9, palette: true, quality: 85, effort: 10 }),
);
await optimizeFallback('404.png', (image) =>
    image.png({ compressionLevel: 9, palette: true, quality: 85, effort: 10 }),
);
await optimizeFallback('500.png', (image) =>
    image.png({ compressionLevel: 9, palette: true, quality: 85, effort: 10 }),
);
await optimizeFallback('rocket.png', (image) =>
    image.png({ compressionLevel: 9, palette: true, quality: 85, effort: 10 }),
);
await optimizeFallback('pikachu.jpg', (image) =>
    image.resize({ width: 400, withoutEnlargement: true }).jpeg({ quality: 80, mozjpeg: true }),
);

for (const { file, widths } of assets) {
    const base = file.replace(/\.(png|jpe?g)$/i, '');

    for (const width of widths) {
        const pipeline = sharp(path.join(DIR, file)).resize({ width, withoutEnlargement: true });

        await pipeline
            .clone()
            .avif({ quality: 55, effort: 6 })
            .toFile(path.join(DIR, `${base}-${width}.avif`));

        await pipeline
            .clone()
            .webp({ quality: 78, effort: 5 })
            .toFile(path.join(DIR, `${base}-${width}.webp`));

        console.log(`${base}-${width}: avif + webp`);
    }
}
