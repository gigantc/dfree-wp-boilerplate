<?php
/**
 * Button Component
 *
 */



// PROPS
$url = $url ?? '#';
$title = $title ?? 'Click Here';
$target = $target ?? '_self';
$variant = $variant ?? 'primary';
$class = $class ?? '';


// Build the class string
$btn_classes = ['btn'];
if ($variant !== 'primary') {
	$btn_classes[] = 'btn--' . $variant;
}
if ($class) {
	$btn_classes[] = $class;
}
$btn_class = implode(' ', $btn_classes);


?>


<a href="<?= esc_url($url) ?>"
   class="<?= esc_attr($btn_class) ?>"
   target="<?= esc_attr($target) ?>">
	<?= esc_html($title) ?>
</a>
