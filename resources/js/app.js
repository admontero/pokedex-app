import './bootstrap';
import InfiniteScroll from 'infinite-scroll';

/**
 * Infinite scroll with a real "load more" link as fallback.
 *
 * The attribute is `data-pk-infinite-scroll` on purpose: the library
 * auto-initializes every `[data-infinite-scroll]` element on its own with no
 * options (and logs "path option required"), so a custom name keeps both
 * instances apart.
 *
 * Full page loads render every page up to the current one, but AJAX requests
 * ask for a single page (the controllers check `X-Requested-With`), so cards
 * are appended once instead of being duplicated.
 */
document.querySelectorAll('[data-pk-infinite-scroll]').forEach((root) => {
    const target = document.querySelector(root.dataset.target);
    const status = root.querySelector('[data-infinite-scroll-status]');
    const loadMore = root.querySelector('[data-load-more]');
    const retry = root.querySelector('[data-retry]');
    const nextLink = root.querySelector('.pagination__next');
    const statusElement = root.querySelector('.page-load-status');

    if (!target || !statusElement || !root.dataset.path) {
        return;
    }

    const announce = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const setLoading = (loading) => {
        target.setAttribute('aria-busy', loading ? 'true' : 'false');

        if (loadMore) {
            loadMore.classList.toggle('disabled', loading);
            loadMore.setAttribute('aria-disabled', loading ? 'true' : 'false');
        }
    };

    const updateShownCount = () => {
        document.querySelectorAll('[data-shown-count]').forEach((element) => {
            element.textContent = target.querySelectorAll('.col').length;
        });
    };

    const infiniteScroll = new InfiniteScroll(target, {
        path: root.dataset.path,
        append: '.col',
        status: statusElement,
        checkLastPage: '.pagination__next',
        // The library's history mode follows the scroll position, so the URL
        // could point to an earlier page than the last one loaded. We update it
        // ourselves on every append to keep "page=N" in sync with the cards.
        history: false,
        fetchOptions: {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
        },
    });

    infiniteScroll.on('request', () => {
        setLoading(true);
        announce('Cargando más Pokémon…');
    });

    infiniteScroll.on('append', (body, path, items) => {
        setLoading(false);
        updateShownCount();

        // Keep the address bar in sync with the last loaded page so reloading
        // or sharing the URL restores the same amount of cards.
        if (path) {
            try {
                window.history.replaceState(null, '', path);
            } catch (error) {
                if (import.meta.env.DEV) {
                    console.error('[Pokédex] No se pudo actualizar la URL:', error);
                }
            }
        }

        const count = Array.isArray(items) ? items.length : 0;
        announce(count > 0 ? `${count} Pokémon más cargados.` : 'Se cargaron más Pokémon.');

        const nextPath = infiniteScroll.getPath();

        if (loadMore && nextPath) {
            loadMore.href = nextPath;
        }

        if (nextLink && nextPath) {
            nextLink.href = nextPath;
        }
    });

    infiniteScroll.on('error', (error) => {
        setLoading(false);
        announce('No se pudieron cargar más Pokémon. Inténtalo de nuevo.');

        if (import.meta.env.DEV) {
            console.error('[Pokédex] Error al cargar más Pokémon:', error);
        }
    });

    infiniteScroll.on('last', () => {
        setLoading(false);

        if (loadMore) {
            loadMore.hidden = true;
        }

        announce('No hay más Pokémon para cargar.');
    });

    loadMore?.addEventListener('click', (event) => {
        if (loadMore.getAttribute('aria-disabled') === 'true') {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        infiniteScroll.loadNextPage();
    });

    retry?.addEventListener('click', () => infiniteScroll.loadNextPage());
});
