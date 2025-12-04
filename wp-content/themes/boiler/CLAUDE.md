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
- Bundles and minifies JS with esbuild
- Outputs to `/css` and `/js`

### Other Commands
```bash
npm run clean           # Remove all compiled CSS and JS files
npm run blocks:generate # Manually regenerate _blocks.scss
npm run css:compile     # Compile CSS with Sass only (no PostCSS)
npm run css:prefix      # Run PostCSS autoprefixer on existing CSS
npm run css:build       # Full CSS build (compile + prefix)
npm run js:main         # Bundle main.js only
npm run js:libs         # Bundle libs only
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
   - `block.json` - Optional metadata (title, description, keywords)
   - `admin-icon.svg` - Optional custom icon for the block picker
   - `admin-image.jpg` - Optional preview image shown in block editor
   - `_{block-name}.scss` - Block styles (must start with underscore)

5. **Automatic SCSS Imports**: The Gulp task `generateBlocksScssTask` scans `/blocks` for all `_*.scss` files and auto-generates `@forward` statements in `src/scss/_blocks.scss`. This file is regenerated on every build/watch cycle.

6. **Manifest Regeneration**: The block manifest is automatically rebuilt when:
   - Theme is activated/switched
   - You can manually trigger rebuild by visiting any page if `manifest.json` is missing

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
- **SCSS**: Compiles `src/scss/main.scss` → `css/main.css` (Dart Sass CLI)
- **PostCSS**: Adds vendor prefixes with autoprefixer after Sass compilation
- **JS Libraries**: Bundles `src/js/libs/modernizr.min.js` → `js/libs/libs.min.js` (esbuild)
- **JS Main**: Bundles `src/js/main.js` → `js/main.min.js` (esbuild with IIFE format)
- **Blocks SCSS**: Auto-generates `src/scss/_blocks.scss` via Node script (`scripts/generate-blocks-scss.js`)
- **BrowserSync**: Live reload for SCSS, JS, PHP, and block changes

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
   - `inc/structure/blocks.php` - ACF block registration system
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

## Adding a New Block

1. Create a new folder in `/blocks/{category}/{Block Name}/`
2. Add required files:
   - `{block-name}.php` - Use lowercase-hyphenated name matching folder
   - `_{block-name}.scss` - Styles (must start with underscore)
   - `block.json` - Optional metadata
3. The block template should check `get_field('is_example')` to show preview image in editor
4. Run `npm run dev` - The block will be auto-registered and styles auto-imported
5. Configure ACF fields in WordPress admin under Custom Fields

Example block structure:
```
blocks/
  text/
    Headline/
      headline.php
      _headline.scss
      block.json
      admin-icon.svg
      admin-image.jpg
```

## Important Notes

- ACF Pro must be installed (currently version 6.4.0.1 included)
- Theme text domain is `gigantc`
- Custom image sizes registered: `lawfirm_img_small`, `lawfirm_img_medium`, `lawfirm_img_large`, `lawfirm_img_x_large`, `lawfirm_img_full`, `lawfirm_img_square`
- The theme enqueues GSAP, ScrollTrigger, and Slick Carousel by default
- Main compiled CSS is loaded in both frontend and Gutenberg editor for block styling consistency
