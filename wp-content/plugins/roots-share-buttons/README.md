# Roots Share Buttons

Add lightweight social sharing buttons with optional share counts.

## Requirements

* PHP >= 5.3

## Features

* Supported networks: Facebook, Twitter, Google+, LinkedIn, Pinterest
* Easily set button order
* Enable share counts
* Lightweight, mobile-first stylesheet
* Lightweight JavaScript function to pop-up a window when a button is clicked
* Automatically include share buttons:
  * Before and/or after content on archive templates
  * Before and/or after content on single templates
  * On specified post types (custom post types are supported)
* Supports custom share button templates
* Includes a `[share]` shortcode for adding the share buttons within post content

## Customization

The `[share]` shortcode includes the template from `templates/shortcode-share.php`. If you'd like to use a custom template, copy `shortcode-share.php` into the `templates/` directory in your theme and also implement the following example snippet:

```php
/**
 * Custom [share] shortcode template
 */
function custom_roots_share_buttons_template() {
  return get_template_directory() . '/templates/shortcode-share.php';
}
add_action('roots/share_template', 'custom_roots_share_buttons_template');
```

## Removing plugin assets

Roots Share Buttons includes one stylesheet and one JavaScript file. If you'd prefer to implement these assets within your theme (which is recommended), you can remove the plugin assets with this snippet:

```php
/**
 * Remove Roots Share Buttons assets
 */
function remove_roots_share_buttons_assets() {
  wp_dequeue_style('roots-share-buttons');
  wp_dequeue_script('roots-share-buttons');
}
add_action('wp_enqueue_scripts', 'remove_roots_share_buttons_assets');
```
