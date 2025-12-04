# dFree's WordPress Theme Boilerplate

**Version 4.0.0** - Modern WordPress theme development, minus the headaches.

A lightning-fast WordPress theme boilerplate built for developers who want to create custom Gutenberg blocks without wrestling with build tools. No Gulp, no webpack drama—just modern tooling that actually makes sense.

## What's This Thing Do?

This boilerplate gives you:

- **Automatic ACF Block System** - Drop a folder in `/blocks`, and boom, it's registered
- **Blazing Fast Builds** - esbuild is 100x faster than webpack (seriously)
- **Smart Block Registry** - Caches block metadata so your site doesn't scan files on every page load
- **Clean Sass Imports** - No more `../../../` nonsense
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

# Clean all compiled files
npm run clean

# Manually regenerate block SCSS imports
npm run blocks:generate

# Bundle block-specific JavaScript files
npm run blocks:js

# Individual builds (rarely needed)
npm run css:build      # Compile CSS only
npm run js:main        # Bundle main.js only
npm run js:libs        # Bundle libraries only
```

### What Happens in Dev Mode

When you run `npm run dev`, it:

1. **Generates Block Imports** - Scans `/blocks` for all `_*.scss` files and creates `src/scss/_blocks.scss`
2. **Compiles Sass** - Converts `src/scss/main.scss` → `css/main.css` (with source maps)
3. **Adds Prefixes** - PostCSS adds vendor prefixes (`-webkit-`, `-moz-`, etc.)
4. **Bundles JavaScript** - esbuild compiles `src/js/main.js` → `js/main.min.js` (with source maps)
5. **Watches Files** - Monitors SCSS and JS for changes, recompiles automatically
6. **Starts BrowserSync** - Live reloads browser on PHP, CSS, JS, and block changes

**Note**: Block JavaScript files are bundled on-demand when you run `npm run build`, not during `npm run dev`.

### What Happens in Production Build

Production builds are optimized for performance:

- **Generates Block Imports** - Scans and creates `src/scss/_blocks.scss`
- **Bundles Block JavaScript** - Each block's `.js` file → `js/blocks/{name}.min.js`
- **Minified CSS** - Compressed output, no source maps
- **Minified JS** - All JavaScript bundled and compressed with esbuild
- **Vendor Prefixes** - Automatically added for browser compatibility
- **No Watch Mode** - One-time build, perfect for deployment

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

The manifest (`blocks/manifest.json`) is automatically rebuilt when:

- Theme is activated or switched
- The manifest file is deleted (it regenerates on next page load)

To manually rebuild:

```bash
# Delete manifest and reload any WordPress page
rm blocks/manifest.json
```

Or activate/deactivate the theme in WordPress admin.

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

### Included Libraries

The theme includes these by default (enqueued in `inc/functions/setup.php`):

- **GSAP** - Animation library
- **ScrollTrigger** - Scroll-based animations
- **Slick Carousel** - Image/content carousels

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
- Get bundled to `js/blocks/image-carousel.min.js`
- Only load on pages that use the carousel block
- Have access to jQuery by default
- Run after DOM is ready

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
├── css/                         # Compiled CSS (auto-generated)
│   └── main.css
│
├── inc/                         # PHP functionality
│   ├── functions/               # Theme functions
│   │   ├── setup.php            # Theme setup, enqueues, image sizes
│   │   └── extras.php           # Helper functions
│   ├── structure/               # Core structure files
│   │   ├── blocks.php           # ACF block registration
│   │   ├── block-registry.php   # Block caching system
│   │   ├── admin.php            # Admin customizations
│   │   ├── core.php             # Core template functions
│   │   ├── posts.php            # Post-related functions
│   │   ├── hooks.php            # Action/filter hooks
│   │   ├── search.php           # Search functionality
│   │   └── acf.php              # ACF options pages
│   └── init.php                 # Loads all functionality
│
├── js/                          # Compiled JavaScript (auto-generated)
│   ├── main.min.js
│   ├── blocks/                  # Block-specific JS (auto-generated)
│   │   └── *.min.js             # Each block's bundled JS
│   └── libs/
│       └── libs.min.js
│
├── scripts/                     # Build scripts
│   ├── generate-blocks-scss.js  # Auto-generates block imports
│   └── bundle-block-js.js       # Bundles block JavaScript files
│
├── src/                         # Source files
│   ├── scss/                    # Sass source files
│   │   ├── main.scss            # Main entry point
│   │   ├── _blocks.scss         # Auto-generated block imports
│   │   ├── _normalize.scss      # CSS reset
│   │   ├── _fonts.scss          # Font declarations
│   │   └── _base.scss           # Base styles
│   └── js/                      # JavaScript source files
│       ├── main.js              # Main entry point
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
- `/css` (compiled CSS)
- `/js` (compiled JavaScript)
- `/inc` (all PHP functionality)
- `/src` (source files, for future edits)
- `functions.php`, `style.css`, theme template files

**Exclude** (add to `.gitignore`):
- `/node_modules`
- `package-lock.json` (or include, depends on your deploy process)
- Source maps (`*.map` files)

### Production Optimization Tips

1. **Enable Object Caching** - Use Redis or Memcached for WordPress
2. **Use a CDN** - Serve static assets from CDN (CloudFlare, etc.)
3. **Minify HTML** - Use a plugin like Autoptimize
4. **Lazy Load Images** - Native lazy loading or a plugin
5. **Database Optimization** - Clean up revisions, transients, etc.

### Regenerating Manifest on Production

If blocks aren't showing up after deployment:

1. Activate a different theme in WordPress admin
2. Reactivate this theme
3. The manifest will rebuild automatically

Or manually delete `/blocks/manifest.json` and load any page.

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

1. **Commit compiled assets** - Include `/css` and `/js` in git
2. **Ignore node_modules** - Never commit dependencies
3. **Include manifest.json** - Prevents rebuild on production
4. **Use .gitignore** - Exclude source maps, editor configs

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
