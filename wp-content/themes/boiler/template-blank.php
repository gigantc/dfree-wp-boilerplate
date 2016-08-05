<?php
/**
 * The template for displaying all single posts.
 *
 * Template name: Blank
 *
 * @package lawfirm
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<div class="text-page-wrapper">
      <?php
      while ( have_posts() ) : the_post();
      the_content();
      endwhile;
      wp_reset_postdata();
      ?>
    </div>
		
		</main><!-- #main -->
		<?php get_footer(); ?>
	</div><!-- #primary -->


