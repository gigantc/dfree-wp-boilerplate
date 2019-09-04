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

if ( ! defined( 'WPINC' ) ) { exit; }

// Only this filter lets user manage table columns. See render_list_table_columns_preferences()
add_filter( "manage_wp-cerber_page_cerber-nexus_columns", 'nexus_slave_list_cols' );
function nexus_slave_list_cols() {
	if ( ! crb_get_configurable_screen() ) {
		return array();
	}

	return array(
		'cb'         => '<input type="checkbox" />', //Render a checkbox instead of text
		'site_name'  => __( 'Website', 'wp-cerber' ),
		'site_url'   => 'URL',
		//'site_status' => __( 'Status', 'wp-cerber' ),
		'wp_v'       => __( 'WordPress', 'wp-cerber' ),
		'plugin_v'   => 'WP Cerber',
		//'new_users'  => __( 'New Users', 'wp-cerber' ),
		'updates'    => __( 'Updates', 'wp-cerber' ),
		'last_scan'  => __( 'Malware Scan', 'wp-cerber' ),
		'site_grp'   => __( 'Group', 'wp-cerber' ),
		'site_owner' => __( 'Owner', 'wp-cerber' ),
		'site_notes' => __( 'Notes', 'wp-cerber' ),
	);
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CRB_Slave_Table extends WP_List_Table {
	function __construct() {
		parent::__construct( array(
			'singular' => 'Site',
			'plural'   => 'Sites',
			'ajax'     => false
		) );
	}

	// Columns definition
	function get_columns() {
		return nexus_slave_list_cols();
	}

	/*protected function get_default_primary_column_name() {
		return 'site_name';
	}*/

	// Sortable columns
	function get_sortable_columns() {
		return array(
			'site_name' => array( 'site_name', false ), // true means dataset is already sorted by ASC
			'site_url'  => array( 'site_url', false ),
			'wp_v'      => array( 'wp_v', false ),
			'plugin_v'  => array( 'plugin_v', false ),
			'updates'   => array( 'updates', false ),
			'last_scan' => array( 'last_scan', false ),
			'site_grp'  => array( 'group_id', false ),
		);
	}
	// Bulk actions
	function get_bulk_actions() {
		return array(
			'nexus_upgrade_cerber'  => __( 'Upgrade WP Cerber', 'wp-cerber' ),
			'nexus_upgrade_plugins' => __( 'Upgrade all active plugins', 'wp-cerber' ),
			'nexus_delete_slave'    => __( 'Delete website', 'wp-cerber' ),
		);
	}

	protected function extra_tablenav( $which ) {
		?>
        <div class="alignleft actions" style="padding-bottom: 2px;">
		<?php

		if ( $which == 'top' ) {

			$groups = nexus_get_groups();
			if ( count( $groups ) > 1 ) {
				$groups = array( '-1' => __( 'All groups', 'wp-cerber' ) ) + $groups;
				echo cerber_select( 'filter_group_id', $groups, (int) crb_array_get( $_GET, 'filter_group_id', '-1' ) );
				echo '<input style="margin: 1px 24px 0 0;" type="submit" value="Filter" class="button action">';
			}

			// for_tb_blur is for removing focus from closing button
			echo '<input type="button" alt="' . CRB_ADD_SLAVE_LNK . '" title="' . __( 'Add a slave website', 'wp-cerber' ) . '" class="thickbox button button-primary" style="margin: 1px 8px 0 0px;" value="Add">';
			?>
            <script>
                jQuery(document).ready(function ($) {
                    $('.thickbox').on('click', function () {
                        setTimeout(function () {
                            $('#TB_closeWindowButton').blur();
                        }, 50);
                    });
                });
            </script>
            </div>
			<?php
		}
	}

		// Retrieve data from the DB
	function prepare_items() {
		global $_wp_column_headers, $cerber_db_errors;;
		// pagination
		$per_page = crb_admin_get_per_page();

		$table       = cerber_get_db_prefix() . CERBER_MS_TABLE;
		$where       = array();
		$join        = '';
		$total_items = 0;

		// Sorting
		$orderby = crb_array_get( $_REQUEST, 'orderby', 'id' );
		$order = crb_array_get( $_REQUEST, 'order', 'DESC' );
		$orderby = sanitize_sql_orderby( $orderby . ' ' . $order ); // !works only with fields, not tables references!
		$orderby = ' ORDER BY ' . $table . '.' . $orderby . ' ';

		// Pagination, part 1, SQL
		$current_page = $this->get_pagenum();
		if ( $current_page > 1 ) {
			$offset = ( $current_page - 1 ) * $per_page;
			$limit  = ' LIMIT ' . $offset . ',' . $per_page;
		}
		else {
			$limit = 'LIMIT ' . $per_page;
		}

		if ( $group_id = cerber_get_get( 'filter_group_id', '\d+' ) ) {
			$where[] = 'group_id = ' . absint( $group_id );
		}

		// Search
		if ( $term = cerber_get_get( 's' ) ) {
			$term = stripslashes( $term );
			$s = '"%' . cerber_real_escape( $term ) . '%"';
			if ( preg_match( '/[^A-Z\d\-\/\.\:]/i', $term ) ) {
				// Mixing columns with different collations for non-latin symbols generates MySQL error
				$where[] = ' (site_name LIKE ' . $s . ' OR site_name_remote LIKE ' . $s . ' OR site_notes LIKE ' . $s . ' OR details LIKE ' . $s . ') ';
			}
			else {
				$where[] = ' (site_name LIKE ' . $s . ' OR site_name_remote LIKE ' . $s . ' OR site_notes LIKE ' . $s . ' OR details LIKE ' . $s . ' OR site_url LIKE ' . $s . ') ';
			}
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE ' . implode( ' AND ', $where );
		}
		else {
			$where = '';
		}

		// Retrieving actual data

		$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM '.$table . " $join $where $orderby $limit";

		if ( $this->items = cerber_db_get_results( $query ) ) {
			$total_items = cerber_db_get_var( 'SELECT FOUND_ROWS()' );
		}

		if ( ! empty( $term ) ) {
			echo '<div style="margin-top:15px;"><b>' . __( 'Search results for:', 'wp-cerber' ) . '</b> “' . htmlspecialchars( $term ) . '”</div>';
		}

		// Pagination, part 2
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );

		/*$this->set_pagination_args( array(
			'total_items' => count( $themes ),
			'per_page' => $per_page,
			'infinite_scroll' => true,
		) );*/
	}

	function single_row( $item ) {
		echo '<tr class="crb-slave-site" data-slave-id="' . $item['id'] . '" data-slave-name="' . $item['site_name'] . '">';
		if ( ! empty( $item['details'] ) ) {
			$item['details'] = unserialize( $item['details'] );
		}
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	function column_cb( $item ) {
		return '<input type="checkbox" name="ids[]" value="' . $item['id'] . '" />';
	}

	function column_site_name( $item ) {
	    static $base_switch;
		if ( ! $base_switch ) {
			$base_switch = wp_nonce_url( cerber_admin_link() . '&cerber_admin_do=nexus_switch', 'control', 'cerber_nonce' );
		}

		$set     = array();
		$onclick = 'onclick="return confirm(\'' . __( 'Are you sure?', 'wp-cerber' ) . '\')"';

		$edit = cerber_admin_link( 'nexus_sites', array( 'site_id' => $item['id'] ) );
		$set['edit'] = '<a href="' . $edit . '">' . __( 'Edit', 'wp-cerber' ) . '</a>';

		//$login        = $item['site_url'] ;
		//$set['login'] = ' <a href="' . $login . '" target="_blank">' . __( 'Log in', 'wp-cerber' ) . '</a>';

		$switch        = $base_switch . '&nexus_site_id=' . $item['id'];
		$set['switch'] = ' <a href="' . $switch . '">' . __( 'Switch to', 'wp-cerber' ) . '</a>';

		/*$set['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array(
				'cerber_admin_do' => 'nexus_delete_slave',
				'site_id'         => $item['id']
			) ), 'control', 'cerber_nonce' ) . '" ' . $onclick . '>' . __( 'Delete', 'wp-cerber' ) . '</a>';
		*/

		return '<strong><a class="row-title" href="' . $switch . '">' . $item['site_name'] . '</a></strong>' . $this->row_actions( $set );
	}

	/**
	 * @param array $item // not object!
	 * @param string $column_name
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		static $pup, $base_scan, $groups;
		if ( ! $groups ) {
			$groups = nexus_get_groups();
		}
		if ( ! $base_scan ) {
			$base_scan = wp_nonce_url( cerber_admin_link( 'scan_main' ) . '&cerber_admin_do=nexus_switch', 'control', 'cerber_nonce' );
		}
		if ( ! $pup ) {
			$pup = nexus_get_update( CERBER_PLUGIN_ID );
		}
		//return $item[ $column_name ]; // raw output as is
		$val = crb_array_get( $item, $column_name, null );
		switch ( $column_name ) {
			case 'site_status':
				if ( ! $val ) {
					return 'Up';
				}

				return 'Down';
				break;
			case 'site_url':
				//.$this->row_actions( array('login'=>'<a href="">Log in</a>') );
				return '<a href="' . $val . '" target="_blank">' . $val . '</a>';
				break;
			case 'last_scan':
				if ( ! $item['refreshed'] ) {
					return __( 'Unknown', 'wp-cerber' );
				}

				$v = '';
				if ( $val ) {
					$nums = cerber_get_set( '_nexus_tmp_' . $item['id'] );
					if ( ! empty( $nums[ CERBER_VULN ] ) ) {
						$v = '<br/><span style="color: red;">' . __( 'Vulnerabilities', 'wp-cerber' ) . ' ' . $nums[ CERBER_VULN ] . '</span>';
					}
					$txt = cerber_auto_date( $val );

					return '<a href="' . $base_scan . '&nexus_site_id=' . $item['id'] . '">' . $txt . '</a>' . $v;
				}

				return '<span style="color: red;">' . __( 'Never', 'wp-cerber' ) . '</span>';
				break;
			case 'wp_v':
				if ( $val && version_compare( $val, cerber_get_wp_version(), '<' ) ) {
					return $val . ' <i style="color: red; font-size: 1.2em;" class="dashicons dashicons-warning"></i>';
				}
				return $val;
			case 'plugin_v':
				if ( $val ) {
					if ( isset( $pup['new_version'] ) && version_compare( $val, $pup['new_version'], '<' ) ) {
						return $val . ' <i style="color: red; font-size: 1.2em;" class="dashicons dashicons-warning"></i>';
					}
				}

				return $val;
			case 'updates':
				return ( ! empty( $item['refreshed'] ) ) ? '<a href="#">' . $val . '</a>' : '';
			case 'site_grp':
				return crb_array_get( $groups, $item['group_id'], 'Unknown' );
				break;
			case 'site_owner':
				if ( ! $owner = crb_array_get( $item['details'], 'owner_biz' ) ) {
					$owner = crb_array_get( $item['details'], 'first_name', '' ) . ' ' . crb_array_get( $item['details'], 'last_name', '' );
				}

				return trim( $owner );
				break;
		}

		return htmlspecialchars( $val );
	}

	function no_items() {
		if ( ! empty( $_GET['s'] ) ) {
			parent::no_items();
		}
		else {
			$no_master = wp_nonce_url( add_query_arg( array(
				'cerber_admin_do' => 'nexus_set_role',
				'nexus_set_role'  => 'none',
			) ), 'control', 'cerber_nonce' );

			echo __( 'No websites configured.', 'wp-cerber' ) . ' <a class="thickbox" href="' . CRB_ADD_SLAVE_LNK . '">' . __( 'Add a new one', 'wp-cerber' ) . '</a> | <a href="' . $no_master . '">' . __( 'Disable master mode', 'wp-cerber' ) . '</a>';
		}
	}
}