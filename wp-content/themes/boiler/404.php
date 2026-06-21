<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package boiler
 */

get_header();
?>

<main>

	<section class="error-404">
		<div class="container">

			<span class="error-code">404</span>

			<h1>Page Not Found</h1>

			<p class="message">You've reached a page that doesn't exist. Try searching for what you need below.</p>

			<form role="search" method="get" class="search-form" action="<?= esc_url( home_url( '/' ) ) ?>">
				<input type="search" name="s" placeholder="Search..." />
				<button type="submit">Search</button>
			</form>

		</div>
	</section>

</main>

<?php get_footer(); ?>
