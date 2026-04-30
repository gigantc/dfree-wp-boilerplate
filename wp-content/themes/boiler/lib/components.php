<?php
/**
 * Component Rendering Functions
 *
 * Helper functions for rendering reusable components
 */

// Track which components are used on the current page
global $dfree_used_components;
$dfree_used_components = array();

/**
 * Render a component with parameters
 *
 * @param string $slug Component slug (e.g., 'button', 'card', 'link')
 * @param array $args Component parameters
 * @return void Outputs HTML
 */
function component($slug, $args = array()) {
	global $dfree_used_components;

	$registry = DFREE_Component_Registry::get_instance();
	$file = $registry->get_component_file($slug);

	if ($file && file_exists($file)) {
		// Track that this component was used
		$dfree_used_components[$slug] = true;

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
 * Register component scripts (but don't enqueue yet)
 * Hooked to wp_enqueue_scripts
 */
function dfree_register_component_scripts() {
	if ( is_admin() ) {
		return;
	}

	$registry = DFREE_Component_Registry::get_instance();
	$components = $registry->get_components();

	foreach ($components as $component) {
		if (!empty($component['has_js'])) {
			$js_file = get_template_directory_uri() . '/dist/js/components/' . $component['slug'] . '.min.js';

			$dependencies = array();
			if (!empty($component['requires']) && is_array($component['requires'])) {
				$dependencies = $component['requires'];
			}

			// Register but don't enqueue - we'll enqueue only used components later
			wp_register_script(
				'component-' . $component['slug'],
				$js_file,
				$dependencies,
				dfree_get_version(),
				true
			);
		}
	}
}
add_action('wp_enqueue_scripts', 'dfree_register_component_scripts', 20);

/**
 * Enqueue scripts only for components that were actually used
 * Hooked to wp_footer before scripts print
 */
function dfree_enqueue_used_component_scripts() {
	global $dfree_used_components;

	if (empty($dfree_used_components)) {
		return;
	}

	$registry = DFREE_Component_Registry::get_instance();

	foreach ($dfree_used_components as $slug => $used) {
		$component = $registry->get_component($slug);

		if ($component && !empty($component['has_js'])) {
			wp_enqueue_script('component-' . $slug);
		}
	}
}
// Priority 5 ensures this runs before wp_print_footer_scripts (priority 20)
add_action('wp_footer', 'dfree_enqueue_used_component_scripts', 5);
