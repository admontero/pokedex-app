/**
 * Minimal PokeAPI-compatible server for the end-to-end tests.
 *
 * The app is pointed at it with POKEMON_BASE_URL, so the tests never hit the
 * real API: responses are deterministic, fast and work offline. Images are
 * served from the repository's own optimized assets.
 */
import { createServer } from 'node:http';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HOST = process.env.MOCK_HOST ?? '127.0.0.1';
const PORT = Number(process.env.MOCK_PORT ?? 4010);
const ORIGIN = `http://${HOST}:${PORT}`;
const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const IMAGES_DIR = path.join(ROOT, 'public/vendor/images');

const POKEMON = [
    { id: 1, name: 'bulbasaur', types: ['grass', 'poison'], abilities: ['overgrow', 'chlorophyll'] },
    { id: 2, name: 'ivysaur', types: ['grass', 'poison'], abilities: ['overgrow', 'chlorophyll'] },
    { id: 3, name: 'venusaur', types: ['grass', 'poison'], abilities: ['overgrow', 'chlorophyll'] },
    { id: 4, name: 'charmander', types: ['fire'], abilities: ['blaze', 'solar-power'] },
    { id: 5, name: 'charmeleon', types: ['fire'], abilities: ['blaze', 'solar-power'] },
    { id: 6, name: 'charizard', types: ['fire', 'flying'], abilities: ['blaze', 'solar-power'] },
    { id: 7, name: 'squirtle', types: ['water'], abilities: ['torrent', 'rain-dish'] },
    { id: 8, name: 'wartortle', types: ['water'], abilities: ['torrent', 'rain-dish'] },
    { id: 9, name: 'blastoise', types: ['water'], abilities: ['torrent', 'rain-dish'] },
    { id: 10, name: 'caterpie', types: ['bug'], abilities: ['shield-dust', 'run-away'] },
    { id: 11, name: 'metapod', types: ['bug'], abilities: ['shed-skin'] },
    { id: 12, name: 'butterfree', types: ['bug', 'flying'], abilities: ['compound-eyes', 'tinted-lens'] },
    { id: 13, name: 'weedle', types: ['bug', 'poison'], abilities: ['shield-dust', 'run-away'] },
    { id: 14, name: 'kakuna', types: ['bug', 'poison'], abilities: ['shed-skin'] },
    { id: 15, name: 'beedrill', types: ['bug', 'poison'], abilities: ['swarm', 'sniper'] },
    { id: 16, name: 'pidgey', types: ['normal', 'flying'], abilities: ['keen-eye', 'tangled-feet'] },
    { id: 17, name: 'pidgeotto', types: ['normal', 'flying'], abilities: ['keen-eye', 'tangled-feet'] },
    { id: 18, name: 'pidgeot', types: ['normal', 'flying'], abilities: ['keen-eye', 'tangled-feet'] },
    { id: 19, name: 'rattata', types: ['normal'], abilities: ['run-away', 'guts'] },
    { id: 20, name: 'raticate', types: ['normal'], abilities: ['run-away', 'guts'] },
    { id: 21, name: 'spearow', types: ['normal', 'flying'], abilities: ['keen-eye', 'sniper'] },
    { id: 22, name: 'fearow', types: ['normal', 'flying'], abilities: ['keen-eye', 'sniper'] },
    { id: 23, name: 'ekans', types: ['poison'], abilities: ['intimidate', 'shed-skin'] },
    { id: 24, name: 'arbok', types: ['poison'], abilities: ['intimidate', 'shed-skin'] },
    { id: 25, name: 'pikachu', types: ['electric'], abilities: ['static', 'lightning-rod'], female: true },
    { id: 26, name: 'raichu', types: ['electric'], abilities: ['static', 'lightning-rod'] },
];

const STATS = {
    hp: 35,
    attack: 45,
    defense: 40,
    'special-attack': 50,
    'special-defense': 45,
    speed: 55,
};

const ARTWORK = `${ORIGIN}/assets/pikachu.jpg`;
const SPRITE = `${ORIGIN}/assets/pikachu-200.webp`;

const assetUrl = (id) => `${ORIGIN}/api/v2/pokemon/${id}/`;

const listEntry = (pokemon) => ({
    name: pokemon.name,
    url: assetUrl(pokemon.id),
});

const detail = (pokemon) => ({
    id: pokemon.id,
    name: pokemon.name,
    weight: 30 + ((pokemon.id * 7) % 900),
    height: 3 + (pokemon.id % 20),
    base_experience: 50 + pokemon.id * 2,
    types: pokemon.types.map((name, index) => ({ slot: index + 1, type: { name } })),
    abilities: pokemon.abilities.map((name, index) => ({
        ability: { name },
        is_hidden: index > 0,
        slot: index + 1,
    })),
    held_items: pokemon.name === 'pikachu' ? [{ item: { name: 'light-ball' } }] : [],
    stats: Object.entries(STATS).map(([name, base], index) => ({
        base_stat: base + ((pokemon.id + index) % 30),
        effort: 0,
        stat: { name },
    })),
    sprites: {
        front_default: SPRITE,
        front_shiny: SPRITE,
        back_default: SPRITE,
        back_shiny: SPRITE,
        front_female: pokemon.female ? SPRITE : null,
        front_shiny_female: pokemon.female ? SPRITE : null,
        back_female: pokemon.female ? SPRITE : null,
        back_shiny_female: pokemon.female ? SPRITE : null,
        other: {
            'official-artwork': {
                front_default: ARTWORK,
            },
        },
    },
});

const json = (response, status, payload) => {
    const body = JSON.stringify(payload);

    response.writeHead(status, {
        'Content-Type': 'application/json; charset=utf-8',
        'Content-Length': Buffer.byteLength(body),
        'Cache-Control': 'no-store',
    });
    response.end(body);
};

const image = async (response, file) => {
    const safeName = path.basename(file);

    try {
        const body = await readFile(path.join(IMAGES_DIR, safeName));
        const extension = path.extname(safeName).toLowerCase();
        const types = {
            '.avif': 'image/avif',
            '.jpg': 'image/jpeg',
            '.jpeg': 'image/jpeg',
            '.png': 'image/png',
            '.webp': 'image/webp',
        };

        response.writeHead(200, {
            'Content-Type': types[extension] ?? 'application/octet-stream',
            'Content-Length': body.length,
            'Cache-Control': 'public, max-age=3600',
        });
        response.end(body);
    } catch (error) {
        json(response, 404, { detail: 'Not found' });
    }
};

const server = createServer(async (request, response) => {
    const url = new URL(request.url ?? '/', ORIGIN);
    const route = url.pathname.replace(/\/+$/, '') || '/';

    if (route === '/health') {
        json(response, 200, { ok: true });
        return;
    }

    if (route.startsWith('/assets/')) {
        await image(response, route.slice('/assets/'.length));
        return;
    }

    if (route === '/api/v2/pokemon') {
        json(response, 200, {
            count: POKEMON.length,
            next: null,
            previous: null,
            results: POKEMON.map(listEntry),
        });
        return;
    }

    if (route.startsWith('/api/v2/pokemon/')) {
        const key = decodeURIComponent(route.slice('/api/v2/pokemon/'.length));
        const pokemon = POKEMON.find((entry) => entry.name === key || String(entry.id) === key);

        if (!pokemon) {
            json(response, 404, { detail: 'Not found' });
            return;
        }

        json(response, 200, detail(pokemon));
        return;
    }

    if (route.startsWith('/api/v2/type/')) {
        const type = decodeURIComponent(route.slice('/api/v2/type/'.length));
        const matches = POKEMON.filter((entry) => entry.types.includes(type));

        json(response, 200, {
            name: type,
            pokemon: matches.map((entry) => ({
                slot: entry.types.indexOf(type) + 1,
                pokemon: listEntry(entry),
            })),
        });
        return;
    }

    json(response, 404, { detail: 'Not found' });
});

server.listen(PORT, HOST, () => {
    console.log(`mock PokeAPI listening on ${ORIGIN}`);
});

const shutdown = () => {
    server.close(() => process.exit(0));
};

process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
