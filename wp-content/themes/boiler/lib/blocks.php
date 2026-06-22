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
// OPT ALL BLOCKS INTO ACF BLOCK VERSION 3
// WP 7.0 iframes the editor; v3 blocks edit fields in the sidebar / expanded
// editor and render a preview in the canvas (in-canvas edit mode is not
// supported in the iframe).
add_filter( 'acf/blocks/default_block_version', function () {
	return 3;
} );

//////////////////////////////////////
// RENDER BLOCK USING REGISTRY
// Uses cached block registry to avoid filesystem scans.
// $is_preview is true when rendering inside the block editor canvas.
function my_acf_block_render_callback( $block, $content = '', $is_preview = false, $post_id = 0, $wp_block = null, $context = false ) {

	// convert name ("acf/block-name") into path friendly slug ("block-name")
	$slug = sanitize_title( str_replace( 'acf/', '', $block['name'] ) );

	// Get block file from registry
	$registry = DFREE_Block_Registry::get_instance();
	$file = $registry->get_block_file( $slug );

	// In the editor canvas, blocks opted into "editor_static_preview" (via
	// block.config.json) show a static preview image + notice instead of their
	// live markup — for blocks that can't render in the editor (carousels
	// needing a JS library, maps, data-driven lists that come up empty).
	if ( $is_preview && $file ) {
		$data = $registry->get_block( $slug );
		if ( ! empty( $data['editor_static_preview'] ) ) {
			dfree_block_static_preview( $file, $block );
			return;
		}
	}

	if ( $file && file_exists( $file ) ) {
		include $file;
	}
}

//////////////////////////////////////
// STATIC EDITOR PREVIEW
// Renders a block's block.preview.jpg with a notice in the editor canvas, for
// blocks flagged "editor_static_preview" that can't render live in the iframe.
function dfree_block_static_preview( $file, $block ) {
	$dir   = dirname( $file );
	$title = $block['title'] ?? '';
	$img   = $dir . '/block.preview.jpg';

	echo '<div class="acf-static-preview">';            // full-width gray band
	echo   '<div class="acf-static-preview__card">';    // centered card
	echo     '<p class="acf-static-preview__notice">';
	echo       '<strong>Image Preview</strong> — this block can not dynamically render in the editor.';
	echo     '</p>';

	if ( file_exists( $img ) ) {
		$img_uri = str_replace( get_template_directory(), get_template_directory_uri(), $img );
		echo   '<img class="acf-static-preview__image" src="' . esc_url( $img_uri ) . '" alt="' . esc_attr( $title ) . ' preview" />';
	}

	echo   '</div>';
	echo '</div>';
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
			// Hide the fields form in the inspector sidebar (unusable at ~280px);
			// editors use the expanded editor. (ACF v3 / WP 7.0 iframe editor.)
			'hide_fields_in_sidebar'      => true,
			// The expanded editor is the way in — label its button per block.
			'expanded_editor_button_text' => 'Edit ' . $block['title'],
			// Full-bleed blocks: disable the alignment control at the source.
			// 'html' => false also drops "Edit as HTML" from the ⋮ menu.
			'supports'                    => array( 'align' => false, 'html' => false ),
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
