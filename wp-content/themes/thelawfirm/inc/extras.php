<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package thelawfirm
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function thelawfirm_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'thelawfirm_body_classes' );


function thelawfirm_console_credits() {
?>
<script>
	console.log('%c                                          ', 'background: #666; color: #ddd; padding-bottom: 7px;');
	console.log('%c         DESIGN & DEVELOPMENT BY          ', 'background: #666; color: #ddd; padding-bottom: 7px;');
	console.log('%c        -     THE LAW FIRM     -          ', 'background: #666; color: #ddd; padding-bottom: 7px;');
	console.log('%c                                          ', 'background: #666; color: #ddd');
</script>
<?php
}
add_action( 'wp_footer', 'thelawfirm_console_credits', 100 );
?>