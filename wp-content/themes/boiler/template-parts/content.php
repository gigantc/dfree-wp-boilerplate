<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package lawfirm
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope="" itemtype="http://schema.org/BlogPosting">

	<?php
	/**
 	 * @hooked lawfirm_post_header() - 10
 	 * @hooked lawfirm_post_content() - 20
 	 * 
	 */
	do_action( 'lawfirm_loop_post' );

	?>

</article><!-- #post-## -->
