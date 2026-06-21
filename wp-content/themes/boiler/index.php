<?php
/**
 * The main template file.
 *
 * The most generic template file in a WordPress theme. Used when nothing more
 * specific matches a query (e.g. blog homepage when no home.php exists).
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package boiler
 */

get_header(); ?>

<main>

	<?php if ( have_posts() ) : ?>

		<?php get_template_part( 'loop' ); ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/content', 'none' ); ?>

	<?php endif; ?>

</main>

<?php get_footer(); ?>
