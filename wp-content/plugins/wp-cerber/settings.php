<?php
/*
	Copyright (C) 2015-19 CERBER TECH INC., https://cerber.tech
	Copyright (C) 2015-19 CERBER TECH INC., https://wpcerber.com

    Licenced under the GNU GPL.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/*

*========================================================================*
|                                                                        |
|	       ATTENTION!  Do not change or edit this file!                  |
|                                                                        |
*========================================================================*

*/



// If this file is called directly, abort executing.
if ( ! defined( 'WPINC' ) ) { exit; }

define('CERBER_SETTINGS','cerber_settings');
define('CERBER_OPT','cerber-main');
define('CERBER_OPT_H','cerber-hardening');
define('CERBER_OPT_U','cerber-users');
define('CERBER_OPT_A','cerber-antispam');
define('CERBER_OPT_C','cerber-recaptcha');
define('CERBER_OPT_N','cerber-notifications');
define('CERBER_OPT_T','cerber-traffic');
define('CERBER_OPT_S','cerber-scanner');
define('CERBER_OPT_E','cerber-schedule');
define('CERBER_OPT_P','cerber-policies');
define('CERBER_OPT_SL','cerber-nexus-slave');
define('CERBER_OPT_MA','cerber-nexus_master');

function cerber_get_setting_id( $tab = null ) {
	$id = ( ! $tab ) ? crb_array_get( $_GET, 'tab' ) : $tab;
	if ( ! $id ) {
		$id = cerber_get_wp_option_id();
	}
	if ( ! $id ) {
		$id = crb_admin_get_page();
	}
	// Some tab names doesn't match WP setting names
    // tab => settings id
	$map = array(
		'scan_settings'    => 'scanner', // define('CERBER_OPT_S','cerber-scanner');
		'scan_schedule'    => 'schedule', // define('CERBER_OPT_E','cerber-schedule');
		'scan_policy'      => 'policies',
		'ti_settings'      => 'traffic',
		'captcha'          => 'recaptcha',
		'cerber-recaptcha' => 'antispam',
		'global_policies'  => 'users',

		'cerber-nexus' => 'nexus-slave',
		'nexus_slave'  => 'nexus-slave',
	);

	if ( isset( $map[ $id ] ) ) {
		return $map[ $id ];
	}

	return $id;
}

/**
 * Works when updating WP options
 *
 * @return bool|string
 */
function cerber_get_wp_option_id( $option_page = null ) {

	if ( ! $option_page ) {
		$option_page = crb_array_get( $_POST, 'option_page' );
	}
	if ( $option_page && ( 0 === strpos( $option_page, 'cerberus-' ) ) ) {
		return substr( $option_page, 9 ); // 8 = length of 'cerberus-'
	}

	return false;
}

function cerber_settings_config( $args = array() ) {
	if ( $args && ! is_array( $args ) ) {
		return false;
	}

	// WP setting is: 'cerber-'.$screen_id
	$screens = array(
		'main'          => array( 'boot', 'liloa', 'stspec', 'proactive', 'custom', 'citadel', 'activity', 'prefs' ),
		'users'         => array( 'us' ),
		'hardening'     => array( 'hwp', 'rapi' ),
		'notifications' => array( 'notify', 'pushit', 'reports' ),
		'traffic'       => array( 'tmain', 'tierrs', 'tlog' ),
		'scanner'       => array( 'smain' ),
		'schedule'      => array( 's1', 's2' ),
		'policies'      => array( 'scanpls', 'scanrecover', 'scanexcl' ),
		'antispam'      => array( 'antibot', 'antibot_more', 'commproc' ),
		'recaptcha'     => array( 'recap' ),
		'nexus-slave'   => array( 'slave_settings' ),
		'nexus_master'  => array( 'master_settings' ),
	);

	// Pushbullet devices
	$pb_set = array();
	if ( cerber_is_admin_page( false, array( 'tab' => 'notifications' ) ) ) {
		$pb_set = cerber_pb_get_devices();
		if ( is_array( $pb_set ) ) {
			if ( ! empty( $pb_set ) ) {
				$pb_set = array( 'all' => __( 'All connected devices', 'wp-cerber' ) ) + $pb_set;
			}
			else {
				$pb_set = array( 'N' => __( 'No devices found', 'wp-cerber' ) );
			}
		}
		else {
			$pb_set = array( 'N' => __( 'Not available', 'wp-cerber' ) );
		}
	}

	// Descriptions
	if ( ! cerber_is_permalink_enabled() ) {
		$custom = '<span style="color:#DF0000;">' . __( 'Please enable Permalinks to use this feature. Set Permalink Settings to something other than Default.', 'wp-cerber' ) . '</span>';
	}
	else {
		$custom = __( 'Be careful about enabling these options.', 'wp-cerber' ) . ' ' . __( 'If you forget your Custom login URL, you will be unable to log in.', 'wp-cerber' );
	}

	$no_wcl = __( 'These restrictions do not apply to IP addresses in the White IP Access List', 'wp-cerber' );

	$sections = array(
		'boot'      => array(
			'name'   => __( 'Plugin initialization', 'wp-cerber' ),
			'fields' => array(
				'boot-mode' => array(
					'title' => __( 'Load security engine', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'Legacy mode', 'wp-cerber' ),
						__( 'Standard mode', 'wp-cerber' )
					)
				),
			),
		),
		'liloa'     => array(
			'name'   => __( 'Limit login attempts', 'wp-cerber' ),
			'fields' => array(
				'attempts'   => array(
					'title' => __( 'Attempts', 'wp-cerber' ),
					'type'  => 'attempts',
				),
				'lockout'    => array(
					'title' => __( 'Lockout duration', 'wp-cerber' ),
					'label' => __( 'minutes', 'wp-cerber' ),
					'size'  => 3,
				),
				'aggressive' => array(
					'title' => __( 'Aggressive lockout', 'wp-cerber' ),
					'type'  => 'aggressive',
				),
				'limitwhite' => array(
					'title' => __( 'Use White IP Access List', 'wp-cerber' ),
					'label' => __( 'Apply limit login rules to IP addresses in the White IP Access List', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'proactive' => array(
			'name' => __( 'Proactive security rules', 'wp-cerber' ),
			'desc' => __( 'Make your protection smarter!', 'wp-cerber' ),
			'fields' => array(
				'subnet'     => array(
					'title' => __( 'Block subnet', 'wp-cerber' ),
					'label' => __( 'Always block entire subnet Class C of intruders IP', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'nonusers'   => array(
					'title' => __( 'Non-existing users', 'wp-cerber' ),
					'label' => __( 'Immediately block IP when attempting to log in with a non-existing username', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'noredirect' => array(
					'title' => __( 'Disable dashboard redirection', 'wp-cerber' ),
					'label' => __( 'Disable automatic redirection to the login page when /wp-admin/ is requested by an unauthorized request', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'wplogin'    => array(
					'title' => __( 'Request wp-login.php', 'wp-cerber' ),
					'label' => __( 'Immediately block IP after any request to wp-login.php', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'page404'    => array(
					'title' => __( 'Display 404 page', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'Use 404 template from the active theme', 'wp-cerber' ),
						__( 'Display simple 404 page', 'wp-cerber' )
					)
				),

			),
		),
		'custom'    => array(
			'name'   => __( 'Custom login page', 'wp-cerber' ),
			'desc' => $custom,
			'fields' => array(
				'loginpath' => array(
					'title'     => __( 'Custom login URL', 'wp-cerber' ),
					'label'     => __( 'must not overlap with the existing pages or posts slug', 'wp-cerber' ),
					'label_pos' => 'below',
					'attr'      => array( 'title' => __( 'Custom login URL may contain Latin alphanumeric characters, dashes and underscores only', 'wp-cerber' ) ),
					'size'      => 30,
					'pattern'   => '[a-zA-Z0-9\-_]{1,100}',
				),
				'loginnowp' => array(
					'title' => __( 'Disable wp-login.php', 'wp-cerber' ),
					'label' => __( 'Block direct access to wp-login.php and return HTTP 404 Not Found Error', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'stspec'    => array(
			'name'   => __( 'Site-specific settings', 'wp-cerber' ),
			'fields' => array(
				'proxy'      => array(
					'title' => __( 'Site connection', 'wp-cerber' ),
					'label' => __( 'My site is behind a reverse proxy', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'cookiepref' => array(
					'title'       => __( 'Prefix for plugin cookies', 'wp-cerber' ),
					'attr'        => array( 'title' => __( 'Prefix may contain only Latin alphanumeric characters and underscores', 'wp-cerber' ) ),
					'placeholder' => 'Latin alphanumeric characters or underscores',
					'size'        => 24,
					'pattern'     => '[a-zA-Z0-9_]{1,24}',
				),
			),
		),
		'citadel'   => array(
			'name' => __( 'Citadel mode', 'wp-cerber' ),
			'desc' => __( 'In the Citadel mode nobody is able to log in except IPs from the White IP Access List. Active user sessions will not be affected.', 'wp-cerber' ),
			'fields' => array(
				'citadel'    => array(
					'title' => __( 'Threshold', 'wp-cerber' ),
					'type'  => 'citadel',
				),
				'ciduration' => array(
					'title' => __( 'Duration', 'wp-cerber' ),
					'label' => __( 'minutes', 'wp-cerber' ),
					'size'  => 3
				),
				'cinotify'   => array(
					'title' => __( 'Notifications', 'wp-cerber' ),
					'type'  => 'checkbox',
					'label' => __( 'Send notification to admin email', 'wp-cerber' ) .
					           ' [ <a href="' . cerber_admin_link( crb_admin_get_tab(), array(
							'page'            => crb_admin_get_page(),
							'cerber_admin_do' => 'testnotify',
							'type'            => 'citadel',
						), true ) . '">' . __( 'Click to send test', 'wp-cerber' ) . '</a> ]'
				),
			),
		),
		'activity'  => array(
			'name'   => __( 'Activity', 'wp-cerber' ),
			'fields' => array(
				'keeplog'     => array(
					'title' => __( 'Keep records for', 'wp-cerber' ),
					'label' => __( 'days', 'wp-cerber' ),
					//'label'  => __( 'days, not logged in visitors', 'wp-cerber' ),
					'size'  => 3
				),
				/*'keeplog_auth' => array(
					'title' => __( 'Keep records for', 'wp-cerber' ),
					'label'  => __( 'days, logged in users', 'wp-cerber' ),
					'size'  => 3
				),*/
				'cerberlab'   => array(
					'title' => __( 'Cerber Lab connection', 'wp-cerber' ),
					'label' => __( 'Send malicious IP addresses to the Cerber Lab', 'wp-cerber' ) . ' <a target="_blank" href="http://wpcerber.com/cerber-laboratory/">Know more</a>',
					'type'  => 'checkbox',
				),
				'cerberproto' => array(
					'title' => __( 'Cerber Lab protocol', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						'HTTP',
						'HTTPS'
					)
				),
				'usefile'     => array(
					'title' => __( 'Use file', 'wp-cerber' ),
					'label' => __( 'Write failed login attempts to the file', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'prefs'     => array(
			'name'   => __( 'Preferences', 'wp-cerber' ),
			'fields' => array(
				'ip_extra'   => array(
					'title' => __( 'Drill down IP', 'wp-cerber' ),
					'label' => __( 'Retrieve extra WHOIS information for IP', 'wp-cerber' ) . ' <a href="' . cerber_admin_link( 'help' ) . '">Know more</a>',
					'type'  => 'checkbox',
				),
				'dateformat' => array(
					'title'     => __( 'Date format', 'wp-cerber' ),
					'label'     => sprintf( __( 'if empty, the default format %s will be used', 'wp-cerber' ), '<b>' . cerber_date( time() ) . '</b>' ) . ' <a target="_blank" href="http://wpcerber.com/date-format-setting/">Know more</a>',
					'label_pos' => 'below',
					'size'      => 16,
				),
				'admin_lang' => array(
					'title' => __( 'Use English for admin interface', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),

		'hwp'  => array(
			'name'   => __( 'Hardening WordPress', 'wp-cerber' ),
			'desc'   => $no_wcl,
			'fields' => array(
				'stopenum' => array(
					'title' => __( 'Stop user enumeration', 'wp-cerber' ),
					'label' => __( 'Block access to user pages like /?author=n', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'adminphp' => array(
					'title' => __( 'Protect admin scripts', 'wp-cerber' ),
					'label' => __( 'Block unauthorized access to load-scripts.php and load-styles.php', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'phpnoupl' => array(
					'title' => __( 'Disable PHP in uploads', 'wp-cerber' ),
					'label' => __( 'Block execution of PHP scripts in the WordPress media folder', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'nophperr' => array(
					'title' => __( 'Disable PHP error displaying', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'xmlrpc'   => array(
					'title' => __( 'Disable XML-RPC', 'wp-cerber' ),
					'label' => __( 'Block access to the XML-RPC server (including Pingbacks and Trackbacks)', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'nofeeds'  => array(
					'title' => __( 'Disable feeds', 'wp-cerber' ),
					'label' => __( 'Block access to the RSS, Atom and RDF feeds', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'rapi' => array(
			'name'   => __( 'Access to WordPress REST API', 'wp-cerber' ),
			'desc'   => $no_wcl,
			'fields' => array(
				'norestuser' => array(
					'title' => __( 'Stop user enumeration', 'wp-cerber' ),
					'label' => __( "Block access to users' data via REST API", 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'norest'     => array(
					'title' => __( 'Disable REST API', 'wp-cerber' ),
					'label' => __( 'Block access to WordPress REST API except any of the following', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'restauth'   => array(
					'title' => __( 'Logged in users', 'wp-cerber' ),
					'label' => __( 'Allow REST API for logged in users', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'restroles'  => array(
					'title' => __( 'Allow REST API for these roles', 'wp-cerber' ),
					'type'  => 'role_select',
				),
				'restwhite'  => array(
					'title'     => __( 'Allow these namespaces', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => "\n",
					'list'      => true,
					'label'     => __( 'Specify REST API namespaces to be allowed if REST API is disabled. One string per line.', 'wp-cerber' ) . ' <a target="_blank" href="https://wpcerber.com/restrict-access-to-wordpress-rest-api/">Read more</a>',
				),

			),
		),

		'us' => array(
			//'name'   => __( 'User related settings', 'wp-cerber' ),
			//'info'   => __( 'User related settings', 'wp-cerber' ),
			'fields' => array(
				'authonly'      => array(
					'title'   => __( 'Authorized users only', 'wp-cerber' ),
					'label'   => __( 'Only registered and logged in website users have access to the website', 'wp-cerber' ),
					'doclink' => 'https://wpcerber.com/only-logged-in-wordpress-users/',
					'type'    => 'checkbox',
					'default' => 0,
				),
				'authonlyacl'   => array(
					'title'   => __( 'Use White IP Access List', 'wp-cerber' ),
					'label'   => __( 'Do not apply this policy to IP addresses in the White IP Access List', 'wp-cerber' ),
					'type'    => 'checkbox',
					'default' => 0,
					'enabler' => array( 'authonly' ),
				),
				'authonlymsg'   => array(
					'title'       => __( 'User Message', 'wp-cerber' ),
					'placeholder' => 'An optional login form message',
					'type'        => 'textarea',
					//'filter'      => 'strip_tags',
					'default'     => __( 'Only registered and logged in users are allowed to view this website', 'wp-cerber' ),
					'enabler'     => array( 'authonly' ),
					'class'       => ''
				),
				'authonlyredir' => array(
					'title'       => __( 'Redirect to URL', 'wp-cerber' ),
					//'label'       => __( 'if empty, visitors are redirected to the login page', 'wp-cerber' )
					'placeholder' => 'http://',
					'type'        => 'url',
					'default'     => '',
					'maxlength'   => 1000,
					'enabler'     => array( 'authonly' ),
				),
				'reglimit'      => array(
					'title'   => __( 'Registration limit', 'wp-cerber' ),
					'type'    => 'reglimit',
					'default' => array( 3, 60 ),
					'pro'     => 1
				),
				'emrule' => array(
					'title' => __( 'Restrict email addresses', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'No restrictions', 'wp-cerber' ),
						__( 'Deny all email addresses that match the following', 'wp-cerber' ),
						__( 'Permit only email addresses that match the following', 'wp-cerber' ),
					)
				),
				'emlist'    => array(
					'title' => '',
					'label'     => __( 'Specify email addresses, wildcards or REGEX patterns. Use comma to separate items.', 'wp-cerber' ). ' ' . __( 'To specify a REGEX pattern wrap a pattern in two forward slashes.', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => ',',
					'list'      => true,
					'default'   => array(),
				),
				'prohibited'    => array(
					'title'     => __( 'Prohibited usernames', 'wp-cerber' ),
					'label'     => __( 'Usernames from this list are not allowed to log in or register. Any IP address, have tried to use any of these usernames, will be immediately blocked. Use comma to separate logins.', 'wp-cerber' ) . ' ' . __( 'To specify a REGEX pattern wrap a pattern in two forward slashes.', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => ',',
					'list'      => true,
					'default'   => array(),
				),
				'auth_expire' => array(
					'title'   => __( 'User session expiration time', 'wp-cerber' ),
					'label'   => __( 'in minutes (leave empty to use default WP value)', 'wp-cerber' ),
					'default' => '',
					'size'    => 6,
					'type'    => 'number',
				),
				'usersort'      => array(
					'title'   => __( 'Sort users in dashboard', 'wp-cerber' ),
					'label'   => __( 'by date of registration', 'wp-cerber' ),
					'default' => '',
					'type'    => 'checkbox',
				),
			)
		),

		'notify'  => array(
			'name'   => __( 'Email notifications', 'wp-cerber' ),
			'fields' => array(
				'notify'         => array(
					'title' => __( 'Lockout notifications', 'wp-cerber' ),
					'type'  => 'notify',
				),
				'email'          => array(
					'title'       => __( 'Email Address', 'wp-cerber' ),
					'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
					'delimiter'   => ',',
					'list'        => true,
					'maxlength'   => 1000,
					'label'       => sprintf( __( 'if empty, the admin email %s will be used', 'wp-cerber' ), '<b>' . get_site_option( 'admin_email' ) . '</b>' )
				),
				'emailrate'      => array(
					'title' => __( 'Notification limit', 'wp-cerber' ),
					'label' => __( 'notification letters allowed per hour (0 means unlimited)', 'wp-cerber' ),
					'size'  => 3
				),
				'notify-new-ver' => array(
					'title' => __( 'New version is available', 'wp-cerber' ),
					'type'  => 'checkbox'
				),
			),
		),
		'pushit'  => array(
			'name'   => __( 'Push notifications', 'wp-cerber' ),
			'fields' => array(
				'pbtoken'  => array(
					'title' => __( 'Pushbullet access token', 'wp-cerber' ),
				),
				'pbdevice' => array(
					'title' => __( 'Pushbullet device', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => $pb_set
				),
			),
		),
		'reports' => array(
			'name'   => __( 'Weekly reports', 'wp-cerber' ),
			'fields' => array(
				'enable-report' => array(
					'title' => __( 'Enable reporting', 'wp-cerber' ),
					'type'  => 'checkbox'
				),
				'wreports'      => array(
					'title' => __( 'Send reports on', 'wp-cerber' ),
					'type'  => 'reptime',
				),
				'email-report'  => array(
					'title'       => __( 'Email Address', 'wp-cerber' ),
					'label'       => __( 'if empty, email from notification settings will be used', 'wp-cerber' ),
					'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
					'delimiter'   => ',',
					'list'        => true,
					'maxlength'   => 1000,
				),
			),
		),

		'tmain'  => array(
			'name'   => __( 'Traffic Inspection', 'wp-cerber' ),
			'fields' => array(
				'tienabled' => array(
					'title' => __( 'Enable traffic inspection', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'Disabled', 'wp-cerber' ),
						__( 'Maximum compatibility', 'wp-cerber' ),
						__( 'Maximum security', 'wp-cerber' )
					),
				),
				'tiipwhite' => array(
					'title' => __( 'Use White IP Access List', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'tiwhite'   => array(
					'title'     => __( 'Request whitelist', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => "\n",
					'list'      => true,
					'label'     => __( 'Enter a request URI to exclude the request from inspection. One item per line.', 'wp-cerber' ) . ' ' . __( 'To specify a REGEX pattern, enclose a whole line in two braces.', 'wp-cerber' ) . ' <a target="_blank" href="https://wpcerber.com/wordpress-probing-for-vulnerable-php-code/">Know more</a>',
				),
			),
		),
		'tierrs' => array(
			'name'   => __( 'Erroneous Request Shielding', 'wp-cerber' ),
			'fields' => array(
				'tierrmon'    => array(
					'title' => __( 'Enable error shielding', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'Disabled', 'wp-cerber' ),
						__( 'Maximum compatibility', 'wp-cerber' ),
						__( 'Maximum security', 'wp-cerber' )
					)
				),
				'tierrnoauth' => array(
					'title' => __( 'Ignore logged in users', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'tlog'   => array(
			'name'   => __( 'Logging', 'wp-cerber' ),
			'fields' => array(
				'timode'      => array(
					'title' => __( 'Logging mode', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						__( 'Logging disabled', 'wp-cerber' ),
						__( 'Smart', 'wp-cerber' ),
						__( 'All traffic', 'wp-cerber' )
					),
				),
				'tinocrabs'   => array(
					'title' => __( 'Ignore crawlers', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'tifields'    => array(
					'title' => __( 'Save request fields', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'timask'      => array(
					'title'       => __( 'Mask these form fields', 'wp-cerber' ),
					'maxlength'   => 1000,
					'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
					'delimiter'   => ',',
					'list'        => true,
				),
				'tihdrs'      => array(
					'title' => __( 'Save request headers', 'wp-cerber' ),
					'label' => __( '', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'tisenv'      => array(
					'title' => __( 'Save $_SERVER', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'ticandy'     => array(
					'title' => __( 'Save request cookies', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'tiphperr'    => array(
					'title' => __( 'Save software errors', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'tithreshold' => array(
					'title' => __( 'Page generation time threshold', 'wp-cerber' ),
					'label' => __( 'milliseconds', 'wp-cerber' ),
					'size'  => 4,
				),
				'tikeeprec'   => array(
					'title' => __( 'Keep records for', 'wp-cerber' ),
					'label' => __( 'days', 'wp-cerber' ),
					'size'  => 4,
				),
			),
		),

		'smain' => array(
			'name'   => __( 'Scanner settings', 'wp-cerber' ),
			'fields' => array(
				'scan_cpt' => array(
					'title' => __( 'Custom signatures', 'wp-cerber' ),
					'type'  => 'textarea',
					'delimiter'   => "\n",
					'list'        => true,
					'label' => __( 'Specify custom PHP code signatures. One item per line. To specify a REGEX pattern, enclose a whole line in two braces.', 'wp-cerber' ) . ' <a target="_blank" href="https://wpcerber.com/malware-scanner-settings/">Read more</a>'
				),
				'scan_uext' => array(
					'title' => __( 'Unwanted file extensions', 'wp-cerber' ),
					'type'   => 'textarea',
					'delimiter'   => ",",
					'list'        => true,
					'label' => __( 'Specify file extensions to search for. Full scan only. Use comma to separate items.', 'wp-cerber' )
				),
				'scan_exclude' => array(
					'title' => __( 'Directories to exclude', 'wp-cerber' ),
					'type'   => 'textarea',
					'delimiter'   => "\n",
					'list'        => true,
					'label' => __( 'Specify directories to exclude from scanning. Use absolute paths. One item per line.', 'wp-cerber' )
				),
				'scan_inew' => array(
					'title' => __( 'Monitor new files', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_imod' => array(
					'title' => __( 'Monitor modified files', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_tmp' => array(
					'title' => __( 'Scan temporary directory', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_sess' => array(
					'title' => __( 'Scan session directory', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_debug' => array(
					'title' => __( 'Enable diagnostic logging', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_qcleanup' => array(
					'title' => __( 'Delete quarantined files after', 'wp-cerber' ),
					'type'    => 'text',
					'label'   => __( 'days', 'wp-cerber' ),
					'size'    => 3
				),

			),
		),

		's1' => array(
			'name'   => __( 'Automated recurring scan schedule', 'wp-cerber' ),
			'fields' => array(
				'scan_aquick' => array(
					'title' => __( 'Launch Quick Scan', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => cerber_get_qs(),
				),
				'scan_afull'  => array(
					'title'   => __( 'Launch Full Scan', 'wp-cerber' ),
					'type'    => 'timepicker',
					'enabled' => 'once a day at',
				),
			),
		),
		's2' => array(
			'name'   => __( 'Scan results reporting', 'wp-cerber' ),
			'desc'   => 'Configure what issues to include in email reports and the condition for sending the report.' . ' <a href="https://wpcerber.com/automated-recurring-malware-scans/" target="_blank">' . __( 'Know more', 'wp-cerber' ) . '</a>',
			'fields' => array(
				'scan_reinc'   => array(
					'title' => __( 'Report an issue if any of the following is true', 'wp-cerber' ),
					'type'  => 'checkbox_set',
					'set'   => array(
						           1 => __( 'Low severity', 'wp-cerber' ),
						           2 => __( 'Medium severity', 'wp-cerber' ),
						           3 => __( 'High severity', 'wp-cerber' )
					           ) + cerber_get_issue_label( array( CERBER_IMD, CERBER_UXT, 50, 51, CERBER_VULN ) ),
				),
				'scan_relimit' => array(
					'title' => __( 'Send email report', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array(
						1 => __( 'After every scan', 'wp-cerber' ),
						3 => __( 'If any changes in scan results occurred', 'wp-cerber' ),
						5 => __( 'If new issues found', 'wp-cerber' ),
					)
				),
				'scan_isize'   => array(
					'title' => __( 'Include file sizes', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_ierrors' => array(
					'title' => __( 'Include scan errors', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'email-scan'   => array(
					'title'       => __( 'Email Address', 'wp-cerber' ),
					'label'       => __( 'if empty, email from notification settings will be used', 'wp-cerber' ),
					'placeholder' => __( 'Use comma to specify multiple values', 'wp-cerber' ),
					'delimiter'   => ',',
					'list'        => true,
					'maxlength'   => 1000,
				),
			),
		),

		'scanpls'     => array(
			'name'   => __( 'Automatic cleanup of malware and suspicious files', 'wp-cerber' ),
			'desc'   => 'These policies are automatically enforced at the end of every scheduled scan based on its results. All affected files are moved to the quarantine.',
			'fields' => array(
				'scan_delunatt'  => array(
					'title' => __( 'Delete unattended files', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_delupl'    => array(
					'title' => __( 'Files in the uploads folder', 'wp-cerber' ),
					'type'  => 'checkbox_set',
					'set'   => array(
						1 => __( 'Low severity', 'wp-cerber' ),
						2 => __( 'Medium severity', 'wp-cerber' ),
						3 => __( 'High severity', 'wp-cerber' )
					),
				),
				'scan_delunwant' => array(
					'title' => __( 'Files with unwanted extensions', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'scanrecover' => array(
			'name'   => __( 'Automatic recovery of modified and infected files', 'wp-cerber' ),
			'fields' => array(
				'scan_recover_wp' => array(
					'title' => __( 'Recover WordPress files', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_recover_pl' => array(
					'title' => __( 'Recover plugins files', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			),
		),
		'scanexcl'    => array(
			'name'   => __( 'Exclusions', 'wp-cerber' ),
			'desc'   => __( 'These files will never be deleted during automatic cleanup.', 'wp-cerber' ),
			'fields' => array(
				'scan_nodeltemp' => array(
					'title' => __( 'Files in the temporary directory', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_nodelsess' => array(
					'title' => __( 'Files in the sessions directory', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'scan_delexdir'  => array(
					'title'     => __( 'Files in these directories', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => "\n",
					'list'      => true,
					'label'     => __( 'Use absolute paths. One item per line.', 'wp-cerber' )
				),
				'scan_delexext'  => array(
					'title'     => __( 'Files with these extensions', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => ",",
					'list'      => true,
					'label'     => __( 'Use comma to separate items.', 'wp-cerber' )
				),
			),
		),


		'antibot'      => array(
			'name'   => __( 'Cerber antispam engine', 'wp-cerber' ),
			'fields' => array(
				'botscomm' => array(
					'title' => __( 'Comment form', 'wp-cerber' ),
					'label' => __( 'Protect comment form with bot detection engine', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'botsreg'  => array(
					'title' => __( 'Registration form', 'wp-cerber' ),
					'label' => __( 'Protect registration form with bot detection engine', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'botsany'  => array(
					'title' => __( 'Other forms', 'wp-cerber' ),
					'label' => __( 'Protect all forms on the website with bot detection engine', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			)
		),
		'antibot_more' => array(
			'name'   => __( 'Adjust antispam engine', 'wp-cerber' ),
			'fields' => array(
				'botssafe'   => array(
					'title' => __( 'Safe mode', 'wp-cerber' ),
					'label' => __( 'Use less restrictive policies (allow AJAX)', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'botsnoauth' => array(
					'title' => __( 'Logged in users', 'wp-cerber' ),
					'label' => __( 'Disable bot detection engine for logged in users', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'botswhite'  => array(
					'title'     => __( 'Query whitelist', 'wp-cerber' ),
					'label'     => __( 'Enter a part of query string or query path to exclude a request from inspection by the engine. One item per line.', 'wp-cerber' ),
					'type'      => 'textarea',
					'delimiter' => "\n",
					'list'      => true,
				),
			)
		),
		'commproc'     => array(
			'name'   => __( 'Comment processing', 'wp-cerber' ),
			'fields' => array(
				'spamcomm'   => array(
					'title' => __( 'If a spam comment detected', 'wp-cerber' ),
					'type'  => 'select',
					'set'   => array( __( 'Deny it completely', 'wp-cerber' ), __( 'Mark it as spam', 'wp-cerber' ) )
				),
				'trashafter' => array(
					'title'   => __( 'Trash spam comments', 'wp-cerber' ),
					'type'    => 'text',
					'enabled' => __( 'Move spam comments to trash after' ),
					'label'   => __( 'days', 'wp-cerber' ),
					'size'    => 3
				),
			)
		),

		'recap' => array(
			'name' => __( 'reCAPTCHA settings', 'wp-cerber' ),
			'desc' => __( 'Before you can start using reCAPTCHA, you have to obtain Site key and Secret key on the Google website', 'wp-cerber' ) . ' <a href="https://wpcerber.com/how-to-setup-recaptcha/">' . __( 'Know more', 'wp-cerber' ) . '</a>',
			'fields' => array(
				'sitekey'       => array(
					'title' => __( 'Site key', 'wp-cerber' ),
					'type'  => 'text',
				),
				'secretkey'     => array(
					'title' => __( 'Secret key', 'wp-cerber' ),
					'type'  => 'text',
				),
				'invirecap'     => array(
					'title' => __( 'Invisible reCAPTCHA', 'wp-cerber' ),
					'label' => __( 'Enable invisible reCAPTCHA', 'wp-cerber' ) . ' ' . __( '(do not enable it unless you get and enter the Site and Secret keys for the invisible version)', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapreg'      => array(
					'title' => __( 'Registration form', 'wp-cerber' ),
					'label' => __( 'Enable reCAPTCHA for WordPress registration form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapwooreg'   => array(
					'title' => '',
					'label' => __( 'Enable reCAPTCHA for WooCommerce registration form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recaplost'     => array(
					'title' => __( 'Lost password form', 'wp-cerber' ),
					'label' => __( 'Enable reCAPTCHA for WordPress lost password form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapwoolost'  => array(
					'title' => '',
					'label' => __( 'Enable reCAPTCHA for WooCommerce lost password form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recaplogin'    => array(
					'title' => __( 'Login form', 'wp-cerber' ),
					'label' => __( 'Enable reCAPTCHA for WordPress login form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapwoologin' => array(
					'title' => '',
					'label' => __( 'Enable reCAPTCHA for WooCommerce login form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapcom'      => array(
					'title' => __( 'Antispam', 'wp-cerber' ),
					'label' => __( 'Enable reCAPTCHA for WordPress comment form', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recapcomauth'  => array(
					'title' => '',
					'label' => __( 'Disable reCAPTCHA for logged in users', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'recaplimit'    => array(
					'title' => __( 'Limit attempts', 'wp-cerber' ),
					'label' => __( 'Lock out IP address for %s minutes after %s failed attempts within %s minutes', 'wp-cerber' ),
					'type'  => 'limitz',
				),
			)
		),

		'master_settings' => array(
			'name'   => __( 'Master settings', 'wp-cerber' ),
			//'info'   => __( 'Master settings', 'wp-cerber' ),
			'fields' => array(
				/*('master_cache'    => array(
					'title' => __( 'Cache Time', 'wp-cerber' ),
					'type'  => 'text',
				),*/
				'master_tolist'  => array(
					'title' => __( 'Return to the website list', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'master_swshow'  => array(
					'title' => __( 'Show "Switched to" notification', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'master_at_site' => array(
					'title' => __( 'Add @ site to the page title', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'master_locale'  => array(
					'title' => __( 'Use master language', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				/*
				'master_dt'      => array(
					'title' => __( 'Use master datetime format', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
				'master_tz'      => array(
					'title' => __( 'Use master timezone', 'wp-cerber' ),
					'type'  => 'checkbox',
				),*/
				'master_diag'    => array(
					'title' => __( 'Enable diagnostic logging', 'wp-cerber' ),
					'type'  => 'checkbox',
				),
			)
		),
		'slave_settings'  => array(
			'name'   => '',
			//'info'   => __( 'User related settings', 'wp-cerber' ),
			'fields' => array(
				'slave_ips'    => array(
					'title' => __( 'Limit access by IP address', 'wp-cerber' ),
					//'placeholder' => 'The IP address of the master',
					'type'  => 'text',
					//'pro'   => 1
				),
				'slave_access' => array(
					'title'     => __( 'Access to this website', 'wp-cerber' ),
					'type'      => 'select',
					'set'       => array(
						2 => __( 'Full access mode', 'wp-cerber' ),
						4 => __( 'Read-only mode', 'wp-cerber' ),
						8 => __( 'Disabled', 'wp-cerber' )
					),
					'label_pos' => 'below',
					'default'   => 2,
				),
				'slave_diag'   => array(
					'title'   => __( 'Enable diagnostic logging', 'wp-cerber' ),
					'default' => 0,
					'type'    => 'checkbox',
				),
			)
		)
	);

	if ( ! lab_lab() ) {
		$sections['slave_settings']['fields']['slave_access']['label'] = '<a href="https://wpcerber.com/pro/" target="_blank">' . __( 'The full access mode requires the PRO version of WP Cerber', 'wp-cerber' ) . '</a>';
	}

	if ( $screen_id = crb_array_get( $args, 'screen_id' ) ) {
		if ( empty( $screens[ $screen_id ] ) ) {
			return false;
		}

		return array_intersect_key( $sections, array_flip( $screens[ $screen_id ] ) );
	}

	if ( $setting = crb_array_get( $args, 'setting' ) ) {
		foreach ( $sections as $s ) {
			if ( isset( $s['fields'][ $setting ] ) ) {
				return $s['fields'][ $setting ];
			}
		}

		return false;

	}

	return $sections;
}

/**
 * Configure WP Settings API stuff for a given admin page
 *
 * @since 7.9.7
 *
 * @param $screen_id string
 * @param $sections array
 */
function cerber_wp_settings_setup( $screen_id, $sections = array() ) {
	if ( ! $sections && ! $sections = cerber_settings_config( array( 'screen_id' => $screen_id ) ) ) {
		return;
	}
	$option = 'cerber-' . $screen_id;
	register_setting( 'cerberus-' . $screen_id, $option );
	global $tmp;
	foreach ( $sections as $section_id => $section_config ) {
		//add_settings_section( $section, $section_config['name'], 'cerber_sapi_section', $option );
		$tmp[ $section_id ] = crb_array_get( $section_config, 'desc' );
		add_settings_section( $section_id, crb_array_get( $section_config, 'name', '' ), function ( $sec ) {
			global $tmp;
			if ( $tmp[ $sec['id'] ] ) {
				echo $tmp[ $sec['id'] ];
			}
		}, $option );
		foreach ( $section_config['fields'] as $field => $config ) {
			if ( isset( $config['pro'] ) && !lab_lab() ) {
				continue;
			}
			$config['setting'] = $field;
			$config['group']   = $screen_id;

			if ( ! isset( $config['class'] ) ) {
				$config['class'] = '';
			}

			if ( ! isset( $config['type'] ) ) {
				$config['type'] = 'text';
			}

			if ( $config['type'] == 'hidden' ) {
				$config['class'] .= ' crb-display-none';
			}

			// Enabling/disabling conditional inputs
			$enabled  = true;
			if ( isset( $config['enabler'][0] ) ) {
				$enab_val = crb_get_settings( $config['enabler'][0] );
				if ( isset( $config['enabler'][1] ) ) {
					if ( $enab_val != $config['enabler'][1] ) {
						$enabled = false;
					}
				}
				else {
					if ( empty( $enab_val ) ) {
						$enabled = false;
					}
				}
			}
			if ( ! $enabled ) {
				$config['class'] .= ' crb-disable-this';
			}

			add_settings_field( $field, $config['title'], 'cerber_field_show', $option, $section_id, $config );
		}
	}
}

/**
 * A set of Cerber setting (WP options)
 *
 * @return array
 */

function cerber_get_setting_list() {
	return array( CERBER_SETTINGS, CERBER_OPT, CERBER_OPT_H, CERBER_OPT_U, CERBER_OPT_A, CERBER_OPT_C, CERBER_OPT_N, CERBER_OPT_T, CERBER_OPT_S, CERBER_OPT_E, CERBER_OPT_P, CERBER_OPT_SL, CERBER_OPT_MA );
}

/*
	WP Settings API & admin stuff
*/
add_action( 'admin_init', 'cerber_admin_init' );
function cerber_admin_init() {
	global $crb_assets_url, $crb_ajax_loader;
	$crb_assets_url  = cerber_plugin_dir_url() . 'assets/';
	$crb_ajax_loader = $crb_assets_url . 'ajax-loader.gif';

	if ( ! cerber_is_admin_page()
	     && ! strpos( $_SERVER['REQUEST_URI'], '/options.php' )
	     && ! nexus_is_valid_request() ) {
		return;
	}

	cerber_wp_settings_setup( cerber_get_setting_id() );

	//add_settings_field('hashauthor',__('Hide author usernames','wp-cerber'),'cerberus_field_show',CERBER_OPT_H,'hwp',array('group'=>$tab,'option'=>'hashauthor','type'=>'checkbox','label'=>__('Replace author username with hash for author pages and URLs','wp-cerber')));
	//add_settings_field('cleanhead',__('Clean up HEAD','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'hwp',array('group'=>$tab,'option'=>'cleanhead','type'=>'checkbox','label'=>__('Remove generator and version tags from HEAD section','wp-cerber')));
	//add_settings_field('ping',__('Disable Pingback','wp-cerber'),'cerberus_field_show','cerber-'.$tab,'hwp',array('group'=>$tab,'option'=>'ping','type'=>'checkbox','label'=>__('Block access to ping functional','wp-cerber')));

}

/*
 * Display a settings form on an admin page
 *
 */
function cerber_show_settings_form( $group = null ) {
	$sf = '';
	$action = '';
	if ( is_multisite() ) {
		$action = '';  // Settings API doesn't work in multisite. Post data will be handled in the cerber_ms_update()
	}
	else {
		if ( nexus_is_valid_request() ) {
			//$action = cerber_admin_link();
		}
		else {
			$action = 'options.php'; // Standard way
		}
	}

	?>
    <div class="crb-admin-form">
        <form id="crb-form-<?php echo $group; ?>" class="crb-settings" method="post" action="<?php echo $action; ?>">

			<?php

			cerber_nonce_field( 'control', true );

			settings_fields( 'cerberus-' . $group ); // option group name, the same as used in register_setting().
			do_settings_sections( 'cerber-' . $group ); // the same as used in add_settings_section()	$page

			echo '<div style="padding-left: 220px">';

			if ( $group == 'hardening' ) {
				echo '<p><a href="' . cerber_admin_link( 'traffic', array( 'filter_wp_type' => 520 ) ) . '">View REST API requests</a> | <a href="' . cerber_admin_link( 'activity', array( 'filter_activity' => 70 ) ) . '">View denied REST API requests</a></p>';
			}

			//submit_button();
			echo crb_admin_submit_button();
			echo '</div>';

			?>

        </form>
    </div>
	<?php
}

/**
 * Generates HTML for a single input field on the settings page.
 * Prepares values to display.
 *
 * @param $args
 */
function cerber_field_show( $args ) {

	//$settings = get_site_option( 'cerber-' . $args['group'] );
	$settings = crb_get_settings();
	if ( is_array( $settings ) ) {
		array_walk_recursive( $settings, 'esc_html' );
	}
	$pre      = '';
	$value    = '';
	$atts     = '';

	if ( isset( $args['value'] ) ) {
		$value = esc_html( $args['value'] ); // 7.9.8
	}

	//$label = empty( $args['label'] ) ? '' : $args['label'];
	$label = crb_array_get( $args, 'label', '' );

	if ( ! empty( $args['doclink'] ) ) {
		$label .= ' &nbsp; <a target="_blank" href="' . $args['doclink'] . '">Read more</a>';
	}

	$placeholder = esc_attr( crb_array_get( $args, 'placeholder', '' ) );
	if ( $placeholder ) {
		$atts .= ' placeholder="' . $placeholder . '" ';
	}

	if ( isset( $args['disabled'] ) ) {
		$atts .= ' disabled="disabled" ';
	}

	if ( isset( $args['required'] ) ) {
		$atts .= ' required="required" ';
	}

	if ( isset( $args['setting'] ) ) {
		if ( ! $value && isset( $settings[ $args['setting'] ] ) ) {
			$value = $settings[ $args['setting'] ];
		}

		if ( ( $args['setting'] == 'loginnowp' || $args['setting'] == 'loginpath' ) && ! cerber_is_permalink_enabled() ) {
			$atts .= ' disabled="disabled" ';
		}
		if ( $args['setting'] == 'loginpath' ) {
			$pre   = cerber_get_home_url() . '/';
			$value = urldecode( $value );
		}
	}

	if ( isset( $args['list'] ) ) {
		$value = cerber_array2text( $value, $args['delimiter'] );
	}

	$name = 'cerber-' . $args['group'] . '[' . $args['setting'] . ']';

	if ( isset( $args['id'] ) ) {
		$id = $args['id'];
	}
	else {
		$id = 'crb-input-' . $args['setting'];
	}

	$class = crb_array_get( $args, 'class', '' );

	$data     = '';
	if ( isset( $args['enabler'][0] ) ) {
		$data .= ' data-enabler="crb-input-' . $args['enabler'][0] . '" ';
	}
	if ( isset( $args['enabler'][1] ) ) {
		$data .= ' data-enabler_value="' . $args['enabler'][1] . '" ';
	}

	switch ( $args['type'] ) {

		case 'limitz':
			$s1 = $args['group'] . '-period';
			$s2 = $args['group'] . '-number';
			$s3 = $args['group'] . '-within';

			$html = sprintf( $label,
				'<input type="text" name="cerber-' . $args['group'] . '[' . $s1 . ']" value="' . $settings[ $s1 ] . '" size="3" maxlength="3" />',
				'<input type="text" name="cerber-' . $args['group'] . '[' . $s2 . ']" value="' . $settings[ $s2 ] . '" size="3" maxlength="3" />',
				'<input type="text" name="cerber-' . $args['group'] . '[' . $s3 . ']" value="' . $settings[ $s3 ] . '" size="3" maxlength="3" />' );
			break;

		case 'attempts':
			$html = sprintf( __( '%s allowed retries in %s minutes', 'wp-cerber' ),
				'<input type="text" id="attempts" name="cerber-' . $args['group'] . '[attempts]" value="' . $settings['attempts'] . '" size="3" maxlength="3" />',
				'<input type="text" id="period" name="cerber-' . $args['group'] . '[period]" value="' . $settings['period'] . '" size="3" maxlength="3" />' );
			break;
		case 'reglimit':
			$html = sprintf( __( '%s allowed registrations in %s minutes from one IP', 'wp-cerber' ),
				'<input type="text" id="reglimit-num" name="cerber-' . $args['group'] . '[reglimit_num]" value="' . $settings['reglimit_num'] . '" size="3" maxlength="3" />',
				'<input type="text" id="reglimit-min" name="cerber-' . $args['group'] . '[reglimit_min]" value="' . $settings['reglimit_min'] . '" size="4" maxlength="4" />' );
			break;
		case 'aggressive':
			$html = sprintf( __( 'Increase lockout duration to %s hours after %s lockouts in the last %s hours', 'wp-cerber' ),
				'<input type="text" id="agperiod" name="cerber-' . $args['group'] . '[agperiod]" value="' . $settings['agperiod'] . '" size="3" maxlength="3" />',
				'<input type="text" id="aglocks" name="cerber-' . $args['group'] . '[aglocks]" value="' . $settings['aglocks'] . '" size="3" maxlength="3" />',
				'<input type="text" id="aglast" name="cerber-' . $args['group'] . '[aglast]" value="' . $settings['aglast'] . '" size="3" maxlength="3" />' );
			break;
		case 'notify':
			$html = '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $args['setting'] . '" name="cerber-' . $args['group'] . '[' . $args['setting'] . ']" value="1" ' . checked( 1, $value, false ) . $atts . ' /><span class="crb-slider round"></span></label>'
			        . __( 'Notify admin if the number of active lockouts above', 'wp-cerber' ) .
			        ' <input type="text" id="above" name="cerber-' . $args['group'] . '[above]" value="' . $settings['above'] . '" size="3" maxlength="3" />' .
			        ' [  <a href="' . cerber_admin_link( crb_admin_get_tab(), array(
					'page'            => crb_admin_get_page(),
					'cerber_admin_do' => 'testnotify',
					'type'            => 'lockout',
				), true ) . '">' . __( 'Click to send test', 'wp-cerber' ) . '</a> ]';
			break;
		case 'citadel':
			$html = sprintf( __( 'Enable after %s failed login attempts in last %s minutes', 'wp-cerber' ),
				'<input type="text" id="cilimit" name="cerber-' . $args['group'] . '[cilimit]" value="' . $settings['cilimit'] . '" size="3" maxlength="3" />',
				'<input type="text" id="ciperiod" name="cerber-' . $args['group'] . '[ciperiod]" value="' . $settings['ciperiod'] . '" size="3" maxlength="3" />' );
			break;
		case 'checkbox':
			$html = '<div style="display: table-cell;"><label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $id . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . $atts . ' /><span class="crb-slider round"></span></label></div>';
			$html .= '<div style="display: table-cell;"><label for="' . $args['setting'] . '">' . $label . '</label></div><i ' . $data . '></i>';
			break;
		case 'textarea':
			$html = '<textarea class="large-text code" id="' . $id . '" name="' . $name . '" ' . $atts . $data . '>' . $value . '</textarea>';
			$html .= '<br/><label class="crb-below" for="' . $args['setting'] . '">' . $label . '</label>';
			break;
		case 'select':
			$html = cerber_select( $name, $args['set'], $value, $class, $id, '', $placeholder );
			//$html .= '<span class="">' . $label . '</span>';
			$html .= '<br/><label class="crb-below">' . $label . '</label>';
			break;
		case 'role_select':
			$html = cerber_role_select( $name . '[]', $value, '', true, '', '100%' );
			break;
		case 'checkbox_set':
			$html = '<div class="crb-checkbox_set" style="line-height: 2em;">';
			foreach ( $args['set'] as $key => $item ) {
				$v    = ( ! empty( $value[ $key ] ) ) ? $value[ $key ] : 0;
				$html .= '<input type="checkbox" value="1" name="' . $name . '[' . $key . ']" ' . checked( 1, $v, false ) . $atts . '/>' . $item . '<br />';
			}
			$html .= '</div>';
			break;
		case 'reptime':
			$html = cerber_time_select( $args, $settings );
			break;
		case 'timepicker':
			$html = '<input class="crb-tpicker" type="text" size="7" id="' . $args['setting'] . '" name="' . $name . '" value="' . $value . '"' . $atts . '/>';
			$html .= ' <label for="' . $args['setting'] . '">' . $label . '</label>';
			break;
		case 'hidden':
			$html = '<input type="hidden" id="' . $args['setting'] . '" class="crb-hidden-field" name="' . $name . '" value="' . $value . '" />';
			break;
		case 'text':
		default:
			/*$type = 'text';
			if ( in_array( $args['type'], array( 'url' ) ) ) {
				$type = $args['type'];
			}*/

			$type = crb_array_get( $args, 'type', 'text' );
			if ( ! in_array( $type, array( 'url', 'number' ) ) ) {
				$type = 'text';
			}

			$size      = '';
			$maxlength = '';
			$class     = 'crb-wide';
			if ( isset( $args['size'] ) ) {
				//$size = ' size="' . $args['size'] . '" maxlength="' . $args['size'] . '" ';
				$size  = ' size="' . $args['size'] . '"';
				$class = '';
			}
			if ( isset( $args['maxlength'] ) ) {
				$maxlength = ' maxlength="' . $args['maxlength'] . '" ';
			}
            elseif ( isset( $args['size'] ) ) {
				$maxlength = ' maxlength="' . $args['size'] . '" ';
			}

			if ( isset( $args['pattern'] ) ) {
				$atts .= ' pattern="' . $args['pattern'] . '"';
			}

			if ( isset( $args['attr'] ) ) {
				foreach ( $args['attr'] as $at_name => $at_value ) {
					$atts .= ' ' . $at_name . ' ="' . $at_value . '" ';
				}
			}
			else {
				if ( isset( $args['title'] ) ) {
					$atts .= ' title="' . $args['title'] . '"';
				}
			}

			$html = $pre . '<input type="' . $type . '" id="' . $args['setting'] . '" name="' . $name . '" value="' . $value . '"' . $atts . ' class="' . $class . '" ' . $size . $maxlength . $atts . $data . ' />';

			if ( ! $size || crb_array_get( $args, 'label_pos' ) == 'below' ) {
				$label = '<br/><label class="crb-below" for="' . $args['setting'] . '">' . $label . '</label>';
			}
			else {
				$label = ' <label for="' . $args['setting'] . '">' . $label . '</label>';
			}

			$html .= $label;
			break;
	}

	if ( ! empty( $args['enabled'] ) ) {
		$name  = 'cerber-' . $args['group'] . '[' . $args['setting'] . '-enabled]';
		$value = 0;
		if ( isset( $settings[ $args['setting'] . '-enabled' ] ) ) {
			$value = $settings[ $args['setting'] . '-enabled' ];
		}
		$checkbox = '<label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $args['setting'] . '-enabled" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . ' /><span class="crb-slider round"></span></label>' . $args['enabled'];
		$html     = $checkbox . ' ' . $html;
	}

	echo $html . "\n";
}

function cerber_checkbox( $name, $value, $label = '', $id = '', $atts = '' ) {
	if ( ! $id ) {
		$id = 'crb-input-' . $name;
	}

	return '<div style="display: table-cell;"><label class="crb-switch"><input class="screen-reader-text" type="checkbox" id="' . $id . '" name="' . $name . '" value="1" ' . checked( 1, $value, false ) . $atts . ' /><span class="crb-slider round"></span></label></div>
	<div style="display: table-cell;"><label for="' . $id . '">' . $label . '</label></div>';
}

/**
 * @param $name string HTML input name
 * @param $list array   List of elements
 * @param null $selected Index of selected element
 * @param string $class HTML class
 * @param string $id HTML ID
 * @param string $multiple
 *
 * @return string
 */
function cerber_select( $name, $list, $selected = null, $class = '', $id = '', $multiple = '', $placeholder = '', $data = array(), $atts = '' ) {
	$options = array();
	foreach ( $list as $key => $value ) {
		$s         = ( $selected == (string) $key ) ? 'selected' : '';
		$options[] = '<option value="' . $key . '" ' . $s . '>' . htmlspecialchars( $value ) . '</option>';
	}
	$p      = ( $placeholder ) ? ' data-placeholder="' . $placeholder . '" placeholder="' . $placeholder . '" ' : '';
	$m      = ( $multiple ) ? ' multiple="multiple" ' : '';
	$the_id = ( $id ) ? ' id="' . $id . '" ' : '';
	$d      = '';
	if ( $data ) {
		foreach ( $data as $att => $val ) {
			$d .= ' data-' . $att . '="' . $val . '"';
		}
	}

	return ' <select name="' . $name . '" ' . $the_id . ' class="crb-select ' . $class . '" ' . $m . $p . $d . ' ' . $atts . '>' . implode( "\n", $options ) . '</select>';
}

function cerber_role_select( $name = 'cerber-roles', $selected = array(), $class = '', $multiple = '', $placeholder = '', $width = '100%' ) {

	if ( ! is_array( $selected ) ) {
		$selected = array( $selected );
	}
	if ( ! $placeholder ) {
		$placeholder = __( 'Select one or more roles', 'wp-cerber' );
	}
	$roles = wp_roles();
	$options = array();
	foreach ( $roles->get_names() as $key => $title ) {
		$s         = ( in_array( $key, $selected ) ) ? 'selected' : '';
		$options[] = '<option value="' . $key . '" ' . $s . '>' . $title . '</option>';
	}

	$m = ( $multiple ) ? 'multiple="multiple"' : '';

	// Setting width via class is not working
	$style = '';
	if ( $width ) {
		//$style = 'max-width: ' . $width.'; min-width:500px;';
		$style = 'width: ' . $width.';';
	}

	return ' <select style="' . $style . '" name="' . $name . '" class="crb-select2 ' . $class . '" ' . $m . ' data-placeholder="' . $placeholder . '" data-allow-clear="true">' . implode( "\n", $options ) . '</select>';
}

function cerber_time_select($args, $settings){

    // Week
	$php_week = array(
		__( 'Sunday' ),
		__( 'Monday' ),
		__( 'Tuesday' ),
		__( 'Wednesday' ),
		__( 'Thursday' ),
		__( 'Friday' ),
		__( 'Saturday' ),
	);
	$field = $args['setting'].'-day';
	if (isset($settings[ $field ])) {
	    $selected = $settings[ $field ];
    }
    else {
	    $selected = '';
    }
	$ret = cerber_select( 'cerber-' . $args['group'] . '[' . $field . ']', $php_week, $selected );
	$ret .= ' &nbsp; ' . _x( 'at', 'preposition of time like: at 11:00', 'wp-cerber' ) . ' &nbsp; ';

	// Hours
	$hours = array();
	for ( $i = 0; $i <= 23; $i ++ ) {
		$hours[] = str_pad( $i, 2, '0', STR_PAD_LEFT ) . ':00';
	}
	$field = $args['setting'] . '-time';
	if ( isset( $settings[ $field ] ) ) {
		$selected = $settings[ $field ];
	}
	else {
		$selected = '';
	}
	$ret .= cerber_select( 'cerber-' . $args['group'] . '[' . $field . ']', $hours, $selected );

	return $ret.' &nbsp; [ <a href="'.cerber_admin_link( crb_admin_get_tab(), array(
			'page'             => crb_admin_get_page(),
			'cerber_admin_do'  => 'testnotify',
			'type'             => 'report',
		), true ).'">'.__('Click to send now','wp-cerber').'</a> ]';
}

/*
	Sanitizing users input for Main Settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT, function ($new, $old, $option) {

	$ret = cerber_set_boot_mode( $new['boot-mode'], $old['boot-mode'] );
	if ( is_wp_error( $ret ) ) {
		cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . $ret->get_error_message() );
		cerber_admin_notice( __( 'Plugin initialization mode has not been changed', 'wp-cerber' ) );
		$new['boot-mode'] = $old['boot-mode'];
	}

	$new['attempts'] = absint( $new['attempts'] );
	$new['period']   = absint( $new['period'] );
	$new['lockout']  = absint( $new['lockout'] );

	$new['agperiod'] = absint( $new['agperiod'] );
	$new['aglocks']  = absint( $new['aglocks'] );
	$new['aglast']   = absint( $new['aglast'] );

	if ( cerber_is_permalink_enabled() ) {
		$new['loginpath'] = urlencode( str_replace( '/', '', $new['loginpath'] ) );
		$new['loginpath'] = sanitize_text_field($new['loginpath']);
		if ( $new['loginpath'] && $new['loginpath'] != $old['loginpath'] ) {
			$href = cerber_get_home_url() . '/' . $new['loginpath'] . '/';
			$url  = urldecode( $href );
			$msg = array();
			$msg_e = array();
			$msg[]  = __( 'Attention! You have changed the login URL! The new login URL is', 'wp-cerber' ) . ': <a href="' . $href . '">' . $url . '</a>';
			$msg_e[]  = __( 'Attention! You have changed the login URL! The new login URL is', 'wp-cerber' ) . ': ' . $url;
			$msg[]  = __( 'If you use a caching plugin, you have to add your new login URL to the list of pages not to cache.', 'wp-cerber' );
			$msg_e[]  = __( 'If you use a caching plugin, you have to add your new login URL to the list of pages not to cache.', 'wp-cerber' );
			cerber_admin_notice( $msg );
			cerber_send_email( 'newlurl', $msg_e );
		}
	} else {
		$new['loginpath'] = '';
		$new['loginnowp'] = 0;
	}

	$new['ciduration'] = absint( $new['ciduration'] );
	$new['cilimit']    = absint( $new['cilimit'] );
	$new['cilimit']    = $new['cilimit'] == 0 ? '' : $new['cilimit'];
	$new['ciperiod']   = absint( $new['ciperiod'] );
	$new['ciperiod']   = $new['ciperiod'] == 0 ? '' : $new['ciperiod'];
	if ( ! $new['cilimit'] ) {
		$new['ciperiod'] = '';
	}
	if ( ! $new['ciperiod'] ) {
		$new['cilimit'] = '';
	}

	if ( absint( $new['keeplog'] ) == 0 ) {
		$new['keeplog'] = '';
	}

	return $new;
}, 10, 3 );
/*
	Sanitizing/checking user input for User tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_U, function ($new, $old, $option) {

	$new['prohibited'] = cerber_text2array($new['prohibited'], ',', 'strtolower');
	$new['emlist'] = cerber_text2array($new['emlist'], ',', 'strtolower');

	$new['authonlymsg'] = strip_tags( $new['authonlymsg'] );

	return $new;
}, 10, 3 );
/*
	Sanitizing/checking user input for reCAPTCHA tab settings
*/
add_filter( 'pre_update_option_' . CERBER_OPT_A, function ( $new, $old, $option ) {
	if ( ! empty( $new['botswhite'] ) ) {
		$new['botswhite'] = cerber_text2array( $new['botswhite'], "\n" );
	}

	if ( empty( $new['botsany'] ) && empty( $new['botscomm'] ) && empty( $new['botsreg'] ) ) {
		update_site_option( 'cerber-antibot', '' );
	}

	return $new;
}, 10, 3 );
/*
	Sanitizing/checking user input for reCAPTCHA tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_C, function ($new, $old, $option) {
	global $wp_cerber;
	// Check ability to make external HTTP requests
	if ($wp_cerber && !empty($new['sitekey']) && !empty($new['secretkey'])) {
		if (!$goo = $wp_cerber->reCaptchaRequest('1')) {
			$labels = cerber_get_labels( 'activity' );
			cerber_admin_notice( __( 'ERROR:', 'wp-cerber' ) . ' ' . $labels[42] );
			cerber_log( 42 );
		}
	}

	$new['recaptcha-period'] = absint( $new['recaptcha-period'] );
	$new['recaptcha-number'] = absint( $new['recaptcha-number'] );
	$new['recaptcha-within'] = absint( $new['recaptcha-within'] );

	return $new;
}, 10, 3 );
/*
	Sanitizing/checking user input for Notifications tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_N, function ($new, $old, $option) {

	$emails = cerber_text2array( $new['email'], ',' );

	$new['email'] = array();
	foreach ( $emails as $item ) {
		if ( is_email( $item ) ) {
			$new['email'][] = $item;
		}
		else {
			cerber_admin_notice( __( '<strong>ERROR</strong>: please enter a valid email address.' ) );
		}
	}

	$emails = cerber_text2array( $new['email-report'], ',' );

	$new['email-report'] = array();
	foreach ( $emails as $item ) {
		if ( is_email( $item ) ) {
			$new['email-report'][] = $item;
		}
		else {
			cerber_admin_notice( __( '<strong>ERROR</strong>: please enter a valid email address.' ) );
		}
	}


	$new['emailrate'] = absint( $new['emailrate'] );

	// set 'default' value for the device setting if a new token has been entered
	if ( $new['pbtoken'] != $old['pbtoken'] ) {
		$list = cerber_pb_get_devices($new['pbtoken']);
		if (is_array($list) && !empty($list)) $new['pbdevice'] = 'all';
		else $new['pbdevice'] = '';
	}

	return $new;
}, 10, 3 );

/*
    Sanitizing/checking user input for Hardening tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_H, function ($new, $old, $option) {

	$new['restwhite'] = cerber_text2array( $new['restwhite'], "\n", function ( $v ) {
		$v = preg_replace( '/[^a-z_\-\d\/]/i', '', $v );

		return trim( $v, '/' );
	} );

	if ( empty( $new['adminphp'] ) ) {
		$new['adminphp'] = 0;
	}

	if ( ! isset( $old['adminphp'] ) ) {
		$old['adminphp'] = '';
	}
	//if ( $new['adminphp'] != $old['adminphp'] ) {
		$result = cerber_htaccess_sync( 'main', $new );
		if ( is_wp_error( $result ) ) {
			$new['adminphp'] = $old['adminphp'];
			cerber_admin_notice( $result->get_error_message() );
		}
	//}

	if ( ! isset( $old['phpnoupl'] ) ) {
		$old['phpnoupl'] = '';
	}
	//if ( $new['phpnoupl'] != $old['phpnoupl'] ) {
		$result = cerber_htaccess_sync( 'media', $new );
		if ( is_wp_error( $result ) ) {
			$new['phpnoupl'] = $old['phpnoupl'];
			cerber_admin_notice( $result->get_error_message() );
		}
	//}

	return $new;
}, 10, 3 );
/*
    Sanitizing/checking user input for Traffic Inspector tab settings
*/
add_filter( 'pre_update_option_'.CERBER_OPT_T, function ($new, $old, $option) {

	$new['tiwhite'] = cerber_text2array( $new['tiwhite'], "\n" );
	foreach ( $new['tiwhite'] as $item ) {
		if ( strrpos( $item, '?' ) ) {
			cerber_admin_notice( 'You may not specify the query string with a question mark: ' . htmlspecialchars( $item ) );
		}
		if ( strrpos( $item, '://' ) ) {
			cerber_admin_notice( 'You may not specify the full URL: ' . htmlspecialchars( $item ) );
		}
	}

	$new['timask'] = cerber_text2array( $new['timask'], "," );
	if ( $new['tithreshold'] ) {
		$new['tithreshold'] = absint( $new['tithreshold'] );
	}
	$new['tikeeprec'] = absint($new['tikeeprec']);
	if ( $new['tikeeprec'] < 1 ) {
		$new['tikeeprec'] = $old['tikeeprec'];
		cerber_admin_notice( 'You may not set <b>Keep records for</b> to 0 days. To completely disable logging set <b>Logging mode</b> to Logging disabled.' );
	}

	return $new;
}, 10, 3 );

/*
    Sanitizing/checking user input for Security Scanner settings
*/
add_filter( 'pre_update_option_' . CERBER_OPT_S, function ( $new, $old, $option ) {

	$new['scan_exclude'] = cerber_normal_dirs( $new['scan_exclude'] );

	$new['scan_cpt']  = cerber_text2array( $new['scan_cpt'], "\n" );
	$new['scan_uext'] = cerber_text2array( $new['scan_uext'], ",", function ( $ext ) {
		$ext = strtolower( trim( $ext, '. *' ) );
		if ( $ext == 'php' || $ext == 'js' || $ext == 'css' || $ext == 'txt' ) {
			$ext = '';
		}

		return $ext;
	} );

	return $new;
}, 10, 3 );

/*
    Sanitizing/checking user input for Scanner Schedule settings
*/
add_filter( 'pre_update_option_' . CERBER_OPT_E, function ( $new, $old, $option ) {
	$new['scan_aquick']        = absint( $new['scan_aquick'] );
	$new['scan_afull-enabled'] = ( empty( $new['scan_afull-enabled'] ) ) ? 0 : 1;

    $sec = cerber_sec_from_time( $new['scan_afull'] );
	if ( ! $sec || ! ( $sec >= 0 && $sec <= 86400 ) ) {
		$new['scan_afull'] = '01:00';
	}

    $emails = cerber_text2array( $new['email-scan'], ',' );
	$new['email-scan'] = array();
	foreach ( $emails as $item ) {
		if ( is_email( $item ) ) {
			$new['email-scan'][] = $item;
		}
		else {
			cerber_admin_notice( __( '<strong>ERROR</strong>: please enter a valid email address.' ) );
		}
	}

	if ( lab_lab() ) {
		if ( cerber_cloud_sync( $new ) ) {
			cerber_admin_message( __( 'The schedule has been updated', 'wp-cerber' ) );
		}
		else {
			cerber_admin_message( __( 'Unable to update the schedule', 'wp-cerber' ) );
		}
	}

	return $new;
}, 10, 3 );

add_filter( 'pre_update_option_' . CERBER_OPT_P, function ( $new, $old, $option ) {

	$new['scan_delexdir'] = cerber_normal_dirs($new['scan_delexdir']);

	$new['scan_delexext'] = cerber_text2array( $new['scan_delexext'], ",", function ( $ext ) {
		$ext = strtolower( trim( $ext, '. *' ) );

		return $ext;
	} );

	return $new;
}, 10, 3 );

function cerber_normal_dirs( $list = array() ) {
	if ( ! is_array( $list ) ) {
		$list = cerber_text2array( $list, "\n" );
	}
	$ready = array();

	foreach ( $list as $item ) {
		$item = rtrim( cerber_normal_path( $item ), '/\\' ) . DIRECTORY_SEPARATOR;
		if ( ! @is_dir( $item ) ) {
			$dir = cerber_get_abspath() . ltrim( $item, DIRECTORY_SEPARATOR );
			if ( ! @is_dir( $dir ) ) {
				cerber_admin_notice( 'Directory does not exist: ' . htmlspecialchars( $item ) );
				continue;
			}
			$item = $dir;
		}
		$ready[] = $item;
	}

	return $ready;
}

/**
 * Let's sanitize and normalize them all
 * @since 4.1
 *
 */
add_filter( 'pre_update_option', 'cerber_o_o_sanitizer', 10, 3 );
function cerber_o_o_sanitizer( $value, $option, $old_value ) {
	if ( in_array( $option, cerber_get_setting_list() ) ) {
		if ( is_array( $value ) ) {
			array_walk_recursive( $value, function ( &$element, $key ) {
				if ( ! is_array( $element ) ) {
					$element = sanitize_text_field( (string) $element );
				}
			} );
		}
		else {
			$value = sanitize_text_field( (string) $value );
		}
		$value = cerber_normalize( $value, $option );
	}

	return $value;
}

/**
 * Fill missed settings (array keys) with empty values
 * @since 5.8.2
 *
 * @param $values
 * @param $group
 *
 * @return array
 */
function cerber_normalize( $values, $group ) {
	$def = cerber_get_defaults();
	if ( isset( $def[ $group ] ) ) {
		$keys  = array_keys( $def[ $group ] );
		$empty = array_fill_keys( $keys, '' );
		$values   = array_merge( $empty, $values );
	}

	return $values;
}

/**
 * Convert an array to text string by using a given delimiter
 *
 * @param array $array
 * @param string $delimiter
 *
 * @return array|string
 */
function cerber_array2text( $array = array(), $delimiter = '') {
	if ( empty( $array ) ) {
		return '';
	}

	if ( is_array( $array ) ) {
	    if ($delimiter == ',') $delimiter .= ' ';
		$ret = implode( $delimiter , $array );
	}
	else {
		$ret = $array;
    }

    return $ret;
}

/**
 * Convert text to array by using a given element delimiter, remove empty and duplicate elements
 * Optionally a callback function may be applied to resulting array elements.
 *
 * @param string $text
 * @param string $delimiter
 * @param string $callback
 *
 * @return array|string
 */
function cerber_text2array( $text = '', $delimiter = '', $callback = '') {

	if ( empty( $text ) ) {
		return array();
	}

	if ( ! is_array( $text ) ) {
		$list = explode( $delimiter, $text );
	}
	else {
		$list = $text;
	}
	$list = array_map( 'trim', $list );

	if ( $callback ) {
		$list = array_map( $callback, $list );
	}

	$list = array_filter( $list );
	$list = array_unique( $list );

	return $list;
}

/*
 * Save settings on the multisite WP.
 * Process POST Form for settings screens.
 * Because Settings API doesn't work in multisite mode!
 *
 */
if (is_multisite())  {
    add_action('admin_init', 'cerber_ms_update');
}
function cerber_ms_update() {
	if ( !cerber_is_http_post() || ! isset( $_POST['action'] ) || $_POST['action'] != 'update' ) {
		return;
	}
	/*if ( ! isset( $_POST['option_page'] ) || false === strpos( $_POST['option_page'], 'cerberus-' ) ) {
		return;
	}*/

	if ( ! $wp_id = cerber_get_wp_option_id() ) {  // 7.9.7
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// See wp_nonce_field() in the settings_fields() function
	check_admin_referer($_POST['option_page'].'-options');

	//$opt_name = 'cerber-' . substr( $_POST['option_page'], 9 ); // 8 = length of 'cerberus-'

	$opt_name = 'cerber-' . $wp_id;

	$old = (array) get_site_option( $opt_name );
	$new = $_POST[ $opt_name ];
	$new = apply_filters( 'pre_update_option_' . $opt_name, $new, $old, $opt_name );
	update_site_option( $opt_name, $new );
}

/*
 * 	Default settings.
 *  Each setting field must have a default value!
 *
 */
function cerber_get_defaults() {
	$all_defaults = array(
		CERBER_OPT   => array(
			'boot-mode'   => 0,
			'attempts'   => 3,
			'period'     => 60,
			'lockout'    => 60,
			'agperiod'   => 24,
			'aglocks'    => 2,
			'aglast'     => 4,
			'limitwhite' => 0,

			'proxy' => 0,
			'cookiepref' => '',

			'subnet'     => 0,
			'nonusers'   => 0,
			'wplogin'    => 0,
			'noredirect' => 0,
			'page404'    => 1,

			'loginpath' => '',
			'loginnowp' => 0,

			'cilimit'    => 200,
			'ciperiod'   => 30,
			'ciduration' => 60,
			'cinotify'   => 1,

			'keeplog'     => 30,
			'ip_extra'    => 1,
			'cerberlab'   => 0,
			'cerberproto' => 0,
			'usefile'     => 0,
			'dateformat'  => '',
			'admin_lang'  => 0

		),
		CERBER_OPT_H => array(
			'stopenum'   => 1,
			'adminphp'   => 0,
			'phpnoupl'   => 0,
			'nophperr'   => 1,
			'xmlrpc'     => 0,
			'nofeeds'    => 0,
			'norestuser' => 1,
			'norest'     => 0,
			'restauth'   => 1,
			'restroles'  => array('administrator'),
			'restwhite'  => 'oembed',
			'hashauthor' => 0,
			'cleanhead'  => 1,
		),
		CERBER_OPT_U => array(
			'authonly'      => 0,
			'authonlyacl'   => 0,
			'authonlymsg'   => __( 'Only registered and logged in users are allowed to view this website', 'wp-cerber' ),
			'authonlyredir' => '',
			'reglimit_num'  => 3,
			'reglimit_min'  => 60,
			'emrule'        => 0,
			'emlist'        => array(),
			'prohibited'    => array(),
			'auth_expire'   => '',
			'usersort'      => '',
		),
		CERBER_OPT_A => array(
			'botscomm'   => 1,
			'botsreg'    => 0,
			'botsany'    => 0,
			'botssafe'   => 0,
			'botsnoauth' => 1,
			'botswhite'  => '',

			'spamcomm'           => 0,
			'trashafter'         => 7,
			'trashafter-enabled' => 0,
		),
		CERBER_OPT_C => array(
			'sitekey' => '',
			'secretkey' => '',
			'invirecap'  => 0,
			'recaplogin' => 0,
			'recaplost' => 0,
			'recapreg' => 0,
			'recapwoologin' => 0,
			'recapwoolost' => 0,
			'recapwooreg' => 0,
			'recapcom' => 0,
			'recapcomauth' => 0,
            'recaptcha-period' => 60,
			'recaptcha-number' => 3,
			'recaptcha-within' => 30,
		),
		CERBER_OPT_N => array(
			'notify'         => 1,
			'above'          => 3,
			'email'          => '',
			'emailrate'      => 12,
			'notify-new-ver' => '1',
			'pbtoken'        => '',
			'pbdevice'       => '',
			'wreports-day'   => '1', // workaround, see cerber_upgrade_settings()
			'wreports-time'  => 9,
			'email-report'   => '',
			'enable-report'  => '1',  // workaround, see cerber_upgrade_settings()
		),
		CERBER_OPT_T => array(
			'tienabled'   => '1',
			'tiipwhite'   => 0,
			'tiwhite'     => '',
			'tierrmon'    => '1',
			'tierrnoauth' => 0,
			'timode'      => '1',
			'tinocrabs'   => '1',
			'tifields'    => 0,
			'timask'      => '',
			'tihdrs'      => 0,
			'tisenv'      => 0,
			'ticandy'     => 0,
			'tiphperr'    => 0,
			'tithreshold' => '',
			'tikeeprec'   => 7,
		),
		CERBER_OPT_S => array(
			'scan_cpt'      => '',
			'scan_uext'     => '',
			'scan_exclude'  => '',
			'scan_inew'     => '1',
			'scan_imod'     => '1',
			'scan_tmp'      => '1',
			'scan_sess'     => '1',
			'scan_debug'    => 0,
			'scan_qcleanup' => '30',
		),
		CERBER_OPT_E => array(
			'scan_aquick'        => 0,
			'scan_afull'         => '0' . rand( 1, 5 ) . ':00',
			'scan_afull-enabled' => 0,
			'scan_reinc'         => array( 3 => 1, CERBER_VULN => 1, CERBER_IMD => 1, 50 => 1, 51 => 1 ),
			'scan_relimit'       => 3,
			'scan_isize'         => 0,
			'scan_ierrors'       => 0,
			'email-scan'         => ''
		),
		CERBER_OPT_P => array(
			'scan_delunatt'   => 0,
			'scan_delupl'     => array(),
			'scan_delunwant'  => 0,
			'scan_recover_wp' => 0,
			'scan_recover_pl' => 0,

			'scan_nodeltemp' => 0,
			'scan_nodelsess' => 0,
			'scan_delexdir'  => array(),
			'scan_delexext'  => array(),
		),
		CERBER_OPT_MA => array(
			'master_tolist'  => 1,
			'master_swshow'  => 1,
			'master_at_site' => 1,
			'master_locale'  => 0,
			'master_dt'      => 0,
			'master_tz'      => 0,
			'master_diag'    => 0,
		),
		CERBER_OPT_SL => array(
			'slave_ips'    => '',
			'slave_access' => 2,
			'slave_diag'   => 0,
		),
	);

	return $all_defaults;
}

/**
 * Upgrade plugin options
 *
 */
function cerber_upgrade_settings() {
	// @since 4.4, move fields to a new option
	if ( $main = get_site_option( CERBER_OPT ) ) {
		if ( ! empty( $main['email'] ) || ! empty( $main['emailrate'] ) ) {
			$new              = get_site_option( CERBER_OPT_N, array() );
			$new['email']     = $main['email'];
			$new['emailrate'] = $main['emailrate'];
			update_site_option( CERBER_OPT_N, $new );
			unset( $main['email'] );
			unset( $main['emailrate'] );
			update_site_option( CERBER_OPT, $main );
		}
	}
	// @since 7.5.4, move some fields CERBER_OPT_С => CERBER_OPT_A
	crb_move_fields( CERBER_OPT_C, CERBER_OPT_A, array(
		'botscomm',
		'botsreg',
		'botsany',
		'botssafe',
		'botsnoauth',
		'botswhite',
		'spamcomm',
		'trashafter'
	) );
	// @since 8.2
	crb_move_fields( CERBER_OPT, CERBER_OPT_N, array(
		'notify',
		'above',
	) );
	// @since 5.7
    // Upgrade plugin settings
	foreach ( cerber_get_defaults() as $option_name => $def_fields ) {
		$values = get_site_option( $option_name );
		if ( ! $values ) {
			$values = array();
		}
		// Add new settings (fields) with their default values
		foreach ( $def_fields as $field_name => $default ) {
			if ( ! isset( $values[ $field_name ] ) && $default !== 1) { // @since 5.7.2 TODO refactor $default !== 1 to more obvious
				$values[ $field_name ] = $default;
			}
		}

		// Remove non-existing/outdated fields, @since 7.5.7
		$values = array_intersect_key( $values, $def_fields );

		// Must be after all operations above
		$values = cerber_normalize($values, $option_name); // @since 5.8.2

		update_site_option( $option_name, $values );
	}
	// @since 7.9.4 Stop user enumeration for REST API
	if ( $h = get_site_option( CERBER_OPT_H ) ) {
		if ( $h['stopenum'] && ! isset( $h['norestuser'] ) ) {
			$h['norestuser'] = 1;
			update_site_option( CERBER_OPT_H, $h );
		}
	}

	if ( ! $key = get_site_option( '_cerberkey_' ) ) {
		$key = cerber_get_site_option( '_cerberkey_' );
	}
	if ( $key ) {
		if ( cerber_update_set( '_cerberkey_', $key ) ) {
			delete_site_option( '_cerberkey_' );
		}
	}
}

/**
 * @param string $from
 * @param string $to
 * @param array $fields
 *
 * @return bool
 */
function crb_move_fields( $from, $to, $fields ) {
	if ( ! $old = get_site_option( $from ) ) {
		return false;
	}
	$new = get_site_option( $to );
	if ( ! $new || ! is_array( $new ) ) {
		$new = array();
	}
	foreach ( $fields as $key ) {
		if ( isset( $old[ $key ] )
		     && ! isset( $new[ $key ] ) ) {
			$new[ $key ] = $old[ $key ]; // move old values
			unset( $old[ $key ] ); // clean up old values
		}
	}
	update_site_option( $from, $old );
	update_site_option( $to, $new );

	return true;
}

/*
 *
 * Right way to save Cerber settings outside of wp-admin settings page
 * @since 2.0
 *
 */
function cerber_save_settings( $options ) {
	foreach ( cerber_get_defaults() as $option_name => $fields ) {
		$filtered = array();
		foreach ( $fields as $field_name => $def ) {
			if ( isset( $options[ $field_name ] ) ) {
				$filtered[ $field_name ] = $options[ $field_name ];
			}
		}
		if ( ! empty( $filtered ) ) {
			$result = update_site_option( $option_name, $filtered );
		}
	}
}

/**
 *
 * @deprecated since 4.0 Use crb_get_settings() instead.
 *
 * @param string $option
 *
 * @return array|bool|mixed
 */
function cerber_get_options( $option = '' ) {
	$options = cerber_get_setting_list();
	$united  = array();
	foreach ( $options as $opt ) {
		$o = get_site_option( $opt );
		if ( ! is_array( $o ) ) {
			continue;
		}
		$united = array_merge( $united, $o );
	}
	$options = $united;
	if ( ! empty( $option ) ) {
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}
		else {
			return false;
		}
	}

	return $options;
}

/**
 * The replacement for cerber_get_options()
 *
 * @param string $option
 *
 * @return array|bool|mixed
 */
function crb_get_settings( $option = '' ) {
	global $wpdb;
	static $united;

	/**
	 * For some hosting environments it might be faster, e.g. Redis enabled
	 */
	if ( defined( 'CERBER_WP_OPTIONS' ) && CERBER_WP_OPTIONS ) {
		return cerber_get_options( $option );
	}

	if ( ! isset( $united ) ) {

		$options = cerber_get_setting_list();
		$in      = '("' . implode( '","', $options ) . '")';
		$united  = array();

	    if ( is_multisite() ) {
		    $sql = 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key IN ' . $in;
	    }
	    else {
		    $sql = 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name IN ' . $in;
	    }

		$set = cerber_db_get_col( $sql );

		if ( ! $set || ! is_array( $set ) ) {
			return false;
		}

	    foreach ( $set as $item ) {
		    if ( empty( $item ) ) {
			    continue;
		    }

		    $value = unserialize( $item );

		    if ( ! $value || ! is_array( $value ) ) {
			    continue;
		    }

		    $united = array_merge( $united, $value );
	    }

    }

	if ( ! empty( $option ) ) {
		if ( isset( $united[ $option ] ) ) {
			return $united[ $option ];
		}
		else {
			return false;
		}
	}

	return $united;
}

/**
 * @param string $option Name of site option
 * @param boolean $unserialize If true the value of the option must be unserialized
 *
 * @return null|array|string
 * @since 5.8.7
 */
function cerber_get_site_option($option = '', $unserialize = true){
    global $wpdb;
	static $values = array();

	if ( ! $option ) {
		return null;
	}

	/**
	 * For some hosting environments it might be faster, e.g. Redis enabled
	 */
	if ( defined( 'CERBER_WP_OPTIONS' ) && CERBER_WP_OPTIONS ) {
		return get_site_option( $option, null );
	}

	if ( isset( $values[ $option ] ) ) {
		return $values[ $option ];
	}

    if ( is_multisite() ) {
	    // @since 7.1
		//$value = $wpdb->get_var( 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key = "' . $option . '"' );
	    $value = cerber_db_get_var( 'SELECT meta_value FROM ' . $wpdb->sitemeta . ' WHERE meta_key = "' . $option . '"' );
	}
	else {
		// @since 7.1
		//$value = $wpdb->get_var( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = "' . $option . '"' );
		$value = cerber_db_get_var( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name = "' . $option . '"' );
	}

	if ( $value ) {
		if ( $unserialize ) {
			$value = @unserialize( $value );
			if ( ! is_array( $value ) ) {
				$value = null;
			}
		}
	}
	else {
		$value = null;
	}

	$values[$option] = $value;
	return $value;
}

/*
	Load default settings, except Custom Login URL
*/
function cerber_load_defaults() {
	$save = array();
	foreach ( cerber_get_defaults() as $option_name => $fields ) {
		foreach ( $fields as $field_name => $def ) {
			$save[ $field_name ] = $def;
		}
	}
	if ( $path = crb_get_settings( 'loginpath' ) ) {
		$save['loginpath'] = $path;
	}
	cerber_save_settings( $save );
}

/**
 * @param string $type Type of notification email
 * @param bool $array  Return as an array
 *
 * @return array|string Email address(es) for notifications
 */
function cerber_get_email( $type = '', $array = false ) {
	$email = '';

	if ( in_array( $type, array( 'report', 'scan' ) ) ) {
		$email = crb_get_settings( 'email-' . $type );
	}

	if ( ! $email ) {
		$email = crb_get_settings( 'email' );
	}

	if ( ! $array && is_array( $email ) ) {
		$email = implode( ', ', $email );
	}

	if ( empty( $email ) ) {
		$email = get_site_option( 'admin_email' );
		if ( $array ) {
			$email = array( $email );
		}
	}

	return $email;
}

/**
 * Sync a set of scanner/uptime bots settings with the cloud
 *
 * @param $data
 *
 * @return bool
 */
function cerber_cloud_sync( $data = array() ) {
	if ( ! lab_lab() ) {
		return false;
	}

	if ( ! $data ) {
		$data = crb_get_settings();
	}

	$full  = ( empty( $data['scan_afull-enabled'] ) ) ? 0 : 1;
	$quick = absint( $data['scan_aquick'] );

	if ( $quick || $full ) {
		$set             = array(
			$quick,
			$full,
			cerber_sec_from_time( $data['scan_afull'] ),
			cerber_get_email( 'scan', true )
		);
		$scan_scheduling = array( // Is used for scheduled scans
			'client'     => $set,
			'site_url'   => cerber_get_home_url(),
			'gmt_offset' => (int) get_option( 'gmt_offset' ),
			'dtf'        => cerber_get_dt_format(),
		);
	}
	else {
		$scan_scheduling = array();
	}

	if ( lab_api_send_request( array(
		'scan_scheduling' => $scan_scheduling
	) ) ) {
		return true;
	}

	return false;
}

/**
 * Is a cloud based service enabled by the site owner
 *
 * @return bool False if nothing cloud related is enabled
 */
function cerber_is_cloud_enabled( $what = '' ) {
	$data = crb_get_settings();

	$s = array( 'quick' => 'scan_aquick', 'full' => 'scan_afull-enabled' );

	if ( $what ) {
		if ( ! empty( $data[ $s[ $what ] ] ) ) {
			return true;
		}

		return false;
	}

	foreach ( $s as $item ) {
		if ( ! empty( $data[ $item ] ) ) {
			return true;
		}
	}

	return false;
}

function cerber_get_role_policies( $role ) {
	if ( $conf = crb_get_settings( 'crb_role_policies' ) ) {
		return crb_array_get( $conf, $role );
	}

	return true;
}

/**
 * @param $policy string
 * @param $user integer | WP_User
 *
 * @return bool|string
 */
function cerber_get_user_policy( $policy, $user = null ) {

	if ( ! ( $user instanceof WP_User ) ) {
		if ( is_numeric( $user ) ) {
			$user = get_user_by( 'id', $user );
		}
		else {
			$user = wp_get_current_user();
		}
	}

	if ( ! $user ) {
		return false;
	}

	$ret = false;

	foreach ( $user->roles as $role ) {
		$policies = cerber_get_role_policies( $role );
		if ( ! empty( $policies[ $policy ] ) ) {
			$ret = $policies[ $policy ];
		}
	}

	return $ret;
}

function crb_admin_cool_features() {
	return
		'<div class="crb-pro-req">' .
		__( 'These features are available in a professional version of the plugin.', 'wp-cerber' ) .
		'<br/><br/>' . __( 'Know more about all advantages at', 'wp-cerber' ) .
		' <a href="https://wpcerber.com/pro/" target="_blank">https://wpcerber.com/pro/</a>
        </div>';
}