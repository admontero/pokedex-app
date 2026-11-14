import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.E2E_APP_URL ?? 'http://127.0.0.1:8123';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: Boolean(process.env.CI),
    retries: process.env.CI ? 1 : 0,
    workers: process.env.CI ? 2 : undefined,
    reporter: process.env.CI
        ? [['github'], ['html', { open: 'never' }]]
        : [['list'], ['html', { open: 'never' }]],
    snapshotPathTemplate:
        '{testDir}/__screenshots__/{projectName}-{platform}/{testFilePath}/{arg}{ext}',
    expect: {
        toHaveScreenshot: {
            animations: 'disabled',
            caret: 'hide',
            maxDiffPixelRatio: 0.05,
            scale: 'css',
        },
    },
    use: {
        baseURL,
        colorScheme: 'light',
        reducedMotion: 'reduce',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'desktop-chromium',
            use: {
                ...devices['Desktop Chrome'],
                viewport: { width: 1280, height: 800 },
            },
        },
        {
            name: 'mobile-chromium',
            use: {
                ...devices['Pixel 7'],
                viewport: { width: 390, height: 844 },
                deviceScaleFactor: 1,
            },
        },
    ],
    webServer: {
        command: 'node scripts/serve-e2e.mjs',
        url: `${baseURL}/up`,
        reuseExistingServer: !process.env.CI,
        timeout: 180_000,
        stdout: 'pipe',
        stderr: 'pipe',
    },
});
