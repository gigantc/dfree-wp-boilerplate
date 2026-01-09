# dFree's WordPress Theme Boilerplate

**Version 4.0.0** - Modern WordPress theme development, minus the headaches.

A lightning-fast WordPress theme boilerplate built for developers who want to create custom Gutenberg blocks without wrestling with build tools. No Gulp, no webpack drama—just modern tooling that actually makes sense.

## What's This Thing Do?

This boilerplate gives you:

- **Automatic ACF Block System** - Drop a folder in `/blocks`, and boom, it's registered
- **Reusable Component System** - Create UI components once, use anywhere with simple `component()` function
- **Blazing Fast Builds** - esbuild is 100x faster than webpack (seriously)
- **Smart Registries** - Caches block and component metadata so your site doesn't scan files on every page load
- **Clean Sass Imports** - No more `../../../` nonsense
- **Modern Asset Structure** - Source files in `/src`, compiled output in `/dist`
- **Live Reload** - BrowserSync watches everything and refreshes instantly
- **Convention Over Configuration** - Folder structure creates block categories automatically

Think of it as the WordPress starter kit you'd build yourself if you had infinite time and patience.

---

## Quick Start

### Prerequisites

- Node.js (v16 or higher)
- WordPress (obviously)
- ACF Pro plugin (included in this theme)

### Get It Running

```bash
# 1. Install dependencies
npm install

# 2. Update BrowserSync proxy
# Edit package.json line 14 and change 'dfreeboilerplate.local' to your local domain

# 3. Start development server
npm run dev
```

That's it. Your browser should open automatically with live reload enabled.

---

## How the Block System Works

This is where things get fun. The block system is built on **convention over configuration**—follow the patterns, and everything just works.

### The Magic Behind It

1. **Block Registry** - On theme activation, the system scans `/blocks` and builds a cached manifest (`manifest.json`)
2. **Auto-Registration** - Every folder in `/blocks` becomes a block category (like `text/`, `images/`, `heroes/`)
3. **Auto-Discovery** - PHP files become blocks, SCSS files become styles, everything links up automatically
4. **Zero Config** - No need to manually register blocks or import stylesheets

### Creating Your First Block

Let's make a "Call to Action" block in the `text` category:

```bash
# 1. Create the folder structure
blocks/
  text/
    Call To Action/
```

**2. Create the PHP template** (`call-to-action.php`):

```php
<?php
/**
 * Block Name: Call To Action
 * Description: A compelling CTA block
 */

// Show preview image in editor
if (get_field('is_example')) : ?>
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/admin-image.jpg" alt="Block Preview">

<?php else :
  // Get ACF fields
  $heading = get_field('cta_heading');
  $button_text = get_field('cta_button_text');
  $button_link = get_field('cta_button_link');
?>

<div class="block-cta">
  <h2><?= esc_html($heading) ?></h2>
  <a href="<?= esc_url($button_link) ?>" class="cta-button">
    <?= esc_html($button_text) ?>
  </a>
</div>

<?php endif; ?>
```

**3. Create the styles** (`_call-to-action.scss`):

```scss
// Note: Must start with underscore!
@use 'src/scss/variables' as v;

.block-cta {
  background: v.$primary-color;
  padding: 3rem;
  text-align: center;

  .cta-button {
    background: white;
    padding: 1rem 2rem;
    border-radius: 8px;
  }
}
```

**4. Add block metadata** (`block.json`):

```json
{
  "title": "Call to Action",
  "description": "A compelling CTA block with heading and button",
  "keywords": ["cta", "button", "action", "conversion"]
}
```

**5. Add optional files**:

- `admin-icon.svg` - Custom icon for the block picker
- `admin-image.jpg` - Preview image shown in the editor (requires `is_example` field)
- `call-to-action.js` - Block-specific JavaScript (auto-loaded when block is used)

**6. Run dev mode**:

```bash
npm run dev
```

The block will automatically:
- Register itself with WordPress
- Get added to the "Text" category
- Have its styles compiled and included
- Be available in the Gutenberg editor

### Configuring ACF Fields

After creating your block files, you need to set up the custom fields:

1. Go to **Custom Fields** in WordPress admin
2. Create a new Field Group
3. Set Location rule: **Block** is equal to **your-block-slug**
4. Add your fields (e.g., `cta_heading`, `cta_button_text`, `cta_button_link`)

**Pro tip**: Always add an `is_example` True/False field to show preview images in the editor.

---

## Block Organization Rules

The folder structure isn't just for looks—it creates your block categories automatically.

### Top-Level Folders = Categories

```
blocks/
  text/          → "Text" category in editor
  images/        → "Images" category
  heroes/        → "Heroes" category
  carousels/     → "Carousels" category
  videos/        → "Videos" category
  misc/          → "Misc" category
```

### Inside Each Category

```
text/
  Call To Action/              ← Folder name becomes block title
    call-to-action.php         ← Required: Block template
    _call-to-action.scss       ← Required: Block styles (underscore required!)
    call-to-action.js          ← Optional: Block-specific JavaScript
    block.json                 ← Optional: Metadata (title, description, keywords)
    admin-icon.svg             ← Optional: Custom icon for block picker
    admin-image.jpg            ← Optional: Preview image for editor
```

### Naming Conventions

- **Folder Name**: Use Title Case with spaces (e.g., "Call To Action")
- **PHP File**: Use lowercase-hyphenated (e.g., `call-to-action.php`)
- **SCSS File**: Same as PHP but with underscore prefix (e.g., `_call-to-action.scss`)
- **Block Slug**: Auto-generated from folder name (e.g., `call-to-action`)

---

## Build System Explained

### The Modern Stack

- **esbuild** - JavaScript bundler (written in Go, stupid fast)
- **Dart Sass** - Official Sass compiler with modern features
- **PostCSS** - Adds vendor prefixes automatically
- **BrowserSync** - Live reload server
- **concurrently** - Runs multiple watch tasks in parallel

### Available Commands

```bash
# Development (watch mode + live reload)
npm run dev

# Production build (minified, no source maps)
npm run build

# Clean all compiled files from /dist
npm run clean

# Individual builds (rarely needed)
npm run blocks:generate        # Regenerate block/component SCSS imports
npm run blocks:generate:watch  # Watch for new block SCSS files
npm run blocks:js              # Bundle block JS (with source maps)
npm run blocks:js:prod         # Bundle block JS (no source maps)
npm run css:build              # Compile frontend CSS only
npm run css:admin:build        # Compile admin CSS only
npm run css:login:build        # Compile login CSS only
npm run js:main                # Bundle main.js (with source maps)
npm run js:main:prod           # Bundle main.js (no source maps)
npm run js:libs                # Bundle libraries (with source maps)
npm run js:libs:prod           # Bundle libraries (no source maps)
```

### What Happens in Dev Mode

When you run `npm run dev`, it:

1. **Generates Block/Component Imports** - Scans `/blocks` and `/components` for all `_*.scss` files and creates `src/scss/_blocks.scss`
2. **Compiles Sass** - Converts:
   - `src/scss/main.scss` → `dist/css/main.css` (with source maps)
   - `src/scss/admin.scss` → `dist/css/admin.css` (with source maps)
   - `src/scss/login.scss` → `dist/css/login.css` (with source maps)
3. **Adds Prefixes** - PostCSS adds vendor prefixes (`-webkit-`, `-moz-`, etc.)
4. **Bundles JavaScript** - esbuild compiles:
   - `src/js/main.js` → `dist/js/main.min.js` (with source maps)
   - `blocks/**/*.js` → `dist/js/blocks/*.min.js` (with source maps)
   - `components/**/*.js` → `dist/js/components/*.min.js` (with source maps)
5. **Watches Everything**:
   - Monitors all SCSS for changes (main, admin, login, blocks, components)
   - Watches `/blocks` and `/components` for new SCSS files (auto-regenerates imports)
   - Watches all JS files for changes
6. **Starts BrowserSync** - Live reloads browser on PHP, CSS, JS, block, and component changes

**New in this version**: Block and component SCSS files are now watched automatically—add a new block or component with SCSS and it's instantly imported without manual intervention!

### What Happens in Production Build

Production builds are optimized for performance:

- **Generates Block/Component Imports** - Scans and creates `src/scss/_blocks.scss`
- **Bundles All JavaScript** - Each block and component's `.js` file → `dist/js/blocks|components/{name}.min.js`
- **Minified CSS** - Compressed output to `dist/css/`, **no source maps**
- **Minified JS** - All JavaScript bundled and compressed to `dist/js/`, **no source maps**
- **Vendor Prefixes** - Automatically added for browser compatibility
- **No Watch Mode** - One-time build, perfect for deployment
- **Smaller Assets** - Source maps excluded means 30-50% smaller file sizes

### The Sass Import Path Magic

The build uses `--load-path=.` which means you can import from the theme root:

```scss
// Clean path (works from anywhere!)
@use 'src/scss/variables' as v;
@use 'src/scss/mixins' as m;

// Old way (don't do this)
@use '../../../src/scss/variables' as v;
```

No more counting `../` levels. Just use clean paths relative to the theme root.

---

## Development Workflow

### Daily Development

```bash
# Start dev server
npm run dev

# Create new blocks in /blocks/{category}/{Block Name}/
# Edit styles in block SCSS files
# Edit scripts in src/js/main.js
# BrowserSync auto-refreshes on save
```

### Adding New Blocks

1. Create folder: `/blocks/{category}/{Block Name}/`
2. Add files:
   - `{block-name}.php` (required)
   - `_{block-name}.scss` (required)
   - `{block-name}.js` (optional - auto-loads when block is used)
   - `block.json` (optional metadata)
3. Save files—build system auto-detects and compiles
4. Go to WordPress admin → Custom Fields → Add field group
5. Block appears in Gutenberg editor automatically

### Editing Existing Blocks

- **PHP Changes** - BrowserSync reloads automatically
- **SCSS Changes** - Compiles and injects new CSS (no page reload!)
- **JS Changes** - Bundles and reloads page

### Managing Block Categories

Want a new category? Just create a top-level folder in `/blocks`:

```bash
mkdir blocks/forms
```

Now all blocks inside `/blocks/forms/` will appear under "Forms" category in the editor.

### Rebuilding the Block Manifest

The manifest (`blocks/manifest.json`) is automatically rebuilt:

- **Development**: Auto-rebuilds on every page load for `.local` or `localhost` domains (new blocks appear immediately)
- **Production**: Uses cached manifest for performance
- **Manual rebuild**: Delete `manifest.json` or reactivate theme

No more manual rebuilds needed during local development!

---

## SCSS Architecture

### Main Entry Point

`src/scss/main.scss` is the master file:

```scss
@use 'normalize';    // CSS reset
@use 'fonts';        // Font declarations
@use 'base';         // Base styles
@use 'blocks';       // Auto-generated block imports
```

### Auto-Generated Block Imports

The `_blocks.scss` file is created automatically:

```scss
// This file is auto-generated by npm run blocks:generate
// Do not edit this file directly—any changes will be overwritten.

@forward '../../blocks/text/Headline/headline';
@forward '../../blocks/text/wysiwyg/wysiwyg';
@forward '../../blocks/images/Image Single/image-single';
@forward '../../blocks/heroes/Main Hero/main-hero';
// ... all other block SCSS files
```

**Never edit `_blocks.scss` manually**—it gets regenerated on every build.

### Creating Shared Variables

Want shared colors, fonts, or breakpoints?

**1. Create `src/scss/_variables.scss`:**

```scss
// Colors
$primary-color: #007bff;
$secondary-color: #6c757d;
$text-color: #212529;

// Typography
$font-family: 'Helvetica Neue', sans-serif;
$base-font-size: 16px;

// Breakpoints
$breakpoint-mobile: 768px;
$breakpoint-tablet: 1024px;
$breakpoint-desktop: 1280px;
```

**2. Import in your blocks:**

```scss
@use 'src/scss/variables' as v;

.block-cta {
  background: v.$primary-color;
  font-family: v.$font-family;

  @media (min-width: v.$breakpoint-tablet) {
    padding: 4rem;
  }
}
```

### PostCSS Autoprefixer

All CSS gets vendor prefixes automatically:

```scss
// You write:
.box {
  display: flex;
  user-select: none;
}

// PostCSS outputs:
.box {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
```

Configured to support:
- Last 2 browser versions
- Browsers with >1% market share
- Excludes dead browsers

### WordPress Admin Styles

Need to style elements in the WordPress admin or Gutenberg editor? Use `src/scss/admin.scss`:

```scss
@use 'variables' as v;

// Gutenberg editor wrapper
.editor-styles-wrapper {
  font-family: v.$dm-sans;
}

// Block inserter preview popup
.wp-admin .block-editor-inserter__preview-container {
  width: 800px !important;

  img {
    object-fit: contain !important;
  }
}

// ACF field styling
.wp-admin .acf-block-body .acf-fields .acf-field-message {
  background-color: #eeeeee !important;
}
```

**How it works:**
- `main.css` loads in both frontend and admin for consistency
- `admin.css` loads after for admin-specific overrides
- Auto-compiles in dev mode with watch
- Keep block-specific admin styles in each block's SCSS using `.wp-admin {}` wrapper

**Commands:**
```bash
npm run css:admin:compile  # Compile admin.scss
npm run css:admin:build    # Full build with autoprefixer
npm run css:admin:watch    # Watch mode (included in dev)
```

### Login Page Styles

Need custom login page styling? Edit `src/scss/login.scss`:

```scss
body.login {
  background-color: #000;

  h1 a {
    background: url('your-logo.svg') no-repeat center;
    // Custom logo styles
  }
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

---

## Component System

**NEW in v4.0**: Reusable UI components make it easy to share elements like buttons, cards, and modals across your blocks.

### What Are Components?

Components are reusable PHP templates with their own SCSS and optional JavaScript. Think of them as building blocks for your blocks.

### Using Components

**Simple function call in any block template:**

```php
// Render a button
component('button', [
  'url' => '#',
  'title' => 'Click Me',
  'variant' => 'primary'  // primary, secondary, text
]);
```

**Real-world example in a block:**

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

### Creating Components

**1. Create folder structure:**

```bash
components/
  Card/
    card.php           # Component template
    _card.scss         # Component styles (underscore required!)
    card.js            # Optional JavaScript
```

**2. Create the template** (`card.php`):

```php
<?php
/**
 * Card Component
 *
 * @param string $title Card title
 * @param string $content Card content
 * @param string $image Image URL
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

**3. Create the styles** (`_card.scss`):

```scss
@use 'src/scss/variables' as v;

.card {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

  img {
    width: 100%;
    border-radius: 4px;
  }

  h3 {
    margin: 1rem 0 0.5rem;
  }
}
```

**4. Run build:**

```bash
npm run build
```

**5. Use it anywhere:**

```php
component('card', [
  'title' => 'My Card',
  'content' => 'Card description here',
  'image' => get_field('card_image')
]);
```

### Component Auto-Loading

Components work just like blocks:

- **Auto-discovered** - Drop folder in `/components`, it's registered
- **SCSS Auto-imported** - Component styles automatically included in main stylesheet
- **JS Auto-bundled** - Component JavaScript bundled to `dist/js/components/{name}.min.js`
- **Cached Manifest** - Component registry cached in `/components/manifest.json`
- **Zero Config** - Just create files and run build

### Built-In Components

This theme includes:

- **Button** - Primary, secondary, and text variants
  ```php
  component('button', [
    'url' => '#',
    'title' => 'Learn More',
    'variant' => 'secondary'
  ]);
  ```

More components coming soon! Create your own following the patterns above.

---

## JavaScript Architecture

### Main Entry Point

`src/js/main.js` is bundled with esbuild:

```javascript
// Import external libraries (if needed)
import $ from 'jquery';

// Your custom code
document.addEventListener('DOMContentLoaded', () => {
  console.log('Theme loaded!');

  // Initialize your components here
});
```

### Auto-Loading Libraries

Libraries are automatically loaded when blocks require them. Just add to your `block.config.json`:

```json
{
  "title": "Hero Carousel",
  "description": "Animated hero with carousel",
  "requires": ["swiper"]
}
```

The theme will automatically enqueue both JS and CSS for any registered library when the block is present on a page.

**Registered Libraries:**
- **GSAP** - Animation library
- **ScrollTrigger** - Scroll-based animations
- **Swiper** - Modern carousel/slider library

**How it works:**
1. Register libraries in `inc/functions/setup.php` using `wp_register_script()` and `wp_register_style()`
2. Add library names to block's `requires` array in `block.config.json`
3. System automatically enqueues when block is detected on page
4. Zero manual conditional loading needed!

### Adding New Libraries

**Option 1: CDN (Recommended for large libraries)**

Edit `inc/functions/setup.php`:

```php
wp_enqueue_script('your-library', 'https://cdn.example.com/library.js', array(), null, true);
```

**Option 2: Bundle with esbuild**

```bash
npm install your-library
```

Then import in `src/js/main.js`:

```javascript
import YourLibrary from 'your-library';
```

### Block-Specific JavaScript

Each block can have its own JavaScript file that gets automatically bundled and loaded **only when the block is present on the page**.

**How It Works:**

1. Add a `.js` file to your block folder with the same name as your block
2. Run `npm run build` or `npm run dev`
3. The JS gets bundled to `js/blocks/{block-name}.min.js`
4. WordPress automatically loads it only on pages with that block

**Example**: Creating a carousel block with JavaScript:

**1. Create `blocks/carousels/Image Carousel/image-carousel.js`:**

```javascript
(function($) {
  'use strict';

  $(document).ready(function() {
    $('.block-carousel').slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 3,
      responsive: [
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    });
  });
})(jQuery);
```

**2. Run the build:**

```bash
npm run build
```

**3. Done!** The JavaScript will:
- Get bundled to `dist/js/blocks/image-carousel.min.js`
- Only load on pages that use the carousel block
- Have access to jQuery by default
- Run after DOM is ready
- Include source maps in dev mode, excluded in production

**Alternative Options (if auto-loading doesn't fit your needs):**

**Manual Inline Scripts:**

```php
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Block-specific code here
});
</script>
```

**Manual Conditional Enqueue:**

```php
function enqueue_block_scripts() {
  if (has_block('acf/your-block-slug')) {
    wp_enqueue_script('your-block-js', get_template_directory_uri() . '/js/your-block.js', array(), '1.0', true);
  }
}
add_action('wp_enqueue_scripts', 'enqueue_block_scripts');
```

---

## Project Structure

```
boiler/
├── blocks/                      # ACF blocks (auto-registered)
│   ├── carousels/
│   ├── heroes/
│   ├── images/
│   ├── misc/
│   ├── text/
│   ├── videos/
│   └── manifest.json            # Cached block registry (auto-generated)
│
├── components/                  # Reusable UI components (auto-registered)
│   ├── Button/
│   │   ├── button.php
│   │   └── _button.scss
│   └── manifest.json            # Cached component registry (auto-generated)
│
├── dist/                        # Compiled assets (auto-generated)
│   ├── css/
│   │   ├── main.css            # Frontend styles
│   │   ├── admin.css           # Admin/editor styles
│   │   └── login.css           # Login page styles
│   └── js/
│       ├── main.min.js
│       ├── blocks/             # Block-specific JS
│       │   └── *.min.js
│       ├── components/         # Component-specific JS
│       │   └── *.min.js
│       └── libs/
│           └── libs.min.js
│
├── inc/                         # PHP functionality
│   ├── functions/               # Theme functions
│   │   ├── setup.php            # Theme setup, enqueues, image sizes
│   │   └── extras.php           # Helper functions
│   ├── structure/               # Core structure files
│   │   ├── blocks.php           # ACF block registration
│   │   ├── block-registry.php   # Block caching system
│   │   ├── components.php       # Component rendering functions
│   │   ├── component-registry.php # Component caching system
│   │   ├── admin.php            # Admin customizations
│   │   ├── core.php             # Core template functions
│   │   ├── posts.php            # Post-related functions
│   │   ├── hooks.php            # Action/filter hooks
│   │   ├── search.php           # Search functionality
│   │   └── acf.php              # ACF options pages
│   └── init.php                 # Loads all functionality
│
├── scripts/                     # Build scripts
│   ├── generate-blocks-scss.js  # Auto-generates block/component imports
│   ├── bundle-block-js.js       # Bundles block JavaScript files
│   ├── bundle-main-js.js        # Bundles main JavaScript
│   └── ensure-dist-dirs.js      # Creates /dist directory structure
│
├── src/                         # Source files
│   ├── scss/                    # Sass source files
│   │   ├── main.scss            # Main frontend entry point
│   │   ├── admin.scss           # WordPress admin styles
│   │   ├── login.scss           # Login page styles
│   │   ├── _blocks.scss         # Auto-generated block/component imports
│   │   ├── _normalize.scss      # CSS reset
│   │   ├── _fonts.scss          # Font declarations
│   │   ├── _base.scss           # Base styles
│   │   └── _variables.scss      # Shared variables, mixins, functions
│   └── js/                      # JavaScript source files
│       ├── main.js              # Main entry point
│       ├── tag.js               # Additional JS files
│       └── libs/
│           └── modernizr.min.js
│
├── functions.php                # WordPress theme entry point
├── style.css                    # Theme metadata (required by WordPress)
├── package.json                 # npm dependencies and scripts
├── postcss.config.js            # PostCSS configuration
├── README.md                    # This file
└── CLAUDE.md                    # Architecture docs for Claude AI
```

---

## Deployment

### Before Deploying

```bash
# 1. Run production build
npm run build

# 2. Test everything locally
# Check all blocks render correctly
# Verify CSS is minified (no source maps)
# Verify JS works without errors

# 3. Commit changes
git add .
git commit -m "Build for production"
```

### What to Deploy

**Include**:
- `/blocks` (PHP templates + manifest.json)
- `/components` (PHP templates + manifest.json)
- `/dist` (all compiled CSS and JavaScript)
- `/inc` (all PHP functionality)
- `/src` (source files, for future edits)
- `functions.php`, `style.css`, theme template files

**Exclude** (add to `.gitignore`):
- `/node_modules`
- `package-lock.json` (or include, depends on your deploy process)
- `dist/**/*.map` (source maps - dev only)

### Production Optimization Tips

1. **Enable Object Caching** - Use Redis or Memcached for WordPress
2. **Use a CDN** - Serve static assets from CDN (CloudFlare, etc.)
3. **Minify HTML** - Use a plugin like Autoptimize
4. **Lazy Load Images** - Native lazy loading or a plugin
5. **Database Optimization** - Clean up revisions, transients, etc.

### Regenerating Manifest on Production

If blocks or components aren't showing up after deployment:

1. Activate a different theme in WordPress admin
2. Reactivate this theme
3. The manifests will rebuild automatically

Or manually delete `/blocks/manifest.json` and `/components/manifest.json` and load any page.

---

## Troubleshooting

### BrowserSync Won't Start

**Problem**: Browser doesn't open, or you see "proxy" errors.

**Solution**: Update the BrowserSync proxy in `package.json` line 14:

```json
"sync": "browser-sync start --proxy YOUR-LOCAL-DOMAIN.test --files ..."
```

Replace `YOUR-LOCAL-DOMAIN.test` with your actual local development URL.

---

### Blocks Not Appearing in Editor

**Problem**: Created a new block, but it doesn't show up in Gutenberg.

**Solution**:
1. Check folder structure: `/blocks/{category}/{Block Name}/{block-name}.php`
2. Verify file naming: PHP file must be lowercase-hyphenated
3. Rebuild manifest: Switch themes back and forth in WordPress admin
4. Check error logs: Look for PHP errors in `wp-content/debug.log`

---

### Styles Not Updating

**Problem**: Changed SCSS, but CSS isn't updating.

**Solution**:
1. Check console for Sass errors
2. Verify file starts with underscore (`_block-name.scss`)
3. Hard refresh browser (`Cmd+Shift+R` or `Ctrl+Shift+R`)
4. Check that `npm run dev` is still running

---

### Import Path Errors in SCSS

**Problem**: Getting "File to import not found" errors.

**Solution**: Use clean paths from theme root:

```scss
// Correct
@use 'src/scss/variables' as v;

// Incorrect
@use '../../../variables' as v;
```

---

### JavaScript Not Bundling

**Problem**: JS changes don't appear in browser.

**Solution**:
1. Check for syntax errors in console
2. Verify `npm run dev` is running
3. Check `src/js/main.js` is the entry point
4. Hard refresh browser

---

### ACF Fields Not Saving

**Problem**: Created ACF field group, but fields don't show in editor.

**Solution**:
1. Check Location rules: Block must match your block slug
2. Verify block slug: Check `/blocks/manifest.json` for exact slug
3. Clear cache: Deactivate/reactivate theme

---

## Advanced Customization

### Custom Image Sizes

Edit `inc/functions/setup.php`:

```php
// Add custom image sizes
add_image_size('my_custom_size', 800, 600, true); // 800px wide, 600px tall, hard crop
```

Then use in templates:

```php
<?php
$image = get_field('featured_image');
echo wp_get_attachment_image($image['ID'], 'my_custom_size');
?>
```

---

### Custom ACF Options Pages

Edit `inc/structure/acf.php`:

```php
if (function_exists('acf_add_options_page')) {
  acf_add_options_page(array(
    'page_title' => 'Site Settings',
    'menu_title' => 'Site Settings',
    'menu_slug'  => 'site-settings',
    'capability' => 'edit_posts',
    'redirect'   => false
  ));
}
```

---

### Limiting Allowed Blocks

Edit `inc/structure/blocks.php` in `acf_allowed_block_types()`:

```php
// Show only custom blocks (no core blocks)
return $block_names; // Already done by default

// Allow specific core blocks
$allowed = array_merge($block_names, array(
  'core/paragraph',
  'core/heading',
  'core/image',
));
return $allowed;
```

---

### BrowserSync Configuration

Edit `package.json` to customize BrowserSync:

```json
"sync": "browser-sync start --proxy YOUR-DOMAIN.test --files '**/*.php' 'css/**/*.css' 'js/**/*.js' 'blocks/**/*.scss' --ignore 'node_modules' --reload-delay 100 --no-notify --open false"
```

Options:
- `--open false` - Don't auto-open browser
- `--no-notify` - Hide BrowserSync notifications
- `--reload-delay 100` - Delay reload by 100ms (helps with rapid changes)

---

## Block HTML/CSS Philosophy

**Keep markup simple and semantic.** Avoid unnecessary complexity in block templates.

### Simplified HTML Rules

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
- **Multiple instances of the same element** needing different styling
- **JavaScript hooks** (when you need to target specific elements)

---

## Tips & Best Practices

### Block Development

1. **Always add `is_example` field** - Makes editor previews work
2. **Use semantic HTML** - Good for SEO and accessibility
3. **Escape output** - Use `esc_html()`, `esc_url()`, `esc_attr()`
4. **Test on mobile** - BrowserSync shows multiple devices
5. **Use block.json** - Better search/discoverability in editor

### SCSS Organization

1. **Use clean import paths** - No relative paths
2. **Create shared variables** - Colors, fonts, breakpoints
3. **Prefix block classes** - Use `.block-{name}` for specificity
4. **Mobile-first CSS** - Base styles for mobile, media queries for larger screens
5. **Use Sass modules** - `@use` instead of `@import`

### Performance

1. **Minimize block complexity** - Simple blocks = fast pages
2. **Lazy load images** - Use native `loading="lazy"` attribute
3. **Use manifest caching** - Don't delete `manifest.json` on production
4. **Optimize images** - Use WebP format when possible
5. **Limit ACF queries** - Cache results with transients if needed

### Version Control

1. **Commit compiled assets** - Include `/dist` folder in git
2. **Ignore node_modules** - Never commit dependencies
3. **Include manifests** - Prevents rebuild on production (`blocks/manifest.json`, `components/manifest.json`)
4. **Use .gitignore** - Exclude source maps (`dist/**/*.map`), node_modules, editor configs

---

## Theme Configuration

### Important Constants

Located in `style.css` (WordPress theme header):

- **Theme Name**: dFree's WP Boilerplate
- **Version**: 4.0.0 (update in `package.json` and `style.css`)
- **Text Domain**: `gigantc` (used for translations)
- **Author**: Dan Freeman

### Included Plugins

- **ACF Pro** - Version 6.4.0.1 (included in theme)

Make sure ACF Pro stays activated—the entire block system depends on it.

---

## Credits & License

Built by Dan Freeman (gigantc.com)

This theme is licensed under GPL v2 or later. Do whatever you want with it—build amazing things.

---

## Need Help?

### Useful Resources

- [ACF Documentation](https://www.advancedcustomfields.com/resources/)
- [esbuild Documentation](https://esbuild.github.io/)
- [Sass Documentation](https://sass-lang.com/documentation)
- [BrowserSync Documentation](https://browsersync.io/docs)

### Common Issues

Check `CLAUDE.md` for technical architecture details and development notes.

---

**Happy theme building!** May your blocks be plentiful and your build times swift.
