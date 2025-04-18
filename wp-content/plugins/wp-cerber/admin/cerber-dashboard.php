<?php
/*
	Copyright (C) 2015-21 CERBER TECH INC., https://cerber.tech
	Copyright (C) 2015-21 Markov Cregory, https://wpcerber.com

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


if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( ! is_multisite() ) {
	add_action( 'admin_menu', 'cerber_admin_menu' );
	define( 'CRB_ADMIN_CAP', 'manage_options' );
}
else {
	add_action( 'network_admin_menu', 'cerber_admin_menu' );  // only network wide menu allowed in multisite mode
	define( 'CRB_ADMIN_CAP', 'manage_network' );
}

function cerber_admin_menu() {

	$position = 100;

	if ( cerber_is_admin_page() ) {
		cerber_check_environment();
		if ( crb_get_settings( 'top_admin_menu' ) ) {
			$position = 1;
		}
	}

	$hook = add_menu_page( 'WP Cerber Security', 'WP Cerber', CRB_ADMIN_CAP, 'cerber-security', 'cerber_render_admin_page', 'dashicons-shield', $position );
	add_action( 'load-' . $hook, 'crb_admin_screen_options' );
	add_submenu_page( 'cerber-security', __( 'Cerber Dashboard', 'wp-cerber' ), __( 'Dashboard', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-security', 'cerber_render_admin_page' );

	$hook = add_submenu_page( 'cerber-security', __( 'Cerber Traffic Inspector', 'wp-cerber' ), __( 'Traffic Inspector', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-traffic', 'cerber_render_admin_page' );
	add_action( 'load-' . $hook, 'crb_admin_screen_options' );

	if ( lab_lab() ) {
		add_submenu_page( 'cerber-security', __( 'Cerber Data Shield Policies', 'wp-cerber' ), __( 'Data Shield', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-shield', 'cerber_render_admin_page' );
		add_submenu_page( 'cerber-security', __( 'Cerber Security Rules', 'wp-cerber' ), __( 'Security Rules', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-rules', 'cerber_render_admin_page' );
	}

	add_submenu_page( 'cerber-security', __( 'Cerber User Security', 'wp-cerber' ), __( 'User Policies', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-users', 'cerber_render_admin_page' );

	if ( cerber_get_upload_dir_mu() ) {
		$hook = add_submenu_page( 'cerber-security', 'Cerber Security: Site Integrity', __( 'Site Integrity', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-integrity', 'cerber_render_admin_page' );
		add_action( 'load-' . $hook, 'crb_admin_screen_options' );
	}

	add_submenu_page( 'cerber-security', __( 'Cerber anti-spam settings', 'wp-cerber' ), __( 'Anti-spam', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-recaptcha', 'cerber_render_admin_page' );

	$hook = add_submenu_page( 'cerber-security', 'Cerber.Hub', 'Cerber.Hub', CRB_ADMIN_CAP, 'cerber-nexus', 'nexus_admin_page' );
	if ( nexus_is_master() ) {
		add_action( 'load-' . $hook, 'nexus_master_screen' );
	}

	if ( ! CRB_Addons::none() ) {
		add_submenu_page( 'cerber-security', __( 'Add-ons', 'wp-cerber' ), __( 'Add-ons', 'wp-cerber' ), CRB_ADMIN_CAP, CRB_ADDON_PAGE, 'cerber_render_admin_page' );
	}

	add_submenu_page( 'cerber-security', __( 'Cerber tools', 'wp-cerber' ), __( 'Tools', 'wp-cerber' ), CRB_ADMIN_CAP, 'cerber-tools', 'cerber_render_admin_page' );

}

add_action( 'admin_bar_menu', 'cerber_admin_bar' );
function cerber_admin_bar( $wp_admin_bar ) {
	if ( ! is_multisite() ) {
		return;
	}
	$args = array(
		'parent' => 'network-admin',
		'id'     => 'cerber_admin',
		'title'  => 'WP Cerber',
		'href'   => cerber_admin_link(),
	);
	$wp_admin_bar->add_node( $args );
}

/**
 * Wrapper for all admin pages
 *
 * @param $title string
 * @param $tabs array
 * @param $active_tab string
 * @param $renderer callable
 */
function cerber_show_admin_page( $title, $tabs = array(), $active_tab = null, $renderer = null ) {

	if ( ! $active_tab ) {
		$active_tab = crb_admin_get_tab( $tabs );
	}

	if ( nexus_is_valid_request() ) {
		$title .= nexus_request_data()->at_site;
	}

	?>
    <div id="crb-admin" class="wrap">

        <h1><?php echo $title; ?></h1>

		<?php

		cerber_show_admin_notice();

		cerber_show_tabs( $active_tab, $tabs );

		echo '<div style="display: table; width:100%;">';

		echo '<div class="crb-main crb-tab-' . $active_tab . '">';

		if ( $active_tab == 'help' ) {
			cerber_show_help();
		}
        elseif ( is_callable( $renderer ) ) {
			call_user_func( $renderer, $active_tab );
		}

		echo '</div>';

		cerber_show_aside( $active_tab );

		echo '</div>';

		?>
    </div>
	<?php
}

/*
	Displays lockouts in the Dashboard
*/
function cerber_show_lockouts( $args = array(), $echo = true ) {
	global $crb_ajax_loader;

	//$wp_cerber->deleteGarbage();

	$per_page = ( ! empty( $args['per_page'] ) ) ? $args['per_page'] : crb_admin_get_per_page();
	$limit = cerber_get_sql_limit( $per_page );
	$controls = empty( $args['no_navi'] );
	$table = '';
	$hint = '';

	if ( $rows = cerber_db_get_results( 'SELECT * FROM ' . CERBER_BLOCKS_TABLE . ' ORDER BY block_until DESC ' . $limit, MYSQL_FETCH_OBJECT ) ) {

		if ( $controls ) {
			$total = cerber_blocked_num();
		}

		$table_rows = array();
		$base_url = cerber_admin_link( 'activity' );

	    $remove = cerber_admin_link( crb_admin_get_tab(), null, true );

	    foreach ( $rows as $row ) {
		    $ip = '<a href="' . $base_url . '&filter_ip=' . $row->ip . '">' . $row->ip . '</a>';

		    $ip_info = cerber_get_ip_info( $row->ip, true );
		    if ( isset( $ip_info['hostname_html'] ) ) {
			    $hostname = $ip_info['hostname_html'];
		    }
		    else {
			    $ip_id = cerber_get_id_ip( $row->ip );
			    $hostname = crb_get_ajax_placeholder( 'hostname', $ip_id );
		    }

		    if ( lab_lab() ) {
			    $single_ip = str_replace( '*', '1', $row->ip );
			    $country   = '</td><td>' . crb_country_html( null, $single_ip );
		    }
		    else {
			    $country = '';
		    }

		    //$list[] = '<td>' . $ip . '</td><td>' . $hostname . $country . '</td><td>' . cerber_date( $row->block_until ) . '</td><td>' . $row->reason . '</td><td><a href="' . $remove . '&cerber_admin_do=lockdel&ip=' . esc_attr( $row->ip ) . '">' . __( 'Remove', 'wp-cerber' ) . '</a></td>';
		    $the_row = '<td>' . $ip . '</td><td>' . $hostname . $country . '</td><td>' . cerber_date( $row->block_until ) . '</td><td>' . $row->reason . '</td>';
		    if ( $controls ) {
			    $the_row .= '<td><a href="' . $remove . '&cerber_admin_do=lockdel&ip=' . esc_attr( $row->ip ) . '">' . __( 'Remove', 'wp-cerber' ) . '</a></td>';
		    }

		    $table_rows[] = $the_row;
		}

	    $heading = array(
		    __( 'IP Address', 'wp-cerber' ),
		    __( 'Hostname', 'wp-cerber' ),
		    __( 'Country', 'wp-cerber' ),
		    __( 'Expires', 'wp-cerber' ),
		    __( 'Reason', 'wp-cerber' ),
		    __( 'Action', 'wp-cerber' ),
	    );

	    if ( ! lab_lab() ) {
		    unset( $heading[2] );
	    }

		if ( $controls ) {
			$navi = cerber_page_navi( $total, $per_page );
			$hint = '<p>' . __( 'Click the IP address to see its activity', 'wp-cerber' ) . '</p>';
		}
		else {
			$navi = '';
			unset( $heading[5] );
        }

		$titles = '<tr><th>' . implode( '</th><th>', $heading ) . '</th></tr>';

		$table = '<table id="crb-locked-out" class="widefat crb-table"><thead>' . $titles . '</thead><tfoot>' . $titles . '</tfoot>' . implode( '</tr><tr>', $table_rows ) . '</tr></table>' . $navi;

	    /*if ( empty( $args['no_navi'] ) ) {
		    $table .= cerber_page_navi( $total, $per_page );
	    }*/

    }
    else {
	    $hint  = '<p>' . sprintf( __( 'No lockouts at the moment. The sky is clear.', 'wp-cerber' ) ) . '</p>';
    }

	$ret = $table . '<div class="cerber-margin">' . $hint . '</div>';

	if ( $echo ) {
		echo $ret;

		return;
	}

	return $ret;
}

function cerber_block_delete( $ip ) {
	$result = cerber_db_query( 'DELETE FROM ' . CERBER_BLOCKS_TABLE . ' WHERE ip = "' . cerber_real_escape( $ip ) . '"' );

	crb_event_handler( 'ip_event', array(
		'e_type' => 'unlocked',
		'ip'     => $ip,
		'result' => $result
	) );

	return $result;
}


/*
	ACL management form in dashboard
*/
function cerber_acl_form(){

	//echo '<h2>'.__('White IP Access List','wp-cerber').'</h2><p><span style="color:green;" class="dashicons-before dashicons-thumbs-up"></span> '.__('These IPs will never be locked out','wp-cerber').' - <a target="_blank" href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Know more</a></p>'.
	echo '<h2>' . __( 'White IP Access List', 'wp-cerber' ) . '</h2><p><a target="_blank" href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">How Access Lists work</a> &nbsp;|&nbsp; <a href="' . cerber_admin_link( 'imex' ) . '">Import entries</a></p>' .
	     cerber_acl_get_table('W');
	//echo '<h2>'.__('Black IP Access List','wp-cerber').'</h2><p><span style="color:red;" class="dashicons-before dashicons-thumbs-down"></span> '.__('Nobody can log in or register from these IPs','wp-cerber').' - <a target="_blank" href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Know more</a></p>'.
	echo '<h2>'.__('Black IP Access List','wp-cerber').'</h2>'.
	     cerber_acl_get_table('B');

	$user_ip = cerber_get_remote_ip();
	$link = cerber_admin_link( 'activity' ) . '&filter_ip=' . $user_ip;
	$name = crb_country_html(null, $user_ip);

	echo '<p style="margin-bottom: 30px;"><b><span class="dashicons-before dashicons-star-filled"></span> '.__('Your IP','wp-cerber').': </b><a href="'.$link.'">'.$user_ip.'</a> '.$name.'</p>';

	?>



    <table class="crb-acl-hints">
        <tr><td colspan="3">Use the following formats to add entries to the access lists</td></tr>
        <tr><td>IPv4</td><td>Single IPv4 address</td><td>192.168.5.22</td></tr>
        <tr><td>IPv4</td><td>Range specified with hyphen (dash)</td><td>192.168.1.45 - 192.168.22.165</td></tr>
        <tr><td>IPv4</td><td>Range specified with CIDR</td><td>192.168.128.0/20</td></tr>
        <tr><td>IPv4</td><td>Subnet Class C specified with CIDR</td><td>192.168.77.0/24</td></tr>
        <tr><td>IPv4</td><td>Any IPv4 address specified with CIDR</td><td>0.0.0.0/0</td></tr>
        <tr><td>IPv4</td><td>Subnet Class C specified with wildcard</td><td>192.168.77.*</td></tr>
        <tr><td>IPv4</td><td>Subnet Class B specified with wildcard</td><td>192.168.*.*</td></tr>
        <tr><td>IPv4</td><td>Subnet Class A specified with wildcard</td><td>192.*.*.*</td></tr>
        <tr><td>IPv4</td><td>Any IPv4 address specified with wildcard</td><td>*.*.*.*</td></tr>
        <tr><td>IPv6</td><td>Single IPv6 address</td><td>2001:0db8:85a3:0000:0000:8a2e:0370:7334</td></tr>
        <tr><td>IPv6</td><td>Range specified with hyphen (dash)</td><td>2001:db8::ff00:41:0 - 2001:db8::ff00:41:12ff</td></tr>
        <tr><td>IPv6</td><td>Range specified with CIDR</td><td>2001:db8::/46</td></tr>
        <tr><td>IPv6</td><td>Range specified with wildcard</td><td>2001:db8::ff00:41:*</td></tr>
        <tr><td>IPv6</td><td>Any IPv6 address</td><td>::/0</td></tr>
    </table>

	<?php
}
/*
	Create HTML to display ACL area: table + form
*/
function cerber_acl_get_table( $tag, $acl_slice = 0 ) {
	global $wpdb;

	$activity_url = cerber_admin_link( 'activity' );
	$acl_slice    = absint( $acl_slice );
	$tag          = preg_replace( '/[^W|B]/', '', $tag );

	if ( $rows = $wpdb->get_results( 'SELECT * FROM ' . CERBER_ACL_TABLE . " WHERE acl_slice = '.$acl_slice.' AND tag = '" . $tag . "' ORDER BY ip_long_begin, ip" ) ) {
		foreach ( $rows as $row ) {
			$links = '';
			if ( ! $row->ver6 || cerber_is_ipv6( $row->ip ) ) {
				$links = '<a class="crb-button-tiny" href="' . $activity_url . '&filter_ip=' . urlencode( cerber_ipv6_short( $row->ip ) ) . '">' . __( 'Check for activities', 'wp-cerber' ) . '</a> ' . cerber_traffic_link( array( 'filter_ip' => $row->ip ) );
			}

			$list[] = '<td>' . $row->ip . '</td><td>' . $row->comments . '</td><td>' . $links . '</td>
            <td><a class="delete_entry crb-button-tiny" href="javascript:void(0)" data-ip="' . $row->ip . '">' . __( 'Remove', 'wp-cerber' ) . '</a>
            </td>';
		}
		$ret = '<table id="acl_' . $tag . '" class="acl-table" data-acl-slice="' . $acl_slice . '"><tr>' . implode( '</tr><tr>', $list ) . '</tr></table>';
	}
	else {
		$ret = '<p style="text-align: center;">' . __( 'List is empty', 'wp-cerber' ) . '</p>';
	}
	$ret = '<div class="acl-wrapper"><div class="acl-items">'
	       . $ret . '</div>
            <form action="" method="post">
	        <table>
            <tr><td><input class="crb-monospace" type="text" name="add_acl" required maxlength="100" placeholder="' . __( 'IP address, range, wildcard, or CIDR', 'wp-cerber' ) . '"> 
            </td><td><input type="submit" class="button button-primary" value="' . __( 'Add Entry', 'wp-cerber' ) . '" ></td></tr>
            <tr><td><input class="crb-monospace" type="text" name="add_acl_comment" maxlength="250" placeholder="' . __( 'Optional comment for this entry', 'wp-cerber' ) . '"> 
            </td><td></td></tr>
            </table>
            <input type="hidden" name="cerber_admin_do" value="add2acl">
	        <input type="hidden" name="acl_tag" value="' . $tag . '">'
	       . cerber_nonce_field()
	       . '</form></div>';

	return $ret;
}
/*
	Handle actions with items in ACLs in the dashboard
*/
function cerber_acl_form_process( $post = array() ) {
	$tag = crb_array_get( $post, 'acl_tag', '', 'W|B' );
	$ip  = trim( crb_array_get( $post, 'add_acl' ) );
	$ip  = preg_replace( CRB_IP_NET_RANGE, ' ', $ip );
	$ip  = preg_replace( '/\s+/', ' ', $ip );
	$comment = strip_tags( stripslashes( crb_array_get( $post, 'add_acl_comment', '' ) ) );

	if ( $tag == 'B' ) {
		if ( ! cerber_can_be_listed( $ip ) ) {
			cerber_admin_notice( __( "You cannot add your IP address or network", 'wp-cerber' ) );

			return;
		}
		$ok = sprintf( __( 'IP address %s has been added to Black IP Access List', 'wp-cerber' ), $ip );
	}
	else {
		$ok = sprintf( __( 'IP address %s has been added to White IP Access List', 'wp-cerber' ), $ip );
	}

	$result = cerber_acl_add( $ip, $tag, $comment );

	if ( is_wp_error( $result ) ) {
		cerber_admin_notice( $result->get_error_message() );
	}
	else {
		cerber_admin_message( $ok );
	}

	return;

}

/*
	AJAX admin requests is landing here
*/
add_action('wp_ajax_cerber_ajax', 'cerber_admin_ajax');
function cerber_admin_ajax() {

	$go = cerber_check_ajax_permissions( false );

	if ( $go === false ) {
		return false;
	}

	$admin = ( $go === true ) ? true : false;

	$response = array();
	$request  = crb_get_request_fields();

	if ( $admin && ( $ip = crb_array_get( $request, 'acl_delete' ) ) ) {
		if ( cerber_acl_remove( $ip, crb_array_get( $request, 'slice' )  ) ) {
			$response['deleted_ip'] = $ip;
		}
		else {
			$response['error'] = 'Unable to delete the entry';
		}
	}
    elseif ( ( $slug = crb_array_get( $request, 'crb_ajax_slug' ) )
	         && $list = crb_array_get( $request, 'crb_ajax_list' ) ) {

	    $response['slug'] = $slug;
		$list = array_unique( $list );
	    $response_data = array();

		/*
		$list = array_map(function ($ip_id){
			return cerber_get_ip_id( $ip_id );
        }, $list);
		$list = array_filter( $list, function ( $ip ) {
			if (filter_var( $ip, FILTER_VALIDATE_IP )){
			    return true;
            }
		});*/

	    if ( $slug == 'hostname' || $slug == 'country' ) {
		    $ip_list = array();
		    foreach ( $list as $ip_id ) {
			    if ( $ip = filter_var( cerber_get_ip_id( $ip_id ), FILTER_VALIDATE_IP ) ) {
				    $ip_list[ $ip_id ] = $ip;
				    $response_data[ $ip_id ] = '';
			    }
			    else {
				    $response_data[ $ip_id ] = '-';
			    }
		    }
	    }

	    switch ( $slug ) {
		    case 'hostname':
			    foreach ( $ip_list as $ip_id => $ip ) {
				    $ip_info = cerber_get_ip_info( $ip );
				    $response_data[ $ip_id ] = $ip_info['hostname_html'];
			    }
			    break;
		    case 'country':
			    if ( $country_list = lab_get_country( $ip_list, false ) ) {
				    foreach ( $country_list as $ip_id => $country ) {
					    if ( $country ) {
						    $response_data[ $ip_id ] = cerber_get_flag_html( $country, cerber_country_name( $country ) );
					    }
					    else {
						    $response_data[ $ip_id ] = __( 'Unknown', 'wp-cerber' );
					    }
				    }
			    }
			    break;
		    case 'cbla':
			    $base = cerber_activity_link( array( 5 ) );
			    foreach ( $list as $user_id ) {
				    $row = cerber_db_get_row( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE activity = 5 AND user_id = ' . absint( $user_id ) . ' ORDER BY stamp DESC LIMIT 1', MYSQL_FETCH_OBJECT );
				    if ( $row ) {
					    if ( $country = crb_country_html( $row->country, $row->ip, false ) ) {
						    $country = '<br />' . $country;
					    }
					    else {
						    $country = '';
                        }
					    $val = '<a href="' . $base . '&amp;filter_user=' . $user_id . '">' . cerber_date( $row->stamp ) . '</a>' . $country;
				    }
				    else {
					    $val = __( 'Never', 'wp-cerber' );
				    }

				    $response_data[ $user_id ] = $val;
			    }
			    break;
		    case 'cbfl':
			    $base = cerber_activity_link( array( 7 ) );
			    foreach ( $list as $user_id ) {
				    $u = get_userdata( $user_id );
				    $val = 0;
				    $failed = cerber_db_get_var( 'SELECT COUNT(user_id) FROM ' . CERBER_LOG_TABLE . ' WHERE ( user_login = "' . $u->user_login . '" OR user_login = "' . $u->user_email . '" ) AND activity = 7 AND stamp > ' . ( time() - 24 * 3600 ) );
				    if ( $failed ) {
					    $val = '<a href="' . $base . '&amp;filter_login=' . $u->user_email . '|' . $u->user_login . '">' . $failed . '</a>';
				    }

				    $response_data[ $user_id ] = $val;
			    }
			    break;
	    }

	    $response['data'] = $response_data;
	}
    elseif ( $admin && isset( $request['dismiss_info'] ) ) {
		if ( isset( $request['button_id'] ) && ( $request['button_id'] == 'lab_ok' || $request['button_id'] == 'lab_no' ) ) {
			lab_user_opt_in( $request['button_id'] );
		}
		else {
			delete_site_option( 'cerber_admin_info' );
		}
	}
    elseif ( $admin && ( $ajax_route = crb_array_get( $request, 'ajax_route' ) ) ) {

		$ref_params = crb_get_referrer_params();

		switch ( $ajax_route ) {
			case 'scanner_analytics':
				$response = cerber_generate_insights( $request['itype'] );
				break;
			case 'user_activity_analytics':
				$data = crb_generate_user_insights( absint( $request['user_id'] ), $ref_params['tab'] );
				$response = array( 'html' => $data, 'error' => '' );
				break;
			case 'ip_extra_info':
				$response = cerber_ip_extra_view_ajax( $request, $ref_params );
				break;
			case 'ip_quick_analytics':
				$data = crb_generate_ip_insights( $request['ip_address'], $ref_params['tab'] );
				$response = array( 'html' => $data, 'error' => '' );
				break;
		}
	}
    elseif ( $admin && ( $usearch = crb_array_get( $request, 'user_search' ) ) ) {
		$users    = get_users( array( 'search' => '*' . esc_attr( $usearch ) . '*', ) );
		$response = array();
		if ( $users ) {
			foreach ( $users as $user ) {
				$data       = get_userdata( $user->ID );
				$response[] = array(
					'id'   => $user->ID,
					'text' => crb_format_user_name( $data )
				);
			}
		}
	}

	echo json_encode( $response );

	if ( ! nexus_is_valid_request() ) {
		wp_die();
	}
}

add_action( 'wp_ajax_cerber_local_ajax', function () {
	check_ajax_referer( 'crb-ajax-admin', 'ajax_nonce' );
	if ( ! is_super_admin() ) {
		wp_die( 'Oops! Access denied.' );
	}

	$done  = array();

	switch ( crb_array_get( $_REQUEST, 'crb_ajax_do' ) ) {
		case 'bg_tasks_run':
			$done = cerber_bg_task_launcher( array_flip( crb_array_get( $_REQUEST, 'tasks', array() ) ) );
			break;
	}

	echo json_encode( array( 'done' => $done ) );
	exit;
} );

/**
 * @param string $group
 * @param string $item_id
 *
 * @return string
 */
function crb_get_ajax_placeholder( $group, $item_id ) {
	global $crb_ajax_loader;

	return '<img class="crb-ajax-load" data-ajax_group="' . $group . '" data-item_id="' . $item_id . '" src="' . $crb_ajax_loader . '" />';
}

/*
 * Retrieve extended IP information
 * @since 2.2
 *
 */
function cerber_get_ip_info( $ip, $cache_only = false ) {

	$ip_id = cerber_get_id_ip( $ip );

	$var = get_transient( $ip_id );
	$ip_info = crb_unserialize( $var ); // lazy way
	if ( $cache_only  ) {
		return $ip_info;
	}

	if ( empty( $ip_info['hostname_html'] ) ) {
		$ip_info  = array();
		$hostname = @gethostbyaddr( $ip );
		if ( ! $hostname ) {
			$hostname = __( 'unknown', 'wp-cerber' );
		}
		$ip_info['hostname'] = $hostname;
		if ( ! filter_var( $hostname, FILTER_VALIDATE_IP ) ) {
			$hostname = str_replace( '.', '.<wbr>', $hostname );
		}
		$ip_info['hostname_html'] = $hostname;
		set_transient( $ip_id, serialize( $ip_info ), 24 * 3600 );
	}

	return $ip_info;
}


/*
	Admin dashboard actions
*/
add_action( 'wp_loaded', function () {  // 'wp_loaded' @since 5.6
	if ( ! is_admin() ) {
		return;
	}

	cerber_admin_request();

}, 0 );
/**
 * @param bool $is_post
 *
 * @return mixed
 *
 */
function cerber_admin_request( $is_post = false ) {
	global $wpdb;

	if ( ! nexus_is_valid_request()
	     && ! cerber_user_can_manage() ) {
		return;
	}

	$get  = crb_get_query_params();

	if ( ( ! $nonce = crb_array_get( $get, 'cerber_nonce' ) )
	     && ( ! $nonce = crb_get_post_fields( 'cerber_nonce' ) ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'control' ) ) {
		return;
	}

	$post = crb_get_post_fields();

	//$q = crb_admin_parse_query( array( 'cerber_admin_do', 'ip' ) );

	$remove_args = array();

	if ( cerber_is_http_get() ) {
		if ( ( $do = crb_array_get( $get, 'cerber_admin_do' ) ) ) {
			switch ( $do ) {
				case 'lockdel':
					$ip = crb_array_get( $get, 'ip' );
					if ( cerber_block_delete( $ip ) ) {
						cerber_admin_message( sprintf( __( 'Lockout for %s was removed', 'wp-cerber' ), $ip ) );
						$remove_args[] = 'ip';
					}
					break;
				case 'testnotify':
					$t = crb_array_get( $get, 'type', '', '\w+' );
					$to = cerber_get_email( $t );
					if ( cerber_send_email( $t ) ) {
						cerber_admin_message( __( 'Email has been sent to', 'wp-cerber' ) . ' ' . $to );
					}
					else {
						cerber_admin_notice( __( 'Unable to send email to', 'wp-cerber' ) . ' ' . $to );
					}
					$remove_args[] = 'type';
					break;
				case 'subscribe':
					$m = crb_array_get( $get, 'mode' );
					$mode = ( 'on' == $m ) ? 'on' : 'off';
					crb_admin_alerts_do( $mode );
					$remove_args[] = 'subscribe';
					$remove_args[] = 'mode';
					break;
				case 'nexus_set_role':
					nexus_enable_role();
					wp_safe_redirect( cerber_admin_link( null, array( 'page' => 'cerber-nexus' ) ) );
					exit();
					break;
				case 'nexus_delete_slave':
					//nexus_delete_slave( cerber_get_get( 'site_id' ) );
					wp_safe_redirect( cerber_admin_link( 'nexus_sites' ) );
					exit();
					break;
				case 'nexus_site_table':
					if ( cerber_get_bulk_action() ) {
						nexus_do_bulk();
					}
					break;
				case 'scan_tegrity':
					$adm  = crb_array_get( $get, 'crb_scan_adm' );
					$file = crb_array_get( $get, 'crb_file_id' );
					if ( in_array( $adm, array( 'delete', 'restore' ) ) ) {
						cerber_quarantine_do( $adm, crb_array_get( $get, 'crb_scan_id' ), crb_array_get( $get, 'crb_file_id' ) );
					}
                    elseif ( $adm == 'remove_ignore' ) {
						if ( crb_remove_ignore( $file ) ) {
							cerber_admin_message( 'The file has been removed from the list' );
						}
					}
					$remove_args = array( 'crb_scan_adm', 'crb_scan_id', 'crb_file_id' );
					break;
				case 'manage_diag_log':
					cerber_manage_diag_log( crb_array_get( $get, 'do_this' ) );
					$remove_args[] = 'do_this';
					break;
				case 'terminate_session':
					crb_sessions_kill( crb_array_get( $get, 'id', null, '\w+' ), crb_array_get( $get, 'user_id' ) );
					$remove_args = array( 'user_id', 'id' );
					break;
				case 'crb_manage_sessions':
					if ( cerber_get_bulk_action() == 'bulk_session_terminate' ) {
						crb_sessions_kill( crb_array_get( $get, 'ids', array(), '\w+' ) );
					}
                    elseif ( cerber_get_bulk_action() == 'bulk_block_user' ) {
						if ( ( $sids = crb_array_get( $get, 'ids', array(), '\w+' ) )
						     && ( $users = cerber_db_get_col( 'SELECT user_id FROM ' . cerber_get_db_prefix() . CERBER_USS_TABLE . ' WHERE wp_session_token IN ("' . implode( '","', $sids ) . '")' ) ) ) {
							array_walk( $users, 'cerber_block_user' );
						}
					}
					break;
				case 'export':
					if ( nexus_is_valid_request() ) {
						return crb_admin_get_tokenized_link();
					}
					crb_admin_download_file( crb_array_get( $get, 'type' ) );
					break;
				case 'load_defaults':
					cerber_load_defaults();
					cerber_admin_message( __( 'Default settings have been loaded', 'wp-cerber' ) );
					break;
				case 'clear_cache':
					CRB_Cache::reset();
					break;
				default:
					return;
			}

			if ( nexus_is_valid_request() ) {
				return array( 'redirect' => true, 'remove_args' => $remove_args );
			}
			else {
				cerber_safe_redirect( $remove_args );
			}

		}

		// TODO: move to the switch above
        if ( cerber_get_get( 'citadel' ) == 'deactivate' ) {
			cerber_disable_citadel();
		}
		elseif ( isset( $_GET['force_repair_db'] ) ) {
			cerber_create_db();
			cerber_upgrade_db( true );
			cerber_admin_message( 'Cerber\'s database tables have been repaired and upgraded' );
			cerber_safe_redirect('force_repair_db');
		}
        elseif ( $table = cerber_get_get( 'truncate', '[a-z_]+' ) ) {
	        if ( 0 === strpos( $table, 'cerber_' ) ) {
		        if ( $wpdb->query( 'TRUNCATE TABLE ' . $table ) ) {
			        cerber_admin_message( 'Table ' . $table . ' has been truncated' );
		        }
		        else {
			        cerber_admin_notice( $wpdb->last_error );
		        }
	        }
	        cerber_safe_redirect( 'truncate' );
        }
        elseif ( isset( $_GET['force_check_nodes'] ) ) {
	        $best = lab_check_nodes( true );
	        cerber_admin_message( 'Connectivity to all Cerber Lab\'s nodes has been checked. The closest node: ' . $best );
	        cerber_safe_redirect( 'force_check_nodes' );
        }
        elseif ( isset( $_GET['clear_up_lab_cache'] ) ) {
	        lab_cleanup_cache();
	        cerber_admin_message( 'The Cerber Lab cache has been cleared' );
	        cerber_safe_redirect( 'clear_up_lab_cache' );
        }
        elseif ( isset( $_GET['clear_up_the_cache'] ) ) {
	        lab_cleanup_cache();
	        cerber_delete_expired_set( true );
	        cerber_admin_message( 'The plugin cache has been cleared' );
	        cerber_safe_redirect( 'clear_up_the_cache' );
        }
	}

	if ( cerber_is_http_post() ) {

	    $redirect = false;

		if ( ( $do = crb_array_get( $post, 'cerber_admin_do' ) ) ) {
			switch ( $do ) {
				case 'update_role_policies':
					crb_admin_save_role_policies( $post );
					$redirect = true;
					break;
				case 'update_geo_rules':
					crb_admin_save_geo_rules( $post );
					break;
				case 'add2acl':
					cerber_acl_form_process( $post );
					break;
				case 'add_slave':
					nexus_add_slave( crb_array_get( $post, 'new_slave_token' ) );
					break;
				case 'install_key':
					$lic = preg_replace( "/[^A-Z0-9]/i", '', crb_array_get( $post, 'cerber_license' ) );
					if ( ( strlen( $lic ) == LAB_KEY_LENGTH ) || empty( $lic ) ) {
						lab_cleanup_cache();
						cerber_delete_expired_set( true );

						lab_update_key( $lic );

						if ( $lic ) {
							if ( lab_validate_lic() ) {
								cerber_admin_message( '<b>Great! You have entered the valid license key.</b><p>Now, whenever you see a green shield icon in the top right-hand corner of any Cerber\'s admin page, it means the professional version works as intended, and your website is protected by WP Cerber Security Cloud.</p>
                                    <p>Please use our client portal to manage your subscription, license keys and get support at <a target="_blank" href="https://my.wpcerber.com">https://my.wpcerber.com</a></p>
                                    <p>Thanks for being our client.</p>' );
							}
							else {
								cerber_admin_notice( 'Error! You have entered an invalid or expired license key.' );
							}
						}

						$redirect = true;
					}
					break;
			}

			if ( $redirect ) {
				if ( nexus_is_valid_request() ) {
				    // No redirection is needed so far, we use a second 'get_page' request
					//return array( 'redirect' => true, 'remove_args' => $remove_args );
				}
				else {
					cerber_safe_redirect( $remove_args );
				}
			}
		}
	}

}

function crb_admin_download_file( $t, $query = array() ) {
	switch ( $t ) {
		case 'activity':
			cerber_export_activity( $query );
			break;
		case 'traffic':
			cerber_export_traffic( $query );
			break;
		case 'get_diag_log':
			cerber_manage_diag_log( 'download' );
			break;
	}
}

function cerber_safe_redirect( $rem_args ) {
	if ( empty( $rem_args ) ) {
		$rem_args = array();
	}
    elseif ( ! is_array( $rem_args ) ) {
		$rem_args = array( $rem_args );
	}
	// Most used common args to remove
	$rem_args = array_merge( $rem_args, array(
		'_wp_http_referer',
		'_wpnonce',
		'cerber_nonce',
		'ids',
		'cerber_admin_do',
		'action',
		'action2'
	) );
	wp_safe_redirect( remove_query_arg( $rem_args ) );
	exit();  // mandatory!
}

function crb_admin_get_tokenized_link() {
	if ( empty( nexus_request_data()->get_params ) ) {
		return false;
	}
	$key = crb_random_string( 26 );
	if ( cerber_update_set( '_the_key_' . $key, array( 'query' => nexus_request_data()->get_params ), 1, true, time() + 60 ) ) {
		return array( 'redirect' => true, 'redirect_url' => home_url( '?cerber_magic_key=' . $key ) );
	}

	cerber_admin_notice( 'Unable to generate a tokenized URL' );

	return false;
}

function cerber_export_activity( $params = array() ) {

	crb_raise_limits( 512 );

	$args = array( 'per_page' => 0 );

	if ( $params ) {
		$args = array_merge( $params, $args );
	}

	list( $query, $per_page, $falist, $ip, $filter_login, $user_id, $search, $sid, $in_url ) = cerber_activity_query( $args );

	// We split into several requests to avoid PHP and MySQL memory limitations

	if ( defined( 'CERBER_EXPORT_CHUNK' ) && is_numeric( CERBER_EXPORT_CHUNK ) ) {
		$per_chunk = CERBER_EXPORT_CHUNK;
	}
	else {
		$per_chunk = 1000; 	// Rows per SQL request: a compromise between the size of SQL data at each iteration and script execution time
	}

	if ( ! $result = cerber_db_query( $query . ' LIMIT ' . $per_chunk ) ) {
		wp_die( 'Nothing to export' );
	}

	$total = cerber_db_get_var( "SELECT FOUND_ROWS()" );

	$info = array();

	if ( $ip ) {
		$info[] = '"Filter by IP:","' . $ip . '"';
	}

	if ( $user_id ) {
		$user    = get_userdata( $user_id );
		$info[] = '"Filter by user:","' . $user->display_name . '"';
	}
	if ( $search ) {
		$info[] = '"Search results for:","' . $search . '"';
	}

	$heading = array(
		__( 'IP Address', 'wp-cerber' ),
		__( 'Date', 'wp-cerber' ),
		__( 'Event', 'wp-cerber' ),
		__( 'Additional Details', 'wp-cerber' ),
		__( 'Local User', 'wp-cerber' ),
		__( 'User login', 'wp-cerber' ),
		__( 'User ID', 'wp-cerber' ),
		__( 'Username', 'wp-cerber' ),
		'Unix Timestamp',
		'Request ID',
		'URL',
	);

	cerber_send_csv_header( 'wp-cerber-activity', $total, $heading, $info );

	$labels = cerber_get_labels( 'activity' );
	$status = cerber_get_labels( 'status' ) + cerber_get_reason();

	$placeholder = array( 0, '', '', '', '' );

	if ( crb_get_settings( 'plain_date' ) ) {
		function _crb_csv_date( $timestamp ) {
			static $gmt_offset;
			if ( $gmt_offset === null ) {
				$gmt_offset = get_option( 'gmt_offset' ) * 3600;
			}
			return date( 'Y-m-d H:i:s', $timestamp + $gmt_offset );
		}
	}
	else {
		function _crb_csv_date( $timestamp ) {
			return cerber_date( $timestamp, false );
		}
	}

	// The loop

	$i = 0;

	do {

		while ( $row = mysqli_fetch_object( $result ) ) {

			$values = array();

			if ( ! empty( $row->details ) ) {
				$details = explode( '|', $row->details );
			}
			else {
				$details = $placeholder;
			}

			$values[] = $row->ip;
			$values[] = _crb_csv_date( $row->stamp );
			$values[] = $labels[ $row->activity ];
			$s        = $details[0];
			$values[] = ( isset( $status[ $s ] ) ) ? $status[ $s ] : '';
			$values[] = $row->display_name;
			$values[] = $row->ulogin;
			$values[] = $row->user_id;
			$values[] = $row->user_login;
			$values[] = $row->stamp;
			$values[] = $row->session_id;
			$values[] = $details[4];

			cerber_send_csv_line( $values );
		}

		mysqli_free_result( $result );

		$i ++;
		$offset = $per_chunk * $i;

	} while ( ( $result = cerber_db_query( $query . ' LIMIT ' . $offset . ', ' . $per_chunk ) )
	          && $result->num_rows );

	exit;

}

function cerber_send_csv_header( $f_name, $total, $heading = array(), $info = array() ) {

	$fname = $f_name . '.csv';

	crb_file_headers( $fname );

	$info[] = '"Generated by:","WP Cerber Security ' . CERBER_VER . '"';
	$info[] = '"Website:","' . get_option( 'blogname' ) . '"';
	$info[] = '"Date:","' . cerber_date( time(), false ) . '"';
	$info[] = '"Rows in this file:","' . $total . '"';

	echo implode( "\r\n", $info ) . "\r\n\r\n";

	foreach ( $heading as &$item ) {
		$item = '"' . str_replace( '"', '""', trim( $item ) ) . '"';
	}

	echo implode( ',', $heading ) . "\r\n";

}

function cerber_send_csv_line( $values ) {
	foreach ( $values as &$value ) {
		$value = '"' . str_replace( '"', '""', trim( $value ) ) . '"';
	}
	$line = implode( ',', $values ) . "\r\n";
	echo $line;
}

function crb_admin_activity_nav_links( $context = '' ) {

	$links = array();

	if ( ! $context ) {
		$links[] = array( array(), __( 'View all', 'wp-cerber' ) );

		$labels = cerber_get_labels( 'activity' );

		$set = array( 5 );
		foreach ( $set as $item ) {
			$links[] = array( array( 'filter_activity' => $item ), $labels[ $item ] );
		}
	}

	if ( $context == 'users' ) {
		$links[] = array( array( 'filter_user' => '*' ), __( 'View all', 'wp-cerber' ) );
	}

	if ( $context != 'suspicious' ) {
		$links[] = array( array( 'filter_activity' => array( 1, 2 ) ), __( 'New users', 'wp-cerber' ) );
		$links[] = array( array( 'filter_set' => 2 ), __( 'Login issues', 'wp-cerber' ) );
	}

	if ( $context != 'users' ) {
		if ( ! $context ) {
			$txt = __( 'Suspicious activity', 'wp-cerber' );
		}
		else {
			$txt = __( 'View all', 'wp-cerber' );
		}

		$links[] = array( array( 'filter_set' => 1 ), $txt );
	}

	if ( ! $context ) {
		$links[] = array( array( 'filter_activity' => array( 10, 11 ) ), __( 'IP blocked', 'wp-cerber' ) );
		$links[] = array( array( 'filter_user' => '*' ), __( 'Users', 'wp-cerber' ) );
		$links[] = array( array( 'filter_user' => 0 ), __( 'Non-authenticated', 'wp-cerber' ) );
		$links[] = array( array( 'filter_user' => get_current_user_id() ), __( 'My activity', 'wp-cerber' ) );
		$links[] = array( array( 'filter_ip' => cerber_get_remote_ip() ), __( 'My IP', 'wp-cerber' ) );
	}

	return crb_make_nav_links( $links, 'activity' );

}

/**
 * @param array $link_list A list of links
 * @param string $tab Admin page tab to generated links for
 * @param string $class CSS class
 *
 * @return string
 * @since 8.8.3.2
 */
function crb_make_nav_links( $link_list, $tab = 'activity', $class = '' ) {

	$params = crb_get_referrer_params();
	unset( $params['page'], $params['tab'], $params['pagen'] );

	$base_url = cerber_admin_link( $tab );
	$ret = '';

	foreach ( $link_list as $link ) {
		$query_string = '';
		$selected = false;
		$other = $params;

		if ( ! empty( $link[0] ) ) {
			$selected = true;
			foreach ( $link[0] as $name => $value ) {
				if ( ! is_array( $value ) ) {
					$query_string .= '&amp;' . $name . '=' . $value;
					if ( crb_array_get( $params, $name ) !== (string) $value ) {
						$selected = false;
					}
					else {
						unset( $other[ $name ] );
					}
				}
				else {
					foreach ( $value as $key => $val ) {
						$query_string .= '&amp;' . $name . '[' . $key . ']=' . $val;
						if ( crb_array_get( $params, array( $name, $key ) ) !== (string) $val ) {
							$selected = false;
						}
						else {
							unset( $other[ $name ][ $key ] );
						}
					}
				}
			}
		}

		$other = array_filter( $other, function ( $val ) {
			return $val === 0 || ! empty( $val );
		} );

		if ( $selected & empty( $other ) ) {
			$ret .= '<span class="crb_selected">' . $link[1] . '</span> ';
		}
		else {
			$ret .= '<a class="" href="' . $base_url . $query_string . '">' . $link[1] . '</a> ';
		}
	}

	return '<div class="crb-tag-buttons ' . $class . '">' . $ret . '</div>';
}

/*
 * Display activities in the WP Dashboard
 *
 *
 */
function cerber_show_activity( $args = array(), $echo = true ) {
	global $crb_ajax_loader;

	$labels        = cerber_get_labels( 'activity' );
	$status_labels = cerber_get_labels( 'status' ) + cerber_get_reason();

	$base_url    = cerber_admin_link( 'activity' );
	$export_link = '';
	$ret         = '';

	list( $query, $per_page, $falist, $filter_ip, $filter_login, $user_id, $search, $sid, $in_url ) = cerber_activity_query( $args );

	$sname = '';
	$info  = '';

	$_find = array();

	if ( $filter_login ) {
		$login = explode( '|', $filter_login );
		if ( is_email( $login[0] ) ) {
			$_find[] = array( 'email', $login[0] );
		}
		else {
			$_find[] = array( 'login', $login[0] );
		}

		if ( isset( $login[1] ) ) {
			if ( is_email( $login[1] ) ) {
				$_find[] = array( 'email', $login[1] );
			}
			else {
				$_find[] = array( 'login', $login[1] );
			}
		}
	}

	if ( $search ) {
		if ( cerber_is_ip( $search ) ) {
			$filter_ip = $search;
		}
		else {
			if ( is_email( $search ) ) {
				$_find[] = array( 'email', $search );
			}
			else {
				$_find[] = array( 'login', $search );
			}
		}
	}

	if ( ! $user_id && $_find ) {
		foreach ( $_find as $item ) {
			if ( $user = get_user_by( $item[0], $item[1] ) ) {
				$user_id = $user->ID;
				break;
			}
		}
	}

	if ( $user_id ) {
		$sname = crb_format_user_name( $user_id );
		$info .= cerber_user_extra_view( $user_id );
	}

	if ( $filter_ip ) {
		$info .= cerber_ip_extra_view( $filter_ip );
	}

	// No cache
	//$rows = cerber_db_get_results( $query, MYSQL_FETCH_OBJECT );
	if ( empty( $args['no_navi'] ) ) {
		list( $rows, $total ) = crb_q_cache_get( array( array( $query, MYSQL_FETCH_OBJECT ), array( "SELECT FOUND_ROWS()" ) ), CERBER_LOG_TABLE );
		$total = $total[0][0];
	}
	else {
		$rows = crb_q_cache_get( array( array( $query, MYSQL_FETCH_OBJECT ) ), CERBER_LOG_TABLE );
	}

	if ( $rows ) {

		if ( empty( $args['no_navi'] ) ) {
			// No cache
			//$total = cerber_db_get_var( "SELECT FOUND_ROWS()" );
		}

		$tbody = '';
		$country = '';
		$geo = lab_lab();

		if ( ! defined( 'CERBER_FULL_URI' ) || ! CERBER_FULL_URI ) {
			$short = true;
			$site_url = cerber_get_site_url();
			$site_url = substr( $site_url, strpos( $site_url, '://' ) + 3 );
			$start = strlen( rtrim( $site_url, '/' ) );
		}
		else {
			$short = false;
			$start = 0;
        }

		foreach ( $rows as $row ) {

			$ip_id = cerber_get_id_ip( $row->ip );

			$activity = '<span class="crb-activity actv' . $row->activity . '" title="' . $row->activity . '">' . $labels[ $row->activity ] . '</span>';

			if ( empty( $args['no_details'] ) && $row->details ) {
				$details = explode( '|', $row->details );

				if ( ! empty( $details[0] ) && isset( $status_labels[ $details[0] ] ) ) {
					$activity .= ' <span class = "crb-log-status crb-status-' . $details[0] . '" title="' . $details[0] . '">' . $status_labels[ $details[0] ] . '</span>';
				}

				if ( ( $uri = crb_array_get( $details, 4 ) )
				     && ( $row->activity < 10 || $row->activity > 12 ) ) {

					if ( $short && 0 === strpos( $uri, $site_url ) ) {
						$ac_uri = substr( $uri, $start );
					}
					else {
						$ac_uri = $uri;
					}

					$activity .= '<p class="crb_act_url" title="' . $uri . '">' . str_replace( array( '-', '/' ), array(
							'<wbr>-',
							'<wbr>/'
						), $ac_uri ) . '</p>';
				}

			}

			$activity = '<div class="crb' . $row->activity . '">' . $activity . '</div>';

			$name = crb_admin_get_user_cell( $row->user_id, $base_url );

			$username = '<a href="' . $base_url . '&amp;filter_login=' . $row->user_login . '">' . $row->user_login . '</a>';

			$ip_info = cerber_get_ip_info( $row->ip, true );
			if ( isset( $ip_info['hostname_html'] ) ) {
				$hostname = $ip_info['hostname_html'];
			}
			else {
				$hostname = crb_get_ajax_placeholder( 'hostname', $ip_id );
			}

			if ( ! empty( $args['date'] ) && $args['date'] == 'ago' ) {
				$date = cerber_ago_time( $row->stamp );
			}
			else {
				$date = '<span title="' . $row->stamp . ' / ' . $row->session_id . ' / ' . $row->activity . '">' . cerber_date( $row->stamp ) . '</span>';
			}

			if ( $geo ) {
				$country = '</td><td>' . crb_country_html( $row->country, $row->ip );
			}

			$tbody .= '<tr class="acrow' . $row->activity . '"><td>' . crb_admin_ip_cell( $row->ip, $base_url . '&amp;filter_ip=' . $row->ip ) . '</td><td>' . $hostname . $country . '</td><td>' . $date . '</td><td class="acinfo">' . $activity . '</td><td>' . $name . '</td><td>' . $username . '</td></tr>';
		}

		$heading = array(
			'crb_ip_col'       => '<div class="crb_act_icon"></div>' . __( 'IP Address', 'wp-cerber' ),
			'crb_hostname_col' => __( 'Hostname', 'wp-cerber' ),
			'crb_country_col'  => __( 'Country', 'wp-cerber' ),
			'crb_date_col'     => __( 'Date', 'wp-cerber' ),
			'crb_event_col'    => __( 'Event', 'wp-cerber' ),
			'crb_user_col'     => __( 'Local User', 'wp-cerber' ),
			'crb_username_col' => __( 'Username', 'wp-cerber' )
		);

		if ( ! $geo ) {
			unset( $heading['crb_country_col'] );
		}

		$titles_head = '';
		foreach ( $heading as $id => $title ) {
			$titles_head .= '<th id="' . $id . '">' . $title . '</th>';
		}

		$titles_foot = '<th>' . implode( '</th><th>', $heading ) . '</th>';

		$class = 'widefat crb-table cerber-margin crb-' . count( $heading ) . '-columns';

		$table = '<table id="crb-activity" class="' . $class . '"><thead><tr>' . $titles_head . '</tr></thead><tfoot><tr>' . $titles_foot . '</tr></tfoot><tbody>' . $tbody . '</tbody></table>';

		if ( empty( $args['no_navi'] ) ) {
			$table .= cerber_page_navi( $total, $per_page );
		}

		//$legend  = '<p>'.sprintf(__('Showing last %d records from %d','wp-cerber'),count($rows),$total);

		if ( empty( $args['no_export'] ) ) {
			$export_link .= '<a class="button button-secondary cerber-button" href="' .
			                cerber_admin_link_add( array(
				                'cerber_admin_do' => 'export',
				                'type'            => 'activity',
			                ), true ) . '"><i class="crb-icon crb-icon-bx-download"></i> ' . __( 'Export', 'wp-cerber' ) . '</a>';
			                //) ) . '"><span class="dashicons dashicons-download" style="vertical-align: middle;"></span> ' . __( 'Export', 'wp-cerber' ) . '</a>';
		}
	}
	else {

		$table = '<p class="cerber-margin">' . __( 'No activity has been logged yet.', 'wp-cerber' ) . '</p>';

		$trf_params = array();
		if ( cerber_is_ip_or_net( $search ) ) {
			$trf_params['filter_ip'] = $search;
		}
		if ( $trf_params ) {
			$table .= '<p class="cerber-margin"><a href="' . cerber_admin_link( 'traffic', $trf_params ) . '">' . __( 'Check for requests', 'wp-cerber' ) . '</a></p>';
		}
	}

	if ( empty( $args['no_navi'] ) ) {

		$display = ( $sid || $in_url ) ? '' : 'display: none;';

		$filters = '<form action="' . cerber_activity_link() . '"><div id="crb-activity-fields"><div>'

		           . crb_get_activity_dd()
		           . cerber_select( 'filter_user', ( $user_id ) ? array( $user_id => $sname ) : array(), $user_id, 'crb-select2-ajax', '', false, esc_html__( 'Filter by registered user', 'wp-cerber' ), array( 'min_symbols' => 3 ) )
		           . '<input type="text" value="' . $search . '" name="search_activity" placeholder="' . esc_html__( 'Search for IP or username', 'wp-cerber' ) . '">
	   
		   <div id="crb-more-activity-fields" style="' . $display . '">		           
		   <input type="text" value="' . $sid . '" name="filter_sid" placeholder="' . esc_html__( 'Request ID', 'wp-cerber' ) . '">
		   <input type="text" value="' . $in_url . '" name="search_url" placeholder="' . esc_html__( 'Search in URL', 'wp-cerber' ) . '">		           
		   </div>
		   
		   </div>
		   
		   <div class="crb-act-controls">
		   <button type="submit" class="cerber-button button button-primary">
		   ' . __( 'Filter', 'wp-cerber' ) . '
		   </button>		   
		   </div>
		   
		   <div class="crb-act-controls">
		   [&nbsp;<a href="#" class="crb-opener" data-target="crb-more-activity-fields">More</a>&nbsp;]		   
		   </div>
		   		   
		   </div>
		   		   
		   <!-- Preserve values - not anymore @since v. 8.8.4 -->
		   <!-- <input type="hidden" name="filter_ip" value="' . htmlspecialchars( $filter_ip ) . '" >
		   <input type="hidden" name="filter_login" value="' . $filter_login . '" > -->
		   
		   <input type="hidden" name="page" value="cerber-security" >
		   <input type="hidden" name="tab" value="activity">
		   </form>';

		$right_links = crb_admin_alert_link() . $export_link;

		$top_bar = '<div id="activity-filter"><div>' . $filters . '</div><div>' . $right_links . '</div></div><br style="clear: both;">';

		$ret = '<div><div class="crb-quick-nav">' . crb_admin_activity_nav_links() . '</div>' . $top_bar . $info . '</div>' . $ret;
	}

	$ret .= $table;

	if ( $echo ) {
		echo $ret;
	}
	else {
		return $ret;
	}

}

/**
 * Parse arguments and create SQL query for retrieving rows from activity log
 *
 * @param array $args Optional arguments to use them instead of using $_GET
 *
 * @return array
 * @since 4.16
 */
function cerber_activity_query( $args = array() ) {
	global $wpdb;

	$ret   = array_fill( 0, 9, '' );
	$where = array();

	$q = crb_admin_parse_query( array(
		'filter_activity',
		'filter_set',
		'filter_ip',
		'filter_login',
		'filter_user',
		'search_activity',
		'filter_sid',
		'search_url',
		'filter_country',
	), $args );

	$falist = array();
	if ( ! empty( $q->filter_set ) ) {
		$falist = crb_get_filter_set( $q->filter_set );
	}
	elseif ( $q->filter_activity ) { // Multiple activities can be requested this way: &filter_activity[]=11&filter_activity[]=7
		if ( is_array( $q->filter_activity ) ) {
			$falist = array_filter( array_map( 'absint', $q->filter_activity ) );
		}
		else {
			$falist = array( absint( $q->filter_activity ) ); // for further using in links
		}
	}
	if ( $falist ) {
		$where[] = 'log.activity IN (' . implode( ',', $falist ) . ')';
	}
	$ret[2] = $falist;

	if ( $q->filter_ip ) {
		$range = cerber_any2range( $q->filter_ip );
		if ( is_array( $range ) ) {
			$where[] = '(log.ip_long >= ' . $range['begin'] . ' AND log.ip_long <= ' . $range['end'] . ')';
		}
        elseif ( cerber_is_ip_or_net( $q->filter_ip ) ) {
	        $where[] = 'log.ip = "' . $q->filter_ip . '"';
		}
		else {
			$where[] = "ip = 'produce-no-result'";
		}
		$ret[3] = preg_replace( CRB_IP_NET_RANGE, ' ', $q->filter_ip );
	}

	if ( $q->filter_login ) {
		if ( strpos( $q->filter_login, '|' ) ) {
			$sanitize = preg_replace( '/["\'<>\/]+/', '', $q->filter_login );
			$logins = explode( '|', $sanitize );
			$where[] = '( log.user_login = "' . implode( '" OR log.user_login = "', $logins ) . '" )';
		}
		else {
			$where[] = $wpdb->prepare( 'log.user_login = %s', $q->filter_login );
		}
		$ret[4] = htmlspecialchars( $q->filter_login );
	}

	/*$user_id = absint( $q->filter_user );
	$ret[5]  = $user_id;
	if ( $user_id ) {
		$where[] = 'log.user_id = ' . $user_id;
	}*/

	if ( isset( $q->filter_user ) ) {
		if ( $q->filter_user == '*' ) {
			$where[] = 'log.user_id != 0';
			$ret[5]  = '';
		}
        elseif ( is_numeric( $q->filter_user ) ) {
			$user_id = absint( $q->filter_user );
			$where[] = 'log.user_id = ' . $user_id;
			$ret[5]  = $user_id;
		}
	}

	if ( $q->search_activity ) {
		$search = stripslashes( $q->search_activity );
		$ret[6] = htmlspecialchars( $search );
		$search = '%' . $search . '%';

		$escaped = cerber_real_escape( $search );
		$w = array();
		$w[] = 'log.user_login LIKE "' . $escaped . '"';
		$w[] = 'log.ip LIKE "' . $escaped . '"';

		$where[] = '(' . implode( ' OR ', $w ) . ')';
	}

	if ( $q->filter_sid ) {
		$ret[7] = $sid = preg_replace( '/[^\w]/', '', $q->filter_sid );
		$where[] = 'log.session_id = "' . $sid . '"';
	}

	if ( $q->search_url ) {
		$search  = stripslashes( $q->search_url );
		$ret[8]  = htmlspecialchars( $search );
		$where[] = 'log.details LIKE "' . cerber_real_escape( '%' . $search . '%' ) . '"';
	}

	if ( $q->filter_country ) {
		$country = substr( $q->filter_country, 0, 3 );
		$ret[9]  = htmlspecialchars( $country );
		$where[] = 'log.country = "' . cerber_real_escape( $country ) . '"';
	}

	$where = ( ! empty( $where ) ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Limits, if specified
	$per_page = ( isset( $args['per_page'] ) ) ? absint( $args['per_page'] ) : crb_admin_get_per_page();
	$ret[1]   = $per_page;

	$calc = ( empty( $args['no_navi'] ) ) ? 'SQL_CALC_FOUND_ROWS' : '';

	if ( $per_page ) {
		$limit = cerber_get_sql_limit( $per_page );
		$sql = 'SELECT ' . $calc . ' * FROM ' . CERBER_LOG_TABLE . " log {$where} ORDER BY stamp DESC {$limit}";
	}
	else {
		$sql = 'SELECT ' . $calc . '  log.*,u.display_name,u.user_login ulogin FROM ' . CERBER_LOG_TABLE . ' log LEFT JOIN ' . $wpdb->users . " u ON (log.user_id = u.ID) {$where} ORDER BY stamp DESC"; // "ORDER BY" is mandatory!
	}

	$ret[0] = $sql;

	return $ret;
}

function crb_admin_ip_cell( $ip, $ip_link = '', $text = '' ) {
	static $cache = array();

	if ( isset( $cache[ $ip ] ) ) {
		return $cache[ $ip ];
	}

	$row = true;
	$acl = cerber_acl_check( $ip, '', 0, $row );

	$tip = '';

	if ( ! empty( $row->comments ) ) {
		$tip = $row->comments;
	}
	else {
		if ( $acl == 'W' ) {
			$tip = __( 'White IP Access List', 'wp-cerber' );
		}
        elseif ( $acl == 'B' ) {
			$tip = __( 'Black IP Access List', 'wp-cerber' );
		}
	}

	if ( $b = cerber_block_check( $ip ) ) {
		$block = ' crb_color_blocked ';
		$tip = ( $tip ) ? ' / ' . $b->reason : $b->reason;
	}
	else {
		$block = '';
	}

	if ( $ip_link ) {
		$ip = '<a href="' . $ip_link . '">' . $ip . '</a>';
	}

	$tip = ( $tip ) ? htmlspecialchars( $tip ) : '';
	$cache[ $ip ] = '<div class="crb_css_table"><div><span class="crb_act_icon crb_ip_acl' . $acl . ' ' . $block . '" title="' . $tip . '"></span></div><div>' . $ip . $text . '</div></div>';

	return $cache[ $ip ];
}

/*
 * Additional information about IP address
 */
function cerber_ip_extra_view( $ip, $context = 'activity' ) {
	if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return '';
	}

	$html = crb_generate_ip_extra_view( $ip, $context, true );
	$class = 'crb-extra-info';
	$ajax = '';

	if ( ! $html ) {
		$ajax = ' data-ajax_route="ip_extra_info" data-ip="' . $ip . '"';
		$class .= ' crb_async_content';
		$html = UIS_LOADER_HTML;
	}

	return '<div id="crb-extra-ip-info" class="' . $class . '" ' . $ajax . '>' . $html . '</div>';
}

/**
 * @param array $request
 * @param array $ref_params
 *
 * @return array
 */
function cerber_ip_extra_view_ajax( $request, $ref_params ) {
	$ip = filter_var( $request['ip'], FILTER_VALIDATE_IP );
	$tab = $ref_params['tab'];

	$ret = crb_generate_ip_extra_view( $ip, $tab );

	return array( 'html' => $ret, 'error' => '' );
}

function crb_generate_ip_extra_view( $ip, $context = 'activity', $cache_only = false ) {

	if ( ! $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return '';
	}

	$ip_navs = '';

	if ( $context == 'activity' ) {
		$ip_navs .= cerber_traffic_link( array( 'filter_ip' => $ip ) );
	}
	else {
		$ip_navs .= ' <a class="crb-button-tiny" href="' . cerber_admin_link( 'activity', array( 'filter_ip' => $ip ) ) . '">' . __( 'Check for activities', 'wp-cerber' ) . '</a>';
	}

	$row = true;
	$acl = cerber_acl_check( $ip, '', 0, $row );

	$comments = ( ! empty( $row->comments ) ) ? $row->comments : '';

	$ip_status = '';

	if ( $acl == 'W' ) {
		$ip_status .= '<span title="' . $comments . '" class="crb_color_green crb_ip_info_label">' . __( 'White IP Access List', 'wp-cerber' ) . '</span>';
	}
    elseif ( $acl == 'B' ) {
		$ip_status .= '<span title="' . $comments . '" class="crb_color_black crb_ip_info_label">' . __( 'Black IP Access List', 'wp-cerber' ) . '</span>';
	}

	$key_cache = '_cache_ipx_' . $ip . $context . (string) $acl;

	if ( $block = cerber_block_check( $ip ) ) {
		$ip_status .= '<span class="crb_color_blocked crb_ip_info_label">' . __( 'Locked out', 'wp-cerber' ) . '</span><span class="crb-log-status crb-status-' . $block->reason_id . '">' . $block->reason . '</span>';
		$key_cache .= '_blocked';
	}

	if ( $cache = cerber_get_set( $key_cache, null, false, true ) ) {
		return $cache;
	}
    elseif ( $cache_only ) {
        return '';
	}

	$whois        = '';
	$country      = '';
	$abuse        = '';
	$network      = '';
	$network_info = '';
	$network_navs = '';

	if ( crb_get_settings( 'ip_extra' ) ) {
		$ip_data = cerber_ip_whois_info( $ip );
		if ( isset( $ip_data['error'] ) ) {
			$whois = $ip_data['error'];
		}
        elseif ( isset( $ip_data['whois'] ) ) {
			$whois = $ip_data['whois'];
		}
		if ( isset( $ip_data['country'] ) ) {
			$country = $ip_data['country'];
		}
		if ( ! empty( $ip_data['data']['abuse-mailbox'] ) ) {
			$abuse = '<p>' . __( 'Abuse email:', 'wp-cerber' ) . ' <a href="mailto:' . $ip_data['data']['abuse-mailbox'] . '">' . $ip_data['data']['abuse-mailbox'] . '</a></p>';
		}
		if ( ! empty( $ip_data['data']['network'] ) ) {
			$network = $ip_data['data']['network'];
			$range   = cerber_any2range( $network );
			//$network_info = '<p>' . __( 'Network:', 'wp-cerber' ) . ' ' . $network . ' &nbsp; <a class="crb-button-tiny" href="' . cerber_admin_link( 'activity', array( 'filter_ip' => $range['range'] ) ) . '">' . __( 'Check for activities', 'wp-cerber' ) . '</a> ' . cerber_traffic_link( array( 'filter_ip' => $range['range'] ) );
			$network_info = __( 'Network:', 'wp-cerber' ) . ' ' . $network;
			$network_navs = '<a class="crb-button-tiny" href="' . cerber_admin_link( 'activity', array( 'filter_ip' => $range['range'] ) ) . '">' . __( 'Check for activities', 'wp-cerber' ) . '</a> ' . cerber_traffic_link( array( 'filter_ip' => $range['range'] ) );
		}
	}

	$form = '';

	if ( ! cerber_is_myip( $ip ) && ! cerber_acl_check( $ip ) ) {

		if ( $network ) {
			$net_button = '<button type="submit" value="' . $network . '" name="add_acl" class="button button-secondary cerber-button">';
		}
		else {
			$net_button = '<button disabled="disabled" class="button button-secondary cerber-button">';
		}

		$net_button .= __( 'Add network to the Black List', 'wp-cerber' ) . '</button> ';

		$form = '<form id="add_acl_black" action="" method="post">
                    <input type="hidden" name="cerber_admin_do" value="add2acl">
				    <input type="hidden" name="add_acl_comment" value="">
				    <input type="hidden" name="acl_tag" value="B">
				    <button type="submit" value="' . $ip . '" name="add_acl" class="crb-button-tiny">' . __( 'Add IP to the Black List', 'wp-cerber' ) . '</button>' .
		        '<!-- <p>' . $net_button . '</p>-->' .
		        cerber_nonce_field( 'control' ) .
		        '</form>';
	}

	$ip_info = '<span id="crb-ip-address">' . $ip . '</span><span id="crb-ip-country">' . $country . '</span>';

	$ip_insights = crb_generate_ip_insights( $ip, $context );

	$ret = '
		<div class="crb_extra_info_row">
		
		<div id="crb_the_summary">
		    <div><div style="white-space: nowrap;">' . $ip_info . '</div><div class="crb-nav-row">' . $ip_status . ' ' . $ip_navs . ' ' . $form . '</div></div>
		    <div><div style="white-space: nowrap;">' . $network_info . '</div><div class="crb-nav-row">' . $network_navs . '</div></div>' . $abuse . '
		</div>
		<div class="crb_async_content" data-ajax_route="ip_quick_analytics" data-ip_address="' . $ip . '" data-the_active_tab="' . $context . '">' . $ip_insights . '
		</div>
						
		</div>
		
		<div id="crb-whois-data">' . $whois . '</div>		
		';

	cerber_update_set( $key_cache, $ret, 0, false, time() + 7200, true );

	return $ret;
}

/**
 * @param string $ip
 * @param string $tab
 * @param false $cache_only
 *
 * @return string|false
 */
function crb_generate_ip_insights( $ip, $tab = 'activity', $cache_only = false ) {

	if ( $tab != 'activity' ) {
		$tab = 'traffic';
	}

	$result = '';
	$links = array();

	$labels = cerber_get_labels();

	if ( $data = crb_q_cache_get( 'SELECT COUNT(activity) as cnt, activity FROM ' . CERBER_LOG_TABLE . ' WHERE ip = "' . $ip . '" GROUP BY activity', CERBER_LOG_TABLE, $cache_only ) ) {
		$links[] = array( array( 'filter_ip' => $ip ), __( 'All' ) );
		foreach ( $data as $item ) {
			$links[] = array( array( 'filter_activity' => $item[1], 'filter_ip' => $ip ), crb_array_get( $labels, $item[1], 'Unknown' ) . ' - ' . $item[0] );
		}
	}

	if ( $links ) {
		$result = '<div id="crb-quick-insights">' . crb_make_nav_links( $links, $tab ) . '</div>';
	}

	if ( ! $result ) {
		if ( $cache_only ) {
			return false;
		}

		$result = __( 'No activity has been logged yet.', 'wp-cerber' );
	}

	return $result;
}

/**
 * Additional information about user
 */
function cerber_user_extra_view( $user_id, $context = 'activity' ) {
	global $wpdb;

	if ( ! $u = get_userdata( $user_id ) ) {
		return '';
	}

	$ret = '';
	$class = '';

	$user_info = array();

	if ( ! is_multisite() && $u->roles ) {
		$roles = array();
		$wp_roles = wp_roles();
		foreach ( $u->roles as $role ) {
			$roles[] = $wp_roles->roles[ $role ]['name'];
		}
		$roles = '<span class="crb_act_role">' . implode( ', ', $roles ) . '</span>';
	}
	else {
		$roles = '';
	}

	if ( ! nexus_is_valid_request() ) {
		$name = '<a href="' . get_edit_user_link( $user_id ) . '">' . $u->display_name . '</a>';
	}
	else {
		$name = $u->display_name;
	}

	$name = '<span class="crb-user-name"><b>' . $name . '</b></span><p>' . $roles . '</p>';

	if ( $context == 'activity' ) {
		$link = cerber_traffic_link( array( 'filter_user' => $user_id ) , 2 );
	}
	else {
		$link = ' <a href="' . cerber_admin_link( 'activity', array( 'filter_user' => $user_id ) ) . '">' . __( 'Check for activities', 'wp-cerber' ) . '</a>';
	}

	$user_info[] = array( $name, $link );

	if ( $avatar = (string) get_avatar( $user_id, 96 ) ) {
		$avatar = '<div>' . $avatar . '</div>';
	}

	// Registered
	$reg = false;
	$time = strtotime( cerber_db_get_var( "SELECT user_registered FROM  {$wpdb->users} WHERE id = " . $user_id ) );
	if ( $time ) {
		$reg = cerber_auto_date( $time, false );
		$country = '';
		if ( $rm = get_user_meta( $user_id, '_crb_reg_', true ) ) {
			if ( $rm['IP'] ) {
				$country = crb_country_html( null, $rm['IP'] );
			}
		}

		$user_info[] = array( __( 'Registered', 'wp-cerber' ), $reg, $country );
	}


	// Activated - BuddyPress
	if ( $log = cerber_get_log( array( 200 ), array( 'id' => $user_id ) ) ) {
		$acted = $log[0];
		$activated = cerber_auto_date( $acted->stamp );
		$country = crb_country_html( null, $acted->ip );

		$user_info[] = array( __( 'Activated', 'wp-cerber' ), $activated, $country );
	}


	// Last seen
	$seen = '';
	$s1 = $wpdb->get_row( 'SELECT stamp,ip FROM  ' . CERBER_TRAF_TABLE . ' WHERE user_id = ' . $user_id . ' ORDER BY stamp DESC LIMIT 1' );
	$s2 = $wpdb->get_row( 'SELECT stamp,ip FROM  ' . CERBER_LOG_TABLE . ' WHERE user_id = ' . $user_id . ' ORDER BY stamp DESC LIMIT 1' );

	$time1 = ( $s1 ) ? $s1->stamp : 0;
	$time2 = ( $s2 ) ? $s2->stamp : 0;

	$sn = ( $time1 < $time2 ) ? $s2 : $s1;

	if ( $sn ) {
		//$seen = cerber_auto_date( $sn->stamp );
		$seen = cerber_ago_time( $sn->stamp );
		$country = crb_country_html( null, $sn->ip );
		$user_info[] = array( __( 'Last seen', 'wp-cerber' ), $seen, $country );
	}

	if ( $usn = crb_sessions_get_num( $user_id ) ) {
		$user_info[] = array(
			'<div style="white-space: nowrap;">' . __( 'Active sessions', 'wp-cerber' ) . '</div>',
			'<a href="' . cerber_admin_link( 'sessions', array( 'filter_user' => $user_id ) ) . '">' . $usn . '</a>'
		);
	}

	$summary = '';
	foreach ( $user_info as $row ) {
		$summary .= '<div><div>' . implode( '</div><div>', $row ) . '</div></div>';
	}

	$ins_class = '';
	if ( ! $ins = crb_generate_user_insights( $user_id, $context, true ) ) {
		$ins_class = 'crb_async_content';
	}

	$ret .= '<div class="crb_extra_info_row">' . $avatar . '<div id="crb_the_summary">' . $summary . '</div><div class="' . $ins_class . '" data-ajax_route="user_activity_analytics" data-user_id="' . $user_id . '" data-the_active_tab="' . $context . '">' . $ins . '</div></div>';

	if ( crb_is_user_blocked( $user_id ) ) {
		$class = 'crb-user-blocked';
	}

	return '<div class="crb-extra-info ' . $class . '" id="crb-user-extra-info">' . $ret . '</div>';

}

// Users -------------------------------------------------------------------------------------

add_filter('users_list_table_query_args' , function ($args) {
	if ( crb_get_settings( 'usersort' ) && empty( $args['orderby'] ) ) {
		$args['orderby'] = 'user_registered';
		$args['order'] = 'desc';
    }
    return $args;
});

/*
	Add custom columns to the Users admin screen
*/
add_filter( 'manage_users_columns', function ( $columns ) {
	return array_merge( $columns,
		array(
			'cbcc' => __( 'Comments', 'wp-cerber' ),
			'cbla' => __( 'Last login', 'wp-cerber' ),
			'cbfl' => '<span title="In last 24 hours">' . __( 'Failed login attempts', 'wp-cerber' ) . '</span>',
			'cbdr' => __( 'Registered', 'wp-cerber' )
		) );
} );
add_filter( 'manage_users_sortable_columns', function ( $sortable_columns ) {
	$sortable_columns['cbdr'] = 'user_registered';

	return $sortable_columns;
} );
/*
	Display custom columns on the Users screen
*/
add_filter( 'manage_users_custom_column', function ( $value, $column, $user_id ) {
	global $wpdb, $user_ID, $crb_ajax_loader;
	$ret = $value;
	switch ( $column ) {
		case 'cbcc' : // to get this work we need add filter 'preprocess_comment'
			if ( $com = get_comments( array( 'author__in' => $user_id ) ) ) {
				$ret = count( $com );
			}
			else {
				$ret = 0;
			}
			break;
		case 'cbla' :
		case 'cbfl' :
			$ret = crb_get_ajax_placeholder( $column, $user_id );
			break;
		case 'cbdr' :
			$time = strtotime( cerber_db_get_var( "SELECT user_registered FROM  $wpdb->users WHERE id = " . $user_id ) );
			$ret = cerber_auto_date( $time );
			if ( $rm = get_user_meta( $user_id, '_crb_reg_', true ) ) {
				if ( $rm['IP'] ) {
					$act_link = cerber_admin_link( 'activity', array( 'filter_ip' => $rm['IP'] ) );
					$ret .= '<br /><a href="' . $act_link . '">' . $rm['IP'] . '</a>';
					if ( $country = crb_country_html( null, $rm['IP'] ) ) {
						$ret .= '<br />' . $country;
					}
				}
				$uid = absint( $rm['user'] );
				if ( $uid ) {
					$name = cerber_db_get_var( 'SELECT meta_value FROM ' . $wpdb->usermeta . ' WHERE user_id  = ' . $uid . ' AND meta_key = "nickname"' );
					if ( ! $user_ID ) {
						$user_ID = get_current_user_id();
					}
					if ( $user_ID == $uid ) {
						$name .= ' (' . __( 'You', 'wp-cerber' ) . ')';
					}
					$ret .= '<br />' . $name;
				}
			}
			break;
	}

	return $ret;
}, 10, 3 );

/*
 	Registering admin widgets
*/
if ( ! is_multisite() ) {
	add_action( 'wp_dashboard_setup', 'cerber_widgets' );
}
else {
	add_action( 'wp_network_dashboard_setup', 'cerber_widgets' );
}
function cerber_widgets() {
	if ( cerber_user_can_manage() ) {
		wp_add_dashboard_widget( 'cerber_quick', __( 'Cerber Quick View', 'wp-cerber' ), 'cerber_quick_w' );
	}
}
/*
	Cerber Quick View widget
*/
function cerber_quick_w(){

	$dash    = cerber_admin_link();
	$act     = cerber_admin_link( 'activity' );
	$traf    = cerber_admin_link( 'traffic' );
	$scanner = cerber_admin_link( 'scan_main' );
	$acl     = cerber_admin_link( 'acl' );
	$sess    = cerber_admin_link( 'sessions' );
	$locks   = cerber_admin_link( 'lockouts' );

	$s_count = cerber_db_get_var('SELECT COUNT(DISTINCT user_id) FROM '. cerber_get_db_prefix() . CERBER_USS_TABLE );

	$failed = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (7) AND stamp > '.(time() - 24 * 3600));
	$failed_prev = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (7) AND stamp > '.(time() - 48 * 3600).' AND stamp < '.(time() - 24 * 3600));

	$failed_ch = cerber_percent($failed_prev,$failed);

	$locked = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (10,11) AND stamp > '.(time() - 24 * 3600));
	$locked_prev = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (10,11) AND stamp > '.(time() - 48 * 3600).' AND stamp < '.(time() - 24 * 3600));

	$locked_ch = cerber_percent($locked_prev,$locked);

	//$lockouts = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_BLOCKS_TABLE);

    $lockouts = cerber_blocked_num(); 
	if ($last = cerber_db_get_var('SELECT MAX(stamp) FROM '.CERBER_LOG_TABLE.' WHERE  activity IN (10,11)')) {
		$last = cerber_ago_time( $last );
	}
	else $last = __('Never','wp-cerber');
	$w_count = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_ACL_TABLE .' WHERE tag ="W"' );
	$b_count = cerber_db_get_var('SELECT count(ip) FROM '. CERBER_ACL_TABLE .' WHERE tag ="B"' );

	if ( cerber_is_citadel() ) {
		$citadel = '<span style="color:#FF0000;">' . __( 'active', 'wp-cerber' ) . '</span> (<a href="' . wp_nonce_url( add_query_arg( array( 'citadel' => 'deactivate' ) ), 'control', 'cerber_nonce' ) . '">' . __( 'deactivate', 'wp-cerber' ) . '</a>)';
	}
	else {
		if ( crb_get_settings( 'citadel_on' ) && crb_get_settings( 'ciperiod' ) ) {
			$citadel = __( 'not active', 'wp-cerber' );
		}
		else {
			$citadel = __( 'disabled', 'wp-cerber' );
		}
	}

	echo '<div class="cerber-widget">';

	echo '<table style="width:100%;"><tr><td style="width:50%; vertical-align:top;"><table><tr><td class="bigdig">'.$failed.'</td><td class="per">'.$failed_ch.'</td></tr></table><p>'.__('failed attempts','wp-cerber').' '.__('in 24 hours','wp-cerber').'<br/>(<a href="'.$act.'&filter_activity=7">'.__('view all','wp-cerber').'</a>)</p></td>';
	echo '<td style="width:50%; vertical-align:top;"><table><tr><td class="bigdig">'.$locked.'</td><td class="per">'.$locked_ch.'</td></tr></table><p>'.__('lockouts','wp-cerber').' '.__('in 24 hours','wp-cerber').'<br/>(<a href="'.$act.'&filter_activity[]=10&filter_activity[]=11">'.__('view all','wp-cerber').'</a>)</p></td></tr></table>';

	echo '<table id="quick-info"><tr><td>'.__('Lockouts at the moment','wp-cerber').'</td><td><b><a href="' . $locks . '">'.$lockouts.'</a></b></td></tr>';
	echo '<tr><td>'.__('Last lockout','wp-cerber').'</td><td>'.$last.'</td></tr>';

	echo '<tr class="with-padding"><td>' . __( 'Logged-in users', 'wp-cerber' ) . '</td><td><b><a href="' . $sess . '">' . $s_count . ' ' . _n( 'user', 'users', $s_count, 'wp-cerber' ) . '</a></b></td></tr>';

	echo '<tr class="with-padding"><td>'.__('White IP Access List','wp-cerber').'</td><td><b><a href="'.$acl.'">'.$w_count.' '._n('entry','entries',$w_count,'wp-cerber').'</a></b></td></tr>';
	echo '<tr><td>'.__('Black IP Access List','wp-cerber').'</td><td><b><a href="'.$acl.'">'.$b_count.' '._n('entry','entries',$b_count,'wp-cerber').'</a></b></td></tr>';
	echo '<tr class="with-padding"><td>'.__('Citadel mode','wp-cerber').'</td><td><b>'.$citadel.'</b></td></tr>';

	$status = ( ! crb_get_settings( 'tienabled' ) ) ? '<span style="color: red;">'.__('disabled','wp-cerber').'</span>' : __('enabled','wp-cerber');
	echo '<tr class="with-padding"><td>'.__('Traffic Inspector','wp-cerber').'</td><td><b>'.$status.'</b></td></tr>';

	$lab = lab_lab();
	if ( $lab ) {
		$status = ( ! lab_is_cloud_ok() ) ? '<span style="color: red;">' . __( 'no connection', 'wp-cerber' ) . '</span>' : __( 'active', 'wp-cerber' );
		echo '<tr><td>Cloud Protection</td><td><b>' . $status . '</b></td></tr>';
	}

	$s    = '';
	$scan = cerber_get_scan();
	if ( ! $scan || ( WEEK_IN_SECONDS < ( time() - $scan['finished'] ) ) ) {
		$s = 'style="color: red;"';
	}

	if ( $scan ) {
		$lms = $scan['mode_h'] . ' ' . cerber_auto_date( $scan['started'] );
	}
	else {
		$lms = __( 'Never', 'wp-cerber' );
	}

	echo '<tr ' . $s . '><td>' . _x( 'Last malware scan', 'Example: Last malware scan: 23 Jan 2018', 'wp-cerber' ) . '</td><td><a href="' . $scanner . '">' . $lms . '</a></td></tr>';

	$link = cerber_admin_link( 'scan_schedule' );
	$quick = ( ! $lab || ! $q = absint( crb_get_settings( 'scan_aquick' ) ) ) ? __( 'Disabled', 'wp-cerber' ) : cerber_get_qs( $q );
	echo '<tr><td>' . __( 'Quick Scan', 'wp-cerber' ) . '</td><td><a href="' . $link . '">' . $quick . '</a></td></tr>';
	$f = ( ! $lab || ! crb_get_settings( 'scan_afull-enabled' ) ) ? __( 'Disabled', 'wp-cerber' ) : crb_get_settings( 'scan_afull' );
	echo '<tr><td>' . __( 'Full Scan', 'wp-cerber' ) . '</td><td><a href="' . $link . '">' . $f . '</a></td></tr>';


	/*
	$dev = crb_get_settings('pbdevice');
	if (!$dev || $dev == 'N') echo '<tr><td style="padding-top:15px;">'.__('Push notifications','wp-cerber').'</td><td style="padding-top:15px;"><b>not configured</b></td></tr>';
	*/
	echo '</table></div>';

	echo '<div class="wilinks">
	<a href="'.$dash.'"><i class="crb-icon crb-icon-bxs-dashboard"></i> ' . __('Dashboard','wp-cerber').'</a> |
	<a href="'.$act.'"><i class="crb-icon crb-icon-bx-pulse"></i> ' . __('Activity','wp-cerber').'</a> |
	<a href="'.$traf.'"><i class="crb-icon crb-icon-bx-show"></i> ' . __('Traffic','wp-cerber').'</a> |
	<a href="'.$scanner.'"><i class="crb-icon crb-icon-bx-radar"></i> ' . __('Integrity','wp-cerber').'</a>
	</div>';
	if ( cerber_check_for_newer() ) {
		echo '<div class="up-cerber">' . __( 'A new version is available', 'wp-cerber' ) . '</div>';
	}
}

/*
	Show Help tab screen
*/
function cerber_show_help() {
	switch ( crb_admin_get_page()){
		case 'cerber-integrity':
			cerber_show_scan_help();
			break;
		case 'cerber-nexus':
			cerber_show_nexus_help();
			break;
		case 'cerber-recaptcha':
			cerber_show_anti_help();
			break;
		default:
			cerber_show_general_help();
	}
}

function cerber_show_nexus_help() {
	global $crb_assets_url;
	?>
    <div id="crb-help">
        <table id="admin-help">
            <tr>
                <td>

                    <div>
                        <h2>How remote management works</h2>

                        <p>The technology enables you to manage the WP Cerber plugin, monitor activity, and upgrade plugins on multiple WordPress powered websites from a main WordPress website which is called a master website.</p>

                        <p>To activate this technology, you need to enable a master mode on the main website and a slave mode on each website you want to connect to the master and manage remotely.</p>

                        <p>Read more: <a href="https://wpcerber.com/manage-multiple-websites/" target="_blank">
                                Manage multiple WP Cerber instances from one dashboard</a></p>

                    </div>

                    <div>
                        <h2>A safety note</h2>

                        <p>All access tokens are stored in the databases of the master and slave websites in unencrypted form (plaintext). Store a backup copy of all websites in a safe and trusted place.</p>
                    </div>

                    <div>
                        <h2>Troubleshooting</h2>
                        <p>
                            If you’re unable to get it working, that may be caused by a number of reasons. Enable the diagnostic log on the master and on the slave to obtain more information. You can view the log on the Tools admin page. Here is a list of the most common causes of issues on the slave side.
                        </p>

                        <ul>
                            <li>A security plugin on the slave website is interfering with the WP Cerber plugin</li>
                            <li>A security directive in the .htaccess file on the slave website is blocking incoming requests as suspicious</li>
                            <li>A firewall or a proxy service (like Cloudflare) is blocking (filtering out) incoming requests to the slave website</li>
                            <li>The IP address of the master is locked out or in the Black Access List on the slave website</li>
                            <li>The slave mode on the remote website has been re-enabled making the security token saved on the master invalid</li>
                            <li>The IP address of the master website does not match the one set in the slave settings on the remote website</li>
                        </ul>

                    </div>

                </td>
                <td>

                    <div>
                        <h2>Getting started on the master</h2>

                        <p>To configure the main, master website go to the Cerber.Hub admin page and enable master mode. Once you’ve done this you can add slave websites by using security tokens generated on slaves.
                        </p>

                    </div>

                    <div>
                        <h2>Adding slave websites</h2>

                        <p>To add a slave website to the master, you need to enable the slave mode on the website. Go to the Cerber.Hub admin page and enable the slave mode. During the activation of the slave mode, a unique security access token is generated and saved into the database of the slave. Keep the token secret.
                        </p>
                        <p>
                            Go to the master website and click the “Add” button on the “My Websites” admin page. Copy the token and paste it in the “Add a salve website” popup window.
                        </p>


                    </div>

                    <div>
                        <h2>Manage websites remotely</h2>

                        <p>Once you’ve added all slave websites on the master you can easily switch between them with a single click in a top navigation menu on the admin bar or by clicking the name of a slave on the “My Websites” master page. Once you’ve switched to a slave website use the plugin menu and admin links the way like you do this normally. To switch back to the master website, click a X icon on the admin bar.
                        </p>
                        <p>
                            Note: when you’re managing remote website, the color of the admin bar is blue and the left admin menu on the master is dimmed.
                        </p>
                    </div>

                </td>
            </tr>
        </table>
    </div>
	<?php

}

function cerber_show_scan_help() {
	global $crb_assets_url;
	?>
    <div id="crb-help">
        <table id="admin-help">
            <tr>
                <td>

                    <div>
                        <h2>Using the malware scanner</h2>

                        <p>To start scanning, click either the Start Quick Scan button or the Start Full Scan button. Do
                            not close the browser window while the scan is in progress. You may just open a new browser
                            tab to do something else on the website. Once the scan is finished you can close the window,
                            the results are stored in the DB until the next scan.</p>

                        <p>Depending on server performance and the number of files, the Quick scan may take about 3-5
                            minutes and the Full scan can take about ten minutes or less.</p>

                        <p>During the scan, the plugin verifies plugins, themes, and WordPress by trying to retrieve
                            checksum data from wordpress.org. If the integrity data is not available, you can upload an
                            appropriate source ZIP archive for a plugin or a theme. The plugin will use it to detect
                            changes in files. You need to do it once, after the first scan.</p>

                        <p>Read more: <a href="https://wpcerber.com/malware-scanner-settings/" target="_blank">Cerber
                                Security Scanner Settings</a></p>

                    </div>

                    <div>
                        <h2>Interpreting scan results</h2>

                        <p>The scanner shows you a list of issues and possible actions you can take. If the integrity of
                            an object has been verified, you see a green mark Verified. If you see the “Integrity data
                            not found” message, you need to upload a reference ZIP archive by clicking “Resolve issue”.
                            For all other issues, click on an appropriate issue link. To view the content of a file,
                            click on its name.</p>
                    </div>

                    <div>
                        <h2>Troubleshooting</h2>

                        <p>If the scanner window stops responding or updating, it usually means the process of scanning on the server is hung. This might happen due to a number of reasons but typically this happens due to a misconfigured server or it’s caused by some hosting limitations. Do the following:</p>

                        <ul>
                            <li>Open the browser console (use F12 key on PC or Cmd + Option + J on Mac) and check it for CERBER ERROR messages</li>
                            <li>Try to disable scanning the session directory or the temp directory (or both) in the scanner settings</li>
                            <li>Enable diagnostic logging in the scanner settings and check the log after scanning</li>
                        </ul>

                        <p>Note: The scanner requires the cURL library to be enabled for PHP scripts. Usually, it's enabled by
                            default.</p>

                        <p>Read more: <a href="https://wpcerber.com/wordpress-security-scanner/" target="_blank">Malware
                                Scanner & Integrity Checker</a></p>

                    </div>

                    <div>
                        <h2>What's the Quick Scan?</h2>

                        <p>During the Quick Scan, the scanner verifies the integrity and inspects the code of all files
                            with executable extensions only.</p>

                        <h2>What's the Full Scan?</h2>

                        <p>During the Full Scan, the scanner verifies the integrity and inspects the content of all
                            files on the website. All media files are scanned for malicious payload.</p>

                        <p>Read more: <a href="https://wpcerber.com/wordpress-security-scanner-scan-malware-detect/"
                                         target="_blank">What Cerber Security Scanner scans and detects</a>
                    </div>

                </td>
                <td>

                    <div>
                        <h2>Configuring scheduled scans</h2>

                        <p>In the Automated recurring scan schedule section you set up your schedule. Select the desired
                            frequency of the Quick Scan and specify the time of the Full Scan. By default, all automated
                            recurring scans are turned off.
                        </p>

                        <p>The Scan results reporting section is about reporting. Here you can easily and flexibly
                            configure conditions for generating and sending reports.
                        </p>

                        <p>The email report will only include issues that match conditions in the Report an issue if any
                            of the following is true filter. So this setting works as a filter for issues you want to
                            get in a email report. The report will only be sent if there are issues to report and the
                            following condition is true.</p>

                        <p>The second condition is configured with Send email report. The report will be sent if a
                            selected condition is true. The last option is the most restrictive.</p>

                        <p>Read more: <a href="https://wpcerber.com/automated-recurring-malware-scans/" target="_blank">Automated
                                recurring scans and email reporting</a></p>

                    </div>

                    <div>
                        <h2>Automatic cleanup of malware</h2>

                        <p>The plugin can automatically delete malicious and suspicious files. Automatic removal
                            policies are enforced at the end of every scheduled scan based on its results. The list of
                            files to be deleted depends on the scanner settings. By default automatic removal is
                            disabled. It's advised to enable it at least for unattended files and files in the media
                            uploads folder for files with the High severity risk. The plugin deletes only files that
                            have malicious or suspicious code payload. All detected malicious and suspicious files are
                            moved to the quarantine.
                        </p>

                        Read more: <a href="https://wpcerber.com/automatic-malware-removal-wordpress/" target="_blank">Automatic cleanup of malware and suspicious files</a>

                    </div>

                    <div>
                        <h2>Deleting files</h2>

                        <p>Usually, you can delete any suspicious or malicious file if it has a checkbox in its row in
                            the leftmost cell. Before deleting a file, click the issue link in its row to see an
                            explanation. When you delete a file WP Cerber moves it to a quarantine folder.</p>
                    </div>

                    <div>
                        <h2>Restoring deleted files</h2>

                        <p>If you delete an important file by chance, you can restore the file from the quarantine. To
                            restore one or more files from within the WordPress dashboard, click the Quarantine tab.
                            Find the filename in the File column and click Restore in the Action column. The file will
                            be restored to its original location.</p>

                        <p>To restore a file manually, you need to use a file manager in your hosting control panel. All
                            deleted files are stored in a special quarantine folder. The location of the folder is shown
                            on the Tools / Diagnostic admin page. The original name and location of a deleted file are
                            saved in a .restore file. It’s a text file. Open it in a browser or a file viewer, find the
                            filename you need to restore in a list of deleted files and copy the file back to its
                            location by using the original name and location of the file.
                        </p>

                    </div>

                </td>
            </tr>
        </table>
    </div>
	<?php

}

function cerber_show_anti_help() {
	global $crb_assets_url;
	?>
    <div id="crb-help">
        <table id="admin-help">
            <tr>
                <td>
	                <?php

	                cerber_help();

	                ?>

                </td>
                <td>
                    <h3>Setting up anti-spam protection</h3>

                    <p>
                        The Cerber anti-spam and bot detection engine is capable to protect virtually any form on a website. It’s a great alternative to reCAPTCHA.
                        Tested with Caldera Forms, Gravity Forms, Contact Form 7, Ninja Forms, Formidable Forms, Fast Secure Contact Form, Contact Form by WPForms and WooCommerce forms.
                    </p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/how-to-stop-spam-user-registrations-wordpress/">How to stop spam user registrations on your WordPress</a></p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/antispam-for-wordpress-contact-forms/">How to stop spam form submissions on your WordPress</a></p>

                    <h3>Configuring exceptions for the anti-spam engine</h3>

                    <p>
                        Usually, you need to specify an exception if you use a plugin or some technology that communicates with your website by submitting forms or sending POST requests programmatically. In this case, Cerber can block these legitimate requests because it recognizes them as generated by bots. This may lead to multiple false positives which you can see on the Activity tab. These entries are marked as <b>Spam form submission denied</b>.
                    </p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a href="https://wpcerber.com/antispam-exception-for-specific-http-request/" target="_blank">Configuring exceptions for the anti-spam engine</a></p>

                    <h3>How to set up reCAPTCHA</h3>

                    <p>

                        Before you can start using reCAPTCHA on the website, you have to obtain a Site key and a Secret key on the Google website. To get the keys you have to have Google account.

                        Register your website and get both keys here: <a href="" target="_blank" rel="noopener noreferrer">https://www.google.com/recaptcha/admin</a>

                        Note: If you are going to use an invisible version, you must get and use Site key and a Secret key for the invisible version only.

                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/how-to-setup-recaptcha/">How to set up reCAPTCHA in details</a></p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/why-recaptcha-does-not-protect-wordpress/">Why does reCAPTCHA not protect WordPress against bots and brute-force attacks?</a></p>
                    </p>

                </td>
            </tr>
        </table>
    </div>
	<?php

}

function cerber_show_general_help() {
    global $crb_assets_url;

	?>
	<div id="crb-help">
        <table id="admin-help">
            <tr><td>

                    <?php

                    cerber_help();

                    ?>


                    <h3>Troubleshooting</h3>

                    <p><a href="https://wpcerber.com/antispam-exception-for-specific-http-request/" target="_blank">Configuring exceptions for the antispam engine</a></p>

                    <p><a href="https://wpcerber.com/wordpress-probing-for-vulnerable-php-code/" target="_blank">I’m getting "Probing for vulnerable code"</a></p>

                    <p><a href="https://wpcerber.com/firewall-http-requests-are-being-blocked/" target="_blank">Some legitimate HTTP requests are being blocked</a></p>

                    <p><a href="https://wpcerber.com/php-warning-cannot-modify-header-information/" target="_blank">PHP Warning: Cannot modify header information – headers already sent in</a></p>

                    <h3>Traffic Inspector</h3>

                    <p>Traffic Inspector is a set of specialized request inspection algorithms that acts as an additional protection layer (firewall) for your WordPress</p>

                    <p>
                        <span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank"
                                                                                               href="https://wpcerber.com/traffic-inspector-in-a-nutshell/">Traffic
                            Inspector in a nutshell</a>
                    </p>

                    <h3>What's new in this version of the plugin?</h3>

                    <p><a href="https://wpcerber.com/security/releases/">Check out release notes</a></p>


                </td>
                <td>
                    <h3>Online Documentation</h3>

                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/toc/">All articles on wpcerber.com</a></p>
                    <p><span class="dashicons dashicons-before dashicons-shield-alt"></span> <a target="_blank" href="https://wpcerber.com/how-to-protect-wordpress-checklist/">How to protect WordPress effectively: a must-do list</a></p>

                    <h3>What is IP address of your computer?</h3>

                    <p>To find out your current IP address go to this page: <a target="_blank" href="https://wpcerber.com/what-is-my-ip/">What is my IP</a>. If you see a different IP address on the Activity tab for your login or logout events, try to enable <b><?php _e('My site is behind a reverse proxy','wp-cerber'); ?></b>.</p>
                    <p>
                        <span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/wordpress-ip-address-detection/">Solving problem with incorrect IP address detection</a>
                    </p>


                    <h3>Setting up antispam protection</h3>

                    <p>
                        The Cerber antispam and bot detection engine is capable to protect virtually any form on a website. It’s a great alternative to reCAPTCHA.
                    </p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/how-to-stop-spam-user-registrations-wordpress/">How to stop spam user registrations on your WordPress</a></p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/antispam-for-wordpress-contact-forms/">Antispam protection for contact forms</a></p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a href="https://wpcerber.com/antispam-exception-for-specific-http-request/" target="_blank">Configuring exceptions for the antispam engine</a></p>


                    <h3>Mobile and browser notifications with Pushbullet</h3>

                    <p>
                        WP Cerber allows you to easily enable desktop and mobile notifications and get notifications instantly and for free. In a desktop browser, you will get popup messages even if you logged out of your WordPress.
                        Before you start receiving notifications you need to install a free Pushbullet mobile application on your mobile device or free browser extension available for Chrome, Firefox and Opera.
                    </p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span>
                        <a target="_blank" href="https://wpcerber.com/wordpress-mobile-and-browser-notifications-pushbullet/">A three steps instruction how to set up push notifications</a>
                    </p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span>
                        <a target="_blank" href="https://wpcerber.com/wordpress-notifications-made-easy/">How to get alerts for specific activity on your website</a>
                    </p>

                    <h3>WordPress security explained</h3>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/why-recaptcha-does-not-protect-wordpress/">Why does reCAPTCHA not protect WordPress against bots and brute-force attacks?</a></p>
                    <p><span class="dashicons dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/why-we-need-to-use-custom-login-url/">Why you need to use Custom login URL for your WordPress</a></p>
                    <p><span class="dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/why-its-important-to-restrict-access-to-rest-api/">Why it's important to restrict access to the WP REST API</a></p>
                    <p><span class="dashicons-before dashicons-book-alt"></span> <a target="_blank" href="https://wpcerber.com/mitigating-brute-force-dos-and-ddos-attacks/">Brute-force, DoS, and DDoS attacks - what's the difference?</a></p>
                </td>
            </tr>
        </table>

		<h3>What is Drill down IP?</h3>

		<p>
			To get extra information like country, company, network info, abuse contact etc. for a specific IP address,
			the plugin makes requests to a limited set of external WHOIS servers which are maintained by appropriate
			Registry. All Registry are accredited by ICANN, so there are no reasons for security concerns. Retrieved
			information isn't storing in the database, but it is caching for up to 24 hours to avoid excessive requests and
			get faster response.
		</p>
		<p><span class="dashicons-before dashicons-info" style="vertical-align: middle;"></span> <a
				href="https://wpcerber.com?p=194">Read more in the Security Blog</a></p>

		<h3>What is Cerber Lab?</h3>

		<p>
			Cerber Laboratory is a forensic team at Cerber Tech Inc. The team studies and analyzes
			patterns of hacker and botnet attacks, malware, vulnerabilities in major plugins and how they are
			exploitable on WordPress powered websites.
		</p>
			<p><span class="dashicons-before dashicons-info" style="vertical-align: middle;"></span>
			<a href="https://wpcerber.com/cerber-laboratory/">Know more</a>
			</p>

		<h3>Do you have an idea for a cool new feature that you would love to see in WP Cerber?</h3>

		<p>
			Feel free to submit your ideas here: <a href="https://wpcerber.com/new-feature-request/">New Feature
				Request</a>.
		</p>

		<h3>Are you ready to translate this plugin into your language?</h3>

		<p>I would appreciate that! Please, <a href="https://wpcerber.com/support/">notify me</a></p>

		<h3 style="margin: 40px 0 40px 0;">Check out other plugins from the trusted author</h3>

		<div>

			<a href="https://wordpress.org/plugins/plugin-inspector/">

				<img src="<?php echo $crb_assets_url . 'inspector.png' ?>"
				     style="float: left; width: 128px; margin-right: 20px;"/>
			</a>
			<h3>Plugin for inspecting code of plugins on your site: <a
					href="https://wordpress.org/plugins/plugin-inspector/">Plugin Inspector</a></h3>
			<p style="font-size: 110%">The Plugin Inspector plugin is an easy way to check plugins installed on your
				WordPress and make sure
				that plugins does not use deprecated WordPress functions and some unsafe functions like eval,
				base64_decode, system, exec etc. Some of those functions may be used to load malicious code (malware)
				from the external source directly to the site or WordPress database.
			</p>
			<p style="font-size: 110%">Plugin Inspector allows you to view all the deprecated functions complete with
				path, line number,
				deprecation function name, and the new recommended function to use. The checks are run through a simple
				admin page and all results are displayed at once. This is very handy for plugin developers or anybody
				who want to know more about installed plugins.
			</p>
		</div>

		<div style="margin: 40px 0 40px 0;">
			<a href="https://wordpress.org/plugins/goo-translate-widget/">
				<img src="<?php echo $crb_assets_url . 'goo-translate.png' ?>"
				     style="float: left; width: 128px; margin-right: 20px;"/>
			</a>

			<h3>Plugin to quick translate site: <a href="https://wordpress.org/plugins/goo-translate-widget/">Google
					Translate Widget</a></h3>
			<p style="font-size: 110%">Google Translate Widget expands your global reach quickly and easily. Google Translate is a free
				multilingual machine translation service provided by Google to translate websites. And now you can allow
				visitors around of the world to get your site in their native language. Just put widget on the sidebar
				with one click.</p>

		</div>

	</div>
	<?php
}

function cerber_help() {
	global $crb_assets_url;

	if ( lab_lab() ) {
		$support = '<p style="margin: 2em 0 5em 0;">Submit a support ticket on our Help Desk: <a href="https://my.wpcerber.com/">https://my.wpcerber.com</a></p>';
	}
	else {
		$support = '<p>Support for the free version is provided on the <a target="_blank" href="https://wordpress.org/support/plugin/wp-cerber">WordPress forum only</a>, though, please note, that it is free support hence it is
                        not always possible to answer all questions on a timely manner, although we do try.</p>
                        
                        <p><a href="https://wpcerber.com/pro/" class="crb-button-tiny">If you need professional and priority support 24/7/365, please buy a PRO license</a></p>';
	}

	?>

    <img style="width: 120px; float: left; margin-right: 30px; margin-bottom: 30px;" src="<?php echo $crb_assets_url . 'wrench.png' ?>"/>

    <h3 style="font-size: 150%;">How to configure the plugin</h3>

    <p style="font-size: 120%;">To get the most out of WP Cerber Security, you need to configure the plugin properly</p>

    <p style="font-size: 120%;">Please read this first: <a target="_blank" href="https://wpcerber.com/getting-started/">Getting Started Guide</a></p>

    <p style="clear: both;"></p>

    <h3>Do you have a question or need help?</h3>

	<?php echo $support; ?>

    <p><span class="dashicons dashicons-before dashicons-format-chat"></span> <a target="_blank" href="https://wordpress.org/support/plugin/wp-cerber">Get answer on the support forum</a></p>

    <form style="margin-top: 2em;" action="https://wpcerber.com" target="_blank">
        <h3>Search plugin documentation on wpcerber.com</h3>
        <input type="text" style="width: 80%;" name="s" placeholder="Enter term to search"><input type="submit" value="Search" class="button button-primary">
    </form>

	<?php
}

/**
 *
 * WP Cerber Dashboard
 *
 *
 */
function cerber_show_dashboard() {

	$kpi_list = cerber_calculate_kpi( 1 );

	$kpi_show = '';
	foreach ( $kpi_list as $kpi ) {
		$kpi_show .= '<td>' . $kpi[1] . '</td><td><span style="z-index: 10;">' . $kpi[0] . '</span></td>';
	}

	$kpi_show = '<table id = "crb-kpi" class=""><tr>' . $kpi_show . '</tr></table>';

	// TODO: add link "send daily report to my email"
	// $kpi_show .= '<p style="text-align: right; margin: 0;">' . __( 'in the last 24 hours', 'wp-cerber' ) . '</p>';
	echo '<div title="In the last 24 hours">' . $kpi_show . '</div>';

	$logged = cerber_db_get_var( 'SELECT ip FROM ' . CERBER_LOG_TABLE. ' LIMIT 1' );
	$nav_links = ( $logged ) ? crb_admin_activity_nav_links( 'users' ) : '';
	$placeholder = '<div class="crb-act-padding crb-dash-placeholder">' . __( 'No activity has been logged yet.', 'wp-cerber' ) . '</div>';

	if ( ! $logged ) {
		$user_act = $placeholder;
	}
	else {
		$user_act = cerber_show_activity( array(
			'filter_user' => '*',
			'per_page'    => 10,
			'no_navi'     => true,
			'no_export'   => true,
			'no_details'  => true,
			'date'        => 'ago'
		), false );
	}

	$dash_widgets[] = array( __( "Users' Activity", 'wp-cerber' ), $user_act, $nav_links );

	$in = implode( ',', crb_get_filter_set( 1 ) );
	$bad_logged = cerber_db_get_var( 'SELECT ip FROM ' . CERBER_LOG_TABLE . ' WHERE activity IN (' . $in . ') LIMIT 1' );

	if ( ! $bad_logged ) {
		$susp_act = $placeholder;
	}
	else {
		$susp_act = cerber_show_activity( array(
			'filter_set' => 1,
			'per_page'    => 10,
			'no_navi'     => true,
			'no_export'   => true,
			'no_details'  => true,
			'date'        => 'ago'
		), false );
	}

	$nav_links = ( $bad_logged ) ? crb_admin_activity_nav_links( 'suspicious' ) : '';
	$dash_widgets[] = array( __( 'Malicious Activity', 'wp-cerber' ), $susp_act, $nav_links );

	/*$total = cerber_db_get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE );

	$nav_links = ( $total ) ? crb_admin_quick_nav( 'dashboard' ) : '';

	echo '<table class="crb-table-heading cerber-margin"><tr><td><h2 style="margin-bottom:0.5em; margin-right: 1em;">' . __( 'Activity', 'wp-cerber' ) . '</h2></td><td>' . $nav_links . '</td></tr></table>';

	/*cerber_show_activity( array(
		'filter_activity' => crb_get_activity_set( 'dashboard' ),
		'per_page'        => 10,
		'no_navi'         => true,
		'no_export'       => true,
		'no_details'      => true,
		'date'            => 'ago'
	) );*/

	$locked = cerber_blocked_num();
	if ( ! $locked ) {
		$view_all = '';
		$locked_ips = '<div class="crb-act-padding crb-dash-placeholder">' . __( 'No lockouts at the moment. The sky is clear.', 'wp-cerber' ) . '</div>';
	}
	else {
		$view_all = '<a href="' . cerber_admin_link( 'lockouts' ) . '">' . __( 'View all', 'wp-cerber' ) . '</a>';
		$locked_ips = cerber_show_lockouts( array(
			'per_page' => 10,
			'no_navi'  => true
		,
		), false );
	}

	$dash_widgets[] = array( __( 'Recently locked out IP addresses', 'wp-cerber' ), $locked_ips, $view_all );

	foreach ( $dash_widgets as $dash_widget ) {
		$heading = '<table class="crb-table-heading"><tr><td><h2 class="crb-act-padding">' . $dash_widget[0] . '</h2></td><td>' . $dash_widget[2] . '</td></tr></table>';
		echo '<div class="crb-dashboard-element">' . $heading . $dash_widget[1] . '</div>';
	}
}


/*
	Admin aside bar
*/
function cerber_show_aside( $tab ) {
	global $crb_assets_url;

	if ( in_array( $tab, array( 'nexus_sites', 'activity', 'lockouts', 'traffic' ) ) ) {
		return;
	}

	$aside = array();

	if ( $tab != 'scan_main' && ! lab_lab() && crb_was_activated( 1 * DAY_IN_SECONDS ) ) {
		/*$aside[] =
            '<a class="crb-button-one" style="background-color: #1DA1F2;" href="https://twitter.com/wpcerber" target="_blank"><span class="dashicons dashicons-twitter"></span> Follow Cerber on Twitter</a>';
		*/

        // <a class="crb-button-one" href="https://wpcerber.com/subscribe-newsletter/" target="_blank"><span class="dashicons dashicons-email-alt"></span> Subscribe to Cerber\'s newsletter</a>
        // <a class="crb-button-one" style="background-color: #3B5998;" href="https://www.facebook.com/wpcerber/" target="_blank"><span class="dashicons dashicons-facebook"></span> Follow Cerber on Facebook</a>
	    //';


		$images = array( 'bn4ra.png', 'bn5ra.png' );
		$d = (int) date( 'z' );
		$n = ( $d & 1 ) ? 1 : 0;
		$ban = $crb_assets_url . $images[ $n ];

        $aside[] = '<a href="https://wpcerber.com/pro/" target="_blank"><img src="'.$ban.'" class="crb-full-width" /></a>';

	}

	$context_links = '';
	$docs = crb_get_doc_links();
	foreach ( $docs as $doc ) {
		$context_links .= '<p><a href="' . $doc[0] . '" target="_blank">' . $doc[1] . '</a></p>';
	}

	$aside[] = '<div class="crb-box" id="crb-blog"><div class="crb-box-inner"> 
			<h3>Documentation & How To</h3>           
            ' . $context_links . '</div></div>';

	if ( ! lab_lab() && crb_was_activated( 2 * WEEK_IN_SECONDS ) ) {
		$r = array();
		$r[0] = crb_get_review_url( 'tpilot' );
		$r[1] = crb_get_review_url( 'wp' );
		shuffle( $r );
		$aside[] = '<a href="' . $r[0] . '" target="_blank"><img class="crb-full-width" src="' . $crb_assets_url . 'fb2b.png" /></a>';
	}

	echo '<div id="crb-aside">' . implode( ' ', $aside ) . '</div>';
}

function crb_get_doc_links() {
	static $links = array(
		'cerber-integrity' => array(
			array( 'https://wpcerber.com/wordpress-security-scanner/', 'How to use the integrity checker and malware scanner' ),
			array( 'https://wpcerber.com/wordpress-security-scanner-scan-malware-detect/', 'What the scanner scans and detects' ),
			array( 'https://wpcerber.com/malware-scanner-settings/', 'Configuring the scanner settings' ),
			array( 'https://wpcerber.com/troubleshooting-malware-scanner/', 'Troubleshooting malware scanner issues' ),
			array( 'https://wpcerber.com/automatic-malware-removal-wordpress/', 'Automatic cleanup of malware and suspicious files' ),
			array( 'https://wpcerber.com/automated-recurring-malware-scans/', 'Automated recurring scans and email reporting' )
		),
		'*' => array(
			array( 'https://wpcerber.com/wordpress-application-passwords-how-to/', 'Managing WordPress application passwords a hassle-free way' ),
			array( 'https://wpcerber.com/how-to-protect-wordpress-checklist/', 'How to protect WordPress effectively: a must-do list' ),
			array( 'https://wpcerber.com/manage-multiple-websites/', 'Managing multiple WP Cerber instances from one dashboard' ),
			array( 'https://wpcerber.com/wordpress/gdpr/', 'Stay in compliance with GDPR' ),
			array( 'https://wpcerber.com/two-factor-authentication-for-wordpress/', 'How to protect user accounts with Two-Factor Authentication' ),
			array( 'https://wpcerber.com/limiting-concurrent-user-sessions-in-wordpress/', 'How to stop your users from sharing their WordPress accounts' ),
			array( 'https://wpcerber.com/wordpress-mobile-and-browser-notifications-pushbullet/', 'Be notified with mobile and browser notifications' ),
			array( 'https://wpcerber.com/wordpress-notifications-made-easy/', 'WordPress notifications made easy' ),
			array( 'https://wpcerber.com/restrict-access-to-wordpress-rest-api/', 'How to restrict access to REST API' ),
            array( 'https://wpcerber.com/automatic-malware-removal-wordpress/', 'Automatic cleanup of malware and suspicious files' ),
			array( 'https://wpcerber.com/automated-recurring-malware-scans/', 'Automated recurring scans and email reporting' ),
		),
	);

	static $get_started = array( 'https://wpcerber.com/getting-started/', '<b>Getting Started Guide</b>' );

	$ret = crb_array_get( $links, crb_admin_get_page() );
	$common = crb_array_get( $links, '*' );

	if ( ! $ret ) {
		array_unshift( $common, $get_started );

		return $common;
	}

	$ret = array_merge( $ret, array_slice( $common, 0, 5 ) );
	$ret[] = $get_started;

	return $ret;
}

/*
	Displaying notices in the dashboard
*/
add_action( 'load-plugins.php', function () {
	add_action( 'admin_notices', 'cerber_show_admin_notice', 999 );
	add_action( 'network_admin_notices', 'cerber_show_admin_notice', 999 );
} );
function cerber_show_admin_notice(){
	global $cerber_shown;
	$cerber_shown = false;

	if ( cerber_is_citadel() && cerber_user_can_manage() ) {
		echo '<div class="update-nag crb-alarm"><p>' .
		     __( 'Attention! Citadel mode is now active. Nobody is able to log in.', 'wp-cerber' ) .
		     ' &nbsp; <a href="' . wp_nonce_url( add_query_arg( array( 'citadel' => 'deactivate' ) ), 'control', 'cerber_nonce' ) . '">' . __( 'Deactivate', 'wp-cerber' ) . '</a>' .
		     ' | <a href="' . cerber_admin_link( 'activity' ) . '">' . __( 'View Activity', 'wp-cerber' ) . '</a>' .
		     '</p></div>';
	}

	if ( ! nexus_is_valid_request() && ! cerber_is_admin_page( true ) ) {
		return;
	}

	//if ($notices = get_site_option('cerber_admin_notice'))
	//	echo '<div class="update-nag crb-note"><p>'.$notices.'</p></div>'; // class="updated" - green, class="update-nag" - yellow and above the page title,
	//if ($notices = get_site_option('cerber_admin_message'))
	//	echo '<div class="updated" style="overflow: auto;"><p>'.$notices.'</p></div>'; // class="updated" - green, class="update-nag" - yellow and above the page title,

	$all = array();
	if ( ! empty( $_GET['settings-updated'] ) ) {
		$all[] = array( __( 'Settings saved', 'wp-cerber' ), 'updated' );
	}

	if ( $notice = cerber_get_set( 'admin_notice' ) ) {
		if ( is_array( $notice ) ) {
			$notice = '<p>' . implode( '</p><p>', $notice ) . '</p>';
		}
		$all[] = array( $notice, 'error' ); // red
		cerber_update_set( 'admin_notice', array() );
	}

	if ( $message = cerber_get_set( 'admin_message' ) ) {
		if ( is_array( $message ) ) {
			$message = '<p>' . implode( '</p><p>', $message ) . '</p>';
		}
		$all[] = array( $message, 'updated' ); // green
		cerber_update_set( 'admin_message', array() );
	}

	if ( $all ) {
		$cerber_shown = true;
		foreach ( $all as $notice ) {
			echo '<div id="setting-error-settings_updated" class="' . $notice[1] . ' settings-error notice is-dismissible crb-admin-message"> 
		' . $notice[0] . '<button type="button" class="notice-dismiss crb-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
	}

	if ( $wide = cerber_get_set( 'cerber_admin_wide', null, false ) ) {
		crb_show_admin_announcement( $wide, false );
		$cerber_shown = true;
		cerber_update_set( 'cerber_admin_wide', '' );
	}

	if ( $cerber_shown || ! cerber_is_admin_page() ) {
		return;
	}

	if ( $msg = get_site_option( 'cerber_admin_info' ) ) {
		crb_show_admin_announcement( $msg );
		$cerber_shown = true;

		return;
	}

	lab_opt_in();
}

/**
 * Generates a link for subscribing on a currently displaying Activity page
 *
 * @return string Link for using in the Dashboard, HTML
 */
function crb_admin_alert_link() {
	$args = array_values( cerber_get_alert_params() );

	// All activities, without any filter is not allowed
	$empty = array_filter( $args );
	if ( empty( $empty ) ) {
		return '';
	}

	$subs = cerber_get_site_option( '_cerber_subs' );

	// Limit to the number of subscriptions
	if ( $subs && count( $subs ) > 50 ) {
		return '';
	}

	$mode = 'on';
	if ( $subs ) {
		$hash = sha1( json_encode( $args ) );
		if ( recursive_search_key( $subs, $hash ) ) {
			$mode = 'off';
		}
	}

	$link = cerber_admin_link_add( array( 'cerber_admin_do' => 'subscribe', 'mode' => $mode ), true );

	if ( $mode == 'on' ) {
		$text = __( 'Create Alert', 'wp-cerber' );
		$icon = 'crb-icon-bx-bell';
	}
	else {
		$text = __( 'Delete Alert', 'wp-cerber' );
		$icon = 'crb-icon-bx-bell-off';
	}


	//return '<span class="dashicons dashicons-email" style="vertical-align: middle;"></span> <a id="subscribe-me" href="' . $link . '" style="margin-right: 1.5em;">'.$text.'</a>';
	return '<a id="subscribe-me" class="button button-secondary cerber-button" href="' . $link . '" style="margin-right: 0.5em;"><i class="crb-icon ' . $icon . '"></i> ' . $text . '</a>';

}

/**
 * Managing the list of subscriptions
 *
 * @param string $mode Add or delete a subscription
 * @param string $hash If specified, subscription with given hash will be removed
 */
function crb_admin_alerts_do( $mode = 'on', $hash = null ) {
	if ( $hash ) {
		$mode = 'off';
	}
	else {
		$args = array_values( cerber_get_alert_params() );
		$hash = sha1( json_encode( $args ) );
	}

	$subs = get_site_option( '_cerber_subs' );

	if ( ! $subs ) {
		$subs = array();
	}

	if ( $mode == 'on' ) {
		$subs[ $hash ] = $args;
		$msg           = __( "The alert has been created", 'wp-cerber' );
	}
	else {
		unset( $subs[ $hash ] );
		$msg = __( "The alert has been deleted", 'wp-cerber' );
	}

	if ( update_site_option( '_cerber_subs', $subs ) ) {
		cerber_admin_message( $msg );
	}
}

// Unsubscribe with a secret hash
function cerber_unsubscribeme() {
	if ( $hash = cerber_get_get( 'unsubscribeme', '[a-z\d]+' ) ) {
		crb_admin_alerts_do( 'off', $hash );
		wp_safe_redirect( remove_query_arg( 'unsubscribeme' ) );
		exit;
	}
}

/*
	Pagination
*/
function cerber_page_navi( $total, $per_page ) {
	$max_links = 10;
	if ( ! $per_page ) {
		$per_page = 25;
	}
	$page = cerber_get_pn();

	$base_url = esc_url( remove_query_arg( 'pagen', add_query_arg( crb_get_query_params(), cerber_admin_link() ) ) );

	$last_page = ceil( $total / $per_page );
	$ret = '';
	if ( $last_page > 1 ) {
		$start = 1 + $max_links * intval( ( $page - 1 ) / $max_links );
		$end = $start + $max_links - 1;
		if ( $end > $last_page ) {
			$end = $last_page;
		}
		if ( $start > $max_links ) {
			$links[] = '<a href="' . $base_url . '&amp;pagen=' . ( $start - 1 ) . '" class="arrows"><b>&laquo;</b></a>';
		}
		for ( $i = $start; $i <= $end; $i ++ ) {
			if ( $page != $i ) {
				$links[] = '<a href="' . $base_url . '&amp;pagen=' . $i . '" >' . $i . '</a>';
			}
			else {
				$links[] = '<a class="active" style="font-size: 16px;">' . $i . '</a> ';
			}
		}
		if ( $end < $last_page ) {
			$links[] = '<a href="' . $base_url . '&amp;pagen=' . $i . '" class="arrows">&raquo;</a>';  // &#10141;
		}
		$ret = '<table class="cerber-margin" style="margin-top:1em; border-collapse: collapse;"><tr><td><div class="pagination">' . implode( ' ', $links ) . '</div></td><td><span style="margin-left:2em;"><b>' . $total . ' ' . _n( 'entry', 'entries', $total, 'wp-cerber' ) . '</b></span></td></tr></table>';
	}

	return $ret;
}

function cerber_get_pn() {
	$q = crb_admin_parse_query( array( 'pagen' ) );

	return ( ! $q->pagen ) ? 1 : absint( $q->pagen );
}

function cerber_get_sql_limit( $per_page = 25 ) {
	return ' LIMIT ' . ( cerber_get_pn() - 1 ) * $per_page . ',' . $per_page;
}

/*
	Plugins screen links
*/
add_filter('plugin_action_links','cerber_action_links',10,4);
function cerber_action_links($actions, $plugin_file, $plugin_data, $context){
	if($plugin_file == CERBER_PLUGIN_ID){
		$link[] = '<a href="' . cerber_admin_link() . '">' . __('Dashboard','wp-cerber') . '</a>';
		$link[] = '<a href="' . cerber_admin_link('main') . '">' . __('Main settings','wp-cerber') . '</a>';
		$actions = array_merge ($link,$actions);
	}
	return $actions;
}

/*
function add_some_pointers() {
	?>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			var options = {'content':'<h3>Info</h3><p>Cerber will request WHOIS database for extra information when you click on IP.</p>','position':{'edge':'right','align':'center'}};
			if ( ! options ) return;
			options = $.extend( options, {
				close: function() {
					//to do
				}
			});

			//$("#ip_extra").click(function(){
			//	$(this).pointer( options ).pointer('open');
			//});

			$('#subscribe-me').pointer( options ).pointer('open');

		});
	</script>
	<?php
}
add_action('admin_enqueue_scripts', 'cerber_admin_enqueue');
function cerber_admin_enqueue($hook) {
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
}
*/


add_action( 'admin_enqueue_scripts', 'cerber_admin_assets', 9999 );
function cerber_admin_assets() {
	global $crb_assets_url;
	//$crb_assets_url = plugin_dir_url( __FILE__ ) . 'assets/';
	//$crb_assets_url  = cerber_plugin_dir_url() . 'assets/';

	if ( cerber_is_admin_page() ) {

		wp_register_style( 'crb_multi_css', $crb_assets_url . 'multi/multi.css', null, CERBER_VER );
		wp_enqueue_style( 'crb_multi_css' );
		wp_enqueue_script( 'crb_multi_js', $crb_assets_url . 'multi/multi.min.js', array(), CERBER_VER );

		wp_register_style( 'crb_tpicker_css', $crb_assets_url . 'tpicker/jquery.timepicker.min.css', null, CERBER_VER );
		wp_enqueue_style( 'crb_tpicker_css' );
		wp_enqueue_script( 'crb_tpicker', $crb_assets_url . 'tpicker/jquery.timepicker.min.js', array(), CERBER_VER );

		// Select2
		wp_register_style( 'select2css', $crb_assets_url . 'select2/dist/css/select2.min.css', null, null );
		wp_enqueue_style( 'select2css' );
		wp_enqueue_script( 'select2js', $crb_assets_url . 'select2/dist/js/select2.min.js', null, null, true );

		wp_register_style( 'crb_magnific_css', $crb_assets_url . 'magnific/magnific-popup.css', null, CERBER_VER );
		wp_enqueue_style( 'crb_magnific_css' );
		wp_enqueue_script( 'crb_magnific_js', $crb_assets_url . 'magnific/jquery.magnific-popup.min.js', null, CERBER_VER );

		wp_register_style( 'nexus_css', $crb_assets_url . 'nexus.css', null, CERBER_VER );
		wp_enqueue_style( 'nexus_css' );

		wp_register_style( 'ui_stack_css', $crb_assets_url . 'ui-stack.css', null, CERBER_VER );
		wp_enqueue_style( 'ui_stack_css' );

		add_thickbox();
	}

	if ( ! defined( 'CERBER_BETA' ) ) {
		wp_enqueue_script( 'cerber_js', $crb_assets_url . 'admin.js', array( 'jquery' ), CERBER_VER, true );
		wp_enqueue_script( 'the-ui-stack', $crb_assets_url . 'ui-stack.js', array( 'jquery' ), CERBER_VER, true );

		if ( cerber_is_admin_page( false, array( 'page' => 'cerber-integrity' ) ) ) {
			wp_enqueue_script( 'cerber_scan', $crb_assets_url . 'scanner.js', array( 'jquery' ), CERBER_VER, true );
		}
	}

	wp_register_style( 'crb_icons_css', $crb_assets_url . 'icons/style.css', null, CERBER_VER );
	wp_enqueue_style( 'crb_icons_css' );

	wp_register_style( 'cerber_css', $crb_assets_url . 'admin.css', null, CERBER_VER );
	wp_enqueue_style( 'cerber_css' );

}

/*
 * JS & CSS for admin head
 *
 */
add_action('admin_head', 'cerber_admin_head' );
add_action('customize_controls_print_scripts', 'cerber_admin_head' ); // @since 5.8.1
function cerber_admin_head() {
	global $crb_assets_url, $crb_ajax_loader;

	//$crb_ajax_loader = $crb_assets_url . 'ajax-loader.gif';
	$crb_ajax_nonce  = wp_create_nonce( 'crb-ajax-admin' );

	$crb_lab_available = ( lab_lab() ) ? 'true' : 'false';

	?>

    <script type="text/javascript">
        crb_ajax_nonce = '<?php echo $crb_ajax_nonce; ?>';
        crb_ajax_loader = '<?php echo $crb_ajax_loader; ?>';
        crb_lab_available = <?php echo $crb_lab_available; ?>;
    </script>

    <?php

	if ( defined( 'CERBER_BETA' ) ) :
		?>
        <style type="text/css" media="all">
            <?php
             readfile(cerber_assets_dir().'/admin.css');
            ?>
        </style>
	<?php
	endif;

	if ( ! cerber_is_admin_page() ) {
		return;
	}

	?>

    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <link rel='dns-prefetch' href='//wpcerber.com' />

    <script type="text/javascript">

        <?php

        echo 'crb_admin_page = "' . crb_admin_get_page() . '";';
        echo 'crb_admin_tab = "' . crb_admin_get_tab() . '";';
        echo 'crb_user_locale = "' . substr( get_user_locale(), 0, 6 ) . '";';

        if ( cerber_is_admin_page( false, array( 'page' => 'cerber-integrity' ) ) ) {
	        //echo 'crb_scan_msg_stats = ' . json_encode( cerber_get_stat_labels() ) . ';';
	        echo 'crb_scan_msg_steps = ' . json_encode( cerber_get_step_description() ) . ';';
	        echo 'crb_scan_msg_issues = ' . json_encode( cerber_get_issue_label() ) . ';';
	        echo 'crb_scan_msg_risks = ' . json_encode( cerber_get_risk_labels() ) . ';';
	        echo 'crb_scan_msg_misc = ' . json_encode( array(
			        'delete_file'     => array(
				        __( 'Are you sure you want to delete selected files?', 'wp-cerber' ),
				        __( 'These files have been moved to the quarantine', 'wp-cerber' )
			        ),
			        'ignore_add_file' => array(
				        __( 'Do you want to add selected files to the ignore list?', 'wp-cerber' ),
				        __( 'These files have been added to the ignore list', 'wp-cerber' ),
			        ),
			        'file_error'      => __( 'Some errors occurred', 'wp-cerber' ),
			        'all_ok'          => __( 'All files have been processed', 'wp-cerber' ),
			        'rerun_needed'    => __( 'This scan report was generated by the previous version of WP Cerber. Please run a new scan to get consistent and accurate results.', 'wp-cerber' ),
		        ) ) . ';';
        }

        ?>
    </script>

    <style type="text/css" media="all">
        /* Thickbox styles */
        #TB_title {
            background-color: #0085ba !important;
            color: #fff;
        }

        .tb-close-icon {
            color: #fff !important;
        }

        #TB_window {
            font-family: 'Roboto', sans-serif;
        }

        /* Hide alien's messages on Cerber's admin pages */
        .update-nag,
        #update-nag,
        #setting-error-tgmpa,
        .pms-cross-promo,
        .vc_license-activation-notice,
        #wordfenceConfigWarning,
        #crb-admin div#message.updated {
            display: none;
        }

        /* Cerber's messages are allowed */
        div.wrap .update-nag,
        .crb-alarm {
            display: inline-block;
        }
    </style>
	<?php
}
/*
 * JS & CSS for admin footer
 *
 */
add_action( 'admin_footer', 'cerber_admin_footer' );
function cerber_admin_footer() {

	//add_some_pointers();

	crb_load_wp_js();

    // Add buttons to the user profile page

    $uid = 0;
	if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
		$uid = get_current_user_id();
	}
    //elseif ( ! empty( $_GET['user_id'] ) && strpos( $_SERVER['SCRIPT_NAME'], 'user-edit.php' ) ) {
    elseif ( ! empty( $_GET['user_id'] ) && is_admin_user_edit() ) {
		$uid = absint( $_GET['user_id'] );
	}

	if ( $uid ) {
		$user_links = '<a href="' . cerber_admin_link( 'activity', array( 'filter_user' => $uid ) ) . '" class="page-title-action">' . __( 'View Activity', 'wp-cerber' ) . '</a>';
		if ( $uss = crb_sessions_get_num( $uid ) ) {
			$user_links .= '<a href="' . cerber_admin_link( 'sessions', array( 'filter_user' => $uid ) ) . '" class="page-title-action">' . __( 'Sessions', 'wp-cerber' ) . ' ' . $uss . '</a>';
        }
		?>
        <script>
            document.querySelector('#profile-page .wp-heading-inline').insertAdjacentHTML('afterend','<?php echo $user_links; ?>');
        </script>
		<?php
	}

	// User blocking admin JS
	$blocked = array();
	if ( $uids = crb_user_id_list() ) {
		foreach ( $uids as $uid ) {
			if ( crb_is_user_blocked( $uid ) ) {
				$blocked[] = $uid;
			}
		}
	}
	?>
    <script>
        // Blocked users
        let crb_blocked_users = <?php echo '[' . implode( ',', $blocked ) . ']'; ?>;
        if (crb_blocked_users.length) {
            crb_blocked_users.forEach(function (uid) {
                let element = document.getElementById('user-' + uid);
                element.classList.add("crb-user-blocked");
            });
        }
        // Switching textarea
        jQuery(document).ready(function ($) {
            let mrow = $('#profile-page .crb_blocked_txt');
            $("#crb_user_blocked").on('change', function () {
                if ($(this).is(':checked')) {
                    mrow.show();
                }
                else {
                    mrow.hide();
                }
            });
        });
    </script>
	<?php

	// Background tasks
	$list = cerber_bg_task_get_all();
	if ( $list ) {
		$list = array_slice( $list, 0, 20 );
		$list = array_keys( $list );
		?>
        <script>
            let crb_bg_tasks = <?php echo '["' . implode( '","', $list ) . '"]'; ?>;
            jQuery(document).ready(function ($) {
                //var crb_bg_tasks = <?php echo '["' . implode( '","', $list ) . '"]'; ?>;
                //console.log('Background tasks: ' + crb_bg_tasks.length)
                if (crb_bg_tasks.length) {
                    console.log('Background tasks: ' + crb_bg_tasks.length);
                    $.post(ajaxurl, {
                            ajax_nonce: crb_ajax_nonce,
                            action: 'cerber_local_ajax',
                            crb_ajax_do: 'bg_tasks_run',
                            tasks: $(crb_bg_tasks).toArray(),
                        }
                    );
                }
            });
        </script>
		<?php
	}

	// ------------------------------------------------------

	if ( ! cerber_is_admin_page() ) {
		return;
	}

	if ( defined( 'CERBER_BETA' ) ) :
		?>
        <script type="text/javascript">
			<?php
            readfile( cerber_assets_dir() . '/admin.js' );
			readfile( cerber_assets_dir() . '/ui-stack.js' );
			readfile( cerber_assets_dir() . '/scanner.js' );
			?>
        </script>
		<?php
	endif;

	// Time pickers
	$format = 'H:i';
	if ( cerber_is_ampm() ) {
		$format = 'h:i A';
	}
	?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.crb-tpicker').timepicker({
                'step': 20,
                'forceRoundTime': true,
                'timeFormat': '<?php echo $format; ?>'
            });
        });
    </script>
	<?php

	if ( ! lab_lab() && cerber_is_admin_page( false, array( 'tab' => array( 'scan_schedule', 'scan_policy' ) ) ) ) :
		?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('input').attr('disabled', 'disabled');
                $('select').attr('disabled', 'disabled');
                $('.crb-slider').css('background-color', '#888');
                $('th').add('td').add('h2').css('color', '#888');

                $('.crb-main h2').first().before('<?php echo str_replace( array(
		            "\n",
		            "\r"
	            ), '', crb_admin_cool_features() ); ?>');
            });
        </script>
		<?php
	endif;

}

function crb_load_wp_js() {
	echo '<script type="text/javascript" src="' . cerber_plugin_dir_url() . 'assets/wp-admin.js' . '"></script>';
}

add_filter( 'admin_footer_text', function ( $text ) {
	if ( ! cerber_is_admin_page() ) {
		return $text;
	}

	return 'If you like <strong>WP Cerber</strong>, <a target="_blank" href="' . crb_get_review_url( 'wp' ) . '">please give it a &#9733;&#9733;&#9733;&#9733;&#9733; review</a>. Thanks!';
}, PHP_INT_MAX );

add_filter( 'update_footer', function ( $text ) {
	if ( ! cerber_is_admin_page() ) {
		return $text;
	}
	if ( lab_lab() ) {
		$pr      = 'PRO';
		$support = '<a target="_blank" href="https://my.wpcerber.com">Get Support</a>';
	}
	else {
		$pr      = '';
		$support = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-cerber">Support Forum</a>';
	}
	if ( 1 == cerber_get_mode() ) {
		$mode = 'in the Standard mode';
	}
	else {
		$mode = 'in the Legacy mode';
	}

	return 'WP Cerber Security ' . $pr . ' ' . CERBER_VER . '. ' . $mode . ' | ' . $support;
}, PHP_INT_MAX );

/*
 * Add per screen user settings
 */
function crb_admin_screen_options() {
	if ( ! $id = crb_get_configurable_screen() ) {
		return;
	}

	add_screen_option( 'per_page', array(
		//'label' => __( 'Number of items per page:' ),
		'default' => 25,
		'option'  => 'cerber_screen_' . $id . '_page', // '_page' is mandatory since WP 5.4.2
	) );
}

/*
 * Enables saving screen options to the user's meta
 */
add_filter( 'set-screen-option', function ( $status, $option, $value ) {
	if ( $id = crb_get_configurable_screen() ) {
		if ( 'cerber_screen_' . $id . '_page' == $option ) {
			return $value;
		}
	}

	return $status;
}, 10, 3 );
/*
 * Returns false if the admin page has no screen options to configure
 *
 * @return false|string
 */
function crb_get_configurable_screen() {
	if ( ! $id = crb_admin_get_tab() ) {
		$id = crb_admin_get_page();
	}
	if ( $id == 'cerber-traffic' ) {
		$id = 'traffic';
	}
	if ( ( $id == 'cerber-nexus' ) && nexus_is_master() ) {
		return 'nexus_sites';
	}
	$ids = array( 'sessions', 'lockouts', 'activity', 'traffic', 'scan_quarantine', 'scan_ignore', 'nexus_sites' );
	if ( ! in_array( $id, $ids ) ) {
		return false;
	}

	return $id;
}

/*
 * Retrieve settings for the current screen
 *
 */
function crb_admin_get_per_page() {
	if ( is_multisite() ) {
		return 50;  // temporary workaround
	}

	if ( nexus_is_valid_request() ) {
		$per_page = nexus_request_data()->screen['per_page'];
	}
    elseif ( function_exists( 'get_current_screen' ) ) {
		set_current_screen();
		$screen = get_current_screen();

		if ( $screen_option = $screen->get_option( 'per_page', 'option' ) ) {
			$per_page = absint( get_user_meta( get_current_user_id(), $screen_option, true ) );
			if ( empty ( $per_page ) ) {
				$per_page = absint( $screen->get_option( 'per_page', 'default' ) );
			}
		}
	}

	if ( empty ( $per_page ) ) {
		$per_page = 25;
	}

	return absint( $per_page );
}

/**
 * @param array $tabs_config array of tabs: title, desc, contents and optional JS callback
 * @param string $submit Text for the submit button
 * @param array $hidden Hidden form fields
 * @param string $form_id
 *
 */
function crb_admin_show_vtabs( $tabs_config, $submit = '', $hidden = array(), $form_id = 'cerber_tabs' ) {

	$tablinks  = '';
	$tabs      = '';

	foreach ( $tabs_config as $tab_id => $tab ) {
		$tab_id = str_replace( '-', '_', $tab_id );
		$tablinks .= '<div class="tablinks" data-tab-id="' . $tab_id . '" data-callback="' . crb_array_get( $tab, 'callback', '' ) . '">' . $tab['title'] . '<br/><span>' . crb_array_get( $tab, 'desc', '' ) . '</span></div>';
		$tabs     .= '<div id="tab-' . $tab_id . '" class="vtabcontent">' . $tab['content'] . '</div>';
	}

	echo '<form id="' . $form_id . '" method="post" action="" class="crb-settings">';

	if ( ! $submit ) {
		$submit = __( 'Save Changes' );
	}

	echo '<table class="vtable" id="crb_vtabs_container" style="width: 100%; border-collapse: collapse;"><tr><td id="crb-vtabs"><div class="vtabs">' . $tablinks . '</div></td><td id="crb-vtabcontent">' . $tabs . '
    </td></tr><tr><td></td><td><div style="padding-left: 3em;">' . crb_admin_submit_button( $submit ) . '</div></td></tr></table>';

	cerber_nonce_field( 'control', true );

	if ( $hidden ) {
		foreach ( $hidden as $name => $val ) {
			echo '<input type="hidden" name="' . $name . '" value="' . $val . '">';
		}
	}

	echo '</form>';

}

function crb_admin_show_geo_rules(){

	$rules = cerber_geo_rule_set();

	$tabs_config = array();

	foreach ( $rules as $rule_id => $rule ) {

		list( $desc, $content ) = crb_admin_geo_selector( $rule_id, $rule );

		if ( isset( $rule['multi_set'] ) ) {
			$names = array( '---first' => $rule['multi_top'] . ' — ' . $desc );
			foreach ( $rule['multi_set'] as $item_id => $item_title ) {
				$id = $rule_id . '_' . $item_id;
				list( $d, $c ) = crb_admin_geo_selector( $id, $rule, 'crb-display-none' );
				$content .= $c;
				if ( cerber_get_geo_rules( $id ) ) {
					$desc = __( 'Role-based rules are configured', 'wp-cerber' );
					$names[ $item_id ] = $item_title . ' — ' . $d;
				}
				else {
					$names[ $item_id ] = $item_title;
				}
			}
			$content = '<p>' . cerber_select( 'sw_' . $rule_id, $names, null, 'crb-geo-switcher', null, null, null, array( 'rule-id' => $rule_id ) ) . '</p>' . $content;
		}

		$tabs_config[ $rule_id ] = array(
			'title'    => $rule['name'],
			'desc'     => $desc,
			'content'  => '<div style="padding-top: 22px;">' . $content . '</div>',
			'callback' => 'geo_rules_activator',
		);
	}

	?>

    <script>
        window.geo_rules_activator = function (rule_id) {

            if( typeof search_fields === 'undefined' ) {
                var search_fields = jQuery('form#crb-geo-rules .multi-wrapper input:text');
            }

            search_fields.each(function () {
                if (jQuery(this).val()) {
                    jQuery(this).val(''); // Reset "Search" field

                    if( typeof wrapper === 'undefined' ) {
                        var wrapper = jQuery('#crb-geo-rules .multi-wrapper');
                    }

                    if( typeof select === 'undefined' ) {
                        var select = jQuery('#crb-geo-rules .crb-mega-select');
                    }

                    wrapper.remove();
                    select.removeAttr('data-multijs');
                    //document.querySelector('#crb-geo-rules .crb-mega-select').removeAttribute('data-multijs');
                    //document.querySelectorAll('#crb-geo-rules .crb-mega-select').removeAttribute('data-multijs');
                }
            });

            if( typeof geo_selectors === 'undefined' ) {
                var geo_selectors = jQuery('form#crb-geo-rules select.crb-mega-select');
            }

            geo_selectors.multi({'search_placeholder': '<?php _e( 'Start typing here to find a country', 'wp-cerber' ); ?>'});

        };
    </script>

	<?php

	crb_admin_show_vtabs( $tabs_config, __( 'Save all rules', 'wp-cerber' ), array( 'cerber_admin_do' => 'update_geo_rules' ), 'crb-geo-rules' );

}

function crb_admin_geo_selector( $rule_id, $rule, $class = '' ) {

	$config   = cerber_get_geo_rules( $rule_id );
	$selector = crb_geo_country_selector( $config, $rule_id, $rule );
	$opt      = crb_get_settings();

	if ( ! empty( $config['list'] ) ) {
		$num = count( $config['list'] );
		if ( $config['type'] == 'W' ) {
			$info = sprintf( _n( 'Permitted for one country', 'Permitted for %d countries', $num, 'wp-cerber' ), $num );
		}
		else {
			$info = sprintf( _n( 'Not permitted for one country', 'Not permitted for %d countries', $num, 'wp-cerber' ), $num );
		}
		if ( $num == 1 ) {
			$info .= ' (' . current( $config['list'] ) . ')';
			//$info .= ' (' . cerber_get_flag_html($c) . $c . ')';
		}
	}
	else {
		$info = __( 'No rule', 'wp-cerber' );
		$info = __( 'Any country is permitted', 'wp-cerber' );
	}

	$note = '';
	switch ( $rule_id ) {
		case 'geo_register':
			if ( ! get_option( 'users_can_register' ) ) {
				$note = 'Registration is disabled in the General WordPress Settings.';
			}
			break;
		case 'geo_restapi':
			if ( $opt['norest'] ) {
				$note = 'REST API is disabled in the Hardening settings of the plugin.';
			}
			break;
		case 'geo_xmlrpc':
			if ( $opt['xmlrpc'] ) {
				$note = 'XML-RPC is disabled in the Hardening settings of the plugin.';
			}
			break;
	}
	if ( $note ) {
		$note = '<p><span class="dashicons-before dashicons-warning"></span> Heads up! ' . $note . '</p>';
	}

	return array(
		$info,
		$note . '<div class="crb-geo-wrapper ' . $class . '" id="crb-geo-wrap_' . $rule_id . '">' . $selector . '</div>'
	);

}

/**
 * Generates GEO rule selector
 *
 * @param array $config Rule configuration
 * @param string $rule_id
 * @param array $rule
 *
 * @return string   HTML code of form
 */
function crb_geo_country_selector( $config = array(), $rule_id = '', $rule = array() ) {

	$ret = '<div class="crb-super-select"><select id="countries-' . $rule_id . '" name="crb-' . $rule_id . '-list[]" class="crb-mega-select" style="display: none;" multiple="multiple">';

	if ( ! empty( $config['list'] ) ) {
		$selected = $config['list'];
	}
	else {
		$selected = null;
	}

	foreach ( cerber_get_country_list() as $code => $country ) {
		if ( $selected && in_array( $code, $selected ) ) {
			$sel = 'selected';
		}
		else {
			$sel = '';
		}
		$ret .= '<option ' . $sel . ' value="' . $code . '">' . $country . '</option>';
	}

	if ( ! empty( $config['type'] ) && $config['type'] == 'B' ) {
		$w = '';
		$b = 'checked="checked"';
	}
	else {
		$w = 'checked="checked"';
		$b = '';
	}

	if ( ! empty( $rule['desc'] ) ) {
		$desc = $rule['desc'];
	}
	else {
		$desc = '<span style="text-transform: lowercase;">' . $rule['name'] . '</span>';
	}


	$ret .= '
        </select>
        
        <div class="crb-super-details">
        <p><span style="opacity: 0.7;">' . __( 'Click on a country name to add it to the list of selected countries', 'wp-cerber' ) . '</span></p>
        
        <p style="margin-top: 2em;">
        <input type="radio" value="W" name="crb-' . $rule_id . '-type" id="geo-type-' . $rule_id . '-W" ' . $w . '>
        <label for="geo-type-' . $rule_id . '-W">' . sprintf( _x( 'Selected countries are permitted to %s, other countries are not permitted to', 'to is a marker of infinitive, e.g. "to use it"', 'wp-cerber' ), $desc ) . '</label>
        <p>
        <input type="radio" value="B" name="crb-' . $rule_id . '-type" id="geo-type-' . $rule_id . '-B" ' . $b . '>
        <label for="geo-type-' . $rule_id . '-B">' . sprintf( _x( 'Selected countries are not permitted to %s, other countries are permitted to', 'to is a marker of infinitive, e.g. "to use it"', 'wp-cerber' ), $desc ) . '</label>
        </p>
        </div>
        
        </div>';

	return $ret;

}

/**
 * A list of available GEO rules
 *
 * @return array
 */
function cerber_geo_rule_set() {
	$set = wp_roles()->role_names;

	$rules = array(
		'geo_login' => array(
			'name'      => __( 'Log into the website', 'wp-cerber' ),
			'multi_set' => $set,
            'multi_top' => __( 'All Users' )
		),
		'geo_register' => array( 'name' => __( 'Register on the website', 'wp-cerber' ) ),
		'geo_submit'   => array( 'name' => __( 'Submit forms', 'wp-cerber' ) ),
		'geo_comment'  => array( 'name' => __( 'Post comments', 'wp-cerber' ) ),
		'geo_xmlrpc'   => array( 'name' => __( 'Use XML-RPC', 'wp-cerber' ) ),
		'geo_restapi'  => array( 'name' => __( 'Use REST API', 'wp-cerber' ) ),
	);

	return $rules;
}

function crb_admin_save_geo_rules( $post_fields = array() ) {
    global $cerber_country_names, $check, $admin_country;

	if ( ! lab_lab() ) {
		return;
	}

	$check = array_keys( $cerber_country_names );

	// Prevent admin country from being blocked
	if ( nexus_is_valid_request() ) {
		$admin_country = null;
	}
	else {
		$admin_country = lab_get_country( cerber_get_remote_ip(), false );
	}

	$geo   = array();

	foreach ( cerber_geo_rule_set() as $rule_id => $rule ) {

		if ( $data = crb_admin_process_geo( $post_fields, $rule_id ) ) {
			$geo[ $rule_id ] = $data;
		}

		if ( isset( $rule['multi_set'] ) ) {
			foreach ( $rule['multi_set'] as $item_id => $item_title ) {
				$id = $rule_id . '_' . $item_id;
				if ( $data = crb_admin_process_geo( $post_fields, $id ) ) {
					$geo[ $id ] = $data;
				}
			}
		}
	}

	if ( update_site_option( CERBER_GEO_RULES, $geo ) ) {
		cerber_admin_message( __( 'Security rules have been updated', 'wp-cerber' ) );
	}
}

function crb_admin_process_geo( $post, $rule_id ) {
	global $check, $admin_country;

	if ( empty( $post[ 'crb-' . $rule_id . '-list' ] ) || empty( $post[ 'crb-' . $rule_id . '-type' ] ) ) {
		return false;
	}

	$list = array_intersect( $post[ 'crb-' . $rule_id . '-list' ], $check );

	if ( $post[ 'crb-' . $rule_id . '-type' ] == 'B' ) {
		$type = 'B';
		if ( $admin_country && ( ( $key = array_search( $admin_country, $list ) ) !== false ) ) {
			unset( $list[ $key ] );
		}
	}
	else {
		$type = 'W';
		if ( $admin_country && ( ( $key = array_search( $admin_country, $list ) ) === false ) ) {
			array_push( $list, $admin_country );
		}
	}

	return array( 'list' => $list, 'type' => $type );
}

/**
 * Generates HTML for showing country in UI
 *
 * @param null $code
 * @param null $ip
 * @param bool $cache_only
 *
 * @return string
 */
function crb_country_html( $code = null, $ip = null, $cache_only = true ) {

	if ( ! lab_lab() ) {
		return '';
	}

	if ( ! $code ) {
		if ( $ip ) {
			$code = lab_get_country( $ip, $cache_only );
		}
		else {
			return '';
		}
	}

	if ( $code ) {
		$ret = cerber_get_flag_html( $code, cerber_country_name( $code ) );
	}
	else {
		$ip_id = cerber_get_id_ip( $ip );
		$ret = crb_get_ajax_placeholder( 'country', $ip_id );
	}

	return $ret;
}

// Traffic =====================================================================================

function cerber_export_traffic( $params = array() ) {

	crb_raise_limits( 512 );

	$args = array(
		'per_page' => 0,
		'columns'  => array( 'ip', 'stamp', 'uri', 'session_id', 'user_id', 'processing', 'request_method', 'http_code', 'wp_type', 'blog_id' )
	);

	if ( $params ) {
		$args = array_merge( $params, $args );
	}

	list( $query, $found ) = cerber_traffic_query( $args );

	// We split into several requests to avoid PHP and MySQL memory limitations

	if ( defined( 'CERBER_EXPORT_CHUNK' ) && is_numeric( CERBER_EXPORT_CHUNK ) ) {
		$per_chunk = CERBER_EXPORT_CHUNK;
	}
	else {
		$per_chunk = 1000; 	// Rows per SQL request, we assume that this number is not too small and not too big
	}

	if ( ! $result = cerber_db_query( $query . ' LIMIT ' . $per_chunk ) ) {
		wp_die( 'Nothing to export' );
	}

	$total = cerber_db_get_var( $found );

	$info = array();

	$heading = array(
		__( 'IP address', 'wp-cerber' ),
		__( 'Date', 'wp-cerber' ),
		'Method',
		'URI',
		'HTTP Code',
		'Request ID',
		__( 'User ID', 'wp-cerber' ),
		__( 'Page generation time', 'wp-cerber' ),
		'Blog ID',
		'Type',
		'Unix Timestamp',
	);

	cerber_send_csv_header( 'wp-cerber-http-requests', $total, $heading, $info );

	//$labels = cerber_get_labels( 'activity' );
	//$status = cerber_get_labels( 'status' );

	if ( crb_get_settings( 'plain_date' ) ) {
		function _crb_csv_date( $timestamp ) {
			static $gmt_offset;
			if ( $gmt_offset === null ) {
				$gmt_offset = get_option( 'gmt_offset' ) * 3600;
			}
			return date( 'Y-m-d H:i:s', $timestamp + $gmt_offset );
		}
	}
	else {
		function _crb_csv_date( $timestamp ) {
			return cerber_date( $timestamp, false );
		}
	}

	// The loop

	$i = 0;

	do {

		while ( $row = mysqli_fetch_assoc( $result ) ) {

			$values = array();

			/*
			$values[] = $row->ip;
			$values[] = cerber_date( $row->stamp );
			$values[] = $row->request_method;
			$values[] = $row->uri;
			$values[] = $row->http_code;
			$values[] = $row->session_id;
			$values[] = $row->user_id;
			$values[] = $row->processing;
			$values[] = $row->blog_id;
			$values[] = $row->wp_type;
			$values[] = $row->stamp;*/

			$values[] = $row['ip'];
			$values[] = _crb_csv_date( $row['stamp'] );
			$values[] = $row['request_method'];
			$values[] = $row['uri'];
			$values[] = $row['http_code'];
			$values[] = $row['session_id'];
			$values[] = $row['user_id'];
			$values[] = $row['processing'];
			$values[] = $row['blog_id'];
			$values[] = $row['wp_type'];
			$values[] = $row['stamp'];

			cerber_send_csv_line( $values );
		}

		mysqli_free_result( $result );

		$i++;
		$offset = $per_chunk * $i;

	} while ( ( $result = cerber_db_query( $query . ' LIMIT ' . $offset . ', ' . $per_chunk ) )
	          && $result->num_rows );

	exit;
}

/*
 * Display traffic in the WP Dashboard
 *
 */
function cerber_show_traffic( $args = array(), $echo = true ) {
	global $wpdb, $crb_ajax_loader;

	$labels = cerber_get_labels( 'activity' );
	$status_labels = cerber_get_labels( 'status' ) + cerber_get_reason();

	$base_url = cerber_admin_link( 'traffic' );
	//$activity_url = cerber_admin_link( 'activity' );

	$export_button = '';
	$ret = '';
	$total = 0;

	list( $query, $found, $per_page, $filter_act, $filter_ip, $prc, $user_id ) = cerber_traffic_query( $args );

	// No cache
	//$rows = cerber_db_get_results( $query, MYSQL_FETCH_OBJECT_K );
	//$total = cerber_db_get_var( $found );

	list( $rows, $total ) = crb_q_cache_get( array(
		array( $query, MYSQL_FETCH_OBJECT_K ),
		array( $found )
	), CERBER_TRAF_TABLE );
	if ( is_object( $rows ) ) { // Due to json
		$rows = get_object_vars( $rows );
	}
	$total = $total[0][0];

	if ( $rows ) {

		// No cache
		// $act_events = cerber_db_get_results( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE session_id IN ("' . implode( '", "', array_keys( $rows ) ) . '" ) ORDER BY stamp DESC', MYSQL_FETCH_OBJECT );
		// $act_events = cerber_db_get_results( 'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE session_id IN ("' . implode( '", "', array_keys( $rows ) ) . '" )', MYSQL_FETCH_OBJECT );
		$act_events = crb_q_cache_get( array(
			array(
				'SELECT * FROM ' . CERBER_LOG_TABLE . ' WHERE session_id IN ("' . implode( '", "', array_keys( $rows ) ) . '" ) ORDER BY stamp DESC',
				MYSQL_FETCH_OBJECT
			)
		), CERBER_LOG_TABLE );

		$events = array();
		if ( $act_events ) {
			foreach ( $act_events as $ac ) {
				$events[ $ac->session_id ][] = $ac;
			}
		}

		$roles = wp_roles()->roles;
		$users = array();
		$acl = array();
		$block = array();
		$wp_objects = array();

		foreach ( $rows as $row ) {

			if ( $row->user_id && ! isset( $users[ $row->user_id ] ) ) {
				if ( $u = get_userdata( $row->user_id ) ) {
					$n = $u->display_name;
					$r = '';
					if ( ! is_multisite() && $u->roles ) {
						$r = array();
						foreach ( $u->roles as $role ) {
							$r[] = $roles[ $role ]['name'];
						}
						$r = '<span class="crb_act_role">' . implode( ', ', $r ) . '</span>';
					}
				}
				else {
					$n = __( 'Unknown', 'wp-cerber' ) . ' (' . $row->user_id . ')';
					$r = '';
				}

				$users[ $row->user_id ]['name'] = $n;
				$users[ $row->user_id ]['roles'] = $r;
			}

			if ( ! isset( $acl[ $row->ip ] ) ) {
				$acl[ $row->ip ] = cerber_acl_check( $row->ip );
			}

			if ( ! isset( $block[ $row->ip ] ) ) {
				$block[ $row->ip ] = cerber_block_check( $row->ip );
			}

			// TODO: make it compatible with multisite WP
			if ( $row->wp_type == 601 && $row->wp_id > 0 ) {
				$title = cerber_db_get_var( 'SELECT post_title FROM ' . $wpdb->posts . ' WHERE ID = ' . absint( $row->wp_id ) );
				if ( $title ) {
					$wp_objects[ $row->wp_id ] = apply_filters( 'the_title', $title, $row->wp_id );
				}
			}

		}
	}


	$info = '';

	if ( $filter_ip ) {
		$info .= cerber_ip_extra_view( $filter_ip, 'traffic' );
	}

	if ( $user_id ) {
		$info .= cerber_user_extra_view( $user_id, 'traffic' );
	}

	if ( $rows ) {

		$tbody = '';
		$country = '';
		$geo = lab_lab();

		if ( ! defined( 'CERBER_FULL_URI' ) || ! CERBER_FULL_URI ) {
			$short = true;
			$site_url = cerber_get_site_url();
			$len = strlen( rtrim( $site_url, '/' ) );
		}
		else {
			$short = false;
			$len = 0;
		}

		foreach ( $rows as $row ) {

			// URI

			$full_uri = urldecode( $row->uri );

			if ( $short && 0 === strpos( $full_uri, $site_url ) ) {
				$row_uri = substr( $full_uri, $len );
			}
			else {
				$row_uri = $full_uri;
			}

			if ( strlen( $row_uri ) > 220 ) {
				$truncated = true;
				$row_uri = substr( $row_uri, 0, 220 );
			}
			else {
				$truncated = false;
			}

			$row_uri = htmlentities( $row_uri, ENT_SUBSTITUTE );
			$row_uri = str_replace( array( '-', '/', '%', '&', '=', '?', '(', ')', ),
				array( '<wbr>-', '<wbr>/', '<wbr>%', '<wbr>&', '<wbr>=', '<wbr>?', '<wbr>(', ')<wbr>' ), $row_uri );

			if ( $truncated ) {
				$row_uri .= ' <span style="color: red;">&hellip;</span>';
			}

			// User

			$ip_id = cerber_get_id_ip( $row->ip );

			if ( $row->user_id ) {
				$name = '<a href="' . $base_url . '&filter_user=' . $row->user_id . '"><b>' . $users[ $row->user_id ]['name'] . '</b></a><br/>' . $users[ $row->user_id ]['roles'];
			}
			else {
				$name = '';
			}

			if ( ! empty( $row->hostname ) ) {
				$hostname = $row->hostname;
			}
			else {
				$ip_info = cerber_get_ip_info( $row->ip, true );
				if ( isset( $ip_info['hostname_html'] ) ) {
					$hostname = $ip_info['hostname_html'];
				}
				else {
					$hostname = crb_get_ajax_placeholder( 'hostname', $ip_id );
				}
			}

			if ( ! empty( $args['date'] ) && $args['date'] == 'ago' ) {
				$date = cerber_ago_time( $row->stamp );
			}
			else {
				$date = '<span title="' . $row->stamp . ' / ' . $row->session_id . '">' . cerber_date( $row->stamp ) . '</span>';
			}

			if ( $geo ) {
				$country = '<p style="">' . crb_country_html( $row->country, $row->ip ) . '</p>';
			}

			$activity = '';
			if ( ! empty( $events[ $row->session_id ] ) ) {
				foreach ( $events[ $row->session_id ] as $a ) {
					$activity .= '<p><a href="' . $base_url . '&filter_activity=' . $a->activity . '" title="' . $a->activity . '" data-no-js="1">' . $labels[ $a->activity ] . '</a>';

					$ad = explode( '|', $a->details );
					if ( ! empty( $ad[0] ) && $ad[0] != 500 ) { // 500 Whitelisted
						$activity .= ' &nbsp;<span class = "crb-log-status crb-status-' . $ad[0] . '" title="' . $ad[0] . '">' . $status_labels[ $ad[0] ] . '</span>';
					}
				}
			}

			if ( $row->processing ) {
				$processing = $row->processing . ' ms';
				if ( ( $threshold = crb_get_settings( 'tithreshold' ) ) && $row->processing > $threshold ) {
					$processing = '<span class="crb-processing crb-above">' . $processing . '</span>';
				}
			}
			else {
				$processing = '';
			}

			$wp_type = '';
			if ( $t = cerber_get_wp_type( $row->wp_type ) ) {
				$wp_type = '<span class="crb-wp-type-' . $row->wp_type . '">' . $t . '</span>';
			}

			$wp_obj = '';
			if ( isset( $wp_objects[ $row->wp_id ] ) ) {
				$wp_obj = '<p><i>' . $wp_objects[ $row->wp_id ] . '</i></p>';
			}

			$more_details = array();

			if ( $truncated ) {
				$full_uri = htmlentities( $full_uri, ENT_SUBSTITUTE );
				$full_uri = str_replace( array( '-', '/', '%', '&', '=', '?', '(', ')', ),
					array( '<wbr>-', '<wbr>/', '<wbr>%', '<wbr>&', '<wbr>=', '<wbr>?', '<wbr>(', ')<wbr>' ), $full_uri );
				$more_details[] = array(
					'Full URL',
					'<span class="crb-monospace">' . $full_uri . '</span>'
				);
			}

			$fields = crb_auto_decode( $row->request_fields );
			$details = crb_auto_decode( $row->request_details );

			if ( ! empty( $details[2] ) ) {
				$more_details[] = array(
					'Referrer',
					'<span class="crb-monospace">' . htmlentities( urldecode( $details[2] ), ENT_SUBSTITUTE ) . '</span>'
				);
			}

			if ( ! empty( $details[1] ) ) {
				$more_details[] = array(
					'User Agent',
					'<span class="crb-monospace">' . htmlentities( $details[1], ENT_SUBSTITUTE ) . '</span>'
				);
			}

			// POST fields

			if ( ! empty( $fields[1] ) ) {
				$more_details[] = array( '', cerber_table_view( 'Form fields', $fields[1] ) );
			}

			// GET fields

			if ( ! empty( $fields[2] ) ) {
				$more_details[] = array( '', cerber_table_view( 'GET fields', $fields[2] ) );
			}

			// Files

			$files = array();
			$f = '';
			if ( ! empty( $fields[3] ) ) {

				foreach ( $fields[3] as $field_name => $file ) {

					if ( is_array( $file['error'] ) ) {
						$f_err = array_shift( $file['error'] );
					}
					else {
						$f_err = $file['error'];
					}

					if ( $f_err == UPLOAD_ERR_NO_FILE ) {
						continue;
					}

					if ( is_array( $file['name'] ) ) {
						$f_name = array_shift( $file['name'] );
					}
					else {
						$f_name = $file['name'];
					}

					if ( is_array( $file['size'] ) ) {
						$f_size = array_shift( $file['size'] );
					}
					else {
						$f_size = $file['size'];
					}

					$files[ $field_name ] = htmlentities( $f_name, ENT_SUBSTITUTE ) . ', size: ' . absint( $f_size ) . ' bytes';
				}

				if ( ! empty( $files ) ) {
					$f = '<span class="crb-ffile">F</span>';
					$more_details[] = array( '', cerber_table_view( 'Files submitted', $files ) );
				}
			}

			// Additional details

			if ( ! empty( $details[5] ) ) {
				$more_details[] = array( 'XML-RPC Data', htmlentities( $details[5], ENT_SUBSTITUTE ) );
			}

			if ( ! empty( $details[6] ) ) {
				$more_details[] = array( '', cerber_table_view( 'Request Headers', $details[6] ) );
			}

			if ( ! empty( $details[10] ) ) {
				$list = array();
				foreach ( $details[10] as $item ) {
					$e = explode( ':', $item, 2 );
					/* $key = ( isset( $list[ $e[0] ] ) ) ? $e[0] . '&nbsp;' : $e[0];
					$list[ $key ] = $e[1];*/
					$list[] = array( $e[0] => $e[1] );
				}
				$more_details[] = array( '', cerber_table_view( 'Response Headers', $list ) );
			}

			if ( ! empty( $details[8] ) ) {
				$more_details[] = array( '', cerber_table_view( 'Request Cookies', $details[8] ) );
			}

			if ( ! empty( $details[9] ) ) {
				$start = strlen( 'Set-Cookie:' );
				$list = array();
				foreach ( $details[9] as $item ) {
					if ( $e = explode( '=', substr( $item, $start ), 2 ) ) {
						$list[ $e[0] ] = $e[1];
					}
				}
				$more_details[] = array( '', cerber_table_view( 'Response Cookies', $list ) );
			}

			if ( ! empty( $details[7] ) ) {
				$more_details[] = array( '', cerber_table_view( '$_SERVER', $details[7] ) );
			}

			$php_errors = '';
			if ( $err_list = crb_auto_decode( $row->php_errors ) ) {
				$errors = array();
				foreach ( $err_list as $err ) {
					$errors[] = array(
						'type' => cerber_get_err_type( $err[0] ) . ' (' . $err[0] . ')',
						'info' => $err[1],
						'file' => $err[2],
						'line' => $err[3]
					);
				}
				$more_details[] = array( '', cerber_table_view( 'Software errors', $errors ) );
				$php_errors = '<span class="crb-php-error">SE &nbsp;' . count( $err_list ) . '</span>';
			}

			$req_status = '';
			if ( ! empty( $row->req_status ) ) {
				$req_status = '<span class="crb-req-status-' . $row->req_status . '">' . $status_labels[ $row->req_status ] . '</span>';
			}

			$request_details = '';
			$more_link = '';
			$toggle_class = '';

			if ( $more_details ) {
				foreach ( $more_details as $d ) {
					if ( $d[0] ) {
						$request_details .= '<div style="margin-bottom: 1.5em;"><p style="font-weight: bold;">' . $d[0] . '</p>' . $d[1] . '</div>';
					}
					else {
						$request_details .= $d[1];
					}
				}
				$more_link = '<a href="#" onclick="return false;" class="crb-traffic-more">Details</a>';
				$toggle_class = 'crb-toggle';
			}

			$request = '<b>' . $row_uri . '</b>' . '<p style="margin-top:1em;"><span class="crb-' . $row->request_method . '">' . $row->request_method . '</span>' . $f . $wp_type . '<span class="crb-' . $row->http_code . '"> HTTP ' . $row->http_code . ' ' . get_status_header_desc( $row->http_code ) . '</span>' . $req_status . $php_errors . '<span>' . $processing . '</span> ' . $more_link . ' ' . $activity . ' </p>' . $wp_obj;

			// Decorating this table can't be done via simple CSS
			if ( ! empty( $even ) ) {
				$class = 'crb-even';
				$even = false;
			}
			else {
				$even = true;
				$class = 'crb-odd';
			}

			$tbody .= '<tr class="' . $class . ' ' . $toggle_class . '">
                    <td>' . $date . '</td>
                    <td class="crb-request">' . $request . '</td>
                    <td>' . crb_admin_ip_cell( $row->ip, $base_url . '&amp;filter_ip=' . $row->ip ) . '</td>
                    <td>' . $hostname . $country . '</td>
                    <td>' . cerber_detect_browser( $details[1] ) . '</td>
                    <td>' . $name . '</td></tr>';

			$tbody .= '<tr class="' . $class . '" style="display: none;"><td></td><td colspan="5" class="crb-traffic-details" data-session-id="' . $row->session_id . '"><div>' . $request_details . '</div></td></tr>';
		}

		$heading = array(
			'crb_date_col'     => __( 'Date', 'wp-cerber' ),
			'crb_request_col'  => __( 'Request', 'wp-cerber' ),
			'crb_ip_col'       => '<div class="crb_act_icon"></div>' . __( 'IP Address', 'wp-cerber' ),
			'crb_hostinfo_col' => __( 'Host Info', 'wp-cerber' ),
			'crb_ua_col'       => __( 'User Agent', 'wp-cerber' ),
			'crb_user_col'     => __( 'Local User', 'wp-cerber' ),
		);

		$titles_head = '';
		foreach ( $heading as $id => $title ) {
			$titles_head .= '<th id="' . $id . '">' . $title . '</th>';
		}

		$titles_foot = '<th>' . implode( '</th><th>', $heading ) . '</th>';

		$table = '<table id="crb-traffic" class="widefat crb-table cerber-margin"><thead><tr>' . $titles_head . '</tr></thead><tfoot><tr>' . $titles_foot . '</tr></tfoot><tbody>' . $tbody . '</tbody></table>';

		if ( empty( $args['no_navi'] ) ) {
			$table .= cerber_page_navi( $total, $per_page );
		}

		//$legend  = '<p>'.sprintf(__('Showing last %d records from %d','wp-cerber'),count($rows),$total);

		//if (empty($args['no_export'])) $export_link = '<a class="button button-secondary cerber-button" href="'.wp_nonce_url(add_query_arg('export_activity',1),'control','cerber_nonce').'"><span class="dashicons dashicons-download" style="vertical-align: middle;"></span> '.__('Export','wp-cerber').'</a>';
	}
	else {
		$table = '<p class="cerber-margin">' . __( 'No requests have been logged.', 'wp-cerber' ) . '</p>';
		cerber_watchdog( true );
	}

	if ( empty( $args['no_navi'] ) ) {

		$nav_links = array();
		$nav_links[] = array( array(), __( 'View all', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_set' => 1 ), __( 'Suspicious requests', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_http_code' => 399, 'filter_http_code_mode' => 'GT' ), __( 'Errors', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_user' => '*' ), __( 'Users', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_user' => 0 ), __( 'Non-authenticated', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_method' => 'POST', 'filter_wp_type' => 519, 'filter_wp_type_mode' => 'GT' ), __( 'Form submissions', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_http_code' => 404 ), __( 'Page Not Found', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_wp_type' => 520 ), 'REST API' );
		$nav_links[] = array( array( 'filter_wp_type' => 515 ), 'XML-RPC' );
		$nav_links[] = array( array( 'filter_user' => get_current_user_id() ), __( 'My requests', 'wp-cerber' ) );
		$nav_links[] = array( array( 'filter_ip' => cerber_get_remote_ip() ), __( 'My IP', 'wp-cerber' ) );

		if ( $threshold = crb_get_settings( 'tithreshold' ) ) {
			$nav_links[] = array( array( 'filter_processing' => $threshold ), __( 'Longer than', 'wp-cerber' ) . ' ' . $threshold . ' ms' );
		}

		$filter_links = crb_make_nav_links( $nav_links, 'traffic' );

		//$search_button = cerber_traffic_search_form();
		$search_button = '<a href="#" id="traffic-search-btn" class="button button-secondary cerber-button" title="Search in the request history"><i class="crb-icon crb-icon-bx-search"></i> ' . __( 'Advanced Search', 'wp-cerber' ) . '</a>';

		$export_button .= '<a class="button button-secondary cerber-button" href="' .
		                  cerber_admin_link_add( array(
			                  'cerber_admin_do' => 'export',
			                  'type'            => 'traffic',
		                  ), true )
		                  . '"><i class="crb-icon crb-icon-bx-download"></i> ' . __( 'Export', 'wp-cerber' ) . '</a>';

		$right_links = $search_button . ' ' . $export_button;

		$refresh = '';
		if ( crb_get_settings( 'timode' ) ) {
			$refresh = '<a href="" style="white-space: pre;"><i class="dashicons dashicons-update" style=""></i> <span>' . __( 'Refresh', 'wp-cerber' ) . '</span></a>';
		}

		$top_bar = '<div id = "activity-filter"><div><div style="display: table-cell; width: 80%;">' . $filter_links . '</div><div style="display: table-cell;">' . $refresh . '</div></div><div>' . $right_links . '</div></div><br style="clear: both;">';

		$ret = '<div class="cerber-margin">' . $top_bar . crb_admin_traffic_search_form() . $info . '</div>' . $ret;
	}

	$ret .= $table;

	if ( ! crb_get_settings( 'timode' ) ) {
		$ret = '<p class="cerber-margin" style="padding: 0.5em; font-weight: bold;">Logging is currently disabled, you are viewing  historical information.</p>' . $ret;
	}

	if ( $echo ) {
		echo $ret;
	}
	else {
		return $ret;
	}

}

/**
 * Detects known browsers/crawlers and platform in User Agent string
 *
 * @param $ua
 *
 * @return string Sanitized browser name and platform on success
 * @since 6.0
 */
function cerber_detect_browser( $ua ) {
	$ua  = trim( $ua );

	if ( empty( $ua ) ) {
		return __( 'Not specified', 'wp-cerber' );
	}

	if ( preg_match( '/\(compatible\;(.+)\)/i', $ua, $matches ) ) {
		$bot_info = explode( ';', $matches[1] );
		foreach ( $bot_info as $item ) {
			if ( stripos( $item, 'bot' )
			     || stripos( $item, 'crawler' )
			     || stripos( $item, 'spider' )
			     || stripos( $item, 'Yandex' )
			     || stripos( $item, 'Yahoo! Slurp' )
			) {
				if ( strpos( $ua, 'Android' ) ) {
					$item .= ' Mobile';
				}
				return htmlentities( $item, ENT_SUBSTITUTE );
			}
		}
		if ( strpos( $ua, 'Google-Read-Aloud' ) ) {
			return 'Google Read Aloud';
		}
	}
    elseif ( strpos( $ua, 'google.com' ) || strpos( $ua, 'Google' ) ) {
		// Various Google bots

		$ret = '';

		if ( false !== strpos( $ua, 'Googlebot' ) ) {
			if ( strpos( $ua, 'Android' ) ) {
				$ret = 'Googlebot Mobile';
			}
            elseif ( false !== strpos( $ua, 'Mozilla' ) ) {
				$ret = 'Googlebot Desktop';
			}
		}
        elseif ( preg_match( '/AdsBot-Google-Mobile|AdsBot-Google|APIs-Google|FeedFetcher-Google/', $ua, $matches ) ) {
			$ret = $matches[0];
		}
        elseif ( 0 === strpos( $ua, 'Googlebot' ) ) {
			if ( preg_match( '/Googlebot-\w+/', $ua, $matches ) ) {
				$ret = $matches[0];
			}
		}
        elseif ( 0 === strpos( $ua, 'Mediapartners-Google' ) ) {
			return 'AdSense Crawler';
		}
        elseif ( 0 === strpos( $ua, 'AdsBot-Google-Mobile-Apps' ) ) {
			return 'Mobile Apps Android';
		}
        elseif ( strpos( $ua, 'DuplexWeb-Google' ) ) {
			return 'Duplex on the Web by Google';
		}
        elseif ( strpos( $ua, 'Google Favicon' ) ) {
			return 'Google Favicon';
		}

		if ( $ret ) {
			return htmlentities( $ret, ENT_SUBSTITUTE );
		}
		else {
			return __( 'Unknown Google\'s bot', 'wp-cerber' );
		}
	}
    /*elseif ( 0 === strpos( $ua, 'Googlebot' ) ) {
		if ( preg_match( '/Googlebot-\w+/', $ua, $matches ) ) {
			return $matches[0];
		}
	}*/
    elseif ( 0 === strpos( $ua, 'WordPress/' ) ) {
		list( $ret ) = explode( ';', $ua, 2 );
		return htmlentities( $ret, ENT_SUBSTITUTE );
	}
    elseif ( 0 === strpos( $ua, 'PayPal IPN' ) ) {
		return 'PayPal Payment Notification';
	}
    elseif (0 === strpos( $ua, 'Wget/' )){
		return htmlentities( $ua, ENT_SUBSTITUTE );
	}
    elseif ( strpos( $ua, 'googleweblight' )){
		return 'Web Light by Google';
	}

	$browsers = array(
		'Firefox/'   => 'Firefox',
		'OPR/'       => 'Opera',
		'Opera/'     => 'Opera',
		'YaBrowser/' => 'Yandex Browser',
		'Trident/'   => 'Internet Explorer',
		'IE/'        => 'Internet Explorer',
		'Edge/'      => 'Microsoft Edge',
		'Edg/'       => 'Microsoft Edge',
		'Chrome/'    => 'Chrome',
		'Safari/'    => 'Safari',
		'Lynx/'      => 'Lynx',
	);

	$systems  = array( 'Android' , 'Linux', 'Windows', 'iPhone', 'iPad', 'Macintosh', 'OpenBSD', 'Unix' );

	$browser = '';
	foreach ( $browsers as $browser_id => $browser_name ) {
		if ( false !== strpos( $ua, $browser_id ) ) {
			$browser = $browser_name;
			break;
		}
	}

	$system = '';
	foreach ( $systems as $system_id ) {
		if ( false !== strpos( $ua, $system_id ) ) {
			$system = $system_id;
			break;
		}
	}

	if ( $browser == 'Lynx' && ! $system ) {
		$system = 'Linux';
	}
    elseif ( $system == 'Macintosh' ) {
		$system = 'Mac';
	}

	if ( $system == 'Android' ) {
		if ( preg_match( '/(Android\s+\d+);/', $ua, $matches ) ) {
			$system = $matches[1];
		}
	}

	if ( $browser && $system ) {
		$ret = $browser . ' on ' . $system;
	}
    elseif ( 0 === strpos( $ua, 'python-requests' ) ) {
		$ret = 'Python Script';
	}
    elseif ( 0 === strpos( $ua, 'ApacheBench' ) ) {
		$ret = $ua;
	}
	else {
		$ret = __( 'Unknown', 'wp-cerber' );
	}

	return htmlentities( $ret, ENT_SUBSTITUTE );
}

/**
 * Create a table view of an array to display it
 *
 * @param $title
 * @param $fields
 *
 * @return string
 */
function cerber_table_view( $title, $fields, $sub_key = null ) {
	if ( empty( $fields ) ) {
		return '';
	}

	if ( ! isset( $sub_key ) ) {
		$class = 'crb-fields-table crb-top-table';
	}
	else {
		$class = 'crb-fields-table crb-sub-table';
	}

	$view = '<table class="' . $class . '">';

	if ( $title ) {
		$view .= '<tr><td colspan="2" style="font-weight: 500">' . $title . '</td></tr>';
	}

	$i = 1;
	foreach ( $fields as $key => $value ) {
		if ( is_array( $value ) ) {
			$view .= '<tr><td>' . htmlentities( $key, ENT_SUBSTITUTE ) . '</td><td>' . cerber_table_view( '', $value, $key ) . '</td></tr>';
		}
		else {
			$view .= '<tr><td>' . htmlentities( $key, ENT_SUBSTITUTE ) . '</td><td><div>' . nl2br( htmlentities( $value, ENT_SUBSTITUTE ) ) . '</div></td></tr>';
		}
		$i ++;
	}

	$view .= '</table>';

	return $view;
}

/**
 * Parse arguments and create SQL query for retrieving rows from the traffic table
 *
 * @param array $args Optional arguments to use them instead of using $_GET
 *
 * @return array
 * @since 6.0
 */
function cerber_traffic_query( $args = array() ) {
	global $wpdb;

	$ret = array_fill( 0, 8, '' );
	$where = array();
	$join = '';
	$join_act = false;

	$q = crb_admin_parse_query( array(
		'filter_sid',
		'filter_http_code',
		'filter_http_code_mode',
		'filter_ip',
		'filter_processing',
        'filter_user',
		'filter_user_alt',
        'filter_user_mode',
        'filter_wp_type',
        'filter_wp_type_mode',
        'search_traffic',
        'filter_method',
        'filter_activity',
        'filter_set',
		'filter_errors',
	), $args );

	if ( preg_match( '/^\w+$/', $q->filter_sid ) ) {
		$where[] = 'log.session_id = "' . $q->filter_sid . '"';
	}

	$falist = array();
	if ( $q->filter_http_code ) { // Multiple codes can be requested this way: &filter_http_code[]=404&filter_http_code[]=405
		if ( is_array( $q->filter_http_code ) ) {
			$falist  = array_filter( array_map( 'absint', $q->filter_http_code ) );
			$where[] = 'log.http_code IN (' . implode( ',', $falist ) . ')';
		}
		else {
			$filter  = absint( $q->filter_http_code );
			$op = '=';
			if ( $q->filter_http_code_mode == 'GT' ) {
				$op = '>';
			}
			$where[] = 'log.http_code ' . $op . $filter;
			$falist  = array( $filter ); // for further using in links
		}
	}
	//$ret[3] = $falist;

	if ( $q->filter_ip ) {
		$range = cerber_any2range( $q->filter_ip );
		if ( is_array( $range ) ) {
			$where[] = '(log.ip_long >= ' . $range['begin'] . ' AND log.ip_long <= ' . $range['end'] . ')';
		}
        elseif ( cerber_is_ip_or_net( $q->filter_ip ) ) {
			$where[] = 'log.ip = "' . $q->filter_ip . '"';
		}
		else {
			$where[] = "log.ip = 'produce-no-result'";
		}
		$ret[4] = preg_replace( CRB_IP_NET_RANGE, '', $q->filter_ip );
	}

	if ( $q->filter_processing ) {
		$p       = absint( $q->filter_processing );
		$where[] = 'log.processing > ' . $p;
		$ret[5]  = $p;
	}

	$filter_user = false;

	if ( $q->filter_user !== false ) {
		$filter_user = $q->filter_user;
	}
    elseif ( $q->filter_user_alt !== false ) {
		$filter_user = $q->filter_user_alt;
	}

	if ( $filter_user !== false ) {
		if ( $filter_user == '*' ) {
			$um      = ( ! empty( $q->filter_user_mode ) ) ? '=' : '!=';
			$where[] = 'log.user_id ' . $um . ' 0';
			$ret[6]  = '*';
		}
        elseif ( preg_match_all( '/\d+/', $filter_user, $matches ) ) {
			$users = implode( ',', $matches[0] );

			if ( ! empty( $q->filter_user_mode ) ) {
				$um = 'NOT IN';
			}
			else {
				$um     = 'IN';
				$ret[6] = $users;
			}

			$where[] = 'log.user_id ' . $um . ' (' . $users . ')';
		}
	}

	if ( $q->filter_wp_type ) {
		$t      = absint( $q->filter_wp_type );
		$ret[7] = $t;
		$op     = '=';
		if ( $q->filter_wp_type_mode == 'GT' ) {
			$op = '>';
		}

		$where[] = 'log.wp_type ' . $op . $t;
	}

	if ( $q->search_traffic ) {
		$search = stripslashes_deep( $q->search_traffic );
		if ( $search['ip'] ) {
			if ( $ip = filter_var( $search['ip'], FILTER_VALIDATE_IP ) ) {
				$where[] = 'log.ip = "' . $ip . '"';
				$ret[4] = $ip;
			}
			else {
				$where[] = 'log.ip LIKE "%' . cerber_real_escape( $search['ip'] ) . '%"';
			}
		}
		if ( $search['uri'] ) {
			$where[] = 'log.uri LIKE "%' . cerber_real_escape( $search['uri'] ) . '%"';
		}
		if ( $search['fields'] ) {
			$where[] = 'log.request_fields LIKE "%' . cerber_real_escape( $search['fields'] ) . '%"';
		}
		if ( $search['details'] ) {
			$where[] = 'log.request_details LIKE "%' . cerber_real_escape( $search['details'] ) . '%"';
		}
		if ( $search['date_from'] ) {
			if ( $stamp = strtotime( 'midnight ' . $search['date_from'] ) ) {
				$gmt_offset = get_option( 'gmt_offset' ) * 3600;
				$where[]    = 'log.stamp >= ' . ( absint( $stamp ) - $gmt_offset );
			}
		}
		if ( $search['date_to'] ) {
			if ( $stamp = strtotime( 'midnight ' . $search['date_to'] ) ) {
				$gmt_offset = get_option( 'gmt_offset' ) * 3600;
				$where[]    = 'log.stamp <= ' . ( absint( $stamp ) - $gmt_offset );
			}
		}
		if ( ! $q->filter_errors && $search['errors'] ) {
			$where[] = 'log.php_errors LIKE "%' . cerber_real_escape( $search['errors'] ) . '%"';
		}
	}

	if ( $q->filter_method ) {
		$where[] = $wpdb->prepare( 'log.request_method = %s', $q->filter_method );
	}

	$activity = null;
	if ( $q->filter_activity ) {
		$activity = absint( $q->filter_activity );
	}
	if ( $q->filter_set ) {
		$activity = implode( ',', crb_get_filter_set( $q->filter_set ) );
	}
	if ( $activity ) {
		$ret[3]   = $activity;
		$where[]  = 'act.activity IN (' . $activity . ')';
		$join_act = true;
	}

	if ( $q->filter_errors ) {
		$where[] = 'log.php_errors != ""';
	}

	// ---------------------------------------------------------------------------------

    $where = ( ! empty( $where ) ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// Limits, if specified
	if ( isset( $args['per_page'] ) ) {
		$per_page = $args['per_page'];
	}
	else {
		$per_page = crb_admin_get_per_page();
	}
	$per_page = absint( $per_page );
	$ret[2]   = $per_page;

	$cols = 'log.*';
	if ( ! empty( $args['columns'] ) ) {
		$cols = '';
		foreach ( $args['columns'] as $col_name ) {
			$cols .= ',log.' . preg_replace( '/[^A-Z_\d]/i', '', $col_name );
		}
		$cols = trim( $cols, ',' );
	}

	if ( $join_act ) {
		$join    = ' JOIN ' . CERBER_LOG_TABLE . ' act ON (log.session_id = act.session_id)';
	}

	if ( $per_page ) {
		$limit = cerber_get_sql_limit( $per_page );
		//$ret[0] = 'SELECT SQL_CALC_FOUND_ROWS log.session_id,log.* FROM ' . CERBER_TRAF_TABLE . " log USE INDEX FOR ORDER BY (stamp) {$where} ORDER BY stamp DESC {$limit}";
		$ret[0] = 'SELECT log.session_id,' . $cols . ' FROM ' . CERBER_TRAF_TABLE . " log {$join} {$where} ORDER BY log.stamp DESC {$limit}";
		//$ret[0] = 'SELECT SQL_CALC_FOUND_ROWS log.*,act.activity FROM ' . CERBER_TRAF_TABLE . ' log USE INDEX FOR ORDER BY (stamp) LEFT JOIN '.CERBER_LOG_TABLE." act ON (log.session_id = act.session_id) {$where} ORDER BY log.stamp DESC {$limit}";
	}
	else {
		$ret[0] = 'SELECT log.session_id,' . $cols . ' FROM ' . CERBER_TRAF_TABLE . " log {$join} {$where} ORDER BY stamp DESC"; // "ORDER BY" is mandatory!
	}

	$ret[1] = 'SELECT COUNT(log.stamp) FROM ' . CERBER_TRAF_TABLE . " log {$join} {$where}";

	return $ret;
}

/**
 * Traffic Search form HTML
 *
 * @return string
 *
 */
function crb_admin_traffic_search_form() {
	ob_start();
	?>
    <div id="crb-traffic-search" style="display: none;">
        <form id="crb-traffic-form" method="get" action="">
            <input type="hidden" name="page" value="cerber-traffic">

            <div>
                <div>
                    <p style="width: 100%;"><label>Activity</label>
		                <?php
		                echo crb_get_activity_dd();
		                ?>
                    </p>

                    <p><label for="search-traffic-url">URL contains</label>
                        <br/><input id="search-traffic-url" type="text" name="search_traffic[uri]"></p>

                    <p><label for="filter-method">Request method</label>
                        <select id="filter-method" name="filter_method">
                            <option value="0">Any</option>
                            <option>GET</option>
                            <option>POST</option>
                        </select></p>

                    <p><label for="search-traffic-fields">POST or GET fields contain</label>
                        <br/><input id="search-traffic-fields" type="text" name="search_traffic[fields]"></p>

                    <p><label for="search-traffic-details">Miscellaneous details contains</label>
                        <br/><input id="search-traffic-details" type="text" name="search_traffic[details]"></p>

                    <p><label for="filter-http-code">HTTP Response Code equals</label>
                        <br/><input id="filter-http-code" type="number" name="filter_http_code"></p>

                    <p><label for="search-traffic-date-from">Date from</label>
                        <br/><input id="search-traffic-date-from" type="date" name="search_traffic[date_from]"
                                    placeholder="month/day/year"></p>

                    <p><label for="search-traffic-date-to">Date to</label>
                        <br/><input id="search-traffic-date-to" type="date" name="search_traffic[date_to]"
                                    placeholder="month/day/year"></p>
                </div>
                <div>
                    <p><label for="search-traffic-ip">Remote IP address contains or equals</label>
                        <br/><input id="search-traffic-ip" type="text" name="search_traffic[ip]"></p>
                    <p>
                        <label for="">User</label>
                        <br/>
                    <?php
                    echo cerber_select( 'filter_user', array(), null, 'crb-select2-ajax', '', false, '', array( 'min_symbols' => 3 ) )
                    ?>
                    </p>

                    <p><label for="filter-user-alt">User ID</label>
                        <br/><input id="filter-user-alt" type="text" name="filter_user_alt" placeholder="Use comma to specify multiple IDs"></p>

                    <p><label for="filter-user-mode">User operator</label>
                        <select id="filter-user-mode" name="filter_user_mode">
                            <option value="0">Include</option>
                            <option value="1">Exclude</option>
                        </select></p>
                    <p><label for="filter-sid">RID</label>
                        <br/><input id="filter-sid" type="text" name="filter_sid" placeholder="Request ID"></p>
                    <p><label for="search-err">Software errors contain</label>
                        <br/><input id="search-err" type="text" name="search_traffic[errors]"></p>
                    <p class="crb-has-checkbox">
                        <input type="checkbox" name="filter_errors" id="filter-err" value="1"><label for="filter-err">Any software error</label>
                    </p>
                    <p style="margin-top: 3em;">The search uses AND logic for all non-empty fields</p>
                </div>
            </div>
            <div>
                <p><input type="submit" class="button button-primary" value="Search" style="width: 8rem;"></p>
            </div>
        </form>
    </div>

	<?php
	return ob_get_clean();
}

function cerber_get_wp_type( $wp_type ) {
	$types = array(
		515 => 'XML-RPC',
		520 => 'REST API'
	);

	if ( isset( $types[ $wp_type ] ) ) {
		return $types[ $wp_type ];
	}

	return '';
}

function cerber_get_err_type( $type ) {
	$list = array(
		'E_ERROR',
		'E_WARNING',
		'E_PARSE',
		'E_NOTICE',
		'E_CORE_ERROR',
		'E_CORE_WARNING',
		'E_COMPILE_ERROR',
		'E_COMPILE_WARNING',
		'E_USER_ERROR',
		'E_USER_WARNING',
		'E_USER_NOTICE',
		'E_STRICT',
		'E_RECOVERABLE_ERROR',
		'E_DEPRECATED',
		'E_USER_DEPRECATED',
		'E_ALL',
	);

	return $list[ intval( log( $type, 2 ) ) ];
}

/**
 * Check if admin AJAX is permitted.
 *
 */
function cerber_check_ajax_permissions( $strict = true ) {
	if ( nexus_is_valid_request() ) {
	    /*
		$nonce = crb_array_get( nexus_request_data()->get_params, 'ajax_nonce' );
		if ( ! $nonce ) {
			$nonce = nexus_request_data()->get_post_data( 'ajax_nonce' );
		}
		if ( ! wp_verify_nonce( $nonce, 'crb-ajax-admin' ) ) {
			return false;
		}
	    */

		return true;
	}

	check_ajax_referer( 'crb-ajax-admin', 'ajax_nonce' );

	if ( ! cerber_user_can_manage() ) {
		if ( $strict ) {
			wp_die( 'Oops! Access denied.' );
		}

		return 0;
	}

	return true;
}

function crb_admin_allowed_ajax( $action ) {
	$list = array(
		'cerber_ajax',
		'cerber_scan_control',
		'cerber_ref_upload',
		'cerber_view_file',
		'cerber_scan_bulk_files'
	);

	return in_array( $action, $list );
}

function crb_is_task_permitted( $die = false ) {
	if ( is_super_admin()
	     || nexus_is_valid_request() ) {
		return true;
	}
	if ( $die ) {
		wp_die( 'Oops! Access denied.' );
	}

	return false;
}

/**
 * Identify and render Cerber's admin page
 *
 * @param string $page_id
 * @param string $active_tab
 */
function cerber_render_admin_page( $page_id = '', $active_tab = '' ) {

    if ( nexus_get_context() ) {
		nexus_show_remote_page();

		return;
	}

	$error = '';

	if ( $page = cerber_get_admin_page_config( $page_id ) ) {
		if ( ! empty( $page['pro'] ) && ! lab_lab() ) {
			$error = 'This feature requires the PRO version of the plugin.';
		}
		else {
			if ( ( $tab_filter = crb_array_get( $page, 'tab_filter' ) )
			     && is_callable( $tab_filter ) ) {
				$page['tabs'] = call_user_func( $tab_filter, $page['tabs'] );
			}

			cerber_show_admin_page( $page['title'], $page['tabs'], $active_tab, $page['callback'] );
		}
	}
	else {
		$error = 'Unknown admin page: ' . htmlspecialchars( $page_id );
	}

	if ( $error ) {
		echo '<div class="crb-generic-error">ERROR: ' . $error . ' on ' . get_bloginfo( 'name' ) . '</div>';
	}
}

function cerber_get_admin_page_config( $page = '' ) {
	if ( ! $page ) {
		if ( ! $page = crb_admin_get_page() ) {
			return false;
		}
	}

	if ( $config = crb_addon_admin_page( $page ) ) {
		return $config;
	}

	$admin_pages = array(
		'cerber-security'  => array(
			'title'      => 'WP Cerber Security',
			'tabs'       => array(
				'dashboard'     => array( 'bxs-dashboard', __( 'Dashboard', 'wp-cerber' ) ),
				'activity'      => array( 'bx-pulse', __( 'Activity', 'wp-cerber' ) ),
				'sessions'      => array( 'bx-group', __( 'Sessions', 'wp-cerber' ) ),
				'lockouts'      => array( 'bxs-shield', __( 'Lockouts', 'wp-cerber' ) ),
				'main'          => array( 'bx-slider', __( 'Main Settings', 'wp-cerber' ) ),
				'acl'           => array( 'bx-lock', __( 'Access Lists', 'wp-cerber' ) ),
				'hardening'     => array( 'bx-shield-alt', __( 'Hardening', 'wp-cerber' ) ),
				//'users'         => array( 'bx-group', __( 'Users', 'wp-cerber' ) ),
				'notifications' => array( 'bx-bell', __( 'Notifications', 'wp-cerber' ) ),
			),
			'tab_filter' => function ( $tabs ) {
				crb_del_expired_blocks();
				$blocked = cerber_blocked_num();
				$acl = cerber_db_get_var( 'SELECT COUNT(ip) FROM ' . CERBER_ACL_TABLE . ' WHERE acl_slice = 0' );
				crb_sessions_del_expired();
				$uss = crb_sessions_get_num();
				if ( ! $uss ) {
					cerber_bg_task_add( 'crb_sessions_sync_all' );
				}
				$tabs['sessions'][1] .= ' <sup>' . $uss . '</sup>';
				$tabs['lockouts'][1] .= ' <sup>' . $blocked . '</sup>';
				$tabs['acl'][1] .= ' <sup>' . $acl . '</sup>';

				return $tabs;
			},
			'callback'   => function ( $tab ) {
				switch ( $tab ) {
					case 'acl':
						cerber_acl_form();
						break;
					case 'activity':
						cerber_show_activity();
						break;
					case 'sessions':
						crb_admin_show_sessions();
						break;
					case 'lockouts':
						cerber_show_lockouts();
						break;
					case 'help':
						cerber_show_help();
						break;
					case 'dashboard':
						cerber_show_dashboard();
						break;
					default:
						cerber_show_settings_form( $tab );
				}
			}
		),
		'cerber-recaptcha' => array(
			'title'    => __( 'Anti-spam and bot detection settings', 'wp-cerber' ),
			'tabs'     => array(
				'antispam' => array( 'bx-chip', __( 'Anti-spam engine', 'wp-cerber' ) ),
				'captcha'  => array( 'bxl-google', 'reCAPTCHA' ),
			),
			'callback' => function ( $tab ) {

				switch ( $tab ) {
					case 'captcha':
						$group = 'recaptcha';
						break;
					default:
						$group = 'antispam';
				}

				cerber_show_settings_form( $group );

			}
		),
		'cerber-traffic'   => array(
			'title'    => __( 'Traffic Inspector', 'wp-cerber' ),
			'tabs'     => array(
				'traffic'     => array( 'bx-show', __( 'Live Traffic', 'wp-cerber' ) ),
				'ti_settings' => array( 'bx-slider', __( 'Settings', 'wp-cerber' ) ),
			),
			'callback' => function ( $tab ) {
				switch ( $tab ) {
					case 'ti_settings':
						cerber_show_settings_form( 'traffic' );
						break;
					default:
						cerber_show_traffic();
				}
			}
		),
		'cerber-shield'    => array(
			'title'    => __( 'Data Shield Policies', 'wp-cerber' ),
			'tabs'     => array(
				'user_shield' => array( 'bx-group', __( 'Accounts & Roles', 'wp-cerber' ) ),
				'opt_shield'  => array( 'bx-slider', __( 'Site Settings', 'wp-cerber' ) ),
			),
			'callback' => function ( $tab ) {
				cerber_show_settings_form( $tab );
			}
		),
		'cerber-users'     => array(
			'title'    => __( 'User Policies', 'wp-cerber' ),
			'tabs'     => array(
				'role_policies'   => array( 'bx-group', __( 'Role-Based', 'wp-cerber' ) ),
				'global_policies' => array( 'bx-user-detail', __( 'Global', 'wp-cerber' ) ),
			),
			'callback' => function ( $tab ) {
				switch ( $tab ) {
					case 'role_policies':
						crb_admin_show_role_policies();
						break;
					case 'global_policies':
						cerber_show_settings_form( 'users' );
						break;
					default:
						cerber_show_settings_form( $tab );
				}
			}
		),
		'cerber-rules'     => array(
			'pro'      => 1,
			'title'    => __( 'Security Rules', 'wp-cerber' ),
			'tabs'     => array(
				'geo' => array( 'bxs-world', __( 'Countries', 'wp-cerber' ) ),
			),
			'callback' => function ( $tab ) {
				switch ( $tab ) {
					case 'geo':
						crb_admin_show_geo_rules();
						break;
					default:
						crb_admin_show_geo_rules();
				}
			}
		),
		'cerber-integrity' => array(
			'title'    => __( 'Site Integrity', 'wp-cerber' ),
			'tabs'     => array(
				'scan_main'       => array( 'bx-radar', __( 'Security Scanner', 'wp-cerber' ) ),
				'scan_settings'   => array( 'bxs-slider-alt', __( 'Settings', 'wp-cerber' ) ),
				'scan_schedule'   => array( 'bx-time', __( 'Scheduling', 'wp-cerber' ) ),
				'scan_policy'     => array( 'bx-bolt', __( 'Cleaning up', 'wp-cerber' ) ),
				'scan_ignore'     => array( 'bx-hide', __( 'Ignore List', 'wp-cerber' ) ),
				'scan_quarantine' => array( 'bx-trash', __( 'Quarantine', 'wp-cerber' ) ),
                'scan_insights' => array( 'bx-flask', __( 'Analytics', 'wp-cerber' ) ),
			),
			'tab_filter' => function ( $tabs ) {
				$numi = 0;
				if ( $list = cerber_get_set( 'ignore-list' ) ) {
					$numi = count( $list );
				}
				$numq = cerber_get_set( 'quarantined_total', null, false );
				if ( ! is_numeric( $numq ) ) {
					cerber_bg_task_add( '_crb_qr_total_sync' );
					$numq = '';
				}
				$tabs['scan_quarantine'][1] .= ' <sup>' . $numq . '</sup>';
				$tabs['scan_ignore'][1] .= ' <sup>' . $numi . '</sup>';

				return $tabs;
			},
			'callback' => function ( $tab ) {
				switch ( $tab ) {
					case 'scan_settings':
						cerber_show_settings_form( 'scanner' );
						break;
					case 'scan_schedule':
						cerber_show_settings_form( 'schedule' );
						break;
					case 'scan_policy':
						cerber_show_settings_form( 'policies' );
						break;
					case 'scan_quarantine':
						cerber_show_quarantine();
						break;
					case 'scan_ignore':
						cerber_show_ignore();
						break;
					case 'scan_insights':
						cerber_scan_insights();
						break;
					case 'help':
						cerber_show_help();
						break;
					default:
						cerber_show_scanner();
				}
			}
		),
		'cerber-tools'     => array(
			'title'    => __( 'Tools', 'wp-cerber' ),
			'tabs'     => array(
				//'imex'       => array( 'bx-layer', __( 'Export & Import', 'wp-cerber' ) ),
				'imex'       => array( 'bx-layer', __( 'Manage Settings', 'wp-cerber' ) ),
				'diagnostic' => array( 'bx-wrench', __( 'Diagnostic', 'wp-cerber' ) ),
				'diag-log'   => array( 'bx-bug', __( 'Diagnostic Log', 'wp-cerber' ) ),
				'change-log' => array( 'bx-collection', __( 'Changelog', 'wp-cerber' ) ),
				'license'    => array( 'bx-key', __( 'License', 'wp-cerber' ) ),
			),
			'callback' => function ( $tab ) {
				switch ( $tab ) {
					case 'diagnostic':
						cerber_show_diag();
						break;
					case 'license':
						cerber_show_lic();
						break;
					case 'diag-log':
						cerber_show_diag_log();
						break;
					case 'change-log':
						cerber_show_change_log();
						break;
					case 'help':
						cerber_show_help();
						break;
					default:
						if ( ! nexus_is_valid_request() ) {
							cerber_show_imex();
						}
						else {
							echo 'This admin page is not available in this mode.';
						}
				}
			}
		),
	);

	return crb_array_get( $admin_pages, $page );
}

function crb_admin_parse_query( $fields, $alt = array() ) {
	$arr = crb_get_query_params();
	$ret = array();

	foreach ( $fields as $field ) {

	    if ( isset( $alt[ $field ] ) ) {
			$val = $alt[ $field ];
		}
		else {
	        $val = crb_array_get( $arr, $field, false );
        }

		if ( is_array( $val ) ) {
			$val = array_map( 'trim', $val );
		}
        elseif ( $val !== false ) {
			$val = trim( $val );
		}

		$ret[ $field ] = $val;
	}

	return (object) $ret;
}

function crb_admin_get_page() {
	if ( nexus_is_valid_request() ) {
		$page = nexus_request_data()->page;
	}
	else {
		$page = cerber_get_get( 'page', '[A-Z0-9\_\-]+' );
	}
	return $page;
}

function crb_admin_get_tab( $tabs = array() ) {
	if ( nexus_is_valid_request() ) {
		$tab = nexus_request_data()->tab;
	}
	else {
		$tab = cerber_get_get( 'tab', '[\w\-]+' );
	}

	if ( empty( $tabs ) ) {
		return $tab;
	}

	$tabs['help'] = 1; // always must be in the array

	if ( ! $tab || ! isset( $tabs[ $tab ] ) ) {
		reset( $tabs );
		$tab = key( $tabs );
	}

	return $tab;
}

function cerber_show_tabs( $active, $tabs = array() ) {
	echo '<h2 class="nav-tab-wrapper cerber-tabs">';

	$args = array( 'page' => crb_admin_get_page() );

	foreach ( $tabs as $tab => $data ) {
		echo '<a href="' . cerber_admin_link( $tab, $args ) . '" class="nav-tab ' . ( $tab == $active ? 'nav-tab-active' : '' ) . '"><i class="crb-icon crb-icon-' . $data[0] . '"></i> ' . $data[1] . '</a>';
	}

	echo '<a href="' . cerber_admin_link( 'help', $args ) . '" class="nav-tab ' . ( $active == 'help' ? 'nav-tab-active' : '' ) . '"><i class="crb-icon crb-icon-bx-idea"></i> ' . __( 'Help', 'wp-cerber' ) . '</a>';

	$lab = lab_indicator();
	$ro  = '';
	if ( nexus_is_valid_request() && ! nexus_is_granted( 'submit' ) ) {
		$ro = '<span style="font-weight: bold; font-size: 0.8em; color: #f00;">Read-only mode</span>';
	}

	echo '<div style="float: right;">' . $ro . ' ' . $lab . '</div>';

	echo '</h2>';
}

// Access Lists (ACL) ---------------------------------------------------------

/**
 * @param string $ip
 * @param string $tag
 * @param string $comment
 * @param int $acl_slice
 *
 * @return bool|WP_Error
 */
function cerber_acl_add( $ip, $tag, $comment = '', $acl_slice = 0 ) {
	global $wpdb;

	$ip = trim( $ip );

	$acl_slice = absint( $acl_slice );
	$v6range = '';
	$ver6 = 0;

	if ( cerber_is_ipv4( $ip ) ) {
		$begin = ip2long( $ip );
		$end   = ip2long( $ip );
	}
    elseif ( cerber_is_ipv6( $ip ) ) {
		$ip = cerber_ipv6_short( $ip );
		list( $begin, $end, $v6range ) = crb_ipv6_prepare( $ip, $ip );
	    $ver6 = 1;
	}
    elseif ( ( $range = cerber_any2range( $ip ) )
	         && is_array( $range ) ) {
		$ver6    = $range['IPV6'];
		$begin   = $range['begin'];
		$end     = $range['end'];
		$v6range = $range['IPV6range'];
	}
	else {
		return new WP_Error( 'acl_wrong_ip', __( 'Incorrect IP address or IP range', 'wp-cerber' ) . ' ' . $ip );
	}

	if ( cerber_db_get_var( 'SELECT ip FROM ' . CERBER_ACL_TABLE . ' WHERE acl_slice = ' . $acl_slice . ' AND ver6 = ' . $ver6 . ' AND ip_long_begin = ' . $begin . ' AND ip_long_end = ' . $end . ' AND v6range = "' . $v6range . '" LIMIT 1' ) ) {
		return new WP_Error( 'acl_wrong_ip', __( 'The IP address you are trying to add is already in the list', 'wp-cerber' ) );
	}

	$result = $wpdb->insert( CERBER_ACL_TABLE, array(
		'ip'            => $ip,
		'ip_long_begin' => $begin,
		'ip_long_end'   => $end,
		'tag'           => $tag,
		'comments'      => $comment,
		'acl_slice'     => $acl_slice,
		'v6range'       => $v6range,
		'ver6'          => $ver6,
	), array( '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%d' ) );

	if ( ! $result ) {
		return new WP_Error( 'acl_db_error', $wpdb->last_error );
	}

	crb_event_handler( 'ip_event', array(
		'e_type'   => 'acl_add',
		'ip'       => $ip,
		'tag'      => $tag,
		'slice'    => $acl_slice,
		'comments' => $comment,
	) );

	return true;

}

function cerber_add_white( $ip, $comment = '' ) {
	return cerber_acl_add( $ip, 'W', $comment );
}

function cerber_add_black( $ip, $comment = '' ) {
	return cerber_acl_add( $ip, 'B', $comment );
}

function cerber_acl_remove( $ip, $acl_slice = 0 ) {

	if ( ! is_numeric( $acl_slice ) ) {
		return false;
	}

	$acl_slice = absint( $acl_slice );
	$ip  = preg_replace( CRB_IP_NET_RANGE, ' ', $ip );

	$ret = cerber_db_query( 'DELETE FROM ' . CERBER_ACL_TABLE . ' WHERE acl_slice = ' . $acl_slice . ' AND ip = "' . $ip . '"' );

	crb_event_handler( 'ip_event', array(
		'e_type' => 'acl_remove',
		'ip'     => $ip,
		'slice'  => $acl_slice,
		'result' => $ret
	) );

	return $ret;
}

/**
 * Can a given IP be added to the blacklist
 *
 * @param $ip string A candidate to be added to the list
 * @param $list string
 *
 * @return bool True if IP can be listed
 */
function cerber_can_be_listed( $ip, $list = 'B' ) {

    if ( $list == 'B' ) {

		$admin_ip = cerber_get_remote_ip();

		if ( cerber_is_ip( $ip ) ) {
			if ( $admin_ip == cerber_ipv6_short( $ip ) ) {
				return false;
			}

			return true;
		}

		// $ip = range

		if ( crb_acl_is_white( $admin_ip ) ) {
			return true;
		}

		if ( ! $range = cerber_any2range( $ip ) ) {
			return false;
		}

		if ( cerber_is_ip_in_range( $range, $admin_ip ) ) {
			return false;
		}

		return true;

	}

	return true;
}

/**
 * Bulk action for WP_List_Table
 *
 * @return bool|array|string
 */
function cerber_get_bulk_action() {

	// GET
	if ( cerber_is_http_get() ) {
		if ( ( $ac = crb_get_query_params( 'action', '[\w\-]+' ) ) && $ac != '-1' ) {
			return $ac;
		}
		if ( ( $ac = crb_get_query_params( 'action2', '[\w\-]+' ) ) && $ac != '-1' ) {
			return $ac;
		}

		return false;
	}

	// POST
	if ( ( $ac = crb_get_post_fields( 'action', false, '[\w\-]+' ) ) && $ac != '-1' ) {
		return $ac;
	}
	if ( ( $ac = crb_get_post_fields( 'action2', false, '[\w\-]+' ) ) && $ac != '-1' ) {
		return $ac;
	}

	return false;
}

function crb_admin_cool_features() {
	return
		'<div class="crb-pro-req">' .
		__( 'These features are available in the professional version of WP Cerber.', 'wp-cerber' ) .
		'<br/><br/>' . __( 'Know more about all advantages at', 'wp-cerber' ) .
		' <a href="https://wpcerber.com/pro/" target="_blank">https://wpcerber.com/pro/</a>
        </div>';
}

/**
 * @param string $url
 * @param string $ancor
 * @param string $msg
 *
 * @return string
 */
function crb_confirmation_link( $url, $ancor, $msg = '' ) {
	if ( ! $msg ) {
		$msg = __( 'Are you sure?', 'wp-cerber' );
	}

	return '<a href="' . $url . '" onclick="return confirm(\'' . $msg . '\');">' . $ancor . '</a>';
}

// Setting up menu editor -----------------------------------------------------

add_action( 'admin_head-nav-menus.php', function () {
	add_meta_box( 'wp_cerber_nav_menu',
		'WP Cerber',
		'cerber_nav_menu_box',
		'nav-menus',
		'side',
		'low' );
} );

function cerber_nav_menu_box() {

    // Warning: do not change array indexes
	$list = array(
		'login-url'  => __( 'Log In', 'wp-cerber' ),
		'logout-url' => __( 'Log Out', 'wp-cerber' ),
		'reg-url'    => __( 'Register', 'wp-cerber' ),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$list['wc-login-url']  = __( 'WooCommerce Log In', 'wp-cerber' );
		$list['wc-logout-url'] = __( 'WooCommerce Log Out', 'wp-cerber' );
	}

	?>
    <div id="posttype-wp-cerber-nav" class="posttypediv">
        <div id="tabs-panel-wp-cerber-nav" class="tabs-panel tabs-panel-active">
            <ul id="wp-cerber-nav-checklist" class="categorychecklist form-no-clear">
				<?php
				$i = - 1;
				foreach ( $list as $key => $value ) :
					$id = 'wp-cerber-' . $key;
					?>
                    <li>
                        <label class="menu-item-title">
                            <input type="checkbox" class="menu-item-checkbox"
                                   name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]"
                                   value="<?php echo esc_attr( $i ); ?>"/> <?php echo esc_html( $value ); ?>
                        </label>
                        <input type="hidden" class="menu-item-type"
                               name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom"/>
                        <input type="hidden" class="menu-item-title"
                               name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]"
                               value="<?php echo esc_html( $value ); ?>"/>
                        <input type="hidden" class="menu-item-url"
                               name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]"
                               value="<?php echo '#will-be-generated-automatically'; ?>"/>
                        <input type="hidden" class="menu-item-attr-title"
                               name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-attr-title]"
                               value="<?php echo '*MENU*CERBER*|' . $id; ?>"/>
                    </li>
					<?php
					$i --;
				endforeach;
				?>
            </ul>
        </div>
        <p class="button-controls">
            <span class="add-to-menu">
					<button type="submit" class="button-secondary submit-add-to-menu right"
                            value="<?php esc_attr_e( 'Add to menu' ); ?>" name="add-post-type-menu-item"
                            id="submit-posttype-wp-cerber-nav"><?php esc_html_e( 'Add to menu' ); ?></button>
					<span class="spinner"></span>
            </span>
        </p>
    </div>
	<?php
}