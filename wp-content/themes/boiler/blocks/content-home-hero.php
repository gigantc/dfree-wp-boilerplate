<?php
/**
 * Block Name: Home Hero
 *
 * This is the template that displays the Home Hero block.
 */

$headline = get_field('home_hero_headline');
$sub_headline = get_field('home_hero_sub_headline');
$button_text = get_field('home_hero_button_text');
$bg = get_field('home_hero_background_image');

$link = get_field('home_hero_button_link');
$link_url = $link['url'];
$link_target = $link['target'] ? $link['target'] : '_self';

$id = 'home-hero-' . $block['id'];
?>


<section class="home-hero" id="<?= $id; ?>">
  <span>
  <h1><?= $headline ?></h1>
  <h2><?= $sub_headline ?></h2>
  <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><div class="btn"><?= $button_text ?></div></a>
  </span>
  <div class="scroll"></div>
  
</section>


<style type="text/css">
  #<?php echo $id; ?> {
    background:url(<?= $bg ?>);
    background-size:cover;
  }
</style>