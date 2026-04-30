<?php
/**
 * Theme Helper Functions
 *
 * Centralized utility functions used across the theme
 */

/**
 * Check if we're in a development environment
 *
 * @return bool True if development, false if production
 */
function dfree_is_development() {
	static $is_dev = null;

	if ($is_dev === null) {
		$host = $_SERVER['HTTP_HOST'] ?? '';
		$is_dev = (
			strpos($host, '.local') !== false ||
			strpos($host, 'localhost') !== false ||
			strpos($host, '.test') !== false
		);
	}

	return $is_dev;
}

/**
 * Responsive image output from an ACF image array
 *
 * Uses wp_get_attachment_image() to generate proper srcset/sizes attributes.
 * Falls back to a plain <img> tag when no attachment ID is available.
 *
 * @param array|null $image ACF image array (must have 'ID' key)
 * @param string     $size  Registered image size: dfree_card, dfree_hero, dfree_square
 * @param array      $attrs Optional HTML attributes to merge (alt, class, sizes, etc.)
 * @return void      Outputs the <img> tag directly
 */
function dfree_image($image, $size = 'dfree_card', $attrs = []) {
	if (!$image) return;

	// Default sizes attribute per preset
	$default_sizes = [
		'dfree_card'   => '(max-width: 768px) 100vw, 400px',
		'dfree_hero'   => '(max-width: 768px) 100vw, 1000px',
		'dfree_square' => '(max-width: 768px) 50vw, 400px',
	];

	$merged = array_merge(
		['sizes' => $default_sizes[$size] ?? ''],
		$attrs
	);

	// ACF image array with attachment ID — use WP responsive output
	if (!empty($image['ID'])) {
		if (empty($merged['alt']) && !empty($image['alt'])) {
			$merged['alt'] = $image['alt'];
		}
		echo wp_get_attachment_image($image['ID'], $size, false, $merged);
		return;
	}

	// Fallback: plain img tag from URL string
	$url = $image['url'] ?? '';
	if (!$url) return;

	$alt = $merged['alt'] ?? ($image['alt'] ?? '');
	$classes = trim('is-generic-fallback ' . ($merged['class'] ?? ''));
	echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($classes) . '" loading="lazy">';
}

/**
 * Inline an SVG file with static caching
 *
 * Reads the SVG once per request and returns the cached contents on
 * subsequent calls. Pass just the filename — the /src/icons/ path is assumed.
 *
 * @param string $name SVG filename (e.g. 'arrow.svg' or 'arrow')
 * @return string SVG markup
 */
function dfree_svg($name) {
	static $cache = [];

	// Allow calling with or without .svg extension
	if (substr($name, -4) !== '.svg') {
		$name .= '.svg';
	}

	if (!isset($cache[$name])) {
		$path = get_template_directory() . '/src/icons/' . $name;
		$cache[$name] = file_exists($path) ? file_get_contents($path) : '';
	}

	return $cache[$name];
}

/**
 * Build a formatted address string from ACF address fields
 *
 * @param array $address_group ACF address group with street, city, state, zipcode keys
 * @return array ['address' => formatted string, 'directions_url' => Google Maps URL]
 */
function dfree_build_address($address_group) {
	$street = $address_group['street']  ?? '';
	$city   = $address_group['city']    ?? '';
	$state  = $address_group['state']   ?? '';
	$zip    = $address_group['zipcode'] ?? '';

	$address = $street;
	if ($city)  $address .= ', ' . $city;
	if ($state) $address .= ', ' . $state;
	if ($zip)   $address .= ' ' . $zip;

	$directions_url = $address ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address) : '#';

	return [
		'address'        => $address,
		'directions_url' => $directions_url,
	];
}

/**
 * Get the theme version for cache busting
 *
 * @return string Theme version or filemtime in development
 */
function dfree_get_version() {
	static $version = null;

	if ($version === null) {
		if (dfree_is_development()) {
			$version = time(); // Bust cache in dev
		} else {
			$theme = wp_get_theme();
			$version = $theme->get('Version') ?: '1.0.0';
		}
	}

	return $version;
}
