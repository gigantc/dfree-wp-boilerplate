<?php
/**
 * Link Component
 *
 * Available parameters:
 * - $url (string) -Link URL (default: '#')
 * - $title (string) - Link text (default: 'Click Here')
 * - $target (string) - Link target (default: '_self')
 * - $variant (string) - Link style: 'arrow'
 * - $wrap (string) - 'a' or 'div' (default: a)
 *
 * Usage:
 * component('link', [
 *   'url' => '/contact',
 *   'title' => 'Contact Us',
 *   'variant' => 'arrow',
 *   'wrap' => 'a'
 * ]);
 */

// PROPS
$url = $url ?? '#';
$title = $title ?? 'Click Here';
$target = $target ?? '_self';
$variant = $variant ?? 'arrow';
$wrap = $wrap ?? 'a';



//two wrap types
if ($wrap == 'a') { ?>

<a href="<?= $url ?>" target="<?= $target ?>">
  <div class="link-arrow">
    <?= $title ?>
    <?= dfree_svg('link_arrow'); ?>
  </div>
</a>

<?php 
  //div wrap that is only the title
  // used when an a tag wraps the entire element
} else { 
?>

<div class="link-arrow">
  <?= $title ?>
  <?= dfree_svg('link_arrow'); ?>
</div>

<?php } ?>