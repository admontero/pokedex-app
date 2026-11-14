/**
 * Start the mock PokeAPI and the Laravel app for Playwright and Lighthouse.
 *
 * Both servers share the same environment so the app never calls the real
 * API during the quality checks. The script waits until both are ready and
 * then stays in the foreground; the test runner stops it when it finishes.
 */
import { spawn, spawnSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { setTimeout as delay } from 'node:timers/promises';

const HOST = '127.0.0.1';
const APP_PORT = Number(process.env.E2E_APP_PORT ?? 8123);
const MOCK_PORT = Number(process.env.E2E_MOCK_PORT ?? 4010);
const APP_URL = `http://${HOST}:${APP_PORT}`;
const MOCK_ORIGIN = `http://${HOST}:${MOCK_PORT}`;

// Fixed key: the tests do not need real encryption, but Laravel refuses to
// boot without one. This is not a secret and is only used by the test server.
const APP_KEY = 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=';

const isWindows = process.platform === 'win32';
const children = [];
let shuttingDown = false;

const stop = (child) => {
    if (!child || child.exitCode !== null) {
        return;
    }

    if (isWindows) {
        spawnSync('taskkill', ['/pid', String(child.pid), '/T', '/F'], { stdio: 'ignore' });

        return;
    }

    try {
        process.kill(-child.pid, 'SIGTERM');
    } catch (error) {
        child.kill('SIGTERM');
    }
};

const shutdown = (code = 0) => {
    if (shuttingDown) {
        return;
    }

    shuttingDown = true;

    for (const child of [...children].reverse()) {
        stop(child);
    }

    process.exit(code);
};

const track = (child, name) => {
    children.push(child);

    child.on('exit', (code) => {
        if (!shuttingDown) {
            console.error(`[e2e] ${name} exited with code ${code ?? 'unknown'}`);
            shutdown(code ?? 1);
        }
    });

    return child;
};

const waitFor = async (url, label, timeout = 120_000) => {
    const startedAt = Date.now();

    while (Date.now() - startedAt < timeout) {
        try {
            const response = await fetch(url, { redirect: 'manual' });

            if (response.status < 500) {
                return;
            }
        } catch (error) {
            // The server is not listening yet.
        }

        await delay(500);
    }

    throw new Error(`Timed out waiting for ${label} at ${url}`);
};

const buildAssets = () => {
    if (existsSync('public/build/manifest.json')) {
        return;
    }

    console.log('[e2e] public/build is missing, running npm run build');

    const npm = isWindows ? 'npm.cmd' : 'npm';
    const result = spawnSync(npm, ['run', 'build'], {
        stdio: 'inherit',
        shell: isWindows,
    });

    if (result.status !== 0) {
        process.exit(result.status ?? 1);
    }
};

process.on('SIGINT', () => shutdown(0));
process.on('SIGTERM', () => shutdown(0));

buildAssets();

// A cached config would ignore the environment variables below.
spawnSync('php', ['artisan', 'config:clear'], { stdio: 'inherit' });

const mock = track(
    spawn(process.execPath, ['tests/e2e/mock-pokeapi.mjs'], {
        env: { ...process.env, MOCK_PORT: String(MOCK_PORT) },
        stdio: 'inherit',
        detached: !isWindows,
    }),
    'mock PokeAPI',
);

await waitFor(`${MOCK_ORIGIN}/health`, 'mock PokeAPI');

const app = track(
    spawn('php', ['artisan', 'serve', '--host', HOST, '--port', String(APP_PORT), '--tries=1', '--no-reload'], {
        env: {
            ...process.env,
            APP_ENV: 'e2e',
            APP_DEBUG: 'false',
            APP_KEY,
            APP_URL,
            POKEMON_BASE_URL: `${MOCK_ORIGIN}/api/v2/`,
            CACHE_DRIVER: 'array',
            SESSION_DRIVER: 'array',
            QUEUE_CONNECTION: 'sync',
            LOG_LEVEL: 'error',
            POKEMON_PER_PAGE: '16',
        },
        stdio: 'inherit',
        detached: !isWindows,
    }),
    'Laravel server',
);

await waitFor(`${APP_URL}/up`, 'Laravel server');

console.log(`[e2e] server ready at ${APP_URL} (mock API at ${MOCK_ORIGIN})`);

// Keep the process alive while the test runner owns the servers.
await new Promise(() => {});
