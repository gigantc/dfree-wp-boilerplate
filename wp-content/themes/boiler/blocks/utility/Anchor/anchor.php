<?php
/**
 * Anchor Block
 *
 * Invisible jump-link target. Editor enters a name, block outputs a span
 * with a slugified id used in URLs like /page#community-map.
 */

$raw = get_field('anchor_id');
$id  = $raw ? sanitize_title($raw) : '';
?>

<?php if (get_field('is_example')) : ?>

  <div class="anchor-block is-example">
    <span class="label">Anchor</span>
    <span class="id">#<?= esc_html($id ?: 'anchor-name') ?></span>
  </div>

<?php elseif (is_admin()) : ?>

  <div class="anchor-block is-editor">
    <span class="label">Anchor</span>
    <span class="id">#<?= esc_html($id ?: 'set-anchor-name') ?></span>
  </div>

<?php elseif ($id) : ?>

  <span class="anchor" id="<?= esc_attr($id) ?>" aria-hidden="true"></span>

<?php endif; ?>
