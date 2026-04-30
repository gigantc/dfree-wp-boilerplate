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
$headline = get_field('hero_headline');
$image = get_field('hero_background_image');

// Community fallback image
if (!$image && get_post_type() === 'community') {
  $image = ['url' => get_template_directory_uri() . '/src/img/generic-community-image.png', 'alt' => get_the_title()];
}

//CTAs
$buttons = get_field('hero_buttons');

?>

  <section class="hero">
    <div class="container">
      
      <div class="image">
        <span></span>
      <?php if ($image) : ?>
        <?php dfree_image($image, 'dfree_hero'); ?>
      <?php endif; ?>
      </div>

      <div class="content">
        <?php if ($headline) : ?>
          <h1><?= $headline ?></h1>
        <?php endif; ?>

        <?php if (have_rows('hero_buttons')) : ?>
          <div class="ctas">
            <?php while (have_rows('hero_buttons')) : the_row();
              $button = get_sub_field('button');
              $variant = $variant_raw = get_sub_field('button_type') ?: 'secondary';
              $icon = get_sub_field('icon');

              if ($button) :
                // desktop variant
                component('button', [
                  'url' => $button['url'],
                  'title' => $button['title'],
                  'target' => $button['target'] ?? '_self',
                  'variant' => $variant,
                  'icon' => $icon,
                  'class' => 'desktop'
                ]);
                // mobile variant (-dark)
                component('button', [
                  'url' => $button['url'],
                  'title' => $button['title'],
                  'target' => $button['target'] ?? '_self',
                  'variant' => $variant . '-dark',
                  'icon' => $icon,
                  'class' => 'mobile'
                ]);
              endif;
            endwhile; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </section>

<?php endif; ?>