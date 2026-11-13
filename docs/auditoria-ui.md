# Auditoría de interfaces — Pokédex

> **Fecha:** 12/11/2026  
> **Alcance:** inicio, búsqueda, detalle de Pokémon, 404, 500 y layout global.  
> **Método:** revisión de Blade/SCSS/JS + HTML renderizado en local + cálculo de contraste WCAG 2.2.  
> **Estado:** P0 y P1 implementados; queda P2.  
> **Actualización 12/11/2026 (P0):** layout accesible, títulos, encabezados, formulario, contraste, alt, ARIA de stats y scroll infinito accesible; traducciones de tipos/stats/sprites y recuperación en 404/500.  
> **Actualización 12/11/2026 (P1):** breadcrumb y navegación con `from`, paginación acumulativa con URL compartible, rediseño de tarjetas con badges tintados, chips de filtros removibles, estados vacío/carga, hero fluido, dependencias locales y atribución a PokéAPI.

---

## 1. Resumen ejecutivo

La aplicación tiene una **base funcional sólida**: Laravel 12 + Bootstrap 5.3, grid responsive, búsqueda por nombre y tipo, filtros reflejados en la URL, caché de la API y vistas de error personalizadas. La navegación principal funciona y el flujo “listado → detalle → anterior/siguiente” es comprensible.

Sin embargo, **todavía no cumple WCAG 2.2 AA** por varios motivos concretos:

- 6 de los 18 colores de tipo no alcanzan 4.5:1 con el texto que usan.
- El buscador no tiene etiquetas reales y el selector de tipo tiene `aria-label="Default select example"`.
- No hay `<main>`, enlace de salto, ni una jerarquía de encabezados coherente.
- Todas las páginas comparten el título `Laravel`.
- El scroll infinito no es operable con teclado ni se anuncia a lectores de pantalla, y no tiene fallback sin JavaScript.
- Los enlaces “Anterior/Siguiente” deshabilitados siguen siendo focusables.
- Las barras de estadísticas anuncian un porcentaje con decimales en lugar del valor real.
- Hay textos alternativos en inglés, duplicados o incorrectos.

En diseño visual, la identidad es reconocible pero **la paleta está poco sistematizada**: el rojo se usa para casi todo, las tarjetas acumulan bloques negros y la tipografía decorativa se aplica a nombres pequeños, lo que reduce legibilidad. La propuesta es evolucionar hacia una Pokédex más limpia y enciclopédica: superficies claras, color de tipo como acento, rojo reservado para acciones primarias y una escala tipográfica clara.

### Lo que ya funciona

- `lang="es"` y viewport correctos.
- Grid responsive de 1 a 4 columnas.
- Búsqueda por término y por tipo, con estado en la URL.
- Tarjetas completamente clicables y con `loading="lazy"`.
- Imágenes con dimensiones en las tarjetas (evita gran parte del CLS).
- Vistas 404/500 personalizadas.
- Uso de utilidades Bootstrap y componentes Blade reutilizables.
- Caché y paginación resueltas en backend.

### Prioridad recomendada

1. **P0 — Accesibilidad básica y corrección de fallos** (1–2 días): etiquetas, contraste, encabezados, títulos, alt, enlaces deshabilitados, ARIA de stats y scroll infinito.
2. **P1 — Navegación y sistema visual** (2–4 días): breadcrumb, paginación/cargar más, rediseño de tarjetas, tokens de color, estados de búsqueda y responsive.
3. **P2 — Pulido** (2–3 días): autocompletado, modo oscuro, optimización de imágenes, pruebas automatizadas de accesibilidad.

---

## 2. Diagnóstico por pantalla

| Pantalla | Ruta | Qué funciona | Problemas principales |
|---|---|---|---|
| Inicio | `/` | Hero claro, contador, buscador, grid de tarjetas | Sin `<h1>` único útil (el hero lo es, pero las tarjetas usan `h5`), contador con contraste insuficiente, tarjetas con bloque negro y tipografía decorativa |
| Búsqueda | `/pokemon/search` | Filtros en URL, chips de término/tipo, grid | Empieza en `h4`, sin `h1`; chips con clases inválidas (`px-2.5`); sin acción para limpiar filtros; sin estado de carga |
| Detalle | `/pokemon/{name}` | Imagen, tipos, medidas, stats, sprites, anterior/siguiente | El nombre es `h5`; secciones en `h6`; `row -0`; stats con ancho fijo y ARIA incorrecta; alt de sprites en inglés y repetidos; enlaces deshabilitados focusables |
| 404 | fallback | Vista propia, imagen temática, enlace de vuelta | Imagen sin alt, solo acción “Volver”, copy con “da click”, sin buscador de recuperación |
| 500 | error | Mensaje controlado y detalle solo en debug | Sin `h1`, sin acciones de reintento/inicio, imagen sin alt |
| Layout global | — | Navbar sticky, contenedor Bootstrap | Logo sin alt, sin navegación ni breadcrumb, sin `<main>`, sin skip link, título fijo “Laravel” |

---

## 3. Hallazgos detallados

### 3.1 Navegabilidad

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| N-01 | Alta | El header solo contiene el logo; no hay navegación visible ni breadcrumb. El logo no tiene `alt`, así que un lector anuncia el nombre del archivo. | Añadir `alt="Pokédex, ir al inicio"`, un enlace “Inicio” visible o un breadcrumb `Inicio / Pokémon / Pikachu`. |
| N-02 | Alta | “Volver” depende de `session('backUrl')`. En una pestaña nueva, una entrada directa o varias pestañas abiertas puede llevar a un destino inesperado. | Usar breadcrumb + parámetro `from` explícito; mantener la sesión solo como fallback. |
| N-03 | Alta | El scroll infinito no ofrece paginación visible ni fallback sin JS. Además, la URL pasa a `?page=3`, pero al recargar solo se ven los 16 Pokémon de esa página, no la lista acumulada. Al volver del detalle se pierde la posición. | Ofrecer “Cargar más” como enlace real (`rel="next"`) y/o paginación numerada; si se mantiene el scroll infinito, no reemplazar la URL o renderizar en servidor las páginas anteriores. |
| N-04 | Media | “Anterior” y “Siguiente” solo muestran flechas y la palabra; no indican a qué Pokémon llevan. | Añadir el nombre: “Anterior: Arbok”, “Siguiente: Raichu”; usar `rel="prev"`/`rel="next"`. |
| N-05 | Media | El detalle no muestra el número/posición del Pokémon ni un breadcrumb. | Mostrar `#025` y “25 de 1351” en el encabezado; breadcrumb de navegación. |
| N-06 | Media | 404 y 500 no ofrecen recuperación real: 404 solo “Volver” y 500 ninguna acción. | En 404: buscador + “Ver todos”. En 500: “Reintentar” + “Ir al inicio”. |
| N-07 | Media | Los filtros activos se muestran como badges, pero no se pueden quitar individualmente ni limpiar todos. | Chips con botón “×” y enlace “Limpiar filtros”. |
| N-08 | Media | Los enlaces deshabilitados usan `class="disabled"` y `href="#"`; siguen siendo focusables y al pulsar Enter saltan al inicio. | Renderizar `<span aria-disabled="true">` sin `href`, o añadir `tabindex="-1"` y `aria-disabled="true"`. |
| N-09 | Media | Los enlaces de navegación usan `role="button"`, lo que cambia su semántica: un lector anuncia “botón” pero navegan. | Eliminar `role="button"`; son enlaces. |
| N-10 | Baja | La búsqueda solo admite nombres; “25” no encuentra a Pikachu y “mr mime” no encuentra “mr-mime”. | Normalizar espacios/guiones y aceptar número de Pokédex. |
| N-11 | Baja | El buscador no está disponible en 404/500, justo donde más ayuda. | Incluir el componente de búsqueda en esas vistas. |

### 3.2 Accesibilidad

#### Estructura y semántica

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-01 | Alta | No existe `<main>`; el contenido vive en un `div.container`. No hay enlace “Saltar al contenido”. | Añadir `<main id="main">` y un skip link visible al enfocar. |
| A-02 | Alta | Jerarquía de encabezados incoherente: inicio `h1 → h5`; búsqueda empieza en `h4`; detalle `h5 → h6`; 404 `h2`; 500 sin encabezado. | Un `h1` por página; secciones en `h2`; títulos de tarjeta en `h2`/`h3` o texto no encabezado. |
| A-03 | Alta | Todas las páginas tienen `<title>Laravel</title>`. | Títulos únicos: “Pokédex — Inicio”, “Resultados para «pika» — Pokédex”, “Pikachu — Pokédex”, “Página no encontrada — Pokédex”. |
| A-04 | Media | No hay `meta description` ni Open Graph. | Añadir descripción por página y `og:title`/`og:description`/`og:image`. |

#### Formularios

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-05 | Alta | El input de búsqueda no tiene `<label>`; solo `placeholder`. El placeholder no sustituye a una etiqueta (WCAG 3.3.2). | `<label for="term">` visible o `visually-hidden`, más `aria-describedby` para ayuda/error. |
| A-06 | Alta | El selector de tipo tiene `aria-label="Default select example"`, texto de ejemplo de Bootstrap en inglés y sin relación con la función. | `aria-label="Filtrar por tipo"` o `<label for="type">Tipo</label>`. |
| A-07 | Alta | Los errores de validación no se asocian al campo (`aria-invalid`, `aria-describedby`) ni se enfocan; el selector de tipo no recibe `is-invalid`. | Añadir resumen de errores con `role="alert"`, `aria-invalid="true"`, `aria-describedby` y foco al primer campo inválido. |
| A-08 | Media | La opción vacía dice “tipo” en minúscula y no explica que muestra todos. | “Todos los tipos”. |
| A-09 | Media | El botón “Buscar” no tiene estado de carga y el formulario puede enviarse dos veces mientras la API responde. | Deshabilitar el botón, mostrar spinner y `aria-busy="true"` durante el envío. |

#### Imágenes, iconos y textos alternativos

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-10 | Alta | El logo del navbar no tiene `alt`. | `alt="Pokédex"` o `alt=""` con texto visible “Inicio” al lado. |
| A-11 | Alta | Los sprites del detalle repiten el mismo `alt` en inglés (`"pikachu's sprite"`) para frente, espalda, normal y shiny. Los sprites femeninos usan el nombre masculino. | Alt específico: “Sprite frontal de Pikachu”, “Espalda shiny de Pikachu”, “Sprite frontal de Pikachu hembra”. |
| A-12 | Media | La imagen principal usa `alt="pikachu image"` (inglés). | “Ilustración oficial de Pikachu”. |
| A-13 | Media | Las tarjetas usan `alt="bulbasaur"` y el enlace ya contiene el nombre, así que el lector lo repite. | `alt=""` si el nombre adyacente basta, o “Ilustración de Bulbasaur”. |
| A-14 | Media | Las imágenes de 404/500 no tienen `alt` ni dimensiones. | `alt=""` (decorativas, el texto ya informa) y `width`/`height`. |
| A-15 | Media | Los iconos de Font Awesome no tienen `aria-hidden="true"`; algunos lectores pueden anunciar caracteres extraños. | `aria-hidden="true"` en iconos decorativos o SVG inline. |
| A-16 | Media | El spinner usa `<span class="sr-only"></span>` vacío. `.sr-only` no existe en Bootstrap 5 (fue reemplazado por `.visually-hidden`). | `<span class="visually-hidden">Cargando más Pokémon…</span>`. |
| A-17 | Baja | Las imágenes de estado (“pikachu llorando”, “equipo rocket”) tienen alt descriptivo pero no aportan información; el mensaje ya está en texto. | `alt=""` o alt que describa el estado, no la escena. |

#### Teclado, foco y componentes

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-18 | Alta | No hay estilo de foco consistente para tarjetas y logo; el foco por defecto puede quedar poco visible sobre sombras y superficies. | `:focus-visible` con anillo de 3px y offset de 2px; variante clara sobre navbar oscuro/rojo. |
| A-19 | Alta | El scroll infinito solo se activa con scroll; no hay botón, no se anuncia la carga y el estado está en un contenedor `display:none`. | Botón “Cargar más”, región `role="status" aria-live="polite"`, `aria-busy` en el grid y fallback de paginación. |
| A-20 | Alta | Las barras de stats usan `aria-valuenow` con el porcentaje decimal (13.72) y `aria-valuemax="100"`, mientras el texto visible es el valor real (35). | `aria-valuenow="35"`, `aria-valuemin="0"`, `aria-valuemax="255"`, `aria-valuetext="35 de 255 (14 %)"`, y `aria-labelledby` con el nombre del stat. |
| A-21 | Media | Los botones `btn-sm` miden ~31px de alto; WCAG 2.2 AA exige 24px, pero 44px es el objetivo recomendado para uso táctil. | Altura mínima 44px en acciones principales; mantener `btn-sm` solo en contextos densos. |
| A-22 | Media | Los labels de stats tienen `width: 200px` fijo y la barra `height: 20px`; a 320px o con zoom alto el contenido se comprime y el número dentro de la barra puede quedar recortado. | Grid responsive: `minmax(6rem, 10rem) 1fr auto`; altura mínima y valor fuera de la barra. |
| A-23 | Media | No se respeta `prefers-reduced-motion` (spinner y futuras transiciones). | `@media (prefers-reduced-motion: reduce)` para desactivar animaciones no esenciales. |
| A-24 | Baja | El foco no se gestiona tras cargar resultados o más tarjetas; el usuario de lector puede quedar desorientado. | Anunciar “16 Pokémon más cargados”; opcionalmente enfocar el primer elemento nuevo. |

#### Contraste

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-25 | Alta | 8 tipos no alcanzan 4.5:1 con su texto actual (ver tabla en §4.1). | Corregir texto/fondo según tabla; usar badges tintados con texto oscuro. |
| A-26 | Alta | El contador del inicio usa `text-danger` (#dc3545) sobre `#f8f9fa`: **4.30:1**, por debajo de 4.5 para texto normal. | Usar `#C1121F` (5.90:1) o `#B02A37` (6.16:1). |
| A-27 | Media | `text-muted` (#6c757d) sobre `#f8f9fa` da **4.45:1**, justo por debajo de AA. | Usar `#5C636A` (5.78:1). |
| A-28 | Media | `text-pokemon-red` (#E60013) sobre `#f8f9fa` da **4.55:1**, pasa por muy poco y no es fiable en pantallas de baja calidad. | Reservar #E60013 para fondos con texto blanco; usar #C1121F para texto sobre claro. |
| A-29 | Baja | El logo amarillo/azul sobre navbar rojo tiene contraste amarillo-rojo de **3.15:1** y azul-rojo de **1.15:1**. Los logos están exentos, pero la marca pierde legibilidad. | Fondo de navbar blanco/hueso o tinta oscura, o versión del logo en blanco. |

#### Idioma y contenido

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| A-30 | Alta | Mezcla de español e inglés: “Lets do it!”, “Female Sprites”, “Front default”, “image”, “'s sprite”, “Default select example”, “hp”, “attack”. | Traducir todo; si algo se mantiene en inglés, marcar `lang="en"`. |
| A-31 | Media | Nombres de tipos en inglés (“grass”, “poison”) en un UI en español. | Mapa de traducción: planta, veneno, fuego, agua, etc. |
| A-32 | Media | Nombres de stats en inglés. | PS, Ataque, Defensa, Ataque especial, Defensa especial, Velocidad. |

### 3.3 Interactividad

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| I-01 | Alta | No hay feedback al enviar la búsqueda; con caché fría la API puede tardar varios segundos. | Estado `loading` en el botón, spinner y bloqueo de doble envío. |
| I-02 | Alta | El error del scroll infinito no permite reintentar; si el script de unpkg falla, no hay paginación. | Botón “Reintentar” y fallback de enlace “Cargar más” sin JS. |
| I-03 | Media | Las tarjetas solo cambian el cursor; no hay elevación, transición ni estado `:active`. | `transform: translateY(-2px)`, sombra y transición de 150ms; respetar reduced motion. |
| I-04 | Media | Los estados vacío/fin de lista son un texto en cursiva sin acción. | Estado vacío con ilustración, explicación y acciones “Limpiar filtros” / “Ver todos”. |
| I-05 | Media | Al volver del detalle se pierde el scroll y, con scroll infinito, las páginas cargadas. | Persistir estado o usar paginación real; restaurar scroll con `history.scrollRestoration`. |
| I-06 | Baja | No hay autocompletado ni sugerencias; el usuario debe conocer el nombre exacto. | `datalist` o autocompletado con los nombres ya cacheados; aceptar número. |
| I-07 | Baja | No hay modo oscuro ni preferencia de tema. | Opcional: `data-bs-theme` con tokens y persistencia. |
| I-08 | Baja | El script de infinite-scroll se carga desde unpkg sin integridad ni versión fija. | Empaquetar con Vite o fijar versión + SRI. |

### 3.4 Paleta, tipografía y sistema visual

#### Contraste actual de los tipos

Fórmula WCAG 2.2. Umbral AA para texto normal: **4.5:1**; texto grande: 3:1. Los badges son texto pequeño (~12px), así que aplica 4.5:1.

| Tipo | Fondo actual | Texto actual | Ratio | Acción propuesta | Fondo / texto propuesto | Ratio |
|---|---|---|---|---|---|---|
| steel | `#A8A8C0` | blanco | **2.33** ❌ | texto oscuro | `#A8A8C0` / `#212529` | 6.63 |
| water | `#3899F8` | blanco | **2.97** ❌ | oscurecer fondo | `#1B6BB0` / blanco | 5.56 |
| bug | `#A8B820` | `#212529` | 7.01 ✅ | mantener | — | — |
| dragon | `#7860E0` | blanco | 4.62 ⚠️ | oscurecer para margen | `#5B45B8` / blanco | 7.03 |
| electric | `#F8D030` | `#212529` | 10.33 ✅ | mantener | — | — |
| ghost | `#6060B0` | blanco | 5.53 ✅ | mantener | — | — |
| fire | `#F05030` | blanco | **3.56** ❌ | oscurecer fondo | `#C0392B` / blanco | 5.44 |
| fairy | `#E79FE7` | `#212529` | 7.72 ✅ | mantener | — | — |
| ice | `#58C8E0` | `#212529` | 7.90 ✅ | mantener | — | — |
| fighting | `#A05038` | blanco | 5.68 ✅ | mantener | — | — |
| normal | `#A8A090` | `#212529` | 5.95 ✅ | mantener | — | — |
| grass | `#78C850` | `#212529` | 7.48 ✅ | mantener | — | — |
| psychic | `#F870A0` | blanco | **2.69** ❌ | texto oscuro | `#F870A0` / `#212529` | 5.74 |
| rock | `#B8A058` | blanco | **2.56** ❌ | texto oscuro | `#B8A058` / `#212529` | 6.03 |
| dark | `#7A5848` | blanco | 6.33 ✅ | mantener | — | — |
| ground | `#E9D6A4` | `#212529` | 10.74 ✅ | mantener | — | — |
| poison | `#B058A0` | blanco | **4.42** ❌ | oscurecer fondo | `#8E3B7E` / blanco | 6.82 |
| flying | `#98A8F0` | `#212529` | 6.75 ✅ | mantener | — | — |

> Alternativa recomendada: badges tintados (`background: color-mix(in srgb, tipo 18%, blanco)`, texto `#212529`) con un punto de color sólido. Es más legible, más sereno y elimina el problema de contraste de forma sistémica.

#### Otros hallazgos visuales

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| V-01 | Alta | La paleta no tiene tokens ni reglas de uso; el rojo se usa para navbar, CTA, contador, progreso y enlaces. Pierde jerarquía. | Definir tokens y reservar rojo para marca/acción primaria; azul para secundaria; amarillo solo como acento sobre oscuro. |
| V-02 | Alta | Los tipos usan clases genéricas (`.water`, `.fire`, `.dark`) y texto blanco u oscuro sin criterio; 6 fallan contraste. | Namespace `type-badge--water` o `data-type`, con pares fondo/texto documentados. |
| V-03 | Alta | Cada tarjeta tiene un bloque `bg-dark` con el nombre en “Pokemon Solid”, una fuente decorativa poco legible a 1.25rem. 16 bloques negros generan una pared visual. | Tarjeta blanca con cabecera tintada por tipo; nombre en Nunito 800; reservar la fuente decorativa para logo/hero. |
| V-04 | Media | Los badges llevan `shadow-lg`, texto de ~12px y clases inválidas `px-2.5`/`py-1.5` (no existen en Bootstrap 5), así que el padding real es el de defecto. | Padding `px-3 py-2`, sin sombra o sombra muy sutil, texto 13–14px. |
| V-05 | Media | El fondo `#f8f9fa` y las tarjetas blancas tienen contraste 1.05:1; la separación depende solo de sombras. | Canvas `#F1F3F5`, borde `#DEE2E6` y sombra suave; así las tarjetas se leen sin depender del shadow. |
| V-06 | Media | El hero usa `display-4` (3.5rem) en móvil; ocupa demasiado y empuja el buscador bajo el fold. | `clamp(2rem, 5vw, 3rem)` o `display-6` responsive; hero más compacto. |
| V-07 | Media | En detalle, el nombre es una barra negra `h5` y las secciones son `h6`; la jerarquía visual no refleja la importancia. | Cabecera con imagen, `h1` grande, tipos, ID y medidas; secciones `h2` con separadores claros. |
| V-08 | Media | Nunito se carga solo en peso 400; todos los `fw-bold`/`fw-semibold` son negritas sintéticas. | Cargar 400/600/700/800 (o variable) y declarar `font-display: swap`. |
| V-09 | Media | `@import` remoto de Bunny Fonts dentro del SCSS bloquea el render; el `@font-face` local no tiene `font-display`. | `<link rel="preconnect">` + hoja de estilos en el head, o self-host; `font-display: swap`. |
| V-10 | Media | Espaciado irregular: grid `g-2` muy justo, `hr d-md-none` como separador, `row -0` inválido, `d-inline-block gap-2` sin efecto. | Escala 4/8/12/16/24/32; `g-3`/`g-4`; separadores con utilidades de margen; eliminar clases inválidas. |
| V-11 | Baja | Las páginas 404/500 usan solo el número gigante, sin ilustración ni acciones; son correctas pero frías. | Añadir mensaje breve, buscador y acciones; mantener la estética de números. |
| V-12 | Baja | Faltan favicon/theme-color/OG; la pestaña y las vistas previas no representan la app. | Añadir `favicon`, `theme-color` y metadatos sociales. |

### 3.5 Responsive y layout

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| R-01 | Media | El logo tiene `width="250"` fijo y no tiene `height`; en 320px va justo y provoca CLS. | `width="250" height="45"` (o `aspect-ratio`) y `max-width: 100%`. |
| R-02 | Media | `display-4` y el padding del hero no escalan bien en móvil. | Tipografía fluida con `clamp()`. |
| R-03 | Alta | En stats, el label fijo de 200px + barra de 20px + valor dentro de la barra se comprime en móvil; valores bajos pueden quedar recortados. | Grid responsive con valor fuera de la barra y `min-height`. |
| R-04 | Media | El detalle pagina sprites con `count() / 2`; si hay un número impar, se pierde el último sprite. | `chunk(2)` o grid CSS; no depender de la paridad. |
| R-05 | Media | La imagen principal, el logo y las imágenes de error no tienen dimensiones; hay CLS. | Añadir `width`/`height` o `aspect-ratio`. |
| R-06 | Media | El botón de búsqueda ocupa `col-lg-1`, muy estrecho para “Buscar” con padding. | `col-lg-2` y reequilibrar input/select (7/3/2). |
| R-07 | Baja | La navbar sticky puede tapar anclas internas. | `scroll-margin-top` en secciones y `scroll-padding-top` en `html`. |
| R-08 | Baja | `col-md-8 mx-auto` en 404/500 sin `.row`; funciona por accidente. | Usar `.row > .col-md-8.mx-auto` o un contenedor centrado explícito. |
| R-09 | Baja | `btn-sm` en acciones principales reduce el objetivo táctil. | Altura mínima 44px en móvil. |

### 3.6 Contenido e internacionalización

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| C-01 | Alta | `APP_NAME=Laravel`, `<title>Laravel</title>` y “Bienvenido a la Laravel Pokédex”. | `APP_NAME=Pokédex`; títulos y copy de producto. |
| C-02 | Alta | Textos en inglés mezclados con español (ver A-30). | Traducir todo el UI; centralizar en `lang/es.json` o archivos de traducción. |
| C-03 | Media | “Resultados” con mayúscula, “17 Resultados de la búsqueda:” y “1351 Resultados” no indican cuántos se están mostrando. | “Mostrando 16 de 1351 Pokémon” / “17 resultados para «pika»”. |
| C-04 | Media | “da click en el link” es un anglicismo. | “haz clic en el enlace”. |
| C-05 | Media | “pokémon” y “Pokémon” se usan de forma inconsistente. | Usar “Pokémon” como nombre propio. |
| C-06 | Baja | No hay atribución a PokéAPI. | Añadir “Datos de PokéAPI” en el footer. |
| C-07 | Baja | `resources/views/welcome.blade.php` es la página por defecto de Laravel y no está ruteada. | Eliminar o documentar como no usada. |

### 3.7 Rendimiento y dependencias

| ID | Severidad | Hallazgo | Propuesta |
|---|---|---|---|
| P-01 | Media | Font Awesome se carga por CDN para solo 3 iconos (flechas). | SVG inline o empaquetar solo los iconos usados. |
| P-02 | Media | Bunny Fonts se importa con `@import` y solo peso 400. | Cargar pesos necesarios con `<link>`/self-host y `font-display: swap`. |
| P-03 | Media | `infinite-scroll` se carga desde unpkg sin versión fija ni integridad. | Empaquetar con Vite o fijar versión + SRI. |
| P-04 | Media | Se importa todo Bootstrap JS sin componentes interactivos de Bootstrap. | Eliminar el bundle JS o importar solo lo necesario. |
| P-05 | Media | Todas las imágenes de tarjeta usan `loading="lazy"`, incluidas las primeras (LCP). | `loading="eager"` y `fetchpriority="high"` para las 4–8 primeras. |
| P-06 | Media | Faltan dimensiones en logo, imagen de detalle y errores. | Añadir `width`/`height`/`aspect-ratio`. |
| P-07 | Baja | `rocket.png` pesa 255KB para mostrarse a 200px; los artwork son PNG grandes. | WebP/AVIF, `srcset` y compresión. |
| P-08 | Baja | `pikachusin.jpg` no se usa. | Eliminar. |

---

## 4. Propuesta de sistema visual

### 4.1 Principios

1. **Claridad antes que decoración:** la información del Pokémon es la protagonista; el color de tipo es un acento, no el fondo de toda la tarjeta.
2. **Un solo rojo para la acción principal:** el rojo Pokédex identifica la marca y los CTA; el resto se resuelve con neutros y azul.
3. **Color + texto:** ningún tipo se comunica solo por color; siempre lleva su nombre.
4. **Superficies limpias:** canvas gris muy claro, tarjetas blancas con borde sutil, sombras suaves.
5. **Tipografía legible:** Nunito para UI; la fuente decorativa solo en logo/hero a tamaños grandes.
6. **Estados explícitos:** hover, focus, active, disabled, loading, empty, error y end-of-list.

### 4.2 Tokens de color propuestos

| Token | Hex | Uso | Contraste |
|---|---|---|---|
| `--pk-red-600` | `#E60013` | Fondo de marca/CTA con texto blanco | 4.80:1 con blanco |
| `--pk-red-700` | `#C1121F` | Texto/enlaces sobre claro | 5.90:1 sobre `#f8f9fa` |
| `--pk-blue-600` | `#3269B1` | Acción secundaria, enlaces | 5.54:1 con blanco |
| `--pk-yellow-400` | `#FFCB05` | Acento, foco sobre oscuro | 10.13:1 con `#212529`; nunca con blanco |
| `--pk-ink` | `#212529` | Texto principal | 15.43:1 con blanco |
| `--pk-muted` | `#5C636A` | Texto secundario | 5.78:1 sobre `#f8f9fa` |
| `--pk-canvas` | `#F1F3F5` | Fondo de página | — |
| `--pk-surface` | `#FFFFFF` | Tarjetas/superficies | — |
| `--pk-border` | `#DEE2E6` | Bordes y separadores | — |
| `--pk-focus` | `#0D6EFD` | Anillo de foco en claro | ≥3:1 |
| `--pk-focus-dark` | `#FFCB05` | Anillo de foco en rojo/oscuro | ≥3:1 |

> `#E60013` sigue siendo válido para botones con texto blanco (4.80:1). No usarlo para texto pequeño sobre fondos claros: para eso está `#C1121F`.

### 4.3 Badges de tipo recomendados

**Opción A (cambio mínimo):** mantener fondo sólido y corregir el par fondo/texto según la tabla de §3.4.

**Opción B (recomendada):** badge tintado con texto oscuro y un punto de color:

```scss
$type-colors: (
  grass: #78C850,
  fire: #F05030,
  water: #3899F8,
  // ...
);

.type-badge {
  display: inline-flex;
  align-items: center;
  gap: .375rem;
  padding: .25rem .625rem;
  border-radius: 999px;
  font-size: .8125rem;
  font-weight: 700;
  color: #212529;
  background: color-mix(in srgb, var(--type-color) 18%, #fff);
  border: 1px solid color-mix(in srgb, var(--type-color) 45%, #fff);

  &::before {
    content: "";
    width: .5rem;
    height: .5rem;
    border-radius: 50%;
    background: var(--type-color);
  }
}

@each $name, $color in $type-colors {
  .type-badge--#{$name} { --type-color: #{$color}; }
}
```

Ventajas: contraste alto y uniforme, menos ruido visual, el color sigue identificando el tipo y el texto evita depender solo del color.

### 4.4 Tipografía

| Rol | Fuente | Tamaño | Peso |
|---|---|---|---|
| Logo/hero | Pokemon Solid | ≥2rem | 400 |
| Título de página | Nunito | `clamp(1.75rem, 4vw, 2.5rem)` | 800 |
| Título de sección | Nunito | 1.25–1.5rem | 700 |
| Nombre en tarjeta | Nunito | 1.125rem | 800 |
| Cuerpo | Nunito | 1rem | 400 |
| Metadatos/badges | Nunito | .8125–.875rem | 600/700 |

- Cargar Nunito 400, 600, 700 y 800 (o la variable).
- `font-display: swap` en todas las fuentes.
- No usar Pokemon Solid por debajo de 2rem ni para párrafos.

### 4.5 Componentes y estados

- **Botón primario:** rojo `#E60013`, texto blanco, altura mínima 44px, `border-radius: .5rem`; hover `#C1121F`.
- **Botón secundario:** azul `#3269B1`, texto blanco; hover más oscuro.
- **Botón terciario:** transparente con borde `--pk-border`, texto `--pk-ink`.
- **Focus:** `outline: 3px solid var(--pk-focus); outline-offset: 2px;` y variante amarilla sobre navbar.
- **Tarjeta:** fondo blanco, borde `1px solid var(--pk-border)`, radio 14px, sombra `0 1px 2px rgba(16,24,40,.06)`; hover eleva 2px y aumenta sombra; `:active` vuelve 1px.
- **Badge:** ver §4.3.
- **Estado vacío:** ilustración pequeña + título + texto + 2 acciones.
- **Carga:** skeleton de tarjetas o spinner con texto; no bloquear toda la página.
- **Error:** mensaje + “Reintentar” + “Volver al inicio”.
- **Fin de lista:** mensaje + enlace “Volver arriba”.

---

## 5. Plan de mejora priorizado

### P0 — Accesibilidad y correcciones críticas

| # | Acción | Archivos | Impacto |
|---|---|---|---|
| 1 | Etiquetas reales en buscador y selector; `aria-describedby`, `aria-invalid`, resumen de errores y foco | `components/pokemon/form-search.blade.php` | Formularios usables con lector |
| 2 | Corregir contraste de tipos y textos (`text-danger`, `text-muted`, `text-pokemon-red`) | `sass/app.scss`, `_variables.scss`, vistas | WCAG 1.4.3 |
| 3 | Un `h1` por página, secciones `h2`, títulos únicos y `meta description` | layout, index, search, show, errores | WCAG 2.4.2/1.3.1 |
| 4 | `<main>`, skip link, `nav aria-label`, logo con alt | `layouts/app.blade.php` | WCAG 2.4.1 |
| 5 | Alt correctos: tarjetas, imagen principal, sprites, imágenes decorativas | card, show, errores, scroller | WCAG 1.1.1 |
| 6 | Corregir ARIA de stats y valor visible | `pokemons/show.blade.php` | WCAG 4.1.2 |
| 7 | Enlaces deshabilitados no focusables, sin `role="button"`, iconos `aria-hidden` | show, search, index | Semántica y teclado |
| 8 | Scroll infinito: botón/enlace “Cargar más”, live region, `aria-busy`, fallback sin JS | `infinite-scroller.blade.php` | WCAG 2.1.1/4.1.3 |
| 9 | Focus visible global y en navbar | `app.scss` | WCAG 2.4.7 |
| 10 | Corregir `.sr-only` → `.visually-hidden`, `font-weight-bold`, `px-2.5`, `row -0`, `antialiased` | varias vistas | Limpieza y consistencia |

### P1 — Navegación, búsqueda y sistema visual (implementado)

| # | Acción | Archivos | Estado |
|---|---|---|---|
| 11 | Breadcrumb + “Volver” con `from` explícito; prev/next con nombre y `rel` | `components/breadcrumb`, `show`, `card`, `PokemonController` | ✅ |
| 12 | Paginación acumulativa: `?page=N` renderiza las N páginas; el scroll pide solo la página nueva por AJAX y sincroniza la URL | `PokemonRepository`, controladores, `infinite-scroller`, `app.js` | ✅ |
| 13 | Rediseño de tarjeta: superficie clara, cabecera tintada por tipo, Nunito 800, badges tintados con punto | `card`, `type-badge`, `app.scss` | ✅ |
| 14 | Buscador: chips removibles, “Limpiar filtros”, estado de carga, copy “Mostrando X de Y”, vacío con acciones | `form-search`, `search`, `empty-state`, `app.js` | ✅ |
| 15 | Responsive: hero fluido (`clamp`), logo con dimensiones, stats en grid, sprites con `chunk(2)`, botón más ancho | `index`, `show`, `app.scss` | ✅ |
| 16 | Tipos, stats y sprites traducidos; `APP_NAME=Pokédex` | vistas, `config/pokemon.php`, `PokemonViewModel` | ✅ |
| 17 | 404/500 con acciones de recuperación y buscador | `errors/404`, `errors/500` | ✅ |
| 18 | Dependencias: Bootstrap JS fuera, `infinite-scroll` empaquetado con Vite, Nunito 400/600/700/800, LCP eager | `package.json`, `app.js`, layout, `card` | ✅ |

> **Nota:** el scroll al volver desde el detalle lo restaura el navegador en la mayoría de casos porque la URL y el HTML son acumulativos. El autocompletado y la búsqueda por número quedan para P2.
>
> **Corrección (scroll infinito):** el wrapper usa `data-pk-infinite-scroll` para que `infinite-scroll` no lo auto-inicialice sin opciones; las peticiones AJAX envían `X-Requested-With` y el servidor devuelve solo la página pedida (las cargas completas siguen siendo acumulativas), y la URL se sincroniza con la última página cargada. Así no se duplican tarjetas ni se muestra el estado de error.
>
> **Corrección (memoria):** `paginate()` cargaba el payload completo de PokeAPI (~500 KB en caché y varios MB en PHP) por cada tarjeta acumulada; con 80 tarjetas el pico era de 172 MB y con 128 de 284 MB, hasta agotar `memory_limit`. Ahora se cachean tarjetas ligeras (`id`, `name`, `image`, `types`) y una ficha de detalle recortada a los campos que usa la vista, y las descargas se hacen en lotes de 16. Resultado: 80 tarjetas en 26 MB (0,15 s), 128 en 26 MB y 640 en 68 MB, con la caché en disco bajando de ~27 MB para 82 Pokémon a ~0,3 MB para 160 entradas.

### P2 — Pulido y calidad continua

| # | Acción | Impacto | Estado |
|---|---|---|---|
| 19 | Autocompletado/sugerencias y búsqueda por número | Descubribilidad | ✅ |
| 20 | Modo oscuro con `data-bs-theme` y tokens | Preferencia | ✅ |
| 21 | WebP/AVIF + `srcset`; comprimir `rocket.png`; eliminar `pikachusin.jpg` | Peso | ⏳ |
| 22 | CI con axe-core/Playwright, Lighthouse CI y validación HTML | Prevención de regresiones | ⏳ |
| 23 | Pruebas de regresión visual desktop/móvil | Consistencia | ⏳ |
| 24 | Documentar tokens y patrones de componentes | Mantenibilidad | ⏳ |

> **Autocompletado (19):** el buscador es un combobox ARIA (`role="combobox"`, `aria-autocomplete="list"`, `aria-expanded`/`aria-controls`, listbox `#term-suggestions` y región `aria-live`). Un módulo JS con debounce de 200 ms consulta `GET /pokemon/suggest?term=…`, cancela peticiones anteriores con `AbortController` y permite navegar con ↑/↓/Enter/Escape o con puntero. Las sugerencias se construyen desde el listado ya cacheado (`PokemonSuggestions`) con este orden: prefijo de nombre, número exacto, prefijo de número y coincidencia contenida; se muestran como `#025 Pikachu` y enlazan al detalle con `from` para conservar el breadcrumb de vuelta. Sin JavaScript el formulario sigue funcionando igual.
>
> **Modo oscuro (20):** el tema se aplica con `data-bs-theme` en `<html>`. Un script inline en `<head>` lee `localStorage` (clave `pk-theme`) o `prefers-color-scheme` antes de cargar el CSS para evitar el destello; el módulo `theme.js` gestiona el botón de la navbar (`aria-pressed`, icono sol/luna), persiste la elección y sigue los cambios del sistema mientras el usuario no haya elegido. Los tokens `--pk-*` tienen variantes claras y oscuras, y Bootstrap hereda `--bs-body-bg`, `--bs-body-color` y `--bs-border-color` de ellos para que tarjetas, formularios y alertas compartan la misma paleta. Los colores fijos (`bg-white`, `bg-dark bg-opacity-10`, `text-dark`) se sustituyeron por utilidades temáticas (`bg-body`, `bg-body-secondary`, `text-body`).

---

## 6. Snippets de referencia

### 6.1 Layout con landmark, skip link y título por página

```blade
<a class="visually-hidden-focusable" href="#main">Saltar al contenido</a>

<nav class="navbar bg-pokemon-red shadow-sm py-2 sticky-top" aria-label="Navegación principal">
    <a class="navbar-brand mx-auto" href="{{ route('pokemons.index') }}" aria-label="Pokédex, ir al inicio">
        <img src="{{ asset('vendor/images/logo.png') }}" alt="" width="250" height="45">
    </a>
</nav>

<main id="main" class="container py-4" tabindex="-1">
    {{ $slot }}
</main>
```

> Para títulos por página, añadir una propiedad `$title` al componente `AppLayout` o usar `@section('title')`/`@yield('title')` en el layout.

### 6.2 Formulario de búsqueda accesible

```blade
<form action="{{ route('pokemons.search') }}" method="GET" role="search" aria-label="Buscar Pokémon">
    <div class="row g-2 align-items-end">
        <div class="col-lg-7">
            <label for="term" class="form-label">Nombre o número</label>
            <input
                class="form-control @error('term') is-invalid @enderror"
                type="search"
                name="term"
                id="term"
                placeholder="Ej. pikachu o 25"
                value="{{ old('term', request()->term) }}"
                @error('term') aria-invalid="true" aria-describedby="term-error" @enderror
            >
            @error('term')
                <div id="term-error" class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3">
            <label for="type" class="form-label">Tipo</label>
            <select name="type" id="type" class="form-select text-capitalize @error('type') is-invalid @enderror"
                @error('type') aria-invalid="true" aria-describedby="type-error" @enderror>
                <option value="">Todos los tipos</option>
                @foreach (config('pokemon.types') as $type)
                    <option value="{{ $type }}" @selected(old('type', request()->type) === $type)>
                        {{ __('types.'.$type) }}
                    </option>
                @endforeach
            </select>
            @error('type')
                <div id="type-error" class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-2">
            <button class="btn btn-pokemon-red w-100" type="submit">Buscar</button>
        </div>
    </div>
</form>
```

### 6.3 Barra de estadística accesible

```blade
@php
    $labelId = 'stat-'.$loop->index.'-label';
@endphp

<div class="stat-row">
    <span id="{{ $labelId }}" class="stat-row__label">{{ __('stats.'.$stat->name) }}</span>

    <div
        class="progress"
        role="progressbar"
        aria-labelledby="{{ $labelId }}"
        aria-valuenow="{{ $stat->value }}"
        aria-valuemin="0"
        aria-valuemax="255"
        aria-valuetext="{{ $stat->value }} de 255 ({{ round($stat->percentage) }}%)"
    >
        <div class="progress-bar" style="width: {{ round($stat->percentage, 1) }}%"></div>
    </div>

    <span class="stat-row__value">{{ $stat->value }}</span>
</div>
```

### 6.4 “Cargar más” con fallback sin JS

```blade
<div id="load-more-status" class="visually-hidden" role="status" aria-live="polite"></div>

<a
    class="btn btn-outline-primary"
    href="{{ $nextPageUrl }}"
    rel="next"
    data-load-more
>
    Cargar más Pokémon
</a>

<noscript>
    <a class="btn btn-outline-primary" href="{{ $nextPageUrl }}" rel="next">Cargar más Pokémon</a>
</noscript>
```

> El JS intercepta `data-load-more`, añade tarjetas y actualiza la región `role="status"` con “16 Pokémon más cargados”. Si el JS falla, el enlace sigue funcionando.

### 6.5 Foco visible

```scss
:where(a, button, input, select, textarea, [tabindex]):focus-visible {
    outline: 3px solid var(--pk-focus, #0d6efd);
    outline-offset: 2px;
    border-radius: .25rem;
}

.navbar :focus-visible {
    outline-color: var(--pk-focus-dark, #ffcb05);
}
```

---

## 7. Verificación propuesta

### Manual

- Recorrido completo solo con teclado: logo → buscador → filtros → tarjetas → cargar más → detalle → anterior/siguiente → volver.
- Lector de pantalla (NVDA en Windows o VoiceOver en macOS): títulos, formularios, stats, carga de más tarjetas y errores.
- Zoom 200% y 400%; ancho 320px; comprobar que no hay scroll horizontal y que los valores de stats se leen.
- `prefers-reduced-motion: reduce`.
- Modo de alto contraste de Windows.
- Desactivar JavaScript: debe seguir habiendo forma de ver más Pokémon.
- Navegación con imágenes deshabilitadas: los alt deben seguir teniendo sentido.

### Automatizada

- `axe-core` con Playwright sobre `/`, `/pokemon/search?term=pika`, `/pokemon/pikachu`, `/pokemon/noexiste` y una vista 500 forzada.
- Lighthouse CI con presupuesto de accesibilidad ≥ 95 y performance ≥ 90 en móvil.
- Validador HTML (Nu HTML Checker) para IDs duplicados, roles y anidamientos.
- Test de tokens de contraste: recorrer la tabla de tipos y fallar si algún par baja de 4.5:1.
- Tests de feature que verifiquen: un solo `h1`, `<title>` único, `label` asociado, `alt` presente o vacío explícito, y ausencia de `role="button"` en enlaces.

### Criterios de aceptación

- 0 violaciones críticas/serias de axe.
- Todo texto normal ≥ 4.5:1; texto grande ≥ 3:1; componentes ≥ 3:1.
- Toda la funcionalidad es alcanzable con teclado y visible al enfocar.
- Ningún estado de carga/error queda sin anuncio.
- Sin scroll horizontal a 320px y 400% de zoom.
- Una sola `h1` y un título de página único por vista.
- Formularios con etiqueta, error asociado y foco gestionado.

---

## 8. Checklist rápido de implementación

- [x] `APP_NAME=Pokédex` y títulos únicos por página.
- [x] `<main>` + skip link + `nav aria-label`.
- [x] Logo con `alt` y dimensiones.
- [x] Un `h1` por página; secciones en `h2`; tarjetas sin `h5`.
- [x] Labels en buscador y selector; eliminar `aria-label="Default select example"`.
- [x] Errores con `role="alert"`, `aria-invalid`, `aria-describedby` y foco.
- [x] Corregir contraste de tipos y de textos rojos/grises.
- [x] Badges tintados con texto oscuro y punto de color.
- [x] Alt específicos en sprites; decorativas con `alt=""`.
- [x] Iconos con `aria-hidden="true"`; `.visually-hidden` en lugar de `.sr-only`.
- [x] Stats con `aria-valuenow` real, `aria-valuemax="255"` y valor fuera de la barra.
- [x] Prev/next deshabilitados como `span` o con `tabindex="-1"` + `aria-disabled`.
- [x] Quitar `role="button"` de enlaces.
- [x] “Cargar más” accesible + live region + fallback sin JS.
- [x] Focus visible global.
- [x] Rediseño de tarjeta y tipografía.
- [x] Breadcrumb y “Volver” con contexto explícito (`from` validado).
- [x] Estados vacío/error/fin de lista con acciones.
- [x] Responsive de stats, hero, logo y sprites.
- [x] Traducción completa de tipos, stats y sprites.
- [x] Dependencias: Bootstrap JS fuera, infinite-scroll empaquetado, Nunito con pesos, imágenes LCP eager.
- [ ] CI con axe/Lighthouse y pruebas de regresión visual (P2).

---

## 9. Archivos revisados

- `resources/views/layouts/app.blade.php`
- `resources/views/pokemons/index.blade.php`
- `resources/views/pokemons/search.blade.php`
- `resources/views/pokemons/show.blade.php`
- `resources/views/components/pokemon/card.blade.php`
- `resources/views/components/pokemon/form-search.blade.php`
- `resources/views/components/pokemon/type-badge.blade.php`
- `resources/views/components/breadcrumb.blade.php`
- `resources/views/components/empty-state.blade.php`
- `resources/views/components/infinite-scroller.blade.php`
- `resources/views/errors/404.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/sass/app.scss`
- `resources/sass/_variables.scss`
- `resources/js/app.js`
- `app/View/Components/AppLayout.php`
- `app/ViewModels/PokemonViewModel.php`
- `app/ViewModels/PokemonListViewModel.php`
- `app/Http/Controllers/PokemonController.php`
- `app/Http/Controllers/PokemonSearchController.php`
- `app/Http/Requests/PokemonSearchRequest.php`
- `app/DTOs/PokemonSearchDTO.php`
- `config/pokemon.php`
- `config/app.php`
- `.env`
- `routes/web.php`
