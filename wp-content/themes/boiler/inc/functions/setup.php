<?php
if ( ! function_exists( 'lawfirm_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function lawfirm_setup() {

	add_editor_style( get_template_directory() . '/css/editor-style.css' );


	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );


	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'lawfirm' ),
		'secondary' => esc_html__( 'Header SubMenu', 'lawfirm' ),
		'footer' => esc_html__( 'Footer Menu', 'lawfirm' )
	) );

	/*
	 * Switch default markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );



	/**
	 * Setup custom image sizes
	 */
  //thumb size is 300 x 300
  add_image_size( 'lawfirm_img_small', 640, 9999, false );
  add_image_size( 'lawfirm_img_medium', 800, 9999, false );
  add_image_size( 'lawfirm_img_large', 1000, 9999, false );
	add_image_size( 'lawfirm_img_x_large', 1500, 9999, false );
	add_image_size( 'lawfirm_img_full', 2000, 9999, false );
  add_image_size( 'lawfirm_img_square', 800, 800, true );
	
	
}
endif;


/**
 * Enqueue scripts and styles.
 */
function lawfirm_scripts() {
	wp_enqueue_style( 'lawfirm-style', get_stylesheet_uri() );
	wp_enqueue_style( 'lawfirm-main-style', get_template_directory_uri() . '/css/main.css', '$deps', '1.0.0', 'screen' );


  //JQUERY
  wp_deregister_script('jquery');
  wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", false, null);


	wp_register_script( 'lawfirm-js', get_template_directory_uri() . '/js/main.min.js', array('jquery'), '1.0.0', true );
  wp_register_script( 'libs', get_template_directory_uri() . '/js/libs/libs.min.js', array('jquery'), '1.0.0', true );

  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'lawfirm-js' );


  //any page speciic scripts
  if (is_page('about')) {
    
  }
  

}
add_action( 'wp_enqueue_scripts', 'lawfirm_scripts' );



//setup ajax using ajaxflow
add_action( 'ajaxflow_nopriv_function_name', 'lawfirm_function_name' );
add_action( 'ajaxflow_function_name', 'lawfirm_load_archive_posts' );

//setup ajax through admin-ajax
add_action( 'wp_ajax_nopriv_function_name', 'lawfirm_function_name' );
add_action( 'wp_ajax_function_name', 'lawfirm_load_archive_posts' );




/**
* Allow .svg uploads to media
* 
*/
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');



/**
* This function removes anonymous functions set by a plugin
*/
function remove_anonymous_object_filter( $tag, $class, $method ) {
	$filters = false;

	if ( isset( $GLOBALS['wp_filter'][$tag] ) ) {
		$filters = $GLOBALS['wp_filter'][$tag];
	}

	if ( $filters ) {
		foreach ( $filters as $priority => $filter ) {
			foreach ( $filter as $identifier => $function ) {
				if ( ! is_array( $function ) ) {
					continue;
				}

				if ( ! $function['function'][0] instanceof $class ) {
					continue;
				}

				if ( $method == $function['function'][1] ) {
					remove_filter( $tag, array( $function['function'][0], $method ), $priority );
				}
			}
		}
	}
}
// remove_anonymous_object_filter('woocommerce_before_add_to_cart_button', 'woocommerce_gravityforms', 'woocommerce_gravityform');


/**
* This is how to activate an hourly WP Cron job whenever the site is loaded
* Once it's created it won't do it again unless deleted in WP CRON SCHEDULER
*/
//hook to activate the hourly event
function lawfirm_projects_cron_activation() {
  if ( !wp_next_scheduled( 'lawfirm_get_project_data' ) ) {
    wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'lawfirm_get_project_data');
  }

  if ( !wp_next_scheduled( 'lawfirm_create_projects' ) ) {
    wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'lawfirm_create_projects');
  }
}
//add_action('wp', 'lawfirm_projects_cron_activation');
// run these scripts once an hour 
function lawfirm_get_project_data(){
  
}


// Add Page Slug to Body Class
// creates a page-[slug] body class
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
  $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );