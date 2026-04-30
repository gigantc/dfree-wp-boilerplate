<?php
/**
 * The header for our theme.
 *
 * Displays everything from <head> through the opening <header>.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package boiler
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="bdhwk">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="apple-touch-icon" href="<?= get_template_directory_uri() ?>/apple-touch-icon.png">
<link rel="icon" type="image/svg+xml" href="<?= get_template_directory_uri() ?>/favicon.svg">

<meta property="og:type" content="website" />
<meta property="og:title" content="<?php bloginfo( 'name' ); ?>" />
<meta property="og:description" content="<?php bloginfo( 'description' ); ?>" />
<meta property="og:url" content="<?= esc_url( home_url( '/' ) ) ?>" />
<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>" />
<meta property="og:locale" content="en_US" />

<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">

	<header class="global-header">
		<div class="container">
			<a href="<?= esc_url( home_url( '/' ) ) ?>" class="branding">dFree's Boilerplate</a>
		</div>
	</header>
