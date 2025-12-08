<?php
/**
 * Block Name: Hero
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

// Fields
$headline = get_field('headline');


?>

  <section class="hero">
    <div class="container">
      <?php if (get_field('headline')) : ?>
        <h1><?= $headline ?></h1>
      <?php endif; ?>
    </div>
  </section>

<?php endif; ?>