<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package boiler
 */

get_header();
?>

<main>
	<header class="archive-header">
		<?php
		$archive_title = get_the_archive_title();
		$archive_title = str_replace( 'Month: ', '', $archive_title );
		$archive_title = str_replace( 'Category: ', '', $archive_title );
		$archive_title = str_replace( 'Tag: ', '', $archive_title );
		?>
		<h2><?= esc_html( strtoupper( $archive_title ) ) ?></h2>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php get_template_part( 'loop' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
