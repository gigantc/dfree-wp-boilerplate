<?php
/**
 * Block Name: Basic Wysiwyg
 *
 * This is the template that an p level element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <img src="<?= get_template_directory_uri() ?>/blocks/basics/image-center.jpg" />


<?php 
// render the block in the browser
else : ?>

<span class="basic-text basic-headline basic-wysiwyg">
  <?php the_field('block_basic_wysiwyg_editor'); ?>
</span>

<?php endif; ?>