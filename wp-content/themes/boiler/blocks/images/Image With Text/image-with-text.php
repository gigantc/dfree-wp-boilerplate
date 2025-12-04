<?php
/**
 * Block Name: Image with Text
 *
 * 
 */


 
// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">


<?php 
// render the block in the browser
else : 

?>

  <section class="image-with-text">
      <div class="image">
        <img src="https://placehold.co/600x400" />
      </div>
      <div class="text">
        <h2>Is this an image?</h2>
        <h4>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</h4>
      </div>
  </section>

<?php endif; ?>