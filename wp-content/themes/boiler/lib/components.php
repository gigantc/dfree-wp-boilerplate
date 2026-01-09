<?php
/**
 * Component Rendering Functions
 *
 * Helper functions for rendering reusable components
 */

/**
 * Render a component with parameters
 *
 * @param string $slug Component slug (e.g., 'button', 'card', 'link')
 * @param array $args Component parameters
 * @return void Outputs HTML
 */
function component($slug, $args = array()) {
	$registry = DFREE_Component_Registry::get_instance();
	$file = $registry->get_component_file($slug);

	if ($file && file_exists($file)) {
		// Extract args as variables for component template
		extract($args);
		include $file;
	} else {
		// Development warning
		if (WP_DEBUG) {
			echo '<!-- Component not found: ' . esc_html($slug) . ' -->';
		}
	}
}

/**
 * Auto-enqueue component scripts and styles
 * Hooked to wp_enqueue_scripts
 */
function dfree_enqueue_component_scripts() {
	if (is_admin()) {
		return;
	}

	$registry = DFREE_Component_Registry::get_instance();
	$components = $registry->get_components();

	foreach ($components as $component) {
		// Enqueue component JavaScript (always load approach)
		if (!empty($component['has_js'])) {
			$js_file = get_template_directory_uri() . '/dist/js/components/' . $component['slug'] . '.min.js';

			$dependencies = array('jquery');
			if (!empty($component['requires']) && is_array($component['requires'])) {
				$dependencies = array_merge($dependencies, $component['requires']);
			}

			wp_enqueue_script(
				'component-' . $component['slug'],
				$js_file,
				$dependencies,
				'1.0.0',
				true
			);
		}

		// Note: Component CSS is already included via _blocks.scss auto-generation
		// No need to enqueue separately
	}
}
add_action('wp_enqueue_scripts', 'dfree_enqueue_component_scripts', 20);
