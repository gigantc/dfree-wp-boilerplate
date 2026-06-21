<?php
/**
 * Divider Block
 *
 * A full-width 2px horizontal rule.
 */
// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">


<?php
// render the block in the browser
else :

?>

<section class="divider-block">
  <div class="container">
    <hr>
  </div>
</section>

<?php endif; ?>