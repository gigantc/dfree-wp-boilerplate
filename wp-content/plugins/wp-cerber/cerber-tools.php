<?php
/*
	Copyright (C) 2015-19 CERBER TECH INC., https://cerber.tech
	Copyright (C) 2015-19 CERBER TECH INC., https://wpcerber.com

    Licenced under the GNU GPL

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

function cerber_show_imex(){
	$form = '<h3>'.__('Export settings to the file','wp-cerber').'</h3>';
	$form .= '<p>'.__('When you click the button below you will get a configuration file, which you can upload on another site.','wp-cerber').'</p>';
	$form .= '<p>'.__('What do you want to export?','wp-cerber').'</p><form action="" method="get">';
	$form .= '<input id="exportset" name="exportset" value="1" type="checkbox" checked> <label for="exportset">'.__('Settings','wp-cerber').'</label>';
	$form .= '<p><input id="exportacl" name="exportacl" value="1" type="checkbox" checked> <label for="exportacl">'.__('Access Lists','wp-cerber').'</label>';
	$form .= '<p><input type="submit" name="cerber_export" id="submit" class="button button-primary" value="'.__('Download file','wp-cerber').'"></form>';

	$form .= '<h3 style="margin-top:2em;">'.__('Import settings from the file','wp-cerber').'</h3>';
	$form .= '<p>'.__('When you click the button below, file will be uploaded and all existing settings will be overridden.','wp-cerber').'</p>';
	$form .= '<p>'.__('Select file to import.','wp-cerber').' '. sprintf( __( 'Maximum upload file size: %s.'), esc_html(size_format(wp_max_upload_size())));
	$form .= '<form action="" method="post" enctype="multipart/form-data">'.wp_nonce_field( 'crb_import', 'crb_field');
	$form .= '<p><input type="file" name="ifile" id="ifile" required="required">';
	$form .= '<p>'.__('What do you want to import?','wp-cerber').'</p><p><input id="importset" name="importset" value="1" type="checkbox" checked> <label for="importset">'.__('Settings','wp-cerber').'</label>';
	$form .= '<p><input id="importacl" name="importacl" value="1" type="checkbox" checked> <label for="importacl">'.__('Access Lists','wp-cerber').'</label>';
	$form .= '<p><input type="submit" name="cerber_import" id="submit" class="button button-primary" value="'.__('Upload file','wp-cerber').'"></p></form>';
	echo $form;
}
/*
	Create export file
*/
add_action('admin_init','cerber_export');
function cerber_export(){
	if ( !cerber_is_http_get() || ! isset( $_GET['cerber_export'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Error!' );
	}
	$p = cerber_plugin_data();
	$data = array('cerber_version' => $p['Version'],'home'=> cerber_get_home_url(),'date'=>date('d M Y H:i:s'));
	if (!empty($_GET['exportset'])) {
	    $data ['options'] = crb_get_settings();
		$data ['geo-rules'] = cerber_get_geo_rules();
	}
	if ( ! empty( $_GET['exportacl'] ) ) {
		$data ['acl'] = cerber_acl_all( 'ip, tag, comments' );
	}
	$file = json_encode($data);
	$file .= '==/'.strlen($file).'/'.crc32($file).'/EOF';
	header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
	header("Content-type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=wpcerber.config");
	echo $file;
	exit;
}

/**
 * Import plugin settings from a file
 *
 */
add_action( 'admin_init', 'cerber_import' );
function cerber_import() {
	global $wpdb, $wp_cerber;
	if ( ! isset( $_POST['cerber_import'] ) || ! cerber_is_http_post() ) {
		return;
	}
	check_admin_referer( 'crb_import', 'crb_field' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Upload failed.' );
	}
	$ok = true;
	if ( ! is_uploaded_file( $_FILES['ifile']['tmp_name'] ) ) {
		cerber_admin_notice( __( 'No file was uploaded or file is corrupted', 'wp-cerber' ) );

		return;
	}
    elseif ( $file = file_get_contents( $_FILES['ifile']['tmp_name'] ) ) {
		$p    = strrpos( $file, '==/' );
		$data = substr( $file, 0, $p );
		$sys  = explode( '/', substr( $file, $p ) );
		if ( $sys[3] == 'EOF' && crc32( $data ) == $sys[2] && ( $data = json_decode( $data, true ) ) ) {

			if ( $_POST['importset'] && $data['options'] && ! empty( $data['options'] ) && is_array( $data['options'] ) ) {
				$data['options']['loginpath'] = urldecode( $data['options']['loginpath'] ); // needed for filter cerber_sanitize_m()
				if ( $data['home'] != cerber_get_home_url() ) {
					$data['options']['sitekey']   = $wp_cerber->getSettings( 'sitekey' );
					$data['options']['secretkey'] = $wp_cerber->getSettings( 'secretkey' );
				}
				cerber_save_settings( $data['options'] ); // @since 2.0
				if ( isset( $data['geo-rules'] ) ) {
					update_site_option( 'geo_rule_set', $data['geo-rules'] );
				}
				if ( ! empty( $data['options']['crb_role_policies'] ) ) {
					update_site_option( CERBER_SETTINGS, array( 'crb_role_policies' => $data['options']['crb_role_policies'] ) );
				}
			}

			if ( isset( $_POST['importacl'] )
			     && ! empty( $data['acl'] )
			     && is_array( $data['acl'] ) ) {
				$acl_ok = true;
				if ( false === $wpdb->query( "DELETE FROM " . CERBER_ACL_TABLE ) ) {
					$acl_ok = false;
				}
				foreach ( $data['acl'] as $row ) {
					// if (!$wpdb->query($wpdb->prepare('INSERT INTO '.CERBER_ACL_TABLE.' (ip,tag,comments) VALUES (%s,%s,%s)',$row[0],$row[1],$row[2]))) $acl_ok = false;
					// @since 3.1 if (!$wpdb->insert(CERBER_ACL_TABLE,array('ip'=>$row[0],'tag'=>$row[1],'comments'=>$row[2]),array('%s','%s','%s'))) $acl_ok = false;
					$ip = cerber_parse_ip( $row[0] );
					if ( ! cerber_acl_add( $ip, $row[1], $row[2] ) ) {
						$acl_ok = false;
						break;
					}
				}
				if ( ! $acl_ok ) {
					cerber_admin_notice( __( 'Error while updating', 'wp-cerber' ) . ' ' . __( 'Access Lists', 'wp-cerber' ) );
				}
			}

			cerber_upgrade_settings(); // In case it was settings from an older version

			cerber_admin_message( __( 'Settings has imported successfully from', 'wp-cerber' ) . ' ' . $_FILES['ifile']['name'] );
		}
		else {
			$ok = false;
		}
    }
	if ( ! $ok ) {
		cerber_admin_notice( __( 'Error while parsing file', 'wp-cerber' ) );
	}
}

/**
 * Displays admin diagnostic page
 */
function cerber_show_diag(){
	$sections = array();

	if ( $d = cerber_environment_diag() ) {
		$sections [] = $d;
	}

    ?>
    <!-- <h3 style="margin-top: 3em;">Diagnostic and maintenance</h3>
    <a href="javascript:void(0)"  onclick="toggle_visibility('diagnostic'); return false;">Show diagnostic information</a>
    -->
    <form id="diagnostic">
        <?php
        foreach ($sections as $section){
	        echo '<div class="diag-section">';
	        echo '<h3>'.$section[0].'</h3>';
	        echo $section[1];
	        echo '</div>';
        }
        ?>
        <div class="diag-section">
            <h3>System Info</h3>
            <div class="diag-text"><?php cerber_show_wp_diag(); ?></div>
        </div>
        <div class="diag-section">
            <h3>Database Info</h3>
			<?php echo cerber_db_diag(); ?>
			<?php echo '<p style="text-align: right;"><a class="button button-secondary" href="' . wp_nonce_url( add_query_arg( array( 'force_repair_db' => 1 ) ), 'control', 'cerber_nonce' ) . '"><span class="dashicons dashicons-admin-tools" style="vertical-align: middle;"></span> Repair tables</a></p>'; ?>
        </div>
        <div class="diag-section">
            <h3>Server Info</h3>
            <textarea name="dia" class="code"><?php
				echo 'PHP version: ' . phpversion() . "\n";
				$server = $_SERVER;
				unset($server['HTTP_COOKIE']);
				foreach ( $server as $key => $value ) {
					echo '[' . $key . '] => ' . @strip_tags( $value ) . "\n";
				}
				?>
			</textarea>
        </div>
        <div class="diag-section">
            <h3>Cerber Security Cloud Status</h3>
			<?php
			echo lab_status();
			?>
            <p style="text-align: right;">
                <a class="button button-secondary" href="<?php echo wp_nonce_url( add_query_arg( array( 'clean_up_the_cache' => 1 ) ), 'control', 'cerber_nonce' ); ?>">Clear up cache</a>
                <a class="button button-secondary" href="<?php echo wp_nonce_url( add_query_arg( array( 'force_check_nodes' => 1 ) ), 'control', 'cerber_nonce' ); ?>">Recheck node status</a>
            </p>
        </div>
	    <?php

        echo '<div class="diag-section"><h3>Maintenance Tasks</h3>';
	    cerber_cron_diag();
	    echo '</div>';

	    if ( $report = get_site_option( '_cerber_report' ) ) {
	        echo '<div class="diag-section"><h3>Reports</h3>';
		    echo cerber_ago_time($report[0]).' ('.cerber_date($report[0]).')';
		    if ($report[1]) {
		        echo ' OK / '.get_site_transient( 'crb_hourly_2' );
            }
            else {
	            echo ' Unable to send email';
            }
		    echo '</div>';
	    }
	    if ( $subs = get_site_option( '_cerber_subs' ) ) {
		    echo '
            <div class="diag-section">
            <h3>Subscriptions for notifications</h3>';
		    echo '<ol>';
		    foreach ( $subs as $hash => $sub ) {
                echo '<li> '.$hash.' | <a href = "' .cerber_admin_link( 'activity' ).'&unsubscribeme='.$hash.'">'.__( 'Unsubscribe', 'wp-cerber' ).'</a></li>';
		    }
		    echo '</ol>';
		    echo '</div>';
	    }
	    ?>
    </form>
    <script type="text/javascript">
        function toggle_visibility(id) {
            var e = document.getElementById(id);
            if(e.style.display === 'block')
                e.style.display = 'none';
            else
                e.style.display = 'block';
        }
    </script>
	<?php
}

function cerber_show_lic() {
	$key = lab_get_key();
	$valid = '';
	if ( ! empty( $key[2] ) ) {
		$lic = $key[2];
		if ( $expires = lab_validate_lic( $lic ) ) {
			$valid = '
                <p><span style="color: green;">This key is valid until '.$expires.'</span></p>
                <p>To move the key to another website or web server, please follow these steps: <a href="https://my.wpcerber.com/how-to-move-license-key/" target="_blank">https://my.wpcerber.com/how-to-move-license-key/</a></p>';
		}
		else {
			$valid = '<p><span style="color: red;">This license key is invalid or expired</span></p>
			<p>If you believe this key is valid, please follow these steps: <a href="https://my.wpcerber.com/how-to-fix-invalid-or-expired-key/" target="_blank">https://my.wpcerber.com/how-to-fix-invalid-or-expired-key/</a></p>';
		}
	}
	else {
		$lic = '';
	}
	?>
    <form method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">License key for the PRO version</th>
                <td>
                    <input name="cerber_license" value="<?php echo $lic; ?>" size="<?php echo LAB_KEY_LENGTH; ?>" maxlength="<?php echo LAB_KEY_LENGTH; ?>" type="text" class="crb-monospace" placeholder="Enter the license key here">
                    <?php echo $valid; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Site ID</th>
                <td>
		            <?php echo '<p class="crb-monospace">'.$key[0].'</p>'; ?>
                </td>
            </tr>
            <tbody>
        </table>
        <div style="padding-left: 220px">
            <input type="hidden" name="cerber_admin_do" value="install_key">
			<?php
			cerber_nonce_field( 'control', true );
            submit_button();
            ?>
        </div>
    </form>
	<?php
}



function cerber_show_wp_diag(){
	global $wpdb;

	$ret = array();

	$tz = date_default_timezone_get();
	$tz = ( $tz !== 'UTC' ) ? '<span style="color: red;">' . $tz . '!</span>' : $tz;

	$sys = array(
		array( 'Server ', $_SERVER['SERVER_SOFTWARE'] ),
		array( 'PHP version ', phpversion() ),
		array( 'Server API ', php_sapi_name() ),
		array( 'WordPress version', cerber_get_wp_version() ),
		array( 'WordPress locale', get_locale() ),
		array( 'Options DB table', $wpdb->prefix . 'options' ),
		array( 'Server platform', PHP_OS ),
		array( 'Memory limit', @ini_get( 'memory_limit' ) ),
		array( 'Default PHP timezone', $tz ),
	);

	if ( nexus_is_valid_request() ) {
		$sys[] = array( 'The IP address of the master is detected as', cerber_get_remote_ip() );
	}
	else {
		$sys[] = array( 'Your IP address is detected as', cerber_get_remote_ip() );
	}

	$ret[] = cerber_make_plain_table( $sys );

	$folder = cerber_get_the_folder();
	if ( is_wp_error( $folder ) ) {
		$folder = $folder->get_error_message();
	}
	else {
		$folder .= 'quarantine' . DIRECTORY_SEPARATOR;
	}



	$folders = array(
		array( 'WordPress root folder (ABSPATH) ', ABSPATH ),
		array( 'WordPress uploads folder', cerber_get_upload_dir() ),
		array( 'WordPress content folder', dirname( cerber_get_plugins_dir() ) ),
		array( 'WordPress plugins folder', cerber_get_plugins_dir() ),
		array( 'WordPress themes folder', cerber_get_themes_dir() ),
		array( 'WordPress must use plugin folder (WPMU_PLUGIN_DIR) ', WPMU_PLUGIN_DIR ),
		array( 'PHP folder for uploading files', ini_get( 'upload_tmp_dir' ) ),
		array( 'Server folder for temporary files', sys_get_temp_dir() ),
		array( 'Server folder for user session data', session_save_path() ),
		array( 'Security scanner quarantine folder', $folder ),
		array( 'Cerber\'s diagnostic log', cerber_get_diag_log() )
	);

	if ( file_exists( ABSPATH . 'wp-config.php' )) {
	    $config = ABSPATH . 'wp-config.php';
	}
    elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
		$config = dirname( ABSPATH ) . '/wp-config.php';
	}
	else {
		$config = 'None?';
    }
	$folders[] = array( 'WordPress config file', $config );

	if ( file_exists( ABSPATH . '.htaccess' ) ) {
		$folders[] = array( 'Main .htaccess file', ABSPATH . '.htaccess' );
	}

	foreach ( $folders as &$folder ) {
		$folder[2] = '';
		$folder[3] = '';
		if ( @file_exists( $folder[1] ) ) {
			if ( wp_is_writable( $folder[1] ) ) {
				$folder[2] = 'Writable';
			}
			else {
				$folder[2] = 'Write protected';
			}
			$folder[3] = cerber_get_chmod( $folder[1] );
		}
		else {
			$folder[2] = 'Not found (no access)';
		}
	}


	$folders[] = array( 'Directory separator', DIRECTORY_SEPARATOR );

	$ret[] = '<p>File system</p>'.cerber_make_plain_table( $folders );

	if ( is_multisite() ) {
		$mu = array();
		if ( defined( 'UPLOADS' ) ) {
			$mu[] = array( 'UPLOADS', UPLOADS );
		}
		if ( defined( 'BLOGUPLOADDIR' ) ) {
			$mu[] = array( 'BLOGUPLOADDIR', BLOGUPLOADDIR );
		}
		if ( defined( 'UPLOADBLOGSDIR' ) ) {
			$mu[] = array( 'UPLOADBLOGSDIR', UPLOADBLOGSDIR );
		}

		$mu[] = array( 'Uploads folder for sites', cerber_get_upload_dir_mu() );

		$ret[] = '<p>Multisite system constant</p>' . cerber_make_plain_table( $mu );
	}

	$pls = array();
	$list = get_option('active_plugins');
	foreach($list as $plugin) {
		$data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
		$pls[] = array($data['Name'], $data['Version']);
	}

	$ret[] = 'Active plugins<br />'.cerber_make_plain_table( $pls );

	echo implode("<br />",$ret);
}

function cerber_make_plain_table( $data ) {
	$ret = '<table class="crb-plain-table">';
	foreach ( $data as $row ) {
		$ret .= '<tr><td>' . implode( '</td><td>', $row ) . '</td></tr>';
	}
	$ret .= '</table>';

	return $ret;
}

function cerber_get_chmod( $file ) {
	return substr( sprintf( '%o', @fileperms( $file ) ), - 4 );
}

/*
 * Create database diagnostic report
 *
 *
 */
function cerber_db_diag(){
    global $wpdb;

	$ret = array();

	$ret[] = 'Database name: ' . DB_NAME;

	$var       = crb_get_mysql_var( 'innodb_buffer_pool_size' );
	$pool_size = round( $var / 1048576 );
	$inno      = 'InnoDB buffer pool size: <b>' . $pool_size . ' MB</b>';
	if ( $pool_size < 16 ) {
		$inno .= ' Your pool size is extremely small!';
	}
    elseif ( $pool_size < 64 ) {
		$inno .= ' It seems your pool size is too small.';
	}
	$ret[] = $inno;

	$var   = crb_get_mysql_var( 'max_allowed_packet' );
	$ret[] = 'Max allowed packet size: <b>' . round( $var / 1048576 ) . ' MB</b>';

	$ret[] = 'Charset: '.$wpdb->charset;
	$ret[] = 'Collate: '.$wpdb->collate;

	$ret[] = cerber_table_info( CERBER_LOG_TABLE );
	$ret[] = cerber_table_info( CERBER_ACL_TABLE );
	$ret[] = cerber_table_info( CERBER_BLOCKS_TABLE );
	$ret[] = cerber_table_info( CERBER_TRAF_TABLE );

	if ( cerber_get_remote_ip() === CERBER_NO_REMOTE_IP ) {
		$ret[] = '<p style="color: #DF0000;">It seems that we are unable to get IP addresses.</p>';
	}

	$err = '';
	if ( $errors = get_site_option( '_cerber_db_errors' ) ) {
		$err = '<p style="color: #DF0000;">Some minor DB errors were detected</p><textarea>' . print_r( $errors, 1 ) . '</textarea>';
		update_site_option( '_cerber_db_errors', '' );
	}

	return $err . implode( '<br />', $ret );
}

/**
 * Creates mini report about given database table
 *
 * @param $table
 *
 * @return string
 */
function cerber_table_info( $table ) {
	global $wpdb;
	if (!cerber_is_table($table)){
		return '<p style="color: #DF0000;">ERROR. Database table ' . $table . ' not found! Click repair button below.</p>';
	}
	$cols = $wpdb->get_results( "SHOW FULL COLUMNS FROM " . $table );

	$columns    = '<table><tr><th style="width: 30%">Field</th><th style="width: 30%">Type</th><th style="width: 30%">Collation</th></tr>';
	foreach ( $cols as $column ) {
		$column    = obj_to_arr_deep( $column );
		$field     = array_shift( $column );
		$type      = array_shift( $column );
		$collation = array_shift( $column );
		$columns  .= '<tr><td><b>' . $field . '</b></td><td>' . $type . '</td><td>' . $collation . '</td></tr>';
	}
	$columns .= '</table>';

	$rows = absint( cerber_db_get_var( 'SELECT COUNT(*) FROM ' . $table ) );

	$sts = $wpdb->get_row( 'SHOW TABLE STATUS WHERE NAME = "' . $table .'"');
	$status = '<table>';
	foreach ( $sts as $key => $value ) {
		$status .= '<tr><td><b>' . $key . '</b></td><td>' . $value . '</td></tr>';
	}
	$status .= '</table>';

	$truncate = '';
	if ($rows) {
		$truncate = ' <a href="'.wp_nonce_url( add_query_arg( array( 'truncate' => $table ) ), 'control', 'cerber_nonce' ).'" class="crb-button-tiny" onclick="return confirm(\'Confirm emptying the table. It cannot be rolled back.\')">Delete all rows</a>';
	}

	return '<p style="font-size: 110%;">Table: <b>' . $table . '</b>, rows: ' . $rows . $truncate. '</p><table class="diag-table"><tr><td class="diag-td">' . $columns . '</td><td class="diag-td">'. $status.'</td></tr></table>';
}


function cerber_environment_diag() {
	$issues = array();
	if ( version_compare( '7.0', phpversion(), '>' ) ) {
		$issues[] = 'Your site run on an outdated (unsupported) version of PHP which is ' . phpversion() . '. We strongly encourage you to upgrade it to a newer version of PHP. See more at: <a target="_blank" href="http://php.net/supported-versions.php">http://php.net/supported-versions.php</a>';
	}
	if ( ! function_exists( 'http_response_code' ) ) {
		$issues[] = 'The PHP function http_response_code() is not found or disabled.';
	}
	if ( ! is_numeric( $_SERVER['REQUEST_TIME_FLOAT'] ) ) {
		$issues[] = 'The server environment variable $_SERVER[\'REQUEST_TIME_FLOAT\'] is not set correctly.';
	}

	/*if ( $c = cerber_cron_diag() ) {
		$issues[] = $c;
	}*/

	$ret = null;
	if ( $issues ) {
		$issues = '<p>' . implode( '</p><p>', $issues ) . '</p>';
		$ret = array(
			'<h3><span style="color: red;" class="dashicons dashicons-warning"></span> Some issues detected. They might affect plugin functionality.</h3>',
			$issues
		);
	}

	return $ret;
}

function cerber_cron_diag() {

	$planned   = array();
	$crb_crons = array(
		'cerber_hourly_1'    => 'Hourly task #1',
		'cerber_hourly_2'    => 'Hourly task #2',
		'cerber_daily'       => 'Daily task',
		//'cerber_bg_launcher' => 'Background tasks'
	);
	foreach ( _get_cron_array() as $time => $item ) {
		foreach ( $crb_crons as $key => $val ) {
			if ( ! empty( $item[ $key ] ) ) {
				$planned[ $key ] = $val . ' scheduled for ' . cerber_date( $time ) . ' (' . cerber_ago_time( $time ) . ')';
			}
		}
	}

	unset( $crb_crons['cerber_daily'] );
	$crb_crons['cerber_daily_1'] = 'Daily task';

	$errors  = array();
	$ok      = array();
	$no_cron = false;
	foreach ( $crb_crons as $key => $task ) {
		$h = get_site_transient( $key );
		if ( ! $h || ! is_array( $h ) ) {
			$errors[] = $task . ' has never been executed';
			if ( $oldest = cerber_db_get_var( 'SELECT MIN(stamp) FROM ' . CERBER_LOG_TABLE ) ) {
				if ( $oldest < ( time() - 24 * 3600 ) ) {
					$no_cron = true;
				}
			}
			continue;
		}
		if ( empty( $h[1] ) ) {
			$errors[] = $task . ' has not finished correctly';
			continue;
		}
		$end = $h[1];
		/*
		if ( $end < ( time() - 2 * 3600 ) ) {
			$errors[] = $val . ' has been executed ' . cerber_ago_time( $end );
		}
		else {
			$ok[] = $val . ' has been executed ' . cerber_ago_time( $end );
		}
		*/
		$dur = $end - $h[0];
		if ( $dur > 60 ) {
			$errors[] = $task . ' has been executed ' . cerber_ago_time( $end ) . ' and it took ' . $dur . ' seconds.';
		}
		else {
			$ok[] = $task . ' has been executed ' . cerber_ago_time( $end ) . ' and it took ' . $dur . ' seconds.';
		}
	}

	if ( $errors ) {
		//echo 'There are some errors';
		echo '<p style="color: red;">' . implode( '<br/>', $errors ) . '</p>';
	}
	if ( $ok ) {
		echo '<p>' . implode( '<br/>', $ok ) . '</p>';
	}
	if ( $planned ) {
		echo '<p>' . implode( '<br/>', $planned ) . '</p>';
	}

	$num = 0;
	if ( $bg = cerber_get_set( '_background_tasks' ) ) {
		$num = count( $bg );
	}
	echo '<p>Background tasks: ' . $num . '</p>';

	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		echo '<p>Note: the internal WordPress cron launcher is disabled on this site.</p>';
		if ( $no_cron ) {
			echo '<p>An external cron launcher has not been configured or does not work properly.</p>';
		}
	}

}

function cerber_show_diag_log() {
	$file = cerber_get_diag_log();
	if ( ! is_file( $file ) ) {
		echo 'The log file has not been created yet.';

		return;
	}
	if ( ! filesize( $file ) ) {
		echo 'The diagnostic log file is empty.';

		return;
	}

	$get  = crb_get_query_params();

	$confirm = ' onclick="return confirm(\'' . __( 'Are you sure?', 'wp-cerber' ) . '\');"';

	$clear = '<a ' . $confirm . ' href="' . cerber_admin_link_add( array(
			'cerber_admin_do' => 'manage_diag_log',
			'do_this'         => 'clear_it',
		) ) . '">Clear the log</a>';

	$dnl = '<a href="' . cerber_admin_link_add( array(
			'cerber_admin_do' => 'export',
			'type'            => 'get_diag_log',
		) ) . '">Download as a file</a>';

	$reverse = '<a href="' . cerber_admin_link_add( array(
			'reverse_log' => ( crb_array_get( $get, 'reverse_log' ) ) ? 0 : 1,
		), false ) . '">Reverse the order</a>';

	$nav     = '<div style="text-align: right; padding-bottom: 1em;">' . $reverse . ' | ' . $dnl . ' | ' . $clear . '</div>';

	if ( empty( $get['reverse_log'] ) ) {
		$log  = @fopen( $file, 'r' );
		$text = fread( $log, 10000000 );
		if ( ! $text ) {
			return;
		}
		fclose( $log );
		/*$p    = strpos( $text, PHP_EOL );
		$text = substr( $text, $p + 1 );*/
		echo $nav;
		echo '<div id="crb-log-viewer"><pre>' . nl2br( htmlentities( $text ) ) . '</pre></div>';
	}
	else {
		$lines = file( $file );
		if ( ! $lines ) {
			return;
		}
		echo $nav;
		echo '<div id="crb-log-viewer"><pre>';
		for ( $i = count( $lines ) - 1; $i >= 0; $i -- ) {
			echo htmlentities( $lines[ $i ] ) . '<br/>';
		}
		echo '</pre></div>';
	}

}

function cerber_manage_diag_log( $v ) {
	if ( $v == 'clear_it' ) {
		cerber_truncate_log( 0 );
	}
    elseif ( $v == 'download' ) {
		header( $_SERVER["SERVER_PROTOCOL"] . ' 200 OK' );
		header( "Content-type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=wpcerber.log" );
		readfile( cerber_get_diag_log() );
		exit;
	}
}

function cerber_show_change_log() {
	if ( ! $text = file( cerber_get_plugins_dir() . '/wp-cerber/changelog.txt' ) ) {
		echo 'File changelog.txt not found';

		return;
	}

	echo '<div id="crb-change-log-view">';
	foreach ( $text as $line ) {
		$line = htmlspecialchars( $line );
		if ( preg_match_all( '/(\[.+?\])(\(.+?\))/', $line, $m ) ) {
			$anchors = $m[1];
			$links   = $m[2];
			$replace = array();
			foreach ( $anchors as $i => $anchor ) {
				$replace[] = '<a href="' . trim( $links[ $i ], '()' ) . '" target="_blank">' . trim( $anchor, '[]' ) . '</a>';
			}
			$line = str_replace( $anchors, $replace, $line );
			$line = str_replace( $links, '', $line );
		}
		if ( preg_match( '/=([\d\.\s]+?)=/', $line, $m ) ) {
			$line = str_replace( $m[0], '<span class="crb-version">' . $m[1] . '</span>', $line );
		}

		echo $line . '<br/>';
	}
	echo '</div>';
}
