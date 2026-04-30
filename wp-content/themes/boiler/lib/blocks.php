<?php
/**
 * ACF Block Registration
 *
 * Uses the cached block registry to register blocks, render templates,
 * create categories, and auto-enqueue per-block JavaScript.
 *
 * @package boiler
 */


//////////////////////////////////////
// RENDER BLOCK USING REGISTRY
// Uses cached block registry to avoid filesystem scans
function my_acf_block_render_callback( $block ) {

	// convert name ("acf/block-name") into path friendly slug ("block-name")
	$slug = sanitize_title( str_replace( 'acf/', '', $block['name'] ) );

	// Get block file from registry
	$registry = DFREE_Block_Registry::get_instance();
	$file = $registry->get_block_file( $slug );

	if ( $file && file_exists( $file ) ) {
		include $file;
	}
}


//////////////////////////////////////
// CREATE ALL CUSTOM BLOCK CATEGORIES
// Uses cached registry to get categories
function my_plugin_block_categories( $categories, $post ) {
	$registry = DFREE_Block_Registry::get_instance();
	$block_categories = $registry->get_categories();

	return array_merge( $categories, $block_categories );
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
	foreach ( $all_blocks as $block ) {
		$blocks[] = 'acf/' . $block['slug'];
	}

	// Restrict blocks for specific post types or pages here if needed
	// if ( $post->post_type == 'documents' ) {
	//   $blocks = array( 'acf/document-download' );
	// }

	return $blocks;
}
add_filter( 'allowed_block_types_all', 'acf_allowed_block_types', 10, 2 );


//////////////////////////////////////
// BLOCK REGISTRATION
add_action( 'acf/init', 'my_acf_init' );
function my_acf_init() {

	if ( ! function_exists( 'acf_register_block' ) ) {
		return;
	}

	$registry = DFREE_Block_Registry::get_instance();
	$blocks   = $registry->get_blocks();

	foreach ( $blocks as $block ) {
		acf_register_block( array(
			'name'            => $block['slug'],
			'title'           => __( $block['title'], 'block-' . $block['slug'] ),
			'description'     => $block['description'],
			'render_callback' => 'my_acf_block_render_callback',
			'category'        => $block['category'],
			'icon'            => $block['icon'],
			'keywords'        => $block['keywords'],
			'mode'            => 'edit',
			'example'         => array(
				'attributes' => array(
					'mode' => 'preview',
					'data' => array( 'is_example' => true ),
				),
			),
		) );
	}
}


//////////////////////////////////////
// REBUILD MANIFEST ON THEME ACTIVATION
function dfree_rebuild_block_manifest_on_activation() {
	$registry = DFREE_Block_Registry::get_instance();
	$registry->rebuild_manifest();
}
add_action( 'after_switch_theme', 'dfree_rebuild_block_manifest_on_activation' );


//////////////////////////////////////
// AUTO-ENQUEUE BLOCK JAVASCRIPT
// Loads block JS files only when blocks are present on the page
function dfree_enqueue_block_scripts() {
	if ( is_admin() ) {
		return;
	}

	$registry   = DFREE_Block_Registry::get_instance();
	$all_blocks = $registry->get_blocks();

	foreach ( $all_blocks as $block ) {
		if ( empty( $block['has_js'] ) || ! has_block( 'acf/' . $block['slug'] ) ) {
			continue;
		}

		$js_file      = get_template_directory_uri() . '/dist/js/blocks/' . $block['slug'] . '.min.js';
		$dependencies = array( 'jquery' );

		if ( ! empty( $block['requires'] ) && is_array( $block['requires'] ) ) {
			$dependencies = array_merge( $dependencies, $block['requires'] );

			foreach ( $block['requires'] as $lib ) {
				if ( wp_style_is( $lib, 'registered' ) ) {
					wp_enqueue_style( $lib );
				}
			}
		}

		wp_enqueue_script(
			'block-' . $block['slug'],
			$js_file,
			$dependencies,
			dfree_get_version(),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dfree_enqueue_block_scripts', 20 );
