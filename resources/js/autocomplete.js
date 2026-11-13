/**
 * Search suggestions for the Pokémon form (ARIA combobox pattern).
 *
 * This is a progressive enhancement: the form keeps working without
 * JavaScript, and the suggestions only add a faster path to a Pokémon page.
 * Requests are debounced and aborted when the term changes, so slow responses
 * never overwrite newer ones.
 */

const DEBOUNCE_MS = 200;
const MIN_LENGTH = 1;

const buildDetailUrl = (pokemonUrl) => {
    const url = new URL(pokemonUrl, window.location.origin);

    // Keep the current list or search page so the detail breadcrumb can point
    // back to where the user came from.
    url.searchParams.set('from', window.location.pathname + window.location.search);

    return url.toString();
};

const createOption = (result, index) => {
    const option = document.createElement('li');

    option.id = `term-suggestion-${index}`;
    option.className = 'search-suggest__option';
    option.setAttribute('role', 'option');
    option.setAttribute('aria-selected', 'false');
    option.dataset.url = buildDetailUrl(result.url);

    if (result.number) {
        const number = document.createElement('span');
        number.className = 'search-suggest__number';
        number.textContent = `#${result.number}`;
        option.append(number);
    }

    const name = document.createElement('span');
    name.className = 'search-suggest__name';
    name.textContent = result.name;
    option.append(name);

    return option;
};

export function initAutocomplete(root) {
    const input = root.querySelector('[data-suggest-input]');
    const list = root.querySelector('[data-suggest-list]');
    const status = root.querySelector('[data-suggest-status]');
    const endpoint = root.dataset.suggestUrl;

    if (!input || !list || !endpoint) {
        return;
    }

    let options = [];
    let activeIndex = -1;
    let controller = null;
    let timer = null;
    let lastTerm = '';

    const announce = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const isOpen = () => !list.hidden;

    const setActive = (index) => {
        const previous = options[activeIndex];

        if (previous) {
            previous.setAttribute('aria-selected', 'false');
        }

        activeIndex = index;

        if (index < 0) {
            input.removeAttribute('aria-activedescendant');
            return;
        }

        const option = options[index];
        option.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({ block: 'nearest' });
    };

    const close = () => {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        setActive(-1);
    };

    const open = () => {
        if (options.length === 0) {
            return;
        }

        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const reset = () => {
        close();
        options = [];
        list.replaceChildren();
    };

    const goTo = (option) => {
        window.location.assign(option.dataset.url);
    };

    const render = (results) => {
        options = results.map((result, index) => createOption(result, index));
        list.replaceChildren(...options);
        activeIndex = -1;
        input.removeAttribute('aria-activedescendant');

        if (options.length === 0) {
            close();
            announce('Sin sugerencias. Pulsa Buscar para ver los resultados.');

            return;
        }

        open();
        announce(`${options.length} sugerencias disponibles. Usa las flechas para elegir.`);
    };

    const load = async (term) => {
        controller?.abort();
        controller = new AbortController();

        const params = new URLSearchParams({ term });

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Unexpected status ${response.status}`);
            }

            const data = await response.json();

            // A newer request may have started while this one was in flight.
            if (term !== lastTerm) {
                return;
            }

            render(Array.isArray(data.results) ? data.results : []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            reset();

            if (import.meta.env.DEV) {
                console.error('[Pokédex] No se pudieron cargar las sugerencias:', error);
            }
        }
    };

    input.addEventListener('input', () => {
        const term = input.value.trim();

        window.clearTimeout(timer);

        if (term.length < MIN_LENGTH) {
            lastTerm = '';
            reset();
            announce('');

            return;
        }

        if (term === lastTerm) {
            return;
        }

        lastTerm = term;
        close();
        timer = window.setTimeout(() => load(term), DEBOUNCE_MS);
    });

    input.addEventListener('focus', () => {
        if (options.length > 0 && input.value.trim() === lastTerm) {
            open();
        }
    });

    input.addEventListener('blur', close);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (isOpen()) {
                event.preventDefault();
            }

            close();

            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            if (options.length === 0) {
                return;
            }

            event.preventDefault();

            if (!isOpen()) {
                open();
                setActive(event.key === 'ArrowDown' ? 0 : options.length - 1);

                return;
            }

            const delta = event.key === 'ArrowDown' ? 1 : -1;
            const next = (activeIndex + delta + options.length) % options.length;
            setActive(next);

            return;
        }

        if (event.key === 'Enter' && isOpen() && activeIndex >= 0) {
            event.preventDefault();
            goTo(options[activeIndex]);
        }
    });

    // pointerdown instead of click: preventing the default keeps the input
    // focused, so the list is not closed by the blur before the navigation.
    list.addEventListener('pointerdown', (event) => {
        const option = event.target.closest('[role="option"]');

        if (!option) {
            return;
        }

        event.preventDefault();
        goTo(option);
    });
}
