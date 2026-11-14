import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];

const pages = [
    { name: 'home', path: '/' },
    { name: 'search results', path: '/pokemon/search?term=pika' },
    { name: 'search empty state', path: '/pokemon/search?term=zzzz' },
    { name: 'pokemon detail', path: '/pokemon/pikachu' },
    { name: 'not found', path: '/pokemon/unknown' },
];

const describeViolations = (violations) =>
    violations
        .map((violation) => {
            const nodes = violation.nodes
                .map((node) => `  ${node.target.join(' ')}: ${node.failureSummary}`)
                .join('\n');

            return `${violation.id}: ${violation.help}\n${nodes}`;
        })
        .join('\n\n');

const analyze = async (page) => {
    const results = await new AxeBuilder({ page }).withTags(WCAG_TAGS).analyze();

    expect(results.violations, describeViolations(results.violations)).toEqual([]);
};

for (const { name, path } of pages) {
    test(`${name} has no WCAG violations`, async ({ page }) => {
        await page.goto(path);
        await page.waitForLoadState('networkidle');

        await analyze(page);
    });
}

test('the home page in dark mode has no WCAG violations', async ({ page }) => {
    await page.addInitScript(() => window.localStorage.setItem('pk-theme', 'dark'));
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('html')).toHaveAttribute('data-bs-theme', 'dark');

    await analyze(page);
});

test('the detail page in dark mode has no WCAG violations', async ({ page }) => {
    await page.addInitScript(() => window.localStorage.setItem('pk-theme', 'dark'));
    await page.goto('/pokemon/pikachu');
    await page.waitForLoadState('networkidle');

    await analyze(page);
});
