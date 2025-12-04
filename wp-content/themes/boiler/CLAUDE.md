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
Runs Gulp with watch mode and BrowserSync. Auto-compiles SCSS, transpiles/minifies JS, and reloads on PHP changes.

**Important**: Update the BrowserSync proxy in `gulpfile.js:135` to match your local domain (currently set to `dfreeboilerplate.local`).

### Production Build
```bash
npm run build
```
Cleans output directories and compiles all assets for production. Outputs minified CSS to `/css` and minified JS to `/js`.

## Architecture Overview

### WordPress Theme Structure
This is a custom WordPress theme built on a modular architecture with Advanced Custom Fields (ACF) Pro for Gutenberg blocks.

### Automatic Block System
The theme uses a **convention-based automatic block registration system** in `/blocks`. Key features:

1. **Auto-Discovery**: All PHP files in `/blocks` subfolders are automatically discovered and registered as ACF blocks
2. **Folder Structure Creates Categories**: Top-level folders in `/blocks` (e.g., `text/`, `images/`, `heroes/`) automatically become block categories in the WordPress editor
3. **Block Metadata**: Each block folder should contain:
   - `{block-name}.php` - Block template file
   - `block.json` - Optional metadata (title, description, keywords)
   - `admin-icon.svg` - Optional custom icon for the block picker
   - `admin-image.jpg` - Optional preview image shown in block editor
   - `_{block-name}.scss` - Block styles (must start with underscore)

4. **Automatic SCSS Imports**: The Gulp task `generateBlocksScssTask` scans `/blocks` for all `_*.scss` files and auto-generates `@forward` statements in `src/scss/_blocks.scss`. This file is regenerated on every build/watch cycle.

### Block Registration Flow
Located in `inc/structure/blocks.php`:
- `my_acf_init()` - Scans `/blocks` recursively, registers each block with ACF
- `my_acf_block_render_callback()` - Dynamically includes the correct block template based on block slug
- `my_plugin_block_categories()` - Creates categories from top-level `/blocks` folders
- `acf_allowed_block_types()` - Controls which blocks appear in the editor (defaults to all discovered blocks)

### Asset Compilation (Gulp)
The `gulpfile.js` handles:
- **SCSS**: Compiles `src/scss/main.scss` → `css/main.css` with autoprefixer and minification
- **JS Libraries**: Concatenates `src/js/libs/*.js` → `js/libs/libs.min.js` with Babel transpilation
- **JS Main**: Concatenates `src/js/*.js` → `js/main.min.js` with Babel transpilation
- **Blocks SCSS**: Auto-generates `src/scss/_blocks.scss` before every compile
- **BrowserSync**: Live reload for SCSS, JS, and PHP changes

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
