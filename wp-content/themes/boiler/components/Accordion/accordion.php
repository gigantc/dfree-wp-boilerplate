<?php
/**
 * Accordion Component
 *
 * Available parameters:
 * - $title (string) - Accordion title (displayed as h4, visible when closed)
 * - $copy (string) - Accordion content (shows/hides on toggle)
 * - $isOpen (boolean) - Whether accordion starts open (default: false)
 * - $class (string) - Additional CSS classes (optional)
 *
 * Usage:
 * component('accordion', [
 *   'title' => 'Frequently Asked Question',
 *   'copy' => 'This is the answer to the question.',
 *   'isOpen' => false
 * ]);
 */

// PROPS
$title = $title ?? '';
$copy = $copy ?? '';
$isOpen = $isOpen ?? false;
$class = $class ?? '';

// Build class string
$accordion_classes = ['accordion'];
if ($isOpen) {
	$accordion_classes[] = 'is-open';
}
if ($class) {
	$accordion_classes[] = $class;
}
$accordion_class = implode(' ', $accordion_classes);

// Generate unique ID for ARIA attributes
$accordion_id = 'accordion-' . uniqid();

// Load caret icon
$icon_path = get_template_directory() . '/src/icons/caret_down.svg';
$icon_svg = file_exists($icon_path) ? file_get_contents($icon_path) : '';
?>

<div class="<?= esc_attr($accordion_class) ?>">
	<button aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
	        aria-controls="<?= esc_attr($accordion_id) ?>">
		<h4><?= esc_html($title) ?></h4>
		<span class="icon" aria-hidden="true">
			<?= $icon_svg ?>
		</span>
	</button>

	<div class="content"
	     id="<?= esc_attr($accordion_id) ?>"
	     aria-hidden="<?= $isOpen ? 'false' : 'true' ?>">
		<div class="inner wysiwyg">
			<?= $copy ?>
		</div>
	</div>
</div>
