<?php
/**
 * Block Registry System
 *
 * Caches block metadata to eliminate filesystem scans on every page load.
 * Builds a manifest.json file containing all block information.
 *
 * @package boiler
 */

class DFREE_Block_Registry {
  private static $instance = null;
  private $manifest_path;
  private $blocks_dir;
  private $blocks = array();

  private function __construct() {
    $this->manifest_path = get_theme_file_path('/blocks/manifest.json');
    $this->blocks_dir = get_theme_file_path('/blocks');
  }

  /**
   * Get singleton instance
   */
  public static function get_instance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Get all blocks from cache
   */
  public function get_blocks() {
    if (empty($this->blocks)) {
      $this->load_manifest();
    }
    return $this->blocks;
  }

  /**
   * Load manifest from file or rebuild if missing
   */
  private function load_manifest() {
    // Try loading from manifest file
    if (file_exists($this->manifest_path)) {
      $manifest_content = file_get_contents($this->manifest_path);
      $manifest = json_decode($manifest_content, true);

      if ($manifest && isset($manifest['blocks'])) {
        $this->blocks = $manifest['blocks'];
        return;
      }
    }

    // Fallback: rebuild manifest if file missing or invalid
    $this->rebuild_manifest();
  }

  /**
   * Rebuild the manifest by scanning the blocks directory
   */
  public function rebuild_manifest() {
    $blocks = array();

    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($this->blocks_dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
      if ('php' === $file->getExtension()) {
        $block_data = $this->extract_block_data($file);
        if ($block_data) {
          $blocks[$block_data['slug']] = $block_data;
        }
      }
    }

    // Save manifest
    $manifest = array(
      'generated' => current_time('mysql'),
      'blocks' => $blocks
    );

    file_put_contents(
      $this->manifest_path,
      json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      LOCK_EX
    );

    $this->blocks = $blocks;
  }

  /**
   * Extract block metadata from file
   */
  private function extract_block_data($file) {
    $folder = dirname($file->getPathname());
    $slug = sanitize_title(basename($folder));
    $category = 'block-' . sanitize_title(basename(dirname($folder)));

    // Read block.json if exists
    $meta_path = $folder . '/block.json';
    $meta = array();
    if (file_exists($meta_path)) {
      $meta_content = file_get_contents($meta_path);
      $meta = json_decode($meta_content, true);
    }

    // Read SVG icon if exists
    $icon_path = $folder . '/admin-icon.svg';
    $icon = '';
    if (file_exists($icon_path)) {
      $icon = file_get_contents($icon_path);
    }

    // Check if block has JavaScript file
    $js_path = $folder . '/' . $slug . '.js';
    $has_js = file_exists($js_path);

    return array(
      'slug'        => $slug,
      'path'        => str_replace($this->blocks_dir . '/', '', $file->getPathname()),
      'title'       => $meta['title'] ?? ucwords(str_replace('-', ' ', $slug)),
      'description' => $meta['description'] ?? 'A custom block for ' . ($meta['title'] ?? $slug),
      'category'    => $category,
      'keywords'    => $meta['keywords'] ?? array(),
      'icon'        => $icon,
      'has_js'      => $has_js,
      'js_path'     => $has_js ? str_replace($this->blocks_dir . '/', '', $js_path) : '',
    );
  }

  /**
   * Get block file path by slug
   */
  public function get_block_file($slug) {
    $blocks = $this->get_blocks();

    if (isset($blocks[$slug])) {
      return $this->blocks_dir . '/' . $blocks[$slug]['path'];
    }

    return null;
  }

  /**
   * Get block categories for WordPress
   */
  public function get_categories() {
    $blocks = $this->get_blocks();
    $categories = array();

    foreach ($blocks as $block) {
      $cat_slug = $block['category'];
      if (!isset($categories[$cat_slug])) {
        $categories[$cat_slug] = array(
          'slug'  => $cat_slug,
          'title' => ucwords(str_replace(['block-', '-'], ['', ' '], $cat_slug)),
          'icon'  => 'welcome-widgets-menus',
        );
      }
    }

    return array_values($categories);
  }
}
