<?php
/**
 * Block Name: Image Single
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


$image = get_field('image_single_image');
$image_url = $image['url'];
$image_alt = $image['alt'];
?>

  <section class="image-single">
    <div class="wrap">
      <img src="<?= esc_url($image_url); ?>" alt="<?= esc_attr($image_alt) ?>"/>
    </div>
  </section>

<?php endif; ?>