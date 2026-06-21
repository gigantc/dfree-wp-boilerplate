<?php
/**
 * Hero Text Only Block
 *
 * Light gray hero with breadcrumbs, page title, and body text. No image.
 */
// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>

  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">

<?php
// render the block in the browser
else :

$headline     = get_field('hero_text_only_headline');
$sub_headline = get_field('hero_text_only_copy');
$ctas         = get_field('hero_text_only_ctas');

$cta_variants = ['primary-dark', 'secondary-dark', 'text-dark'];

?>

<section class="hero-text-only">
  <div class="container">

    <?php component('breadcrumbs', []); ?>

    <div class="content">
      <h1><?= esc_html($headline) ?></h1>
      <div class="description">
        <h4><?= $sub_headline ?></h4>
      </div>

      <?php if ($ctas) : ?>
        <div class="ctas">
          <?php foreach ($ctas as $index => $cta) :
            $cta_link = $cta['link'] ?? null;
            $cta_icon = $cta['icon'] ?? null;
            if (!$cta_link || !is_array($cta_link)) continue;
            $variant = $cta_variants[$index] ?? 'text';
          ?>
            <?php component('button', [
              'url'     => $cta_link['url'],
              'title'   => $cta_link['title'],
              'target'  => $cta_link['target'] ?? '_self',
              'variant' => $variant,
              'icon'    => $cta_icon ?: '',
            ]); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

  </div>
</section>

<?php endif; ?>
