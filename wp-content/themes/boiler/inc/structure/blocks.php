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
  // include a template part from within the "/blocks" folder
  // every folder in /blocks needs a call

  // uncomment this if you have items in the /blocks folder root
  // if( file_exists( get_theme_file_path("/blocks/content-{$slug}.php") ) ) {
  //   include( get_theme_file_path("/blocks/content-{$slug}.php") );
  // }

  //all folder names in /blocks
  $block_folder_names = array(
    'basics',
    // 'headers',
    // 'videos',
    // 'carousels',
    // 'galleries',
    // 'images'
  );

  foreach ($block_folder_names as $folder) {
    if( file_exists( get_theme_file_path("/blocks/{$folder}/content-{$slug}.php") ) ) {
      include( get_theme_file_path("/blocks/{$folder}/content-{$slug}.php") );
    }
  }


}

//create custom block categories
function my_plugin_block_categories( $categories, $post ) {
  // if ( $post->post_type !== 'post' ) {
  //     return $categories;
  // }
  return array_merge(
      $categories,
      array(
          array(
            'slug' => 'block-basic',
            'title' => __( 'Basic Blocks', 'block-basic' ),
            'icon'  => 'edit',
          ),
          // array(
          //   'slug' => 'block-videos',
          //   'title' => __( 'Videos', 'block-video' ),
          //   'icon'  => 'welcome-widgets-menus',
          // ),
          // array(
          //   'slug' => 'block-carousels',
          //   'title' => __( 'Carousels', 'block-carousels' ),
          //   'icon'  => 'welcome-widgets-menus',
          // ),
          // array(
          //   'slug' => 'block-galleries',
          //   'title' => __( 'Galleries', 'block-galleries' ),
          //   'icon'  => 'welcome-widgets-menus',
          // ),
          // array(
          //   'slug' => 'block-images',
          //   'title' => __( 'Images', 'block-images' ),
          //   'icon'  => 'welcome-widgets-menus',
          // ),
      )
  );
}
add_filter( 'block_categories', 'my_plugin_block_categories', 10, 2 );



//add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks ) {

  //all default block types set
  $blocks = array(
    'acf/basic-headline',
    'acf/basic-text',
    'acf/basic-list',
    'acf/basic-wysiwyg'
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

    //--------------------------------
    // BASIC BLOCKS
    //--------------------------------
    acf_register_block(array(
      'name'        => 'basic-headline',
      'title'       => __('Simple Headline'),
      'description'   => __('a simple headline element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-basic',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="heading" class="svg-inline--fa fa-heading fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M448 96v320h32a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16H320a16 16 0 0 1-16-16v-32a16 16 0 0 1 16-16h32V288H160v128h32a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16H32a16 16 0 0 1-16-16v-32a16 16 0 0 1 16-16h32V96H32a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h160a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16h-32v128h192V96h-32a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h160a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16z"></path></svg>',
      'keywords'      => array( 'basic', 'headline'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));

    // register a basic text block
    acf_register_block(array(
      'name'        => 'basic-text',
      'title'       => __('Simple Text'),
      'description'   => __('a simple text block element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-basic',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="text-size" class="svg-inline--fa fa-text-size fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M624 32H272a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-32h88v304h-40a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16h-40V112h88v32a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM304 224H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-16h56v128H96a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h128a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16h-24V288h56v16a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16z"></path></svg>',
      'keywords'      => array( 'basic', 'text'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));

    // register a basic text list block
    acf_register_block(array(
      'name'        => 'basic-list',
      'title'       => __('Simple List'),
      'description'   => __('a simple bullet list element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-basic',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="list" class="svg-inline--fa fa-list fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>',
      'keywords'      => array( 'basic', 'list'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));

    // register a basic Wysiwyg editor
    acf_register_block(array(
      'name'        => 'basic-wysiwyg',
      'title'       => __('Wysiwyg Editor'),
      'description'   => __('a simple Wysiwyg element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-basic',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="superscript" class="svg-inline--fa fa-superscript fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M496 160h-16V16a16 16 0 0 0-16-16h-48a16 16 0 0 0-14.29 8.83l-16 32A16 16 0 0 0 400 64h16v96h-16a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h96a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zM336 64h-67a16 16 0 0 0-13.14 6.87l-79.9 115-79.9-115A16 16 0 0 0 83 64H16A16 16 0 0 0 0 80v48a16 16 0 0 0 16 16h33.48l77.81 112-77.81 112H16a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h67a16 16 0 0 0 13.14-6.87l79.9-115 79.9 115A16 16 0 0 0 269 448h67a16 16 0 0 0 16-16v-48a16 16 0 0 0-16-16h-33.48l-77.81-112 77.81-112H336a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16z"></path></svg>',
      'keywords'      => array( 'basic', 'wysiwyg'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));


  }
}