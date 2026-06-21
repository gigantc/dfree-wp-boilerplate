<?php
/**
 * Button Component
 *
 * Available parameters:
 * - $url (string) - Button URL (default: '#')
 * - $title (string) - Button text (default: 'Click Here')
 * - $target (string) - Link target (default: '_self')
 * - $variant (string) - Button style: 'primary', 'secondary', 'text' (default: 'primary')
 * - $class (string) - Additional CSS classes
 * - $icon (array|string) - Custom Icon array ['path','url','style'], or file path string
 *
 * Usage:
 * component('button', [
 *   'url' => '/contact',
 *   'title' => 'Contact Us',
 *   'variant' => 'primary',
 *   'icon' => '/src/icons/arrow_right.svg'
 * ]);
 */

// PROPS
$url = $url ?? '#';
$title = $title ?? 'Click Here';
$target = $target ?? '_self';
$variant = $variant ?? 'primary';
$class = $class ?? '';
$icon = $icon ?? '';

// Process icon
$icon_svg = '';
$icon_style = 'line';
if ($icon) {
	if (is_array($icon) && isset($icon['path'])) {
		// Custom Icon plugin array
		$icon_style = $icon['style'] ?? 'line';
		if (!empty($icon['path']) && file_exists($icon['path'])) {
			$icon_svg = file_get_contents($icon['path']);
		}
	} elseif (is_string($icon)) {
		// Try as absolute path first (ACF upload)
		if (file_exists($icon)) {
			$icon_svg = file_get_contents($icon);
		} else {
			// Try as theme-relative path
			$icon_path = get_template_directory() . $icon;
			if (file_exists($icon_path)) {
				$icon_svg = file_get_contents($icon_path);
			}
		}
	}
}

// Build the class string
$btn_classes = ['btn'];
if ($variant !== 'primary') {
	$btn_classes[] = 'btn--' . $variant;
}
if ($icon_svg) {
	$btn_classes[] = 'btn--has-icon';
}
if ($class) {
	$btn_classes[] = $class;
}
$btn_class = implode(' ', $btn_classes);
?>

<a href="<?= esc_url($url) ?>"
   class="<?= esc_attr($btn_class) ?>"
   target="<?= esc_attr($target) ?>">
	<span class="btn-text"><?= esc_html($title) ?></span>
	<?php if ($icon_svg) : ?>
		<span class="icon-<?= esc_attr($icon_style) ?>"><?= $icon_svg ?></span>
	<?php endif; ?>
</a>
