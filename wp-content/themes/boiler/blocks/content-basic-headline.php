<?php
/**
 * Block Name: Basic Headline
 *
 * This is the template that an H level element
 */



$headline = get_field('block_basic_headline');
$headline_type = get_field('block_basic_headline_type'); ?>

<span class="wrap basic-headline">

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