<?php
/**
 * Toggle Switch Component (iOS Style)
 *
 * Available parameters:
 * - $name (string) - Input name attribute (required)
 * - $id (string) - Unique ID for input/label association (defaults to $name)
 * - $checked (bool) - Whether toggle is checked/on (default: false)
 * - $disabled (bool) - Whether toggle is disabled (default: false)
 * - $label (string) - Optional label text
 * - $label_position (string) - Label position: 'left' or 'right' (default: 'right')
 * - $value (string) - Value when checked (default: '1')
 * - $class (string) - Additional CSS classes for wrapper
 * - $attrs (array) - Additional HTML attributes for input element
 *
 * Usage:
 * component('toggle-switch', [
 *   'name' => 'notifications',
 *   'id' => 'notification-toggle',
 *   'checked' => true,
 *   'label' => 'Enable Notifications'
 * ]);
 */

// Defaults
$name = $name ?? '';
$id = $id ?? $name;
$checked = $checked ?? false;
$disabled = $disabled ?? false;
$label = $label ?? '';
$label_position = $label_position ?? 'right';
$value = $value ?? '1';
$class = $class ?? '';
$attrs = $attrs ?? [];

// Validate required parameters
if (empty($name)) {
    error_log('Toggle Switch component: "name" parameter is required');
    return;
}

// Build attributes string
$attrs_string = '';
if (!empty($attrs)) {
    foreach ($attrs as $attr => $attr_value) {
        $attrs_string .= sprintf(' %s="%s"', esc_attr($attr), esc_attr($attr_value));
    }
}

// Wrapper classes
$wrapper_classes = ['toggle-switch-wrapper'];
if (!empty($class)) {
    $wrapper_classes[] = $class;
}
if ($label_position === 'left') {
    $wrapper_classes[] = 'label-left';
}
?>

<div class="<?= esc_attr(implode(' ', $wrapper_classes)) ?>">
    <?php if (!empty($label) && $label_position === 'left') : ?>
        <label for="<?= esc_attr($id) ?>" class="toggle-label">
            <?= esc_html($label) ?>
        </label>
    <?php endif; ?>

    <label class="toggle-switch <?= $disabled ? 'disabled' : '' ?>">
        <input
            type="checkbox"
            name="<?= esc_attr($name) ?>"
            id="<?= esc_attr($id) ?>"
            value="<?= esc_attr($value) ?>"
            <?= $checked ? 'checked' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
            <?= $attrs_string ?>
        >
        <span class="slider"></span>
    </label>

    <?php if (!empty($label) && $label_position === 'right') : ?>
        <label for="<?= esc_attr($id) ?>" class="toggle-label">
            <?= esc_html($label) ?>
        </label>
    <?php endif; ?>
</div>
