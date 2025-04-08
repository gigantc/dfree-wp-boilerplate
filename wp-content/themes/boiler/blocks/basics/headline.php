<?php
/**
 * Block Name: Basic Headline
 *
 * This is the template that an H level element
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


    <img src="<?= get_template_directory_uri() ?>/blocks/basics/image-center.jpg" />


<?php 
// render the block in the browser
else : 

$headline = get_field('block_basic_headline');
$headline_type = get_field('block_basic_headline_type'); ?>

<span class="basic-headline">

  <?php
  if($headline_type == 'h1'){ ?>
    <h1><?= $headline ?></h1>
  <?php }

  if($headline_type == 'h2'){ ?>
    <h2><?= $headline ?></h2>
  <?php }

  if($headline_type == 'h3'){ ?>
    <h3><?= $headline ?></h3>
  <?php }

  if($headline_type == 'h4'){ ?>
    <h4><?= $headline ?></h4>
  <?php }

  if($headline_type == 'h5'){ ?>
    <h5><?= $headline ?></h5>
  <?php }

  if($headline_type == 'h6'){ ?>
    <h6><?= $headline ?></h6>
  <?php } ?>

</span>



<?php endif; ?>