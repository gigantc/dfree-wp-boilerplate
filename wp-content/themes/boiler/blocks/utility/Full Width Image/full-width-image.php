<?php
/**
 * Full Width Image
 *
 * A full-width image. 'nuff said.
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">


<?php
// render the block in the browser
else :


//image
$image = get_field('full_width_image');

?>

<section class="full-width-image">
  <div class="container">
    <?php dfree_image($image, 'dfree_hero'); ?>
  </div>
</section>

<?php endif; ?>