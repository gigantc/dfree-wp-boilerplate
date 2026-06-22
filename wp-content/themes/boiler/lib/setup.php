<?php
if ( ! function_exists( 'dfree_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function dfree_setup() {

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable Post Thumbnails support.
	add_theme_support( 'post-thumbnails' );

	// Register nav menus.
	register_nav_menus( array(
		'primary'   => esc_html__( 'Primary Menu', 'boiler' ),
		'secondary' => esc_html__( 'Header SubMenu', 'boiler' ),
		'footer'    => esc_html__( 'Footer Menu', 'boiler' )
	) );

	// HTML5 markup support.
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/**
	 * Custom image sizes
	 * Used by dfree_image() helper for responsive output
	 */
	add_image_size( 'dfree_card',   800, 9999, false );
	add_image_size( 'dfree_hero',   2000, 9999, false );
	add_image_size( 'dfree_square', 800, 800, true );
}
endif;
add_action( 'after_setup_theme', 'dfree_setup' );


/**
 * Generate WebP versions of JPEG sub-sizes for smaller file sizes
 * Original uploads remain JPEG; all resized versions become WebP
 */
add_filter( 'image_editor_output_format', function( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	return $formats;
} );


/**
 * Disable WordPress emoji scripts and styles (not needed)
 */
function dfree_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'dfree_disable_emojis' );


/**
 * Enqueue scripts and styles.
 */
function dfree_scripts() {
	wp_enqueue_style( 'dfree-style', get_stylesheet_uri() );
	wp_enqueue_style( 'dfree-main-style', get_template_directory_uri() . '/dist/css/main.css', array(), dfree_get_version(), 'screen' );

	wp_enqueue_script( 'dfree-js', get_template_directory_uri() . '/dist/js/main.min.js', array( 'jquery' ), dfree_get_version(), true );

	// Libs (Modernizr)
	wp_register_script( 'libs', get_template_directory_uri() . '/dist/js/libs/libs.min.js', array( 'jquery' ), dfree_get_version(), true );
	wp_enqueue_script( 'libs' );

	// Register GSAP for auto-loading via block requirements
	wp_register_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js', array(), '3.13.0', true );
	wp_register_script( 'scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js', array( 'gsap' ), '3.13.0', true );

	// Register Swiper for conditional loading
	wp_register_script( 'swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js', array( 'jquery' ), '11.0.5', true );
	wp_register_style(  'swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.css', array(), '11.0.5' );

	// Auto-load libraries based on blocks present on page
	$registry           = DFREE_Block_Registry::get_instance();
	$all_blocks         = $registry->get_blocks();
	$required_libraries = array();

	$blocks_with_requirements = array_filter( $all_blocks, function( $block ) {
		return ! empty( $block['requires'] );
	} );

	foreach ( $blocks_with_requirements as $block ) {
		if ( has_block( 'acf/' . $block['slug'] ) ) {
			$required_libraries = array_merge( $required_libraries, $block['requires'] );
		}
	}

	foreach ( array_unique( $required_libraries ) as $library ) {
		if ( wp_script_is( $library, 'registered' ) ) {
			wp_enqueue_script( $library );
		}
		if ( wp_style_is( $library, 'registered' ) ) {
			wp_enqueue_style( $library );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'dfree_scripts' );


/**
 * Load admin-chrome styles.
 * Only admin.css (admin-chrome overrides) loads in the wp-admin chrome.
 * main.css is the FRONT-END stylesheet (modern-normalize + unscoped resets);
 * loading it in the chrome interferes with WP's admin UI (e.g. it breaks the
 * Meta Boxes drawer / Edit Block slide-out animations in WP 7.0). Block
 * previews get main.css inside the editor iframe via enqueue_block_assets.
 */
function dfree_load_admin_styles() {
	wp_register_style( 'dfree-admin-style', get_template_directory_uri() . '/dist/css/admin.css', array(), dfree_get_version() );
	wp_enqueue_style( 'dfree-admin-style' );
}
add_action( 'admin_enqueue_scripts', 'dfree_load_admin_styles' );


/**
 * Load theme styles INTO the block editor iframe.
 * WP 7.0 iframes the editor for classic themes, so styles enqueued via
 * admin_enqueue_scripts no longer reach block previews. enqueue_block_assets
 * fires inside the iframe context when in the editor. main.css carries base +
 * block styles (base typography is scoped to .editor-styles-wrapper so it
 * applies in the canvas); admin.css carries editor-only overrides.
 */
function dfree_enqueue_editor_iframe_styles() {
	if ( ! is_admin() ) {
		return;
	}
	wp_enqueue_style( 'dfree-editor-iframe-main', get_template_directory_uri() . '/dist/css/main.css', array(), dfree_get_version() );
	wp_enqueue_style( 'dfree-editor-iframe-style', get_template_directory_uri() . '/dist/css/admin.css', array( 'dfree-editor-iframe-main' ), dfree_get_version() );
}
add_action( 'enqueue_block_assets', 'dfree_enqueue_editor_iframe_styles' );


/**
 * Allow .svg uploads to media
 */
function dfree_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'dfree_mime_types' );


/**
 * Add post slug to body class — creates a {post_type}-{slug} body class
 */
function dfree_add_slug_body_class( $classes ) {
	global $post;
	if ( isset( $post ) ) {
		$classes[] = $post->post_type . '-' . $post->post_name;
	}
	return $classes;
}
add_filter( 'body_class', 'dfree_add_slug_body_class' );
