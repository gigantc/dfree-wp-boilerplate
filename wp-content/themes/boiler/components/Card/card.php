<?php
/**
 * Card Component
 *
 * Available parameters:
 * - $icon (array|string) - Custom Icon array ['path','url','style'], ACF image array, or file path string
 * - $title (string) - Card title
 * - $description (string) - Card description text
 * - $link (array) - ACF link array with url, title, target
 * - $full_card_link (bool) - Wrap entire card in link (default: true)
 * - $has_border (bool) - Show border on card (default: true)
 * - $show_arrow_link (bool) - Show arrow link at bottom of card (default: true)
 *
 * Usage:
 * component('card', [
 *   'icon' => get_sub_field('icon'),
 *   'title' => get_sub_field('title'),
 *   'description' => get_sub_field('description'),
 *   'link' => get_sub_field('link'),
 *   'full_card_link' => tr
 *   'has_border' => true
 * ]);
 */

// PROPS
$icon = $icon ?? null;
$title = $title ?? '';
$description = $description ?? '';
$link = $link ?? null;
$full_card_link = $full_card_link ?? true;
$has_border = $has_border ?? true;
$show_arrow_link = $show_arrow_link ?? true;

// Process icon SVG
$icon_svg = '';
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


// Process link
$url = '';
$target = '_self';
$link_title = '';
if ($link) {
	$url = $link['url'] ?? '';
	$target = $link['target'] ?? '_self';
	$link_title = $link['title'] ?? '';
}

// Determine if we need a link wrapper
$has_link = $link && $url;
$wrap_in_link = $has_link && $full_card_link;
?>

<?php if ($wrap_in_link) : ?>
<a class="card-outer-link" href="<?= esc_url($url) ?>" target="<?= esc_attr($target) ?>">
<?php endif; ?>
<div class="card<?= $has_border ? ' card--border' : '' ?>">
	<?php if ($icon_svg) : ?>
		<span class="icon icon-<?= esc_attr($icon_style) ?>">
			<?= $icon_svg ?>
		</span>
	<?php endif; ?>
	<?php if ($title) : ?>
		<h3><?= esc_html($title) ?></h3>
	<?php endif; ?>
	<?php if ($description) : ?>
		<p><?= $description ?></p>
	<?php endif; ?>
	<?php if ($has_link && $link_title && $show_arrow_link) : ?>
		<?php
		component('linkarrow', [
			'url' => $url,
			'title' => $link_title,
			'target' => $target,
			'wrap' => $full_card_link ? 'div' : 'a'
		]);
		?>
	<?php endif; ?>
</div>
<?php if ($wrap_in_link) : ?>
</a>
<?php endif; ?>
