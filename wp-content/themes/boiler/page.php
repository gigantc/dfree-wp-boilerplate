<?php
/**
 * The template for displaying all pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package boiler
 */

get_header(); ?>

<main>

	<?php
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;
	?>

</main>

<?php get_footer(); ?>
