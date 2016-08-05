<?php
/**
* The template for displaying 404 pages (not found).
*
* @link https://codex.wordpress.org/Creating_an_Error_404_Page
*
* @package lawfirm
*/

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<div class="container">
					<header class="page-header">
						<h1>Page Not Found</h1>
					</header><!-- .page-header -->
					
					<div class="page-content">
						<p>This page was deleted or may have never existed.  Don't worry, just contact us and we'll point you back to the light.  Or have you tried looking over there?</p>
						<a href="/contact"><div class="btn">Contact Us</div></a>
					</div><!-- .page-content -->
				</div>
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
