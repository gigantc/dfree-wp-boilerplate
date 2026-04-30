<?php
/**
 * Card List Component
 *
 * A bordered card with an icon, title, description, icon list, and linkarrow CTA.
 *
 * Available parameters:
 * - $icon       (array|string) — Custom Icon array ['path','url','style'], ACF image array, or file path string
 * - $title      (string)       — Card heading (h3)
 * - $description (string)      — Subtitle text below heading (p)
 * - $icon_list  (array)        — Array of items, each: ['icon' => mixed, 'text' => string, 'link' => string URL]
 * - $link       (array)        — ACF link array ['url', 'title', 'target'] — rendered as linkarrow CTA
 * - $has_border (bool)         — Show border (default: true)
 *
 * Usage:
 * component('card-list', [
 *   'icon'        => get_sub_field('icon'),
 *   'title'       => get_sub_field('title'),
 *   'description' => get_sub_field('description'),
 *   'icon_list'   => $icon_list_items,
 *   'link'        => get_sub_field('link'),
 * ]);
 */

// PROPS
$icon        = $icon        ?? null;
$title       = $title       ?? '';
$description = $description ?? '';
$icon_list   = $icon_list   ?? [];
$link        = $link        ?? null;
$has_border  = $has_border  ?? true;

// Process card icon SVG (custom_icon array, ACF image array, or file path string)
$icon_svg   = '';
$icon_style = 'line';
if ($icon) {
  if (is_array($icon) && isset($icon['path'])) {
    // Custom Icon plugin array
    $icon_style = $icon['style'] ?? 'line';
    if (!empty($icon['path']) && file_exists($icon['path'])) {
      $icon_svg = file_get_contents($icon['path']);
    }
  } elseif (is_array($icon) && isset($icon['ID'])) {
    // ACF image array
    $icon_path = get_attached_file($icon['ID']);
    if ($icon_path && file_exists($icon_path)) {
      $icon_svg = file_get_contents($icon_path);
    }
  } elseif (is_string($icon) && file_exists($icon)) {
    // Direct file path
    $icon_svg = file_get_contents($icon);
  }
}

// Process CTA link
$url        = $link['url']    ?? '';
$target     = $link['target'] ?? '_self';
$link_title = $link['title']  ?? '';
$has_link   = $link && $url && $link_title;
?>

<div class="card-list<?= $has_border ? ' card-list--border' : '' ?>">

  <div class="card-content">

    <?php if ($icon_svg) : ?>
      <span class="icon icon-<?= esc_attr($icon_style) ?>">
        <?= $icon_svg ?>
      </span>
    <?php endif; ?>

    <?php if ($title) : ?>
      <h3><?= esc_html($title) ?></h3>
    <?php endif; ?>

    <?php if ($description) : ?>
      <p><?= esc_html($description) ?></p>
    <?php endif; ?>

    <?php if (!empty($icon_list)) : ?>
      <ul class="icon-list">
        <?php foreach ($icon_list as $item) :

          $item_text = $item['text'] ?? '';
          $item_url  = $item['link'] ?? ''; // plain URL string
          $item_icon = $item['icon'] ?? null;

          // Process list item icon SVG
          $item_svg   = '';
          $item_style = 'line';
          if ($item_icon) {
            if (is_array($item_icon) && isset($item_icon['path'])) {
              $item_style = $item_icon['style'] ?? 'line';
              if (!empty($item_icon['path']) && file_exists($item_icon['path'])) {
                $item_svg = file_get_contents($item_icon['path']);
              }
            } elseif (is_array($item_icon) && isset($item_icon['ID'])) {
              $item_icon_path = get_attached_file($item_icon['ID']);
              if ($item_icon_path && file_exists($item_icon_path)) {
                $item_svg = file_get_contents($item_icon_path);
              }
            } elseif (is_string($item_icon) && file_exists($item_icon)) {
              $item_svg = file_get_contents($item_icon);
            }
          }

        ?>
          <li>
            <?php if ($item_svg) : ?>
              <span class="icon icon-<?= esc_attr($item_style) ?>"><?= $item_svg ?></span>
            <?php endif; ?>
            <?php if ($item_url) : ?>
              <a href="<?= esc_url($item_url) ?>"><?= esc_html($item_text) ?></a>
            <?php elseif ($item_text) : ?>
              <span><?= esc_html($item_text) ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  </div>

  <?php if ($has_link) : ?>
    <?php component('linkarrow', [
      'url'    => $url,
      'title'  => $link_title,
      'target' => $target,
      'wrap'   => 'a',
    ]); ?>
  <?php endif; ?>

</div>
