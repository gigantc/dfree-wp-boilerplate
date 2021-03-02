<?php
/**
* The header for our theme.
*
* This is the template that displays all of the <head> section and everything up until <div id="content">
*
* @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
*
* @package lawfirm
*/

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> id="bdhwk">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="apple-touch-icon" href="<?= get_template_directory_uri() ?>/apple-touch-icon.png">
<link rel="icon" type="image/svg+xml" href="<?= get_template_directory_uri() ?>/favicon.svg" >

<meta property="og:type" content="website" />
<meta property="og:title" content="dfree boilerplate" />
<meta property="og:description" content="A basic boilerplate that does some things." />
<meta property="og:url" content="" />
<meta property="og:site_name" content="dfree boilerplate" />
<meta property="og:image" content="<?= get_template_directory_uri() ?>/tile-wide.png" />
<meta property="og:locale" content="en_US" />



<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	
	<header class="global-header">
    
  </header>