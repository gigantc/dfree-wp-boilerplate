<?php
/**
 * Core Structure Hooks
 *
 * @package lawfirm
 */


/**
 * General
 * @see  lawfirm_setup()
 * @see  lawfirm_scripts()
 */
add_action( 'after_setup_theme',      'lawfirm_setup' );
add_action( 'wp_enqueue_scripts',     'lawfirm_scripts',       10 );


//blog
// add_action( 'lawfirm_loop_post',     'lawfirm_post_header',     10 );
// add_action( 'lawfirm_loop_post',     'lawfirm_post_content',      20 );
