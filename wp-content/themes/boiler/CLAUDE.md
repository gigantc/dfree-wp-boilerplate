# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## WordPress Block Development Standards

These rules apply to all block, component, and section work in this theme.

- Always use **vanilla JS** — never jQuery
- Use **SCSS variables** from `src/scss/_variables.scss` instead of raw rgba/hex values
- Use **font shorthand** properties, not longhand (`font-size`, `font-weight`, etc. separately)
- Before delivering any block, **verify PHP class names match SCSS selectors and JS targets**

### Reusable Components

- Before creating any markup in a new block, check `/components/` for existing reusable components (Card, LinkArrow, Button, etc.)
- Always reuse existing components rather than duplicating markup

### ACF Block Conventions

- ACF JSON field groups must include `graphql` properties on **all** field types, including `custom_icon`
- Always use `get_sub_field()` inside repeater loops, never `get_field()`
- Use `sanitize_title()` consistently when generating section IDs or slugs from field values

### Animation Approach

- Prefer GPU-composited animations (`opacity`, `transform`/`translateY`) over height/layout-based animations
- Use GSAP only when genuinely needed — vanilla CSS transitions are preferred for simple show/hide

## Development Commands

### Initial Setup
```bash
npm install
```

### Development
```bash
npm run dev
```
Runs modern build tools with watch mode and BrowserSync:
- Auto-generates `_blocks.scss` from block, component, and section partials
- **Watches for new SCSS files** and regenerates `_blocks.scss` automatically
- Watches and compiles SCSS with Dart Sass
- Watches and bundles JS with esbuild (100x faster than webpack)
- Live reloads on PHP, CSS, JS, and block SCSS changes

**Important**: Update the BrowserSync proxy in `package.json` scripts to match your local domain (currently set to `dfreeboilerplate.local`).

### Production Build
```bash
npm run build
```
Compiles all assets for production:
- Generates `_blocks.scss`
- Compiles and minifies CSS (no source maps)
- Bundles and minifies JS with esbuild (no source maps)
- Outputs to `/dist/css` and `/dist/js`

### Other Commands
```bash
npm run clean                   # Remove all compiled CSS and JS files from /dist
npm run blocks:generate         # Manually regenerate _blocks.scss
npm run blocks:generate:watch   # Watch for new block SCSS files (included in dev)
npm run css:compile             # Compile frontend CSS with Sass only (no PostCSS)
npm run css:prefix              # Run PostCSS autoprefixer on existing CSS
npm run css:build               # Full frontend CSS build (compile + prefix)
npm run css:admin:compile       # Compile admin CSS with Sass only (no PostCSS)
npm run css:admin:prefix        # Run PostCSS autoprefixer on admin CSS
npm run css:admin:build         # Full admin CSS build (compile + prefix)
npm run css:admin:watch         # Watch admin.scss for changes (included in dev)
npm run css:login:compile       # Compile login CSS with Sass only (no PostCSS)
npm run css:login:prefix        # Run PostCSS autoprefixer on login CSS
npm run css:login:build         # Full login CSS build (compile + prefix)
npm run css:login:watch         # Watch login.scss for changes (included in dev)
npm run js:main                 # Bundle main.js only
npm run js:main:prod            # Bundle main.js without source maps
npm run js:libs                 # Bundle libs only
npm run js:libs:prod            # Bundle libs without source maps
npm run blocks:js               # Bundle all block JS files
npm run blocks:js:prod          # Bundle block JS without source maps
npm run components:js           # Bundle all component JS files
npm run components:js:prod      # Bundle component JS without source maps
npm run singles:js              # Bundle all section JS files
npm run singles:js:prod         # Bundle section JS without source maps
```

### Configuration Files

**postcss.config.js** - PostCSS configuration with autoprefixer:
- Targets last 2 browser versions
- Browsers with >1% market share
- Excludes dead browsers
- Automatically adds vendor prefixes (-webkit-, -moz-, -ms-)

### Sass Import Paths

The build uses `--load-path=.` which sets the theme root as the base for imports.

**This means you can use clean paths in your SCSS:**
```scss
// ✅ Clean path (works from anywhere)
@use 'src/scss/variables' as v;

// ❌ Old way (relative paths)
@use '../../../src/scss/variables' as v;
```

**All block SCSS files should use the clean path format.** No more counting `../` levels!

## Architecture Overview

### WordPress Theme Structure
This is a custom WordPress theme built on a modular architecture with Advanced Custom Fields (ACF) Pro for Gutenberg blocks.

### Automatic Block System
The theme uses a **convention-based automatic block registration system** with **cached manifest** in `/blocks`. Key features:

1. **Block Registry with Caching**: Uses `DFREE_Block_Registry` class to cache block metadata in `/blocks/manifest.json`, eliminating filesystem scans on every page load
2. **Auto-Discovery**: All PHP files in `/blocks` subfolders are scanned once and cached
3. **Folder Structure Creates Categories**: Top-level folders in `/blocks` (e.g., `hero/`, `utility/`, `feature/`, `layout/`) automatically become block categories in the WordPress editor
4. **Block Metadata**: Each block folder should contain:
   - `{block-name}.php` - Block template file
   - `block.config.json` - Optional metadata (title, description, keywords)
   - `block.icon.svg` - Optional custom icon for the block picker
   - `block.preview.jpg` - Optional preview image shown in block editor
   - `_{block-name}.scss` - Block styles (must start with underscore)

5. **Automatic SCSS Imports**: The Node script `generate-blocks-scss.js` scans `/blocks`, `/components`, and `/singles` for all `_*.scss` files and auto-generates `@forward` statements in `src/scss/_blocks.scss`. This file is regenerated on every build/watch cycle.

6. **Manifest Regeneration**:
   - **Development**: Auto-rebuilds on every page load for `.local` or `localhost` domains (new blocks appear immediately)
   - **Production**: Uses cached `manifest.json` for performance
   - **Manual rebuild**: Delete `manifest.json` or reactivate theme

### Block Registration Flow
Located in `lib/block-registry.php` and `lib/blocks.php`:
- `DFREE_Block_Registry` class - Manages cached block manifest in `/blocks/manifest.json`
  - `get_blocks()` - Returns cached block list
  - `rebuild_manifest()` - Scans `/blocks` and regenerates manifest file
  - `get_block_file($slug)` - Returns file path for a block from cache
  - `get_block($slug)` - Returns the full cached data array for one block (includes the `editor_static_preview` flag)
- `my_acf_init()` - Registers blocks with ACF using cached manifest (all blocks are ACF v3 — see Block Editor section)
- `my_acf_block_render_callback()` - Includes the correct block template using registry lookup; receives `$is_preview` and renders a static preview for flagged blocks
- `my_plugin_block_categories()` - Creates categories from registry cache
- `acf_allowed_block_types()` - Controls which blocks appear in the editor using registry

### Block Editor (WP 7.0 / ACF v3)
WordPress 7.0 iframes the post editor; all ACF blocks are registered as **version 3**. Key points:

- **Editing model**: fields are edited in the **expanded editor** (not in-canvas — ACF removed in-canvas edit mode under the iframe). The canvas shows a styled, **non-interactive** preview. Inspector fields are hidden via `hide_fields_in_sidebar`.
- **Registration** (`lib/blocks.php`): `add_filter('acf/blocks/default_block_version', fn() => 3)`; per-block `hide_fields_in_sidebar => true`, `expanded_editor_button_text => 'Edit ' . $title`, `supports => ['align' => false, 'html' => false]`.
- **Editor styling**: `main.css` + `admin.css` load into the iframe via `enqueue_block_assets`. Base styles are scoped to `.bdhwk, .editor-styles-wrapper` so previews match the front end. **`main.css` must NOT load in the admin chrome** (only `admin.css` does) — its resets break WP's chrome animations. The iframe rem base is set via `.block-editor-iframe__html{ font-size:62.5% }`.
- **Static editor preview**: blocks that can't render in the editor (carousels, maps, data-driven lists) opt in with `"editor_static_preview": true` in `block.config.json`. The render callback then shows the block's `block.preview.jpg` with a notice instead of the live template (`dfree_block_static_preview()`). Requires a `block.preview.jpg`.

### Asset Compilation (Modern Build)
Modern npm scripts using esbuild, Dart Sass, and PostCSS:
- **SCSS**: Compiles `src/scss/main.scss` → `dist/css/main.css` (Dart Sass CLI)
- **Admin SCSS**: Compiles `src/scss/admin.scss` → `dist/css/admin.css`
- **Login SCSS**: Compiles `src/scss/login.scss` → `dist/css/login.css`
- **PostCSS**: Adds vendor prefixes with autoprefixer after Sass compilation
- **JS Libraries**: Bundles `src/js/libs/modernizr.min.js` → `dist/js/libs/libs.min.js` (esbuild)
- **JS Main**: Bundles `src/js/main.js` → `dist/js/main.min.js` (esbuild with IIFE format)
- **Block JS**: Bundles each `blocks/**/*.js` → `dist/js/blocks/{name}.min.js` (esbuild)
- **Component JS**: Bundles each `components/**/*.js` → `dist/js/components/{name}.min.js` (esbuild)
- **Section JS**: Bundles each `singles/**/*.js` → `dist/js/singles/{name}.min.js` (esbuild)
- **Blocks/Components/Singles SCSS**: Auto-generates `src/scss/_blocks.scss` via Node script (`scripts/generate-blocks-scss.js`)
- **BrowserSync**: Live reload for SCSS, JS, PHP, block, and component changes
- **Source Maps**: Generated in dev mode only, excluded from production builds

**Build Tools**:
- **esbuild** - Lightning-fast JavaScript bundler (written in Go, 100x faster than webpack)
- **Dart Sass** - Official Sass implementation with modern features
- **PostCSS** - CSS post-processor with autoprefixer for vendor prefixes
- **concurrently** - Run multiple watch processes in parallel
- **BrowserSync** - Live reload development server

**Build Pipeline**:
```
SCSS → Sass (compile) → PostCSS (autoprefix) → CSS
JS   → esbuild (bundle + minify) → JS
```

**No build tool abstraction** - Direct CLI commands in `package.json` for full control and transparency.

### PHP Initialization Chain
1. `functions.php` - Requires `lib/init.php`
2. `lib/init.php` - Loads all functionality files:
   - `lib/helpers.php` - Utility functions (`dfree_image()`, `dfree_build_address()`, `dfree_svg()`, etc.)
   - `lib/setup.php` - Theme setup, scripts/styles enqueuing, image sizes
   - `lib/admin.php` - Admin customizations
   - `lib/block-registry.php` - Block registry with caching
   - `lib/blocks.php` - ACF block registration system
   - `lib/component-registry.php` - Component registry with caching
   - `lib/components.php` - Component rendering functions
   - `lib/section-registry.php` - Section registry with caching (for CPT singles)
   - `lib/sections.php` - Section rendering functions (`single()` helper)
   - `lib/acf.php` - ACF options pages

### SCSS Architecture
Main entry point: `src/scss/main.scss`
```scss
@use 'normalize';    // CSS reset
@use 'fonts';        // Font declarations
@use 'base';         // Base styles
@use 'header';       // Global header
@use 'footer';       // Global footer
@use 'error-404';    // 404 page
@use 'blocks';       // Auto-generated block/component/single imports
```

All block, component, and single styles in `_*.scss` partials are automatically imported via the `_blocks.scss` file.

### Self-Hosted Fonts

The boilerplate ships with the **system font stack** (defined in `$font-stack` in `_variables.scss`). To add a custom self-hosted font:

1. Drop the woff2 file into `/src/fonts/`
2. Add an `@font-face` declaration in `_fonts.scss`
3. Update `$font-stack` in `_variables.scss` to use the new family
4. (Optional) Preload the critical weight in `lib/setup.php` via a `wp_head` action
5. Run `npm run build`

### Responsive Images

The theme uses a **centralized image helper** for responsive `srcset`/`sizes` output. All images from ACF fields should use `dfree_image()` instead of raw `<img>` tags.

**Custom Sizes** (registered in `lib/setup.php`):

| Size | Dimensions | Use Case |
|------|-----------|----------|
| `dfree_card` | 800w, proportional | Cards, carousel slides (~400px rendered, 2x retina) |
| `dfree_hero` | 2000w, proportional | Heroes, split images (~1000px rendered, 2x retina) |
| `dfree_square` | 800x800, cropped | Headshots, square thumbnails (~400px rendered, 2x retina) |

**Helper function** (`lib/helpers.php`):
```php
// In block/section/component templates:
dfree_image($image, 'dfree_hero');
dfree_image($image, 'dfree_card', ['alt' => 'Custom alt text']);
dfree_image($image, 'dfree_square', ['class' => 'photo']);
```

- `$image` — ACF image array (return format: array). Must have `'ID'` key for responsive output.
- `$size` — One of the registered sizes above
- `$attrs` — Optional HTML attributes (`alt`, `class`, `sizes`, etc.)

**Default `sizes` attributes per preset:**
- `dfree_card` → `(max-width: 768px) 100vw, 400px`
- `dfree_hero` → `(max-width: 768px) 100vw, 1000px`
- `dfree_square` → `(max-width: 768px) 50vw, 400px`

**How it works:**
- Uses `wp_get_attachment_image()` internally, which auto-generates `srcset` from all available sizes
- WordPress default sizes (medium 300w, medium_large 768w, large 1024w, 1536w, 2048w) fill the srcset gaps
- Falls back to a plain `<img>` tag when no attachment ID is available (e.g., fallback placeholder images)

**Rules:**
- **Never use `$image['url']` directly** in `<img>` tags — always use `dfree_image()`
- For fallback/placeholder images without an attachment ID, pass a URL-only array: `['url' => '...', 'alt' => '...']`

### WordPress Admin Styles
Admin-specific styles: `src/scss/admin.scss`
```scss
@use 'variables' as v;  // Import variables

.editor-styles-wrapper {
  // Gutenberg editor wrapper styles
}

.wp-admin {
  // Block-specific admin overrides
  .hero {
    // Admin-specific styles
  }
}
```

**How it works:**
- `main.css` loads in both frontend and admin for block consistency
- `admin.css` loads after main.css for admin-specific overrides
- Both compile automatically in dev mode
- Use for editor-only styling, preview adjustments, etc.

### Login Page Styles
Login-specific styles: `src/scss/login.scss`

```scss
// Custom login page styles
body.login {
  background-color: #000;
  // Login customizations
}
```

**How it works:**
- Compiles from `src/scss/login.scss` to `dist/css/login.css`
- Loaded only on `/wp-login.php`
- Minified and auto-prefixed in production
- Full SCSS features available (variables, nesting, etc.)

## Block Organization & Folder Structure

The `/blocks` folder uses a **semantic organization system** based on block purpose and intent. This structure is documented in detail at `/blocks/block-folder-structure.md`.

### Main Categories

**layout/** - Structural, content-agnostic blocks organized into subfolders:
- `split/` - Two-column layout patterns (50/50, 30/70, media+content, etc.)
- `grid/` - Repeated multi-column layouts (card grids, logo grids, etc.)
- `carousel/` - Generic carousel engine (minimal styling, no content assumptions)
- `stack/` - Vertical repetition patterns (stacked cards, alternating sections)
- `accordion/` - Generic accordion behavior (expand/collapse, accessibility)

**hero/** - Page entry blocks (high-impact, typically used once per page):
- Standard page heroes
- Split heroes (media + text)
- Hero carousels

**feature/** - Opinionated storytelling/marketing blocks:
- Feature splits (media + text + CTA)
- Icon feature lists
- Stat/metric callouts
- Testimonials
- Case study previews
- FAQ accordions (purpose-specific, not generic)

**utility/** - Helper blocks supporting layout and editing:
- Spacers
- Dividers
- Anchors/jump links
- Headlines
- Text Blocks
- CTA bars

**misc/** - Truly one-off blocks (use sparingly with justification)

### Layout vs Feature Decision

The critical distinction is **intent and reusability**, not whether it's CMS-controlled:

- **Layout** → Structural, reusable pattern that's content-agnostic
- **Feature** → Opinionated design with specific messaging purpose

**Quick test:** "Could this block be reused for a completely different purpose without feeling weird?"
- Yes → `layout/`
- No → `feature/`

See `/blocks/block-folder-structure.md` for complete organization guidelines and examples.

## Component System

The theme includes a **reusable component system** for shared UI elements like buttons, cards, and modals.

### Component Structure
Components live in `/components` with auto-discovery and caching:

```
/components/
  manifest.json          ← Auto-generated cache
  Button/
    button.php           ← Component template
    _button.scss         ← Component styles (must start with _)
    button.js            ← Optional JavaScript
```

### Component Registry
Located in `lib/component-registry.php`:
- `DFREE_Component_Registry` class - Manages cached component manifest in `/components/manifest.json`
  - `get_instance()` - Singleton accessor
  - `get_components()` - Returns all cached components
  - `get_component_file($slug)` - Returns file path for a component from cache
- **Auto-rebuild** in development (`.local` or `localhost` domains)
- **Production** uses cached manifest for performance

### Using Components

**Simple function call:**
```php
// Button component
component('button', [
  'url' => '#',
  'title' => 'Click Me',
  'variant' => 'primary'
]);
```

**In block templates:**
```php
<?php if (have_rows('ctas')) : ?>
  <div class="ctas">
    <?php while (have_rows('ctas')) : the_row();
      $cta = get_sub_field('cta');
      component('button', [
        'url' => $cta['url'],
        'title' => $cta['title'],
        'target' => $cta['target'] ?? '_self',
        'variant' => 'primary'
      ]);
    endwhile; ?>
  </div>
<?php endif; ?>
```

### Creating a New Component

1. Create folder in `/components/{ComponentName}/`
2. Add files:
   - `{component-name}.php` - Template with doc comments explaining parameters
   - `_{component-name}.scss` - Styles (underscore prefix required)
   - `{component-name}.js` - Optional JavaScript
3. Run `npm run build`
4. Use with `component('component-name', $args)`

**Component folder names use spaces** (like blocks): `Card Provider`, not `CardProvider`. The registry slugifies via `sanitize_title()` so the folder name and the PHP filename must agree (`Card Provider/card-provider.php`).

### Included Components

The boilerplate ships with these generic components:
- `Button` — primary, secondary, dark/light, text variants
- `Accordion` — expand/collapse with optional GSAP animation
- `Breadcrumbs` — auto-generates trail from CPT/page hierarchy (configure CPT parents at top of `breadcrumbs.php`)
- `Card` — generic content card
- `Card List` — list of card-like items
- `LinkArrow` — arrow link styling
- `ToggleSwitch` — UI toggle switch

### Component Auto-Loading
- **SCSS**: Automatically imported into `_blocks.scss` (alongside block styles)
- **JavaScript**: Auto-bundled to `dist/js/components/{name}.min.js`
- **Enqueuing**: Component JS only loads when the component is actually used on the page (tracked via `$dfree_used_components` global, enqueued at `wp_footer` priority 5)
- **Libraries**: Components can declare library dependencies in `component.config.json` `requires` array

## Singles System (CPT Single Page Sections)

The theme includes a **section system** for building CPT single page templates from modular, reusable sections. This keeps complex single templates organized into manageable pieces.

### Concept: Blocks vs Components vs Singles
- **Blocks** (`/blocks/`) — ACF Gutenberg blocks for the block editor
- **Components** (`/components/`) — Reusable UI atoms (buttons, cards, breadcrumbs)
- **Singles** (`/singles/`) — CPT single page sections (e.g., provider hero, facility hours)

### Singles Structure
Sections live in `/singles/{cpt}/` with auto-discovery and caching:

```
/singles/
  manifest.json              ← Auto-generated cache
  provider/
    Hero/
      hero.php               ← Section template
      _hero.scss             ← Section styles (must start with _)
      hero.js                ← Optional JavaScript
      section.config.json    ← Optional: library dependencies
  shared/
    Related Items/
      related-items.php
      _related-items.scss
```

### Section Registry
Located in `lib/section-registry.php`:
- `DFREE_Section_Registry` class - Manages cached section manifest in `/singles/manifest.json`
- Path-based keys: sections are keyed by `{cpt}/{slug}` (e.g., `provider/hero`, `shared/related-items`)
- **Auto-rebuild** in development (`.local` or `localhost` domains)

### Using Singles

**Path-based function call:**
```php
// In single-{cpt}.php
single('cpt-name/hero');
single('cpt-name/about');
single('shared/related-items', ['post_type' => 'cpt-name']);
```

### Creating a New Section

1. Create folder in `/singles/{cpt}/{Section Name}/` (space-separated, like blocks)
2. Add files:
   - `{section-name}.php` - Template (NO `is_example` check — sections are not blocks)
   - `_{section-name}.scss` - Styles (underscore prefix required)
   - `{section-name}.js` - Optional JavaScript
   - `section.config.json` - Optional: library dependencies
3. Run `npm run build`
4. Use with `single('{cpt}/{section-name}')` in the single template

### Section Config (Library Dependencies)
Sections can declare library dependencies in `section.config.json`, identical to blocks' `block.config.json`:

```json
{
  "requires": ["swiper"]
}
```

The system automatically enqueues both JS and CSS for required libraries when the section is used.

### Section Auto-Loading
- **SCSS**: Automatically imported into `_blocks.scss` (alongside block and component styles)
- **JavaScript**: Auto-bundled to `dist/js/singles/{name}.min.js`
- **Enqueuing**: Section JS only loads when the section is actually used on the page

## Adding a New Block

1. Create a new folder in `/blocks/{category}/{Block Name}/`
2. Add required files:
   - `{block-name}.php` - Use lowercase-hyphenated name matching folder
   - `_{block-name}.scss` - Styles (must start with underscore)
   - `{block-name}.js` - Optional JavaScript (will be auto-loaded when block is used)
   - `block.config.json` - Optional metadata
   - `block.icon.svg` - Optional custom icon
   - `block.preview.jpg` - Optional preview image
3. The block template should check `get_field('is_example')` to show preview image in the block inserter popup
4. Run `npm run build` - The block will be auto-registered, styles auto-imported, and JS bundled
5. Configure ACF fields in WordPress admin under Custom Fields
6. If the block can't render in the editor (JS-dependent carousel/map, or data-driven and empty without page context), add `"editor_static_preview": true` to `block.config.json` so the canvas shows `block.preview.jpg` instead of a broken render (see Block Editor section)

**Block JavaScript:**
- Each block can have its own JS file (e.g., `carousel.js`)
- Gets bundled to `dist/js/blocks/{block-name}.min.js`
- Only loads when the block is present on the page
- Has access to jQuery by default
- Source maps in dev mode only

## Block SCSS Standards

### Typography
- **Never declare font properties in block SCSS** — no `font-family`, `font-size`, `line-height`, `letter-spacing`, or `font-weight`
- All text styles inherit from `_base.scss`. Block SCSS only sets `color`, `margin`, `padding`, and layout properties on heading/text elements
- Figma designs may use pixel values that don't match the base scale — map to the closest heading level by size:
  - `h1` → 6.4rem
  - `h2` → 4.0rem
  - `h3` → 3.0rem
  - `h4` → 2.0rem ← use for ~24px Figma values
  - `h5` → 1.6rem
  - `h6` → 1.8rem
  - `p` → 16px
- Change the HTML element in the PHP template (e.g. `h3` → `h4`) to match the correct base style rather than overriding font in SCSS

### Padding & Container
- **Padding goes on the root block class**, never on `.container`
- `.container` is a global style from `_base.scss` — never add `padding` or `max-width` to it inside a block
- `.container` is used only as a scope wrapper for targeting children

```scss
.block-name{
  position:relative;
  background-color:v.$light-gray;
  width:100%;
  padding:64px 0px;   // ← padding here

  .container{         // ← no properties, scope only
    .heading{
      h2{ color:v.$primary; }
    }
  }
}
```

### SCSS File Structure
```scss
//////////////////////////////////////
// Block Name

//imports
@use 'src/scss/variables' as v;

.block-name{
  position:relative;
  background-color:v.$lighter-gray;
  width:100%;
  padding:64px 0px;

  .container{
    // child styles
  }
}

//admin display overrides
.wp-admin{
  .block-name{
    // admin-specific tweaks
  }
}
```

- Banner comment (`//////...` + `// Block Name`) is the project convention — always include it
- Admin overrides go **outside** the root block class: `.wp-admin { .block-name { } }`

### SCSS Formatting Conventions
- No spaces after colons (`padding:80px 0px;`)
- No spaces before braces (`.block-name{`)
- DOM-structure nesting (`.container{ .heading{ h2{...} } }`)
- Margins use full longhand (`0px 0px 60px 0px`)
- Use `0px` not `0`
- `//imports` comment (lowercase)
- `//admin display overrides` + `.wp-admin{}` section at bottom of every block SCSS
- Compact formatting, minimal whitespace

### Font Shorthand
- Always use the `font` shorthand: `font:300 16px/20px v.$font-stack;`
- Never use individual properties (`font-size`, `font-family`, `font-weight`, `line-height` separately)
- Format: `font:{weight} {size}/{line-height} {family};`
- Use `normal` for line-height when no specific value: `font:400 18px/normal v.$font-stack;`

## ACF Field Group JSON Standards

When generating ACF field group JSON for import, follow these conventions exactly.

### Group Structure
- **Title**: `"Block: {Block Name}"`
- **`graphql_field_name`**: `"block:PascalCaseName"` (e.g. `"block:AccordionStack"`)
- **`show_in_graphql`**: `1`
- **`map_graphql_types_from_location_rules`**: `0`
- **`graphql_types`**: `""`
- **Location**: `param: "block"`, `operator: "=="`, `value: "acf/{block-slug}"`

### Field Order
1. **Message field** (always first) — `type: "message"`, `label: "Block Type"`, `name: ""`
   - Message body: `<strong style="font-size:24px;">{Block Name}</strong>\r\n\r\n{short description}`
2. **Tab fields** — organize remaining fields into semantic tabs (varies per block)
3. **Data fields** — under their respective tabs

### Field Naming
- **Top-level fields**: prefixed with block slug → `{block_slug}_{field_name}` (e.g. `accordion_stack_headline`)
- **Repeater/group sub-fields**: short names, no prefix (e.g. `title`, `copy`, `description`)
- **Tabs and message fields**: `name: ""` (empty)

### All Data Fields Must Include
```json
"show_in_graphql": 1,
"graphql_description": "",
"graphql_field_name": "camelCaseName",
"graphql_non_null": 0
```

### Other Field Notes
- **No `is_example` field** — do not include it in the field group
- **`allow_in_bindings`**: context-dependent per field
- **Tabs**: `placement: "top"`, `endpoint: 0`, `selected: 0`
- **Repeaters**: use descriptive `button_label` (e.g. `"Add New Item"`); set `collapsed` to the key of the title field or `""` if not needed
- **Sub-fields can use `group` type** to nest related fields within a repeater row

## Block HTML/CSS Philosophy

**Keep markup simple and semantic.** Avoid unnecessary complexity in block templates.

### Simplified HTML Rules

1. **Only use BEM for the root block class**
   ```html
   <!-- ✅ Good -->
   <section class="hero">

   <!-- ❌ Bad -->
   <section class="hero hero--main">
   ```

2. **Child elements use simple class names**
   ```html
   <!-- ✅ Good -->
   <button class="prev">Previous</button>
   <div class="dots"></div>
   <div class="ctas">...</div>

   <!-- ❌ Bad -->
   <button class="hero__prev hero__button">Previous</button>
   ```

3. **Single semantic elements don't need classes**
   ```html
   <!-- ✅ Good - H1 is unique in the slide -->
   <div class="swiper-slide">
     <h1>Headline</h1>
   </div>
   ```
   Style it with: `.hero h1 { ... }`

4. **Eliminate wrapper divs that serve no purpose**

5. **Use CSS nesting instead of verbose class names**
   ```scss
   // ✅ Good - Clean SCSS nesting
   .hero {
     .prev { ... }
     .next { ... }
     .dots { ... }
     h1 { ... }
   }
   ```

### When to Use Classes

- **Third-party library requirements** (e.g., `swiper`, `swiper-slide`)
- **Reusable components** (e.g., `btn`, `container`)
- **Multiple instances of the same element** that need different styling
- **JavaScript hooks** (when you need to target specific elements)

## Script Loading Strategy

The theme uses **automatic script and style loading** based on block requirements:

**Always Loaded:**
- `libs.min.js` - Core libraries (Modernizr)
- `main.min.js` - Theme JavaScript
- jQuery (WordPress default)

**Block-Specific JavaScript (Auto-Loaded):**
The theme automatically enqueues block JavaScript files when blocks are present on the page:
- Managed by `dfree_enqueue_block_scripts()` in `lib/blocks.php`
- Checks each block in the registry for `has_js` flag
- Uses `has_block()` to detect if block is on current page
- Only enqueues the block's JS file if block is present
- Each block's JS is bundled separately to `dist/js/blocks/{block-name}.min.js`
- Zero configuration required - just add a `.js` file to your block folder

**Component-Specific JavaScript (Auto-Loaded):**
Component JavaScript is automatically bundled and enqueued only for components used on the page:
- Managed by `dfree_register_component_scripts()` + `dfree_enqueue_used_component_scripts()` in `lib/components.php`
- Each component's JS is bundled separately to `dist/js/components/{component-name}.min.js`
- Tracked via the `$dfree_used_components` global, set when `component()` is called
- Enqueued at `wp_footer` priority 5 (before footer scripts print)

**Library Auto-Loading (via block.config.json):**
Libraries automatically load when blocks require them. Add to `block.config.json`:

```json
{
  "title": "Hero Carousel",
  "description": "Animated hero carousel",
  "requires": ["swiper"]
}
```

The system automatically enqueues both JS and CSS when the block is present.

**Registered Libraries:**
- **GSAP** - Animation library
- **ScrollTrigger** - Scroll-based animations (requires gsap)
- **Swiper** - Modern carousel/slider library

**How it works:**
1. Register libraries in `lib/setup.php` using `wp_register_script()` and `wp_register_style()`
2. Add library names to block's `requires` array in `block.config.json`
3. System automatically enqueues when block is detected on page
4. Both scripts AND styles are enqueued automatically

**To add a new library:**
```php
// In lib/setup.php
wp_register_script('your-lib', 'https://cdn.example.com/lib.js', array(), '1.0', true);
wp_register_style('your-lib', 'https://cdn.example.com/lib.css', array(), '1.0');
```

Then add `"requires": ["your-lib"]` to any block's `block.config.json`.

**Performance Impact:**
- Block JS: Only loads code for blocks actually used on the page
- Component JS: Only loads code for components actually rendered
- Library auto-loading: Zero overhead, only loads when needed
- No manual conditional checks required

## WYSIWYG Fields

All WYSIWYG field output should be wrapped with a `wysiwyg` CSS class. This enables global styling for links, blockquotes, and lists in `_base.scss`.

```php
// Add to existing wrapper div
<div class="description wysiwyg"><?= $field_value ?></div>

// Or if no wrapper exists
<div class="wysiwyg"><?= $field_value ?></div>
```

Block SCSS that styles `p` elements should use `> p` (direct child) to avoid overriding `.wysiwyg blockquote p` styles.

## CPT Block Templates

Default blocks for new CPT posts can be pre-filled in `lib/blocks.php` via the `register_post_type_args` filter. ACF Pro's CPT UI does not expose WordPress's `template` argument, so it must be added in PHP. Example:

```php
function dfree_cpt_block_templates( $args, $post_type ) {
  if ( $post_type === 'your-cpt' ) {
    $args['template'] = [
      ['acf/hero'],
      ['acf/text-block'],
    ];
  }
  return $args;
}
add_filter( 'register_post_type_args', 'dfree_cpt_block_templates', 20, 2 );
```

## Directory Structure

```
/src/                    ← Source files
  /scss/                 ← SCSS source files
    main.scss           ← Frontend styles entry
    admin.scss          ← Admin/editor styles entry
    login.scss          ← Login page styles
    _variables.scss     ← Variables, mixins, functions
    _base.scss          ← Base styles
    _header.scss        ← Global header
    _footer.scss        ← Global footer
    _error-404.scss     ← 404 page
    _blocks.scss        ← Auto-generated block/component imports
  /js/                   ← JavaScript source files
    main.js
    tag.js
    /libs/              ← Third-party libraries

/dist/                   ← Compiled assets (gitignored source maps)
  /css/
    main.css            ← Compiled frontend CSS
    admin.css           ← Compiled admin CSS
    login.css           ← Compiled login CSS
  /js/
    main.min.js         ← Bundled main JS
    /libs/
      libs.min.js       ← Bundled libraries
    /blocks/
      {block}.min.js    ← Individual block JS
    /components/
      {component}.min.js ← Individual component JS
    /singles/
      {section}.min.js   ← Individual section JS

/blocks/                 ← Block templates and assets
  manifest.json         ← Auto-generated block cache
  block-folder-structure.md ← Folder taxonomy guide
  {category}/
    {BlockName}/
      {block-name}.php
      _{block-name}.scss
      {block-name}.js
      block.config.json

/components/             ← Reusable UI components
  manifest.json         ← Auto-generated component cache
  {ComponentName}/
    {component-name}.php
    _{component-name}.scss
    {component-name}.js

/singles/                ← CPT single page sections (empty by default)
  manifest.json         ← Auto-generated section cache

/lib/                    ← Theme functionality (flat structure)
  init.php              ← Loads all functionality files
  helpers.php           ← Utility functions (dfree_image, dfree_build_address, etc.)
  setup.php             ← Theme setup, scripts/styles enqueuing
  admin.php             ← Admin customizations
  acf.php               ← ACF options pages
  block-registry.php    ← Block manifest & caching
  blocks.php            ← Block registration
  component-registry.php ← Component manifest & caching
  components.php        ← Component rendering
  section-registry.php  ← Section manifest & caching
  sections.php          ← Section rendering (single() helper)
```

## Important Notes

- ACF Pro must be installed
- Targets WordPress 7.0 (iframed editor; all blocks registered as ACF v3 — see Block Editor section)
- Theme text domain is `boiler`
- Custom image sizes registered: `dfree_card` (800w), `dfree_hero` (2000w), `dfree_square` (800x800 cropped)
- `main.css` is loaded on the frontend and into the editor **iframe** (via `enqueue_block_assets`); the admin chrome loads only `admin.css`
- All compiled assets output to `/dist` folder (source files in `/src`)
- Source maps generated only in dev mode, excluded from production builds
- Component and block SCSS auto-imported into main stylesheet
- WordPress emoji scripts disabled globally
