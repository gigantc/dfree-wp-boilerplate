<?php
/**
 * Include Advanced Custom Fields within theme
 *
 * @link http://www.advancedcustomfields.com/resources/including-acf-in-a-plugin-theme/
 * @package lawfirm
 */





//////////////////////////////////////
// SCAN /blocks FOLDER
// This dynamically renders ACF blocks by scanning all subfolders within the /blocks directory. 
//  It extracts the block’s slug from its registered name (e.g., 'acf/headline' → 'headline').
function my_acf_block_render_callback( $block ) {
  
  // convert name ("acf/block-name") into path friendly slug ("block-name")
  $slug = str_replace('acf/', '', $block['name']);

  // Scan all block folders recursively
  $block_dirs = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator( get_theme_file_path('/blocks'), RecursiveDirectoryIterator::SKIP_DOTS )
  );

  foreach ($block_dirs as $file) {
    if (
      $file->getFilename() === "{$slug}.php"
      && strpos($file->getPathname(), '.php') !== false
    ) {
      include $file->getPathname();
      return;
    }
  }

}




//////////////////////////////////////
// CREATE ALL CUSTOM BLOCK CATEGORIES
//creates categories based on the top level folders names in /blocks
function my_plugin_block_categories( $categories, $post ) {
  $block_base_path = get_theme_file_path('/blocks');
  $block_categories = [];

  // Define custom icons per category
  //commented out unless you want to create custom icons
  // $category_icons = [
  //   'text'     => 'welcome-widgets-menus',
  //   'images'   => 'welcome-widgets-menus',
  //   'videos'   => 'welcome-widgets-menus',
  //   'heroes'   => 'welcome-widgets-menus',
  //   'carousels'=> 'welcome-widgets-menus',
  //   'misc'     => 'welcome-widgets-menus',
  // ];

  // Get all first-level directories in /blocks
  foreach (glob($block_base_path . '/*', GLOB_ONLYDIR) as $folder) {
    $basename = basename($folder);
    $slug     = 'block-' . $basename;
    $title    = ucwords(str_replace('-', ' ', $basename));
    $icon     = $category_icons[$basename] ?? 'welcome-widgets-menus'; // fallback

    $block_categories[] = array(
      'slug'  => $slug,
      'title' => __($title, $slug),
      'icon'  => $icon,
    );
  }

  return array_merge($categories, $block_categories);
}
add_filter( 'block_categories_all', 'my_plugin_block_categories', 10, 2 );





//////////////////////////////////////
// DISPLAY BLOCKS IN THE ADMIN 
// Add only blocks that are needed
function acf_allowed_block_types( $allowed_blocks, $block_editor_context ) {
  global $post;

  //all default block types set
  $blocks = array(
    'acf/headline',
    'acf/text',
    'acf/wysiwyg',
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





//////////////////////////////////////
// BLOCK REGISTRATION
add_action('acf/init', 'my_acf_init');
function my_acf_init() {
  
  // check function exists
  if( function_exists('acf_register_block') ) {

    // Scan all block folders recursively
    $block_dirs = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator( get_theme_file_path('/blocks'), RecursiveDirectoryIterator::SKIP_DOTS )
    );

    foreach ($block_dirs as $file) {
      if ($file->getExtension() === 'php') {
        $folder = dirname($file->getPathname());
        $slug = basename($folder);
        $category = 'block-' . basename(dirname($folder));
        $meta_path = $folder . '/block.json';
        $meta = file_exists($meta_path) ? json_decode(file_get_contents($meta_path), true) : [];
        
        $title = $meta['title'] ?? ucwords(str_replace('-', ' ', $slug));
        $description = $meta['description'] ?? __('A custom block for ' . $title);
        $keywords = $meta['keywords'] ?? [];

        $icon_path = $folder . '/admin-icon.svg';
        $icon = file_exists($icon_path) ? file_get_contents($icon_path) : '';

        acf_register_block(array(
          'name'            => $slug,
          'title'           => __($title, 'block-' . $slug),
          'description'     => $description,
          'render_callback' => 'my_acf_block_render_callback',
          'category'        => $category,
          'icon'            => $icon,
          'keywords'        => $keywords,
          'mode'            => 'preview',
          'example'         => [
            'attributes' => [
              'mode' => 'preview',
              'data' => ['is_example' => true],
            ]
          ]
        ));
      }
    }
  }
}