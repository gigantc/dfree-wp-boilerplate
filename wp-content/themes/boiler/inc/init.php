<?php
/**
 * Lawfirm Theme Engine...bitches.
 *
 * @package Lawfirm
 */
/**
 * Setup.
 * Enqueue styles, register widget regions, etc.
 */
require get_template_directory() . '/inc/functions/setup.php';
/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/functions/extras.php';
/**
 * Admin.
 * admin dashboard, user accounts and logins
 */
require get_template_directory() . '/inc/structure/admin.php';
/**
 * Structure.
 * Template functions used throughout the theme.
 */
require get_template_directory() . '/inc/structure/core.php';
require get_template_directory() . '/inc/structure/posts.php';
require get_template_directory() . '/inc/structure/hooks.php';
require get_template_directory() . '/inc/structure/block-registry.php';
require get_template_directory() . '/inc/structure/blocks.php';
require get_template_directory() . '/inc/structure/search.php';
/**
 * ACF in the Admin Dashboard
 */
require get_template_directory() . '/inc/structure/acf.php';