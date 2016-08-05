<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package thelawfirm
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		while ( have_posts() ) : the_post();
			$post_id = get_the_id();
			get_template_part( 'template-parts/content', get_post_format() ); ?>

			<article class="post">
			  <header class="post-header">
			    <h1><?php the_title(); ?></h1>
			    <p><?php echo get_the_date('m.d.y')."&nbsp;&nbsp;|&nbsp;&nbsp;";?></p>
			    <?php wp_reset_postdata(); ?>
			  </header>

			  <aside class="share-this">
			    <div>
			      <p>Share This</p>
			      <?php echo do_shortcode('[share]'); ?>
			    </div>
			  </aside>
			</article>
			<?php
		endwhile; // End of the post builder loop
		?>


		</main><!-- #main -->
		<?php get_footer(); ?>
	</div><!-- #primary -->


