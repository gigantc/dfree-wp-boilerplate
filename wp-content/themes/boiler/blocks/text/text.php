<?php
/**
 * Block Name: Basic Text
 *
 * This is the template that an p level element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <img src="<?= get_template_directory_uri() ?>/blocks/basics/image-center.jpg" />


<?php 
// render the block in the browser
else : 

$copy = get_field('block_text');
?>
  
  <span class="block-text">
    <p><?= $copy ?></p>
  </span>


<?php endif; ?>