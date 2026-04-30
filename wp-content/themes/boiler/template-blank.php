<?php
/**
 * Blank Template
 *
 * Template name: Blank
 *
 * @package boiler
 */

get_header(); ?>

<main>
	<div class="text-page-wrapper">
		<?php
		while ( have_posts() ) : the_post();
			the_content();
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</main>

<?php get_footer(); ?>
