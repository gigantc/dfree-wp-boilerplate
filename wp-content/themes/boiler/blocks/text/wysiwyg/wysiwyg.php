<?php
/**
 * Block Name: Basic Wysiwyg
 *
 * This is the template that an p level element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <!-- This will dynamically use an image in the folder called 'admin-image.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/admin-image.jpg" alt="Block Preview">


<?php 
// render the block in the browser
else : ?>

<div class="block-wysiwyg">
  <?php the_field('block_wysiwyg_editor'); ?>
</div>

<?php endif; ?>