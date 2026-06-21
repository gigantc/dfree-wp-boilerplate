<?php
/**
 * Single Page Section Rendering
 *
 * Helper function for rendering CPT single page sections.
 * Usage: single('provider/hero'), single('shared/helpful-resources')
 */

global $dfree_used_singles;
$dfree_used_singles = array();

/**
 * Render a single page section
 *
 * @param string $key Section path key (e.g., 'provider/hero', 'shared/related-items')
 * @param array $args Parameters passed to the section template
 */
function single($key, $args = array()) {
	global $dfree_used_singles;

	$registry = DFREE_Section_Registry::get_instance();
	$file = $registry->get_section_file($key);

	if ($file && file_exists($file)) {
		$dfree_used_singles[$key] = true;
		extract($args);
		include $file;
	} else {
		if (WP_DEBUG) {
			echo '<!-- Single section not found: ' . esc_html($key) . ' -->';
		}
	}
}

/**
 * Register section scripts
 */
function dfree_register_single_scripts() {
	if ( is_admin() ) {
		return;
	}

	$registry = DFREE_Section_Registry::get_instance();
	$sections = $registry->get_sections();

	foreach ($sections as $section) {
		if (!empty($section['has_js'])) {
			$handle = 'single-' . str_replace('/', '-', $section['key']);
			$js_file = get_template_directory_uri() . '/dist/js/singles/' . $section['slug'] . '.min.js';
			$dependencies = !empty($section['requires']) ? $section['requires'] : array();

			wp_register_script($handle, $js_file, $dependencies, dfree_get_version(), true);
		}
	}
}
add_action('wp_enqueue_scripts', 'dfree_register_single_scripts', 20);

/**
 * Enqueue scripts only for sections that were actually used
 */
function dfree_enqueue_used_single_scripts() {
	global $dfree_used_singles;

	if (empty($dfree_used_singles)) {
		return;
	}

	$registry = DFREE_Section_Registry::get_instance();

	foreach ($dfree_used_singles as $key => $used) {
		$section = $registry->get_section($key);
		if (!$section) continue;

		// Enqueue required library scripts and styles
		if (!empty($section['requires'])) {
			foreach ($section['requires'] as $lib) {
				wp_enqueue_script($lib);
				// Also enqueue matching style if registered (e.g., swiper CSS)
				if (wp_style_is($lib, 'registered')) {
					wp_enqueue_style($lib);
				}
			}
		}

		// Enqueue section JS
		if (!empty($section['has_js'])) {
			$handle = 'single-' . str_replace('/', '-', $section['key']);
			wp_enqueue_script($handle);
		}
	}
}
add_action('wp_footer', 'dfree_enqueue_used_single_scripts', 5);
