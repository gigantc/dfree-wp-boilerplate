<?php
/**
 * Admin dashboard customizations
 */

// Remove unnecessary dashboard widgets
function dfree_remove_dashboard_meta() {
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
}
add_action( 'admin_init', 'dfree_remove_dashboard_meta' );


/**
 * Custom sidebars for different user roles
 *
 * Configure superadmin lists below to grant full admin access.
 * Anyone not in the lists with user_level > 0 will have restricted menus.
 */
function dfree_remove_menu() {
	// Usernames that can edit anything
	$superadmins = array(
		// 'superadmin'
	);

	// Email addresses that can edit anything
	$superadmin_emails = array(
		// 'admin@example.com'
	);

	// Email domains that can edit anything (without the @)
	$superadmin_domains = array(
		// 'example.com'
	);

	$current_user = wp_get_current_user();

	$email_parts = explode( '@', $current_user->user_email );
	$user_domain = isset( $email_parts[1] ) ? strtolower( $email_parts[1] ) : '';

	$is_superadmin = in_array( $current_user->user_login, $superadmins )
				  || in_array( $current_user->user_email, $superadmin_emails )
				  || in_array( $user_domain, $superadmin_domains );

	if ( ! $is_superadmin && $current_user->user_level > 0 ) {
		remove_menu_page( 'edit.php?post_type=acf-field-group' ); // ACF
		remove_menu_page( 'edit-comments.php' );                  // Comments
		remove_menu_page( 'plugins.php' );                        // Plugins
		remove_menu_page( 'index.php' );                          // Dashboard
		remove_menu_page( 'options-general.php' );                // Settings
		remove_menu_page( 'tools.php' );                          // Tools
		remove_menu_page( 'graphiql-ide' );                       // GraphQL
		remove_menu_page( 'themes.php' );                         // Appearance
	}
}
add_action( 'admin_menu', 'dfree_remove_menu', 9999 );


// Admin sidebar icons
function dfree_replace_admin_menu_icons_css() {
	?>
	<style>
	#adminmenu #toplevel_page_social-accounts div.wp-menu-image::before {content:'\f237';}
	#adminmenu #menu-posts-location div.wp-menu-image::before {content:'\f231';}
	#adminmenu #menu-users div.wp-menu-image::before {content:'\f307';}
	#adminmenu #menu-posts-project div.wp-menu-image::before {content:'\f308';}
	#adminmenu #menu-media div.wp-menu-image::before {content:'\f306';}
	#adminmenu #menu-posts div.wp-menu-image::before {content:'\f488';}
	#adminmenu #toplevel_page_edit-post_type-acf-field-group div.wp-menu-image::before {content:'\f511';}
	#adminmenu #menu-dashboard div.wp-menu-image::before {content:'\f154';}
	#adminmenu #menu-settings div.wp-menu-image::before {content:'\f111';}
	#adminmenu #collapse-button div::after {content:'\f341';}
	#adminmenu #menu-posts-locations div.wp-menu-image::before {content:'\f231';}
	#adminmenu #menu-posts-people div.wp-menu-image::before {content:'\f338';}
	#adminmenu #toplevel_page_social-contact div.wp-menu-image::before {content:'\f237';}
	</style>
	<?php
}
add_action( 'admin_head', 'dfree_replace_admin_menu_icons_css' );


// Hide Patterns and Media tabs in block editor inserter
remove_theme_support( 'core-block-patterns' );
add_filter( 'should_load_remote_block_patterns', '__return_false' );

add_action( 'enqueue_block_editor_assets', function() {
	wp_add_inline_script( 'wp-blocks', "
		wp.domReady(function() {
			var observer = new MutationObserver(function() {
				document.querySelectorAll('[role=\"tab\"]').forEach(function(tab) {
					var text = tab.textContent.trim();
					if (text === 'Patterns' || text === 'Media') {
						tab.style.display = 'none';
					}
				});
			});
			observer.observe(document.body, { childList: true, subtree: true });
		});
	" );
} );


/**
 * Custom Login Page
 */
function dfree_login_css() {
	wp_enqueue_style( 'dfree-login-css', get_template_directory_uri() . '/dist/css/login.css', false );
}

function dfree_login_url() {
	return home_url();
}

function dfree_login_title() {
	return get_option( 'blogname' );
}
add_action( 'login_enqueue_scripts', 'dfree_login_css', 10 );
add_filter( 'login_headerurl', 'dfree_login_url' );
add_filter( 'login_headertext', 'dfree_login_title' );
