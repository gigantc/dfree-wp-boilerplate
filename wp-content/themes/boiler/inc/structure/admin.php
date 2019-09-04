<?php
/**
 * Admin dashboard
 *
 * 
 * 
 *
 * 
 */
 // Remove unnecessary dashboard from from backend because its annoying as shit
function lawfirm_remove_dashboard_meta() {
  remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
}
add_action( 'admin_init', 'lawfirm_remove_dashboard_meta' );



//Custom sidebars for different user roles
//*
//*
//*
// superadmin - can edit anything (must be in the superadmin array below)
// admin can see most things beside development menus
// editor or below can only see posts, job postings and events
function lawfirm_remove_menu(){
    // provide a list of usernames that can edit anything - superadmin accounts
    $superadmins = array( 
        'superadmin', 
        'admin'
    );
    // get the current user
    $current_user = wp_get_current_user();
   
    if( !in_array( $current_user->user_login, $superadmins ) && $current_user->user_level > 7){
        // remove if not a superadmin
        remove_menu_page( 'edit.php?post_type=acf-field-group' );// Custom Fields
        remove_menu_page( 'cptui_main_menu' );//Custom Post Types
        remove_menu_page( 'edit-comments.php' );//Comments
        remove_menu_page( 'plugins.php' );//Plugins
        remove_menu_page( 'index.php' );//Dashboard
        remove_menu_page( 'options-general.php' );//Settings

    } elseif($current_user->user_level < 8) {
        // remove if less than a normal admin (Editor and below)
        remove_menu_page( 'index.php' ); //Dashboard
        remove_menu_page( 'edit.php?post_type=acf-field-group' );// Custom Fields
        remove_menu_page( 'cptui_main_menu' );//Custom Post Types
        remove_menu_page( 'edit-comments.php' );//Comments
        remove_menu_page( 'themes.php' );//Appearance
        remove_menu_page( 'plugins.php' );//Plugins
        remove_menu_page( 'tools.php' );//Tools
        remove_menu_page( 'options-general.php' );//Settings
        remove_menu_page( 'upload.php' );//media
        remove_menu_page( 'edit.php?post_type=project' );// Projects
        remove_menu_page( 'edit.php?post_type=page' );// Pages
        remove_menu_page( 'edit.php?post_type=location' );// Projects
        remove_menu_page( 'wpseo_dashboard' );//Yoast
    } else {
  
    }
}
add_action( 'admin_menu', 'lawfirm_remove_menu', 999 );

// User Level 0 converts to Subscriber
// User Level 1 converts to Contributor
// User Level 2 converts to Author
// User Level 3 converts to Editor
// User Level 4 converts to Editor
// User Level 5 converts to Editor
// User Level 6 converts to Editor
// User Level 7 converts to Editor
// User Level 8 converts to Administrator
// User Level 9 converts to Administrator
// User Level 10 converts to Administrator 



//admin sidebar icons
function replace_admin_menu_icons_css() {
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
add_action( 'admin_head', 'replace_admin_menu_icons_css' );


// changes POSTS in admin menu
function lawfirm_change_post_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'News';
    $submenu['edit.php'][5][0] = 'News Posts';
    $submenu['edit.php'][10][0] = 'Add News Post';
    $submenu['edit.php'][16][0] = 'News Post Tags';
    echo '';
}
add_action( 'admin_menu', 'lawfirm_change_post_label' );





/**
 * Login and User accounts
 *
 * 
 * 
 *
 * 
 */

/**
 * Redirects the user to the custom "Forgot your password?" page instead of
 * wp-login.php?action=lostpassword.
 */
function redirect_to_custom_lostpassword() {
    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        if ( is_user_logged_in() ) {
            $this->redirect_logged_in_user();
            exit;
        }
        wp_redirect( home_url( 'reset-password' ) );
        exit;
    }
}
//add_action( 'login_form_lostpassword', 'redirect_to_custom_lostpassword' );


/**
 * Redirects the user to the custom login page page instead of wp-login.php
 * if using WP Cerber you should do it through the plugin - not here
 */
function redirect_login_page() {
    $login_page  = home_url( '/login/' );
    $page_viewed = basename($_SERVER['REQUEST_URI']);
 
    if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
}
//add_action('init','redirect_login_page');


/**
 * Redirects the user to a custom login failed page
 * 
 */
function login_failed() {
    $login_page  = home_url( '/login/' );
    wp_redirect( $login_page . '?login=failed' );
    exit;
}
//add_action( 'wp_login_failed', 'login_failed' );


//send user to a registration sucessful page upon user creation
//same page as when a user hits reset password
function verify_username_password( $user, $username, $password ) {
    $login_page  = home_url( '/registration-success' );
    if( $username == "" || $password == "" ) {
        wp_redirect( $login_page );
        exit;
    }
}
//add_filter( 'authenticate', 'verify_username_password', 1, 3);


//rediercts to login page when user logs out
function logout_page() {
    $login_page  = home_url( '/login/' );
    wp_redirect( $login_page . "?login=false" );
    exit;
}
//add_action('wp_logout','logout_page');


//restrict users from the admin panel
//add_action( 'init', 'blockusers_init' );
function blockusers_init() {
    if ( is_admin() && ! current_user_can( 'administrator' ) && 
       ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        wp_redirect( home_url() );
        exit;
    }
}


/**
 * Custom Login Page
 */
// point to /css/login.css
function lawfirm_login_css() {
  wp_enqueue_style( 'lawfirm_login_css', get_template_directory_uri() . '/css/login.css', false );
}

// changing the logo link from wordpress.org to your site
function lawfirm_login_url() {
  return home_url();
}

// changing the alt text on the logo to show your site name
function lawfirm_login_title() {
  return get_option( 'blogname' );
}
add_action( 'login_enqueue_scripts', 'lawfirm_login_css', 10 );
add_filter( 'login_headerurl', 'lawfirm_login_url' );
add_filter( 'login_headertext', 'lawfirm_login_title' );

?>