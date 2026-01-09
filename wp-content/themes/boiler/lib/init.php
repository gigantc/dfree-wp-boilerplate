<?php
/**
 * Theme Engine
 *
 * @package Lawfirm
 */

// Setup
require get_template_directory() . '/lib/setup.php';

// Admin
require get_template_directory() . '/lib/admin.php';

// Blocks
require get_template_directory() . '/lib/block-registry.php';
require get_template_directory() . '/lib/blocks.php';

// Components
require get_template_directory() . '/lib/component-registry.php';
require get_template_directory() . '/lib/components.php';

// ACF
require get_template_directory() . '/lib/acf.php';