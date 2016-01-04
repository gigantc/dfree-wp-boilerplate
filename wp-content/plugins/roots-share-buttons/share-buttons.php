<?php

namespace Roots\ShareButtons;

/*
Plugin Name:   Roots Share Buttons
Plugin URI:    https://roots.io/plugins/share-buttons/
Description:   Add lightweight social sharing buttons with optional share counts.
Version:       1.0.3
Author:        Ben Word
Author URI:    https://roots.io/
License:       MIT License
License URI:   http://opensource.org/licenses/MIT
*/

define('ROOTS_SHARE_PATH', plugin_dir_path(__FILE__));
define('ROOTS_SHARE_FOLDER', __FILE__ );

require_once(__DIR__ . '/vendor/phpuri.php');
require_once(__DIR__ . '/lib/admin.php');
require_once(__DIR__ . '/lib/buttons.php');
require_once(__DIR__ . '/lib/share-count.php');
require_once(__DIR__ . '/lib/shortcode.php');

function activation() {
  require_once(ROOTS_SHARE_PATH . 'lib/activation.php');
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activation');

function load_textdomain() {
  load_plugin_textdomain('roots-share-buttons', false, ROOTS_SHARE_PATH . '/lang');
}
add_action('plugins_loaded', __NAMESPACE__ . '\\load_textdomain');
