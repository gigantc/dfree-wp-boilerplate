<?php
/**
 * Block Name: Text Block
 *
 * Centered or left-aligned text block with headline and WYSIWYG body.
 */



// render the example image pop-up in the gutenburg admin
if (get_field('is_example')) : ?>


  <!-- This will dynamically use an image in the folder called 'block.preview.jpg' for the pop-up display -->
  <img src="<?= get_template_directory_uri() . str_replace(get_theme_file_path(), '', __DIR__) ?>/block.preview.jpg" alt="Block Preview">


<?php
// render the block in the browser
else :

// Global Fields
$headline = get_field('text_block_headline');
$body = get_field('text_block_body');
$alignment = get_field('text_block_alignment');

$align_class = ($alignment === 'left') ? 'align-left' : 'align-center';

?>

  <section class="text-block <?= $align_class ?>">
    <div class="container">

      <?php if ($headline) { ?>
        <h2><?= $headline ?></h2>
      <?php } ?>

      <?php if ($body) { ?>
        <div class="body wysiwyg">
          <?= $body ?>
        </div>
      <?php } ?>

    </div>
  </section>

<?php endif; ?>
