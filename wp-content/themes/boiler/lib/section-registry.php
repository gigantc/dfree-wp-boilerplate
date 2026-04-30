<?php
/**
 * Section Registry
 *
 * Manages section discovery, caching, and metadata for CPT single templates.
 * Sections are keyed by path (e.g., 'provider/hero', 'shared/helpful-resources').
 */

if (!class_exists('DFREE_Section_Registry')) {
	class DFREE_Section_Registry {
		private static $instance = null;
		private $sections = array();
		private $manifest_path;
		private $sections_dir;

		// Transient cache settings
		private $transient_key = 'dfree_section_registry';
		private $transient_ttl = 5;

		public static function get_instance() {
			if (self::$instance === null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->sections_dir = get_template_directory() . '/singles';
			$this->manifest_path = $this->sections_dir . '/manifest.json';
			$this->load_manifest();
		}

		private function load_manifest() {
			if (dfree_is_development()) {
				$cached = get_transient($this->transient_key);
				if ($cached !== false) {
					$this->sections = $cached;
					return;
				}
				$this->rebuild_manifest();
				set_transient($this->transient_key, $this->sections, $this->transient_ttl);
				return;
			}

			if (file_exists($this->manifest_path)) {
				$this->sections = $this->read_manifest();
			} else {
				$this->rebuild_manifest();
			}
		}

		private function read_manifest() {
			if (!file_exists($this->manifest_path)) {
				return array();
			}
			$json = file_get_contents($this->manifest_path);
			$data = json_decode($json, true);
			return isset($data['sections']) ? $data['sections'] : array();
		}

		public function rebuild_manifest() {
			$this->sections = array();

			if (!is_dir($this->sections_dir)) {
				return;
			}

			$this->scan_directory($this->sections_dir);
			$this->save_manifest();

			if (dfree_is_development()) {
				delete_transient($this->transient_key);
			}
		}

		/**
		 * Recursively scan directory for sections.
		 * Builds path-based keys like 'provider/hero', 'shared/related-items'.
		 */
		private function scan_directory($dir) {
			$items = scandir($dir);

			foreach ($items as $item) {
				if ($item === '.' || $item === '..' || $item === 'manifest.json') {
					continue;
				}

				$path = $dir . '/' . $item;

				if (!is_dir($path)) {
					continue;
				}

				// Check for a PHP file matching the slugified folder name
				$slug = sanitize_title($item);
				$php_file = $path . '/' . $slug . '.php';

				if (file_exists($php_file)) {
					// Build path key from relative path, slugifying each segment
					$relative = str_replace($this->sections_dir . '/', '', $path);
					$segments = explode('/', $relative);
					$key = implode('/', array_map('sanitize_title', $segments));

					$this->register_section($key, $slug, $path);
				}

				// Recurse into subdirectories
				$this->scan_directory($path);
			}
		}

		private function register_section($key, $slug, $path) {
			$relative_path = str_replace($this->sections_dir . '/', '', $path);

			$scss_file = $path . '/_' . $slug . '.scss';
			$has_scss = file_exists($scss_file);

			$js_file = $path . '/' . $slug . '.js';
			$has_js = file_exists($js_file);

			// Load config if exists
			$config_file = $path . '/section.config.json';
			$config = array();
			if (file_exists($config_file)) {
				$config_json = file_get_contents($config_file);
				$config = json_decode($config_json, true) ?: array();
			}

			$this->sections[$key] = array(
				'key' => $key,
				'slug' => $slug,
				'path' => $relative_path . '/' . $slug . '.php',
				'has_scss' => $has_scss,
				'has_js' => $has_js,
				'requires' => isset($config['requires']) ? $config['requires'] : array(),
			);
		}

		private function save_manifest() {
			$data = array(
				'generated' => date('Y-m-d H:i:s'),
				'sections' => $this->sections,
			);
			$json = json_encode($data, JSON_PRETTY_PRINT);
			file_put_contents($this->manifest_path, $json);
		}

		public function get_sections() {
			return $this->sections;
		}

		public function get_section_file($key) {
			if (!isset($this->sections[$key])) {
				return false;
			}
			return $this->sections_dir . '/' . $this->sections[$key]['path'];
		}

		public function get_section($key) {
			return isset($this->sections[$key]) ? $this->sections[$key] : false;
		}
	}
}
