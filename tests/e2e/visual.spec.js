import { test, expect } from '@playwright/test';

const visual = process.env.VISUAL === '1';

const settle = async (page, { loadLazyImages = false } = {}) => {
    await page.waitForLoadState('networkidle');

    if (loadLazyImages) {
        // Full-page screenshots need the lazy sprites below the fold too.
        await page.evaluate(async () => {
            const step = window.innerHeight;

            for (let y = 0; y < document.body.scrollHeight; y += step) {
                window.scrollTo(0, y);
                await new Promise((resolve) => setTimeout(resolve, 60));
            }

            window.scrollTo(0, 0);
        });
    }

    await page.evaluate(async () => {
        await document.fonts.ready;

        const viewportBottom = window.innerHeight;
        const pending = Array.from(document.images).filter((image) => {
            if (image.complete || image.getClientRects().length === 0) {
                return false;
            }

            // Lazy images outside the viewport are intentionally not loaded.
            return image.loading !== 'lazy' || image.getBoundingClientRect().top < viewportBottom;
        });

        await Promise.all(
            pending.map(
                (image) =>
                    new Promise((resolve) => {
                        image.addEventListener('load', resolve, { once: true });
                        image.addEventListener('error', resolve, { once: true });
                    }),
            ),
        );
    });
};

test.describe('visual regression', () => {
    test.skip(
        !visual,
        'Set VISUAL=1 to compare screenshots; baselines are platform-specific.',
    );

    test.setTimeout(60_000);

    test('home in light mode', async ({ page }) => {
        await page.goto('/');
        await settle(page);

        await expect(page).toHaveScreenshot('home-light.png');
    });

    test('home in dark mode', async ({ page }) => {
        await page.addInitScript(() => window.localStorage.setItem('pk-theme', 'dark'));
        await page.goto('/');
        await settle(page);

        await expect(page).toHaveScreenshot('home-dark.png');
    });

    test('search results', async ({ page }) => {
        await page.goto('/pokemon/search?term=pika');
        await settle(page);

        await expect(page).toHaveScreenshot('search.png');
    });

    test('pokemon detail', async ({ page }) => {
        await page.goto('/pokemon/pikachu');
        await settle(page, { loadLazyImages: true });

        await expect(page).toHaveScreenshot('detail.png', { fullPage: true });
    });

    test('not found page', async ({ page }) => {
        await page.goto('/pokemon/unknown');
        await settle(page, { loadLazyImages: true });

        await expect(page).toHaveScreenshot('not-found.png', { fullPage: true });
    });
});
