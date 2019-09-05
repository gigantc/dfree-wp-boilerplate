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