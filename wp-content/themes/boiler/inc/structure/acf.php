<?php
/**
 * Include Advanced Custom Fields within theme
 *
 * @link http://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
 * @package lawfirm
 */


/**
 * Setup Options Pages
 */
if( function_exists('acf_add_options_page') ) {

  acf_add_options_page(array(
    'page_title'  => 'Social Accounts',
    'menu_title'  => 'Social Accounts',
    'menu_slug'   => 'social-accounts',
    'capability'  => 'edit_posts',
    'redirect'    => false
  ));

  // acf_add_options_sub_page(array(
  //  'page_title'  => 'Theme Header Settings',
  //  'menu_title'  => 'Header',
  //  'parent_slug' => 'theme-general-settings',
  // ));
}


function my_acf_block_render_callback( $block ) {
  
  // convert name ("acf/block-name") into path friendly slug ("block-name")
  $slug = str_replace('acf/', '', $block['name']);
  // include a template part from within the "template-parts/blocks" folder
  if( file_exists( get_theme_file_path("/template-parts/blocks/content-{$slug}.php") ) ) {
    include( get_theme_file_path("/template-parts/blocks/content-{$slug}.php") );
  }
}



//add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks ) {
 
  return array(
    'acf/home-hero',
    'acf/callout-with-icons'
  );

}
add_filter( 'allowed_block_types', 'acf_allowed_block_types' );



//block registration
add_action('acf/init', 'my_acf_init');
function my_acf_init() {
  
  // check function exists
  if( function_exists('acf_register_block') ) {
    
    // register a home hero block
    acf_register_block(array(
      'name'        => 'home_hero',
      'title'       => __('Home Hero'),
      'description'   => __('The Home Hero Block'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'layout',
      'icon'        => 'welcome-view-site',
      'keywords'      => array( 'hero', 'home' ),
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