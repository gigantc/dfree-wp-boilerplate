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

  //all folder names in /blocks
  //add new ones as needed
  $block_folder_names = array(
    'text',
    'images'
    'videos',
    'heroes',
    'carousels',
    "misc"
  );

  foreach ($block_folder_names as $folder) {
    if( file_exists( get_theme_file_path("/blocks/{$folder}/{$slug}.php") ) ) {
      include( get_theme_file_path("/blocks/{$folder}/{$slug}.php") );
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
            'slug' => 'block-text',
            'title' => __( 'Text', 'block-text' ),
            'icon'  => 'welcome-widgets-menus',
          ),
          array(
            'slug' => 'block-images',
            'title' => __( 'Images', 'block-images' ),
            'icon'  => 'welcome-widgets-menus',
          ),
          array(
            'slug' => 'block-videos',
            'title' => __( 'Videos', 'block-video' ),
            'icon'  => 'welcome-widgets-menus',
          ),
          array(
            'slug' => 'block-heroes',
            'title' => __( 'Heroes', 'block-heroes' ),
            'icon'  => 'welcome-widgets-menus',
          ),
          array(
            'slug' => 'block-carousels',
            'title' => __( 'Carousels', 'block-carousels' ),
            'icon'  => 'welcome-widgets-menus',
          ),
          array(
            'slug' => 'block-misc',
            'title' => __( 'Misc', 'block-misc' ),
            'icon'  => 'welcome-widgets-menus',
          ),
      )
  );
}
add_filter( 'block_categories_all', 'my_plugin_block_categories', 10, 2 );



// Add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks, $block_editor_context ) {
  global $post;

  //all default block types set
  $blocks = array(
    'acf/headline',
    'acf/text',
  );


  // If the post type is 'documents', restrict blocks
  // if( $post->post_type == 'documents' ) {
  //     $blocks = array(
  //         'acf/document-download',
  //         'acf/document-download-cat'
  //     );
  // }

  // Restrict blocks for specific pages by ID, slug, or title
  //replace XXIDXX with your page ID
  // if( is_page( XXIDXX ) || is_page( 'example-page' ) ) { 
  //     $blocks = array(
  //         'acf/page-specific-block',
  //         'acf/page-specific-hero',
  //     );
  // }
  return $blocks; 

}
add_filter( 'allowed_block_types_all', 'acf_allowed_block_types', 10, 2 );



//block registration
add_action('acf/init', 'my_acf_init');
function my_acf_init() {
  
  // check function exists
  if( function_exists('acf_register_block') ) {


    //////////////////////////////////////
    // TEXT BLOCKS

    // Register a Headline Block
    acf_register_block(array(
      'name'        => 'headline',
      'title'       => __('Simple Headline'),
      'description'   => __('a simple headline element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-text',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="heading" class="svg-inline--fa fa-heading fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M448 96v320h32a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16H320a16 16 0 0 1-16-16v-32a16 16 0 0 1 16-16h32V288H160v128h32a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16H32a16 16 0 0 1-16-16v-32a16 16 0 0 1 16-16h32V96H32a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h160a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16h-32v128h192V96h-32a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h160a16 16 0 0 1 16 16v32a16 16 0 0 1-16 16z"></path></svg>',
      'keywords'      => array( 'text', 'headline'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));

    // Register a Text Block
    acf_register_block(array(
      'name'        => 'text',
      'title'       => __('Simple Text'),
      'description'   => __('a simple text block element'),
      'render_callback' => 'my_acf_block_render_callback',
      'category'      => 'block-text',
      'mode' => 'preview',
      'icon'        => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="text-size" class="svg-inline--fa fa-text-size fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M624 32H272a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-32h88v304h-40a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16h-40V112h88v32a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM304 224H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-16h56v128H96a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h128a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16h-24V288h56v16a16 16 0 0 0 16 16h32a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16z"></path></svg>',
      'keywords'      => array( 'paragraph', 'text'),
      'example' => [
        'attributes' => [
          'mode' => 'preview',
          'data' => ['is_example' => true],
        ]
      ]
    ));

    

    


  }
}