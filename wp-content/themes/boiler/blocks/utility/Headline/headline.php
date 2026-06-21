<?php
/**
 * Headline Block
 *
 * Simple H2 headline that can be used above any block.
 */

// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>

  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">

<?php
// render the block in the browser
else :

$headline   = get_field('headline_text');
$alignment  = get_field('headline_alignment') ?: 'left';
$background = get_field('headline_background') ?: 'white';

if (!$headline) return;

$classes = "headline-block align-{$alignment} bg-{$background}";
?>

<section class="<?= esc_attr($classes) ?>">
  <div class="container">
    <h2><?= esc_html($headline) ?></h2>
  </div>
</section>

<?php endif; ?>
