<?php
/**
 * Block Name: Basic Text
 *
 * This is the template that an p level element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>

  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">


<?php 
// render the block in the browser
else : 

$copy = get_field('block_text');

?>

  <div class="block-text">
    <p><?= esc_html( $copy ) ?></p>
  </div>


<?php endif; ?>