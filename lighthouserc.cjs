module.exports = {
    ci: {
        collect: {
            url: [
                'http://127.0.0.1:8123/',
                'http://127.0.0.1:8123/pokemon/pikachu',
            ],
            numberOfRuns: 1,
            startServerCommand: 'node scripts/serve-e2e.mjs',
            startServerReadyPattern: '\\[e2e\\] server ready',
            startServerReadyTimeout: 180000,
            settings: {
                chromeFlags: '--no-sandbox --disable-dev-shm-usage',
            },
        },
        assert: {
            assertions: {
                'categories:performance': ['warn', { minScore: 0.8 }],
                'categories:accessibility': ['error', { minScore: 0.95 }],
                'categories:best-practices': ['warn', { minScore: 0.9 }],
                'categories:seo': ['warn', { minScore: 0.9 }],
                'button-name': 'error',
                'color-contrast': 'error',
                'image-alt': 'error',
                'link-name': 'error',
            },
        },
        upload: {
            target: 'filesystem',
            outputDir: '.lighthouseci',
        },
    },
};
