import { test, expect } from '@playwright/test';

test('the autocomplete suggests a Pokémon and navigates with the keyboard', async ({ page }) => {
    await page.goto('/');

    const input = page.getByRole('combobox', { name: 'Nombre o número' });
    const listbox = page.getByRole('listbox', { name: 'Sugerencias de Pokémon' });

    await input.fill('pika');

    await expect(listbox).toBeVisible();
    await expect(listbox.getByRole('option').first()).toContainText('#025');
    await expect(listbox.getByRole('option').first()).toContainText('pikachu');

    await input.press('ArrowDown');
    await expect(input).toHaveAttribute('aria-activedescendant', 'term-suggestion-0');

    await input.press('Enter');

    await expect(page).toHaveURL(/\/pokemon\/pikachu(\?|$)/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('pikachu');
});

test('the theme toggle persists the choice across reloads', async ({ page }) => {
    await page.goto('/');

    const html = page.locator('html');
    const toggle = page.getByRole('button', { name: 'Modo oscuro' });

    await expect(html).toHaveAttribute('data-bs-theme', 'light');
    await expect(toggle).toHaveAttribute('aria-pressed', 'false');

    await toggle.click();

    await expect(html).toHaveAttribute('data-bs-theme', 'dark');
    await expect(toggle).toHaveAttribute('aria-pressed', 'true');

    await page.reload();

    await expect(html).toHaveAttribute('data-bs-theme', 'dark');
    await expect(toggle).toHaveAttribute('aria-pressed', 'true');
});

test('the search accepts a Pokédex number', async ({ page }) => {
    await page.goto('/pokemon/search?term=25');

    await expect(page.getByRole('heading', { level: 1 })).toContainText('Resultados para «25»');
    await expect(page.getByRole('link', { name: /pikachu/i }).first()).toBeVisible();
});

test('the search filters by type', async ({ page }) => {
    await page.goto('/pokemon/search?type=electric');

    await expect(page.getByText('de tipo').first()).toContainText('Eléctrico');
    await expect(page.getByRole('link', { name: /pikachu/i }).first()).toBeVisible();
});

test('the list exposes a load more fallback link', async ({ page }) => {
    await page.goto('/');

    const loadMore = page.getByRole('link', { name: 'Cargar más Pokémon' });

    await expect(loadMore).toBeVisible();
    await expect(loadMore).toHaveAttribute('href', /page=2/);
});

test('an unknown Pokémon renders the custom 404 page', async ({ page }) => {
    const response = await page.goto('/pokemon/unknown');

    expect(response?.status()).toBe(404);
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Pokémon no encontrado');
});
