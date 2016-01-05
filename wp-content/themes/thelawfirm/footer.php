<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package thelawfirm
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'thelawfirm' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'thelawfirm' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( esc_html__( 'Theme: %1$s by %2$s.', 'thelawfirm' ), 'thelawfirm', '<a href="http://thelawfirm.kssdev.com" rel="designer">The Law Firm, Your Welcome.</a>' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

<script>
	console.log('%c                                          ', 'background: #64A6A0; color: #fffef2; padding-bottom: 7px;');
	console.log('%c         DESIGN & DEVELOPMENT BY          ', 'background: #64A6A0; color: #fffef2; padding-bottom: 7px;');
	console.log('%c     -     KITCHEN SINK STUDIOS     -     ', 'background: #64A6A0; color: #fffef2; padding-bottom: 7px;');
	console.log('%c                                          ', 'background: #64A6A0; color: #fffef2');
</script><!-- Console Credit -->

</body>
</html>
