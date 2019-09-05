<?php
/**
 * Include Advanced Custom Fields within theme
 *
 * @link http://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
 * @package lawfirm
 */

function my_acf_block_render_callback( $block ) {
  
  // convert name ("acf/block-name") into path friendly slug ("block-name")
  $slug = str_replace('acf/', '', $block['name']);
  // include a template part from within the "template-parts/blocks" folder
  if( file_exists( get_theme_file_path("/blocks/content-{$slug}.php") ) ) {
    include( get_theme_file_path("/blocks/content-{$slug}.php") );
  }
}



//add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks ) {

  //all default block types set
  $blocks = array(
    'acf/splash-page',
    'acf/callout-with-icons'
  );


  //document posts only block types
  // if( $post->post_type == 'documents' ) {
  //     $blocks = array(
  //       'acf/document-download',
  //       'acf/document-download-cat'
  //     );
  // }
 
  return $blocks; 

}
add_filter( 'allowed_block_types', 'acf_allowed_block_types' );



//block registration
add_action('acf/init', 'my_acf_init');
function my_acf_init() {
  
  // check function exists
  if( function_exists('acf_register_block') ) {
    
    // register a home hero block
    acf_register_block(array(
      'name'        => 'splash-page',
      'title'       => __('Splash Page'),
      'description'   => __('The full temp splash page'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'layout',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="water" class="svg-inline--fa fa-water fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M562.1 383.9c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144c-21.5-2.4-42.1-10.5-57.9-22.9-14.1-11.1-34.2-11.3-48.2 0-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3zm0-144C540.6 93.4 520 85.4 504.2 73 490.1 61.9 470 61.7 456 73c-37.9 30.4-107.2 30.4-145.7-1.5-13.5-11.2-33-9.1-46.7 1.8-38 30.1-106.9 30-145.2-1.7-13.5-11.2-33.3-8.9-47.1 2-15.5 12.2-36 20.1-57.7 22.4-7.9.8-13.6 7.8-13.6 15.7v32.2c0 9.1 7.6 16.8 16.7 16 28.8-2.5 56.1-11.4 79.4-25.9 56.5 34.6 137 34.1 192 0 56.5 34.6 137 34.1 192 0 23.3 14.2 50.9 23.3 79.1 25.8 9.1.8 16.7-6.9 16.7-16v-31.6c.1-8-5.7-15.4-13.8-16.3z"></path></svg>',
      'keywords'      => array( 'splash', 'home', 'temp' ),
    ));

    // register a home hero callout gallery block with icons
    acf_register_block(array(
      'name'        => 'callout-with-icons',
      'title'       => __('Callout Gallery With Icons'),
      'description'   => __('A Callout Gallery Block with Icons'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'layout',
      'icon'        => 'images-alt',
      'keywords'      => array( 'callout', 'gallery', 'icons', 'home' ),
    ));


  }
}