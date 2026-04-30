<?php
/**
 * Theme Engine
 *
 * @package boiler
 */

// Helpers (load first - other files depend on these)
require get_template_directory() . '/lib/helpers.php';

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

// Sections (CPT single page templates)
require get_template_directory() . '/lib/section-registry.php';
require get_template_directory() . '/lib/sections.php';

// ACF
require get_template_directory() . '/lib/acf.php';
