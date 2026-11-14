# Sistema de diseño — Pokédex

> **Alcance:** tokens, tipografía, componentes y patrones de accesibilidad que
> comparten el listado, la búsqueda, el detalle y las páginas de error.
> **Fuente de verdad:** `resources/sass/_variables.scss` (Bootstrap) y
> `resources/sass/app.scss` (tokens y componentes).

---

## 1. Principios

1. **Enciclopedia, no arcade.** Superficies limpias, color de tipo como acento y
   rojo reservado para acciones primarias y la marca.
2. **El color nunca es el único mensaje.** Los tipos llevan texto además del
   color; los estados llevan icono, texto o `aria-*`.
3. **Tokens antes que valores sueltos.** Cualquier color, sombra o radio nuevo
   debe entrar como variable `--pk-*` y tener variante clara y oscura.
4. **Progressive enhancement.** El formulario, el scroll y el detalle funcionan
   sin JavaScript; el autocompletado y el tema solo mejoran la experiencia.
5. **Contraste y foco visibles.** Todo texto cumple WCAG 2.2 AA (4.5:1) y el
   foco se dibuja con `outline` de 3 px.

---

## 2. Tokens

### 2.1 Marca

| Token | Valor | Uso |
|---|---|---|
| `--pk-red-600` | `#E60013` | Navbar, acciones primarias, marca |
| `--pk-red-700` | `#C1121F` | Enlaces y texto de marca sobre fondos claros |
| `--pk-blue-600` | `#3269B1` | Acciones secundarias y navegación anterior/siguiente |
| `--pk-yellow-400` | `#FFCB05` | Foco sobre la navbar y acentos puntuales |

Bootstrap expone además `pokemon-red`, `pokemon-red-dark`, `pokemon-blue` y
`pokemon-yellow` como utilidades (`btn-pokemon-red`, `text-pokemon-blue`…).

### 2.2 Semánticos (claro / oscuro)

| Token | Claro | Oscuro | Uso |
|---|---|---|---|
| `--pk-ink` | `#212529` | `#E9ECEF` | Texto principal |
| `--pk-muted` | `#5C636A` | `#ADB5BD` | Texto secundario, metadatos |
| `--pk-canvas` | `#F1F3F5` | `#0F1216` | Fondo de página |
| `--pk-surface` | `#FFFFFF` | `#1A1F26` | Tarjetas, navbar de contenido, formularios |
| `--pk-border` | `#DEE2E6` | `#343A40` | Bordes y separadores |
| `--pk-hover` | `#E9ECEF` | `#2B3138` | Hover y fondos secundarios |
| `--pk-link` | `#C1121F` | `#FF8A94` | Enlaces |
| `--pk-focus` | `#0D6EFD` | `#6EA8FE` | Foco general |
| `--pk-focus-dark` | `#FFCB05` | `#FFCB05` | Foco sobre la navbar roja |
| `--pk-primary` | `#0B5ED7` | `#6EA8FE` | Botones outline primarios |
| `--pk-tint-base` | `#FFFFFF` | `#1A1F26` | Base para `color-mix` de tipos |
| `--pk-accent-soft` | `rgba(230,0,19,.08)` | `rgba(230,0,19,.28)` | Selección de sugerencias |
| `--pk-shadow-sm` | `0 1px 2px rgba(16,24,40,.06)` | `0 1px 2px rgba(0,0,0,.4)` | Tarjetas en reposo |
| `--pk-shadow-lg` | `0 .75rem 1.5rem rgba(16,24,40,.12)` | `0 .75rem 1.5rem rgba(0,0,0,.55)` | Hover y listas flotantes |

El tema oscuro también reasigna las variables de Bootstrap
(`--bs-body-bg`, `--bs-body-color`, `--bs-border-color`, `--bs-secondary-bg`,
`--bs-tertiary-bg`, `--bs-link-color`…) para que tarjetas, formularios, alertas
y `.bg-body-secondary` hereden la misma paleta.

### 2.3 Colores de tipo

Cada tipo define `--pk-type-<nombre>` y las clases `.type-badge--<nombre>` /
`.type-tint--<nombre>` fijan `--type-color`. Los fondos se calculan con
`color-mix` al 16–18 % sobre `--pk-tint-base`, y el borde al 42–45 %.

| Tipo | Color | Tipo | Color |
|---|---|---|---|
| normal | `#A8A090` | grass | `#78C850` |
| fighting | `#A05038` | electric | `#F8D030` |
| flying | `#98A8F0` | psychic | `#F870A0` |
| poison | `#8E3B7E` | ice | `#58C8E0` |
| ground | `#E9D6A4` | dragon | `#5B45B8` |
| rock | `#B8A058` | dark | `#7A5848` |
| bug | `#A8B820` | fairy | `#E79FE7` |
| ghost | `#6060B0` | steel | `#A8A8C0` |
| fire | `#C0392B` | water | `#1B6BB0` |

### 2.4 Forma, espacio y movimiento

- **Radios:** tarjetas `1rem`, controles `.75rem`, chips y badges `999px`.
- **Espaciado:** escala de Bootstrap (`g-2`, `g-3`, `g-4`, `p-3`, `p-md-4`).
- **Sombras:** solo `--pk-shadow-sm` y `--pk-shadow-lg`; no se inventan valores.
- **Movimiento:** transiciones de 150 ms en tarjetas; con
  `prefers-reduced-motion: reduce` se desactivan los desplazamientos y se
  alarga el spinner.

---

## 3. Tipografía

Nunito se sirve en local desde `public/vendor/fonts/` (woff2, pesos 400, 600,
700 y 800). No hay peticiones a Bunny Fonts ni a Google Fonts. El layout
precarga 400 y 800 para evitar el salto de métricas.

| Rol | Clase / token | Peso |
|---|---|---|
| Título de página | `.hero-title` / `.h3` | 800 |
| Título de detalle | `.pokemon-detail-title` | 800 |
| Nombre de tarjeta | `.pokemon-card__name` | 800 |
| Cuerpo | `body` | 400 |
| Etiquetas y botones | `.fw-semibold`, `.btn` | 600–700 |
| Metadatos | `.text-muted-pk`, `.small` | 400–600 |

Las fuentes decorativas `Pokemon Solid` y `Pokemon Hollow` siguen disponibles
como `.pokemon-solid` / `.pokemon-hollow`, pero no se usan en texto pequeño.

---

## 4. Componentes

### 4.1 Layout y navbar

`resources/views/layouts/app.blade.php` define:

- `<html lang="es" data-bs-theme="…">` con el tema aplicado antes del CSS.
- Skip link a `#main`, `<nav aria-label="Navegación principal">` y `<main id="main" tabindex="-1">`.
- Logo con `x-picture` (`eager`, `fetchpriority="high"`).
- Botón de tema con `aria-pressed`, iconos sol/luna y `title` dinámico.

El tema se guarda en `localStorage` con la clave `pk-theme`. Si no hay elección
explícita se sigue `prefers-color-scheme`.

### 4.2 `x-picture`

```blade
<x-picture
    base="vendor/images/logo"
    fallback="vendor/images/logo.png"
    :width="250"
    :height="45"
    loading="eager"
    fetchpriority="high"
/>
```

Genera `<source type="image/avif">` y `<source type="image/webp">` con
`srcset` 1x/2x, más el PNG/JPG de fallback con `width`, `height`, `alt`,
`loading` y `decoding`. Los derivados se regeneran con `npm run images`.

### 4.3 `x-icon`

Sustituye a Font Awesome. Es un SVG inline de 24×24 con `stroke="currentColor"`,
`aria-hidden="true"` y `focusable="false"`.

```blade
<x-icon name="arrow-left" class="me-2" />
```

Iconos disponibles: `arrow-left`, `arrow-left-long`, `arrow-right-long`,
`xmark`. Para añadir uno, amplía el mapa de `resources/views/components/icon.blade.php`.

### 4.4 Tarjeta de Pokémon

`x-pokemon.card` es un enlace completo con:

- `type-tint--<tipo>` en el contenedor y `type-badge` por cada tipo.
- Número `#025` como texto (`aria-hidden`), nombre visible y tipos con etiqueta.
- Imagen con `width`/`height`, `aspect-ratio: 1`, `loading` y `fetchpriority`
  según la posición (las 8 primeras van `eager`/`high`).

### 4.5 Búsqueda y combobox

`x-pokemon.form-search` mantiene el formulario GET clásico y añade:

- `<label for="term">`, ayuda `aria-describedby`, errores con `aria-invalid`.
- `role="combobox"`, `aria-autocomplete="list"`, `aria-expanded`,
  `aria-controls="term-suggestions"`, `aria-haspopup="listbox"`.
- Listbox `#term-suggestions` y región `aria-live="polite"`.
- Select de tipo con etiqueta real y validación en servidor.

`resources/js/autocomplete.js` hace debounce de 200 ms, cancela peticiones con
`AbortController`, permite ↑/↓/Enter/Escape y navega con puntero. Sin JS el
formulario sigue enviándose.

### 4.6 Chips de filtros

`.filter-chip` muestra el filtro activo y un enlace de quitar con
`aria-label` descriptivo. El botón “Limpiar filtros” usa `btn-outline-secondary`,
cuyos colores salen de `--pk-muted` para cumplir contraste en ambos temas.

### 4.7 Estados vacío, carga y error

- `x-empty-state`: título, descripción, imagen opcional y acciones.
- `.page-load-status` contiene el spinner, el estado “no hay más” y el error con
  botón “Reintentar”; el contenedor está oculto hasta que el scroll lo activa.
- El enlace “Cargar más Pokémon” es el fallback sin JavaScript y se actualiza
  tras cada página.

### 4.8 Estadísticas

Cada fila usa `.stat-row` (grid de etiqueta, barra y valor). La barra es
`role="progressbar"` con `aria-labelledby`, `aria-valuenow`, `aria-valuemin`,
`aria-valuemax` y `aria-valuetext` en español (“45 de 255 (18%)”). En móvil la
etiqueta ocupa la fila completa y la barra comparte línea con el valor.

---

## 5. Patrones de accesibilidad

- **Landmarks:** `nav`, `main`, `footer`; skip link visible al foco.
- **Foco:** `:focus-visible` con outline de 3 px y offset de 2 px; sobre la
  navbar se usa amarillo para contrastar con el rojo.
- **Contraste:** los tokens semánticos y los botones outline están ajustados a
  ≥4.5:1. axe se ejecuta en CI sobre las páginas principales en claro y oscuro.
- **Formularios:** etiqueta visible, ayuda, error asociado y `aria-invalid`.
- **Combobox:** patrón ARIA completo, opción activa con
  `aria-activedescendant`, anuncios en `aria-live`.
- **Scroll infinito:** `aria-busy` en el contenedor, región `aria-live` para
  “Cargando más Pokémon…”, enlace real de respaldo y botón de reintento.
- **Imágenes:** decorativas con `alt=""`; ilustraciones con alt descriptivo;
  dimensiones explícitas para evitar CLS.
- **Movimiento:** respeto a `prefers-reduced-motion`.
- **Tema:** `color-scheme` correcto en claro/oscuro y persistencia sin destello.

---

## 6. Calidad y comandos

```bash
# PHP
php artisan test
vendor/bin/pint --test

# Assets
npm run build
npm run images        # regenera AVIF/WebP y comprime originales

# Navegador (arranca mock de PokeAPI + Laravel en el puerto 8123)
npm run test:e2e      # accesibilidad, HTML y smoke en desktop y móvil
npm run test:a11y     # solo axe
npm run test:html     # solo html-validate
npm run test:visual   # regresión visual (VISUAL=1), baselines por plataforma
npm run lighthouse    # Lighthouse CI contra el servidor e2e
npm run quality       # e2e + Lighthouse
```

El entorno e2e se compone de:

- `tests/e2e/mock-pokeapi.mjs`: PokeAPI compatible, 26 Pokémon deterministas,
  imágenes locales y endpoints `/api/v2/pokemon`, `/api/v2/pokemon/{id}` y
  `/api/v2/type/{type}`.
- `scripts/serve-e2e.mjs`: arranca el mock y `php artisan serve` con
  `POKEMON_BASE_URL`, `CACHE_DRIVER=array`, `SESSION_DRIVER=array` y una
  `APP_KEY` fija de test.
- `playwright.config.js`: proyectos `desktop-chromium` (1280×800) y
  `mobile-chromium` (390×844), `webServer` compartido y capturas en
  `tests/e2e/__screenshots__/<proyecto>-<plataforma>/`.

Las capturas visuales dependen de la plataforma: se generan en Windows y el job
`visual` de CI corre en `windows-latest`. Para actualizarlas:

```bash
npx playwright test tests/e2e/visual.spec.js --update-snapshots
```

### Checklist para un componente nuevo

1. ¿Usa tokens `--pk-*` y funciona en claro y oscuro?
2. ¿Tiene foco visible, nombre accesible y estado anunciado si es dinámico?
3. ¿Mantiene el contraste ≥4.5:1 (axe lo comprobará)?
4. ¿Tiene fallback sin JavaScript si aporta funcionalidad?
5. ¿Usa `x-picture` o `x-icon` en lugar de assets/CDN externos?
6. ¿Añade una prueba feature o e2e si cambia el comportamiento?
7. ¿Actualiza este documento si introduce un token o patrón nuevo?
