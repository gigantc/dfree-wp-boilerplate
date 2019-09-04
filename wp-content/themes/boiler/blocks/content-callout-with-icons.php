<?php
/**
 * Block Name: Callout Gallery With Icons
 *
 * This is the template that displays a callout gallery with icons
 */




?>


<section class="hero-callout" style="background: url('<?= the_field('callout_gallery_with_icons_background_image') ?>') center no-repeat; background-size:cover;">
  <div class="overlay"></div>
  <div class="prev"></div>
  <div class="next"></div>
  <div class="slider">
    <?php
    if( have_rows('callout_gallery_with_icons') ):
        while ( have_rows('callout_gallery_with_icons') ) : the_row(); ?>
            <div class="slide">
              <div class="image"><img src="<?= the_sub_field('icon') ?>" /></div>
              <span>
                <h1><?= the_sub_field('headline') ?></h1>
                <h2><?= the_sub_field('sub_headline') ?></h2>
              </span>
            </div>
        <?php endwhile;
    endif;
    ?>
  </div>
  </section>