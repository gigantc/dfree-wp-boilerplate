<?php
/**
 * Component Registry
 *
 * Manages component discovery, caching, and metadata
 * Mirrors the DFREE_Block_Registry pattern for consistency
 */

if (!class_exists('DFREE_Component_Registry')) {
	class DFREE_Component_Registry {
		private static $instance = null;
		private $components = array();
		private $manifest_path;
		private $components_dir;

		/**
		 * Get singleton instance
		 */
		public static function get_instance() {
			if (self::$instance === null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			$this->components_dir = get_template_directory() . '/components';
			$this->manifest_path = $this->components_dir . '/manifest.json';
			$this->load_manifest();
		}

		/**
		 * Load manifest from cache or rebuild
		 */
		private function load_manifest() {
			// Auto-rebuild in development
			$is_dev = (strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
			           strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

			if ($is_dev || !file_exists($this->manifest_path)) {
				$this->rebuild_manifest();
			} else {
				$this->components = $this->read_manifest();
			}
		}

		/**
		 * Read manifest from file
		 */
		private function read_manifest() {
			if (!file_exists($this->manifest_path)) {
				return array();
			}

			$json = file_get_contents($this->manifest_path);
			$data = json_decode($json, true);

			return isset($data['components']) ? $data['components'] : array();
		}

		/**
		 * Scan components directory and rebuild manifest
		 */
		public function rebuild_manifest() {
			$this->components = array();

			if (!is_dir($this->components_dir)) {
				return;
			}

			// Recursively scan components directory
			$this->scan_directory($this->components_dir);

			// Save to manifest file
			$this->save_manifest();
		}

		/**
		 * Recursively scan directory for components
		 */
		private function scan_directory($dir) {
			$items = scandir($dir);

			foreach ($items as $item) {
				if ($item === '.' || $item === '..' || $item === 'manifest.json') {
					continue;
				}

				$path = $dir . '/' . $item;

				// Skip if not a directory
				if (!is_dir($path)) {
					continue;
				}

				// Check for component PHP file
				$component_slug = strtolower(str_replace(' ', '-', $item));
				$php_file = $path . '/' . $component_slug . '.php';

				if (file_exists($php_file)) {
					$this->register_component($item, $path, $component_slug);
				}

				// Recursively scan subdirectories
				$this->scan_directory($path);
			}
		}

		/**
		 * Register a component
		 */
		private function register_component($folder_name, $path, $slug) {
			$relative_path = str_replace($this->components_dir . '/', '', $path);

			// Check for SCSS file
			$scss_file = $path . '/_' . $slug . '.scss';
			$has_scss = file_exists($scss_file);
			$scss_path = $has_scss ? $relative_path . '/_' . $slug . '.scss' : '';

			// Check for JS file
			$js_file = $path . '/' . $slug . '.js';
			$has_js = file_exists($js_file);
			$js_path = $has_js ? $relative_path . '/' . $slug . '.js' : '';

			// Load config if exists
			$config_file = $path . '/component.config.json';
			$config = array();
			if (file_exists($config_file)) {
				$config_json = file_get_contents($config_file);
				$config = json_decode($config_json, true);
			}

			// Register component
			$this->components[$slug] = array(
				'slug' => $slug,
				'path' => $relative_path . '/' . $slug . '.php',
				'name' => isset($config['name']) ? $config['name'] : $folder_name,
				'description' => isset($config['description']) ? $config['description'] : '',
				'has_scss' => $has_scss,
				'scss_path' => $scss_path,
				'has_js' => $has_js,
				'js_path' => $js_path,
				'variants' => isset($config['variants']) ? $config['variants'] : array(),
				'requires' => isset($config['requires']) ? $config['requires'] : array(),
			);
		}

		/**
		 * Save manifest to file
		 */
		private function save_manifest() {
			$data = array(
				'generated' => date('Y-m-d H:i:s'),
				'components' => $this->components,
			);

			$json = json_encode($data, JSON_PRETTY_PRINT);
			file_put_contents($this->manifest_path, $json);
		}

		/**
		 * Get all components
		 */
		public function get_components() {
			return $this->components;
		}

		/**
		 * Get component file path
		 */
		public function get_component_file($slug) {
			if (!isset($this->components[$slug])) {
				return false;
			}

			return $this->components_dir . '/' . $this->components[$slug]['path'];
		}

		/**
		 * Get component metadata
		 */
		public function get_component($slug) {
			return isset($this->components[$slug]) ? $this->components[$slug] : false;
		}
	}
}
