/**
 * Run Lighthouse CI against the local e2e server.
 *
 * Lighthouse needs a Chrome binary. Locally we reuse the Chromium that
 * Playwright already downloaded, so there is no second browser install.
 */
import { spawn } from 'node:child_process';
import { chromium } from '@playwright/test';

const env = { ...process.env };

if (!env.CHROME_PATH) {
    env.CHROME_PATH = chromium.executablePath();
}

const child = spawn('npx', ['lhci', 'autorun'], {
    env,
    stdio: 'inherit',
    shell: process.platform === 'win32',
});

child.on('exit', (code) => process.exit(code ?? 1));
