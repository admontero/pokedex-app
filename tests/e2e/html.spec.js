import { test, expect } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { HtmlValidate } from 'html-validate';

const config = JSON.parse(
    readFileSync(new URL('../../.htmlvalidate.json', import.meta.url), 'utf8'),
);
const htmlvalidate = new HtmlValidate(config);

const pages = [
    { name: 'home', path: '/' },
    { name: 'search results', path: '/pokemon/search?term=pika' },
    { name: 'search empty state', path: '/pokemon/search?term=zzzz' },
    { name: 'pokemon detail', path: '/pokemon/pikachu' },
    { name: 'not found', path: '/pokemon/unknown' },
];

const describeMessages = (report) =>
    report.results
        .flatMap((result) =>
            result.messages.map(
                (message) => `${message.ruleId} (${message.line}:${message.column}): ${message.message}`,
            ),
        )
        .join('\n');

for (const { name, path } of pages) {
    test(`${name} is valid HTML`, async ({ page }) => {
        await page.goto(path);

        const report = await htmlvalidate.validateString(await page.content());

        expect(report.valid, describeMessages(report)).toBe(true);
    });
}
