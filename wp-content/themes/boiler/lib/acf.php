<?php
/**
 * Include Advanced Custom Fields within theme
 *
 * @link http://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
 * @package boiler
 */


/**
 * Configure ACF after the plugin has initialized.
 *
 * Registering options pages here avoids early textdomain loading notices in
 * newer WordPress versions, and disabling block preloading keeps large ACF
 * block pages from exhausting memory in the block editor.
 */
add_action( 'acf/init', function() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_update_setting( 'preload_blocks', false );

	acf_add_options_page( array(
		'page_title' => 'Global Blocks',
		'menu_title' => 'Global Blocks',
		'menu_slug'  => 'global-blocks',
		'capability' => 'edit_posts',
		'redirect'   => false,
	) );

	// Add additional options pages here as needed:
	// acf_add_options_page( array(
	//   'page_title' => 'Navigation',
	//   'menu_title' => 'Navigation',
	//   'menu_slug'  => 'navigation',
	//   'capability' => 'edit_posts',
	//   'redirect'   => false,
	// ) );
} );
