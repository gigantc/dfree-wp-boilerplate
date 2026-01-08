# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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
- Auto-generates `_blocks.scss` from block partials
- **Watches for new block SCSS files** and regenerates `_blocks.scss` automatically
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
3. **Folder Structure Creates Categories**: Top-level folders in `/blocks` (e.g., `text/`, `images/`, `heroes/`) automatically become block categories in the WordPress editor
4. **Block Metadata**: Each block folder should contain:
   - `{block-name}.php` - Block template file
   - `block.config.json` - Optional metadata (title, description, keywords)
   - `block.icon.svg` - Optional custom icon for the block picker
   - `block.preview.jpg` - Optional preview image shown in block editor
   - `_{block-name}.scss` - Block styles (must start with underscore)

5. **Automatic SCSS Imports**: The Gulp task `generateBlocksScssTask` scans `/blocks` for all `_*.scss` files and auto-generates `@forward` statements in `src/scss/_blocks.scss`. This file is regenerated on every build/watch cycle.

6. **Manifest Regeneration**:
   - **Development**: Auto-rebuilds on every page load for `.local` or `localhost` domains (new blocks appear immediately)
   - **Production**: Uses cached `manifest.json` for performance
   - **Manual rebuild**: Delete `manifest.json` or reactivate theme

### Block Registration Flow
Located in `inc/structure/block-registry.php` and `inc/structure/blocks.php`:
- `DFREE_Block_Registry` class - Manages cached block manifest in `/blocks/manifest.json`
  - `get_blocks()` - Returns cached block list
  - `rebuild_manifest()` - Scans `/blocks` and regenerates manifest file
  - `get_block_file($slug)` - Returns file path for a block from cache
- `my_acf_init()` - Registers blocks with ACF using cached manifest
- `my_acf_block_render_callback()` - Includes the correct block template using registry lookup
- `my_plugin_block_categories()` - Creates categories from registry cache
- `acf_allowed_block_types()` - Controls which blocks appear in the editor using registry

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
- **Blocks/Components SCSS**: Auto-generates `src/scss/_blocks.scss` via Node script (`scripts/generate-blocks-scss.js`)
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
1. `functions.php` - Requires `inc/init.php`
2. `inc/init.php` - Loads all functionality files:
   - `inc/functions/setup.php` - Theme setup, scripts/styles enqueuing, image sizes
   - `inc/functions/extras.php` - Helper functions
   - `inc/structure/admin.php` - Admin customizations
   - `inc/structure/core.php` - Core template functions
   - `inc/structure/posts.php` - Post-related functions
   - `inc/structure/hooks.php` - Action/filter hooks
   - `inc/structure/block-registry.php` - Block registry with caching
   - `inc/structure/blocks.php` - ACF block registration system
   - `inc/structure/component-registry.php` - Component registry with caching
   - `inc/structure/components.php` - Component rendering functions
   - `inc/structure/search.php` - Search functionality
   - `inc/structure/acf.php` - ACF options pages

### SCSS Architecture
Main entry point: `src/scss/main.scss`
```scss
@use 'normalize';    // CSS reset
@use 'fonts';        // Font declarations
@use 'base';         // Base styles
@use 'blocks';       // Auto-generated block imports
```

All block styles in `/blocks/**/_*.scss` are automatically imported via the `_blocks.scss` file.

### WordPress Admin Styles
Admin-specific styles: `src/scss/admin.scss`
```scss
@use 'variables' as v;  // Import variables

.editor-styles-wrapper {
  // Gutenberg editor wrapper styles
}

.wp-admin {
  // Block-specific admin overrides
  .hero-carousel {
    // Admin-specific styles
  }
}
```

**How it works:**
- `main.css` loads in both frontend and admin for block consistency
- `admin.css` loads after main.css for admin-specific overrides
- Both compile automatically in dev mode
- Use for editor-only styling, preview adjustments, etc.

**Commands:**
```bash
npm run css:admin:compile  # Compile admin.scss
npm run css:admin:build    # Full build with autoprefixer
npm run css:admin:watch    # Watch mode (included in dev)
```

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

**Commands:**
```bash
npm run css:login:compile  # Compile login.scss
npm run css:login:build    # Full build with autoprefixer
npm run css:login:watch    # Watch mode (included in dev)
```

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
Located in `inc/structure/component-registry.php`:
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
  'variant' => 'primary'  // primary, secondary, text
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

**Example component template:**
```php
<?php
/**
 * Card Component
 *
 * Available parameters:
 * - $title (string) - Card title
 * - $content (string) - Card content
 * - $image (string) - Image URL
 */

// Defaults
$title = $title ?? '';
$content = $content ?? '';
$image = $image ?? '';
?>

<div class="card">
  <?php if ($image) : ?>
    <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($title) ?>">
  <?php endif; ?>
  <h3><?= esc_html($title) ?></h3>
  <p><?= esc_html($content) ?></p>
</div>
```

### Component Auto-Loading
- **SCSS**: Automatically imported into `_blocks.scss` (alongside block styles)
- **JavaScript**: Auto-bundled to `dist/js/components/{name}.min.js`
- **Enqueuing**: Component JS always loads (configured for simple components)
- **No config needed**: Just create the files and run build

## Adding a New Block

1. Create a new folder in `/blocks/{category}/{Block Name}/`
2. Add required files:
   - `{block-name}.php` - Use lowercase-hyphenated name matching folder
   - `_{block-name}.scss` - Styles (must start with underscore)
   - `{block-name}.js` - Optional JavaScript (will be auto-loaded when block is used)
   - `block.config.json` - Optional metadata
   - `block.icon.svg` - Optional custom icon
   - `block.preview.jpg` - Optional preview image
3. The block template should check `get_field('is_example')` to show preview image in editor
4. Run `npm run build` - The block will be auto-registered, styles auto-imported, and JS bundled
5. Configure ACF fields in WordPress admin under Custom Fields

Example block structure:
```
blocks/
  text/
    Headline/
      headline.php
      _headline.scss
      headline.js          ← Optional: Block-specific JavaScript
      block.config.json
      block.icon.svg
      block.preview.jpg
```

**Block JavaScript:**
- Each block can have its own JS file (e.g., `carousel.js`)
- Gets bundled to `dist/js/blocks/{block-name}.min.js`
- Only loads when the block is present on the page
- Has access to jQuery by default
- Runs after DOM is ready
- Source maps in dev mode only

Example `carousel.js`:
```javascript
(function($) {
  'use strict';

  $(document).ready(function() {
    $('.block-carousel').slick({
      dots: true,
      infinite: true,
      speed: 300
    });
  });
})(jQuery);
```

## Block HTML/CSS Philosophy

**Keep markup simple and semantic.** Avoid unnecessary complexity in block templates.

### Simplified HTML Rules

Based on the Hero Carousel implementation, follow these guidelines:

1. **Only use BEM for the root block class**
   ```html
   <!-- ✅ Good -->
   <section class="hero-carousel">

   <!-- ❌ Bad -->
   <section class="hero-carousel hero-carousel--main">
   ```

2. **Child elements use simple class names**
   ```html
   <!-- ✅ Good -->
   <button class="prev">Previous</button>
   <div class="dots"></div>
   <div class="ctas">...</div>

   <!-- ❌ Bad -->
   <button class="hero-carousel__prev hero-carousel__button">Previous</button>
   <div class="hero-carousel__dots hero-carousel__pagination"></div>
   <div class="hero-carousel__ctas hero-carousel__actions">...</div>
   ```

3. **Single semantic elements don't need classes**
   ```html
   <!-- ✅ Good - H1 is unique in the slide -->
   <div class="swiper-slide">
     <h1>Headline</h1>
   </div>

   <!-- ❌ Bad - Unnecessary class -->
   <div class="swiper-slide">
     <h1 class="hero-carousel__headline">Headline</h1>
   </div>
   ```
   Style it with: `.hero-carousel h1 { ... }`

4. **Eliminate wrapper divs that serve no purpose**
   ```html
   <!-- ✅ Good - Container combined with functional wrapper -->
   <div class="swiper-wrapper container">
     <div class="swiper-slide">...</div>
   </div>

   <!-- ❌ Bad - Extra nesting -->
   <div class="swiper-wrapper">
     <div class="hero-carousel__content">
       <div class="container">
         <div class="swiper-slide">...</div>
       </div>
     </div>
   </div>
   ```

5. **Use CSS nesting instead of verbose class names**
   ```scss
   // ✅ Good - Clean SCSS nesting
   .hero-carousel {
     .prev { ... }
     .next { ... }
     .dots { ... }
     h1 { ... }
   }

   // ❌ Bad - Unnecessary BEM repetition
   .hero-carousel__prev { ... }
   .hero-carousel__next { ... }
   .hero-carousel__dots { ... }
   .hero-carousel__headline { ... }
   ```

### When to Use Classes

- **Third-party library requirements** (e.g., `swiper`, `swiper-slide`)
- **Reusable components** (e.g., `btn`, `container`)
- **Multiple instances of the same element** (e.g., multiple buttons that need different styling)
- **JavaScript hooks** (when you need to target specific elements)

### Example: Hero Carousel

**Simplified HTML structure:**
```html
<section class="hero-carousel">
  <div class="swiper">
    <div class="swiper-wrapper container">
      <div class="swiper-slide">
        <h1>Headline</h1>
        <div class="ctas">
          <a href="#" class="btn">Click Me</a>
        </div>
      </div>
    </div>
    <button class="prev">Prev</button>
    <button class="next">Next</button>
    <div class="dots"></div>
  </div>
</section>
```

**Styling approach:**
```scss
.hero-carousel {
  // Block-level styles
  background: black;

  // Child elements via nesting
  h1 {
    font-size: 3rem;
    color: white;
  }

  .prev,
  .next {
    position: absolute;
    // button styles
  }

  .dots {
    // pagination styles
  }

  .ctas {
    display: flex;
    gap: 1rem;
  }
}
```

## Script Loading Strategy

The theme uses **automatic script and style loading** based on block requirements:

**Always Loaded:**
- `libs.min.js` - Core libraries (Modernizr)
- `main.min.js` - Theme JavaScript
- jQuery (WordPress default)

**Block-Specific JavaScript (Auto-Loaded):**
The theme automatically enqueues block JavaScript files when blocks are present on the page:
- Managed by `dfree_enqueue_block_scripts()` in `inc/structure/blocks.php`
- Checks each block in the registry for `has_js` flag
- Uses `has_block()` to detect if block is on current page
- Only enqueues the block's JS file if block is present
- Each block's JS is bundled separately to `dist/js/blocks/{block-name}.min.js`
- Zero configuration required - just add a `.js` file to your block folder

**Component-Specific JavaScript (Auto-Loaded):**
Component JavaScript is automatically bundled and enqueued:
- Managed by `dfree_enqueue_component_scripts()` in `inc/structure/components.php`
- Each component's JS is bundled separately to `dist/js/components/{component-name}.min.js`
- Always loaded (configured for lightweight components)
- Zero configuration required - just add a `.js` file to your component folder

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
1. Register libraries in `inc/functions/setup.php` using `wp_register_script()` and `wp_register_style()`
2. Add library names to block's `requires` array in `block.config.json`
3. System automatically enqueues when block is detected on page (lines 116-136 in setup.php)
4. Both scripts AND styles are enqueued automatically

**To add a new library:**
```php
// In inc/functions/setup.php
wp_register_script('your-lib', 'https://cdn.example.com/lib.js', array(), '1.0', true);
wp_register_style('your-lib', 'https://cdn.example.com/lib.css', array(), '1.0');
```

Then add `"requires": ["your-lib"]` to any block's `block.config.json`.

**Performance Impact:**
- Block JS: Only loads code for blocks actually used on the page
- Library auto-loading: Zero overhead, only loads when needed
- No manual conditional checks required

## Directory Structure

```
/src/                    ← Source files
  /scss/                 ← SCSS source files
    main.scss           ← Frontend styles entry
    admin.scss          ← Admin/editor styles entry
    login.scss          ← Login page styles
    _variables.scss     ← Variables, mixins, functions
    _base.scss          ← Base styles
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

/blocks/                 ← Block templates and assets
  manifest.json         ← Auto-generated block cache
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

/inc/                    ← PHP functionality
  /functions/           ← Setup and helper functions
  /structure/           ← Core architecture files
    block-registry.php
    blocks.php
    component-registry.php
    components.php
```

## Important Notes

- ACF Pro must be installed (currently version 6.4.0.1 included)
- Theme text domain is `gigantc`
- Custom image sizes registered: `lawfirm_img_small`, `lawfirm_img_medium`, `lawfirm_img_large`, `lawfirm_img_x_large`, `lawfirm_img_full`, `lawfirm_img_square`
- Main compiled CSS is loaded in both frontend and Gutenberg editor for block styling consistency
- All compiled assets output to `/dist` folder (source files in `/src`)
- Source maps generated only in dev mode, excluded from production builds
- Component and block SCSS auto-imported into main stylesheet
