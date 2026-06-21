<?php
/**
 * The loop template file.
 *
 * Included on pages like index.php, archive.php and search.php to display a loop of posts.
 * Learn more: http://codex.wordpress.org/The_Loop
 *
 * @package boiler
 */

do_action( 'boiler_loop_before' );

while ( have_posts() ) : the_post();

	/* Include the Post-Format-specific template for the content.
	 * To override in a child theme, include a file named content-___.php
	 * (where ___ is the Post Format name) and that will be used instead.
	 */
	get_template_part( 'template-parts/content', get_post_format() );

endwhile;

do_action( 'boiler_loop_after' );
