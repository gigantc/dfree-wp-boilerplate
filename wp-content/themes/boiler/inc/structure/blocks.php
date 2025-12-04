<?php
/**
 * Include Advanced Custom Fields within theme
 *
 * @link http://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
 * @package lawfirm
 */





//////////////////////////////////////
// RENDER BLOCK USING REGISTRY
// Uses cached block registry to avoid filesystem scans
function my_acf_block_render_callback( $block ) {

  // convert name ("acf/block-name") into path friendly slug ("block-name")
  $slug = sanitize_title(str_replace('acf/', '', $block['name']));

  // Get block file from registry
  $registry = DFREE_Block_Registry::get_instance();
  $file = $registry->get_block_file($slug);

  if ($file && file_exists($file)) {
    include $file;
  }

}




//////////////////////////////////////
// CREATE ALL CUSTOM BLOCK CATEGORIES
// Uses cached registry to get categories
function my_plugin_block_categories( $categories, $post ) {
  $registry = DFREE_Block_Registry::get_instance();
  $block_categories = $registry->get_categories();

  return array_merge($categories, $block_categories);
}
add_filter( 'block_categories_all', 'my_plugin_block_categories', 10, 2 );





//////////////////////////////////////
// DISPLAY BLOCKS IN THE ADMIN
// Add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks, $block_editor_context ) {
  global $post;

  // Get all blocks from registry (cached)
  $registry = DFREE_Block_Registry::get_instance();
  $all_blocks = $registry->get_blocks();

  $blocks = array();
  foreach ($all_blocks as $block) {
    $blocks[] = 'acf/' . $block['slug'];
  }

  // use these functions to manually set pages or post to only get specific block

  // If the post type is 'documents', restrict blocks
  // if( $post->post_type == 'documents' ) {
  //     $blocks = array(
  //         'acf/document-download',
  //         'acf/document-download-cat'
  //     );
  // }

  // Restrict blocks for specific pages by ID, slug, or title
  //replace XXIDXX with your page ID
  // if( is_page( XXIDXX ) || is_page( 'example-page' ) ) {
  //     $blocks = array(
  //         'acf/page-specific-block',
  //         'acf/page-specific-hero',
  //     );
  // }

  return $blocks;

}
add_filter( 'allowed_block_types_all', 'acf_allowed_block_types', 10, 2 );





//////////////////////////////////////
// BLOCK REGISTRATION
add_action('acf/init', 'my_acf_init');
function my_acf_init() {

  // check function exists
  if( function_exists('acf_register_block') ) {

    // Get blocks from registry (cached)
    $registry = DFREE_Block_Registry::get_instance();
    $blocks = $registry->get_blocks();

    foreach ($blocks as $block) {
      acf_register_block(array(
        'name'            => $block['slug'],
        'title'           => __($block['title'], 'block-' . $block['slug']),
        'description'     => $block['description'],
        'render_callback' => 'my_acf_block_render_callback',
        'category'        => $block['category'],
        'icon'            => $block['icon'],
        'keywords'        => $block['keywords'],
        'mode'            => 'preview',
        'example'         => [
          'attributes' => [
            'mode' => 'preview',
            'data' => ['is_example' => true],
          ]
        ]
      ));
    }
  }
}

//////////////////////////////////////
// REBUILD MANIFEST ON THEME ACTIVATION
function dfree_rebuild_block_manifest_on_activation() {
  $registry = DFREE_Block_Registry::get_instance();
  $registry->rebuild_manifest();
}
add_action('after_switch_theme', 'dfree_rebuild_block_manifest_on_activation');

//////////////////////////////////////
// AUTO-ENQUEUE BLOCK JAVASCRIPT
// Loads block JS files only when blocks are present on the page
function dfree_enqueue_block_scripts() {
  // Only run on frontend
  if ( is_admin() ) {
    return;
  }

  $registry = DFREE_Block_Registry::get_instance();
  $all_blocks = $registry->get_blocks();

  // Check each block to see if it's on the page and has JS
  foreach ( $all_blocks as $block ) {
    if ( !empty( $block['has_js'] ) && has_block( 'acf/' . $block['slug'] ) ) {
      $js_file = get_template_directory_uri() . '/js/blocks/' . $block['slug'] . '.min.js';

      wp_enqueue_script(
        'block-' . $block['slug'],
        $js_file,
        array( 'jquery' ),
        '1.0.0',
        true
      );
    }
  }
}
add_action( 'wp_enqueue_scripts', 'dfree_enqueue_block_scripts', 20 );