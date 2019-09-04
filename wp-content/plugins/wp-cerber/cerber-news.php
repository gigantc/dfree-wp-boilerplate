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
if ( ! defined( 'WPINC' ) ) {
	exit;
}


function cerber_push_the_news( $version ) {

	$news['3.0'] =
		'<h3>Welcome a new version with reCAPTCHA and WordPress filters</h3>
<ul>
 	<li>Now you can use Google reCAPTCHA to protect WordPress registration form from spam registrations. Also reCAPTCHA available for lost password and login forms. <a href="https://wpcerber.com/how-to-setup-recaptcha/">How to setup reCAPTCHA</a>.</li>
 	<li>The registration process, WordPress registration form, XML-RPC, WP REST API are controlled by <a href="http://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">IP Access Lists</a>.</li>
 	<li>Registration is impossible if a particular IP address is locked out.</li>
 	<li>Registration with a prohibited username is impossible.</li>
 	<li><a href="http://wpcerber.com/wp-cerber-hooks/">A set of filters and actions</a>. They are useful if you want to customize some aspects of the plugin as you want.</li>
 	<li>A new action <strong>Get WHOIS info</strong> that obtains detailed WHOIS information about given IP address. You can use it in vary <a href="http://jetflow.io">jetFlow.io automation scenarios</a>. For instance, you can monitor countries from what your users are logged in on the website or you <a href="http://wpcerber.com/notifications-on-wordpress-user-logs-in/">monitor user logins with notifications</a>.</li>
 	<li>A new trigger <strong>IP locked out</strong> that starts automation scenario after a suspicious IP address has been locked out by the WP Cerber plugin.</li>
</ul>

';

	$news['4.0'] =
		'<h3>Welcome a new version with extended Access Lists and reCAPTCHA for WooCommerce</h3>
<ul>
 	<li>reCAPTCHA for WooCommerce forms. <a href="https://wpcerber.com/how-to-setup-recaptcha/">How to set up reCAPTCHA</a>.</li>
 	<li>IP Access Lists has got support for IP networks in three forms: ability to restrict access with IPv4 ranges, IPv4 CIDR notation and IPv4 subnets: A,B,C has been added. Read more: <a href="http://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Access Lists for WordPress</a>.</li>
 	<li>Cerber can automatically detect an IP network of an intruder and suggest you to block the entire network right from the Activity screen.</li>
 	<!-- <li>reCAPTCHA will not be shown and processed for IP addresses from the White IP Access List.</li> -->
</ul>

 <p><a href="https://wpcerber.com/wp-cerber-security-4-0/" target="_blank">Read a full list of changes and improvements</a></p> 
';

	$news['4.3'] =
		'<h3>What\'s new in version 4.3</h3>
<ul>
 	<li>Do you want to keep eye on specific activity on your website? I have good news for you! Track them like a PRO. Use powerful subscriptions to get email notifications according to filters for events you have set. Filter out activities that you are interested to monitor and then click Subscribe. <a href="https://wpcerber.com/wordpress-notifications-made-easy/">Read more</a></li>
 	<li>Search and/or filter activity by IP address, username (login), specific event and a user. You can use any combination of them. </li>
 	<li>Now you can export activity from your WordPress website to a CSV file. You can export all activities or a set of filtered activities only as it described above. When you will import the CSV file in your spreadsheet editor, don\'t forget to select UTF-8 charset.</li>
 	<li>You can use multiple email addresses for notifications (Main Settings -> Notifications -> Email Address). Use a comma to specify several addresses.</li>
</ul>
';

	$news['7.9.3'][] = 'New settings for the Traffic Inspector firewall allow you to fine-tune its behavior. You can enable less or more restrictive firewall rules.';
	$news['7.9.3'][] = 'Troubleshooting of possible issues with scheduled maintenance tasks has been improved.';
	$news['7.9.3'][] = 'To make troubleshooting easier the plugin logs not only a lockout event but also logs and displays the reason for the lockout.';
	$news['7.9.3'][] = 'Compatibility with ManageWP and Gravity Forms has been improved.';
	$news['7.9.3'][] = 'The layout of the Activity and Live Traffic pages has been improved.';
	$news['7.9.3'][] = 'Bug fixed: The malware scanner wrongly prevents PHP files with few specific names in one particular location from being deleted after a manual scan or during the automatic malware removal.';
	$news['7.9.3'][] = 'Bug fixed: The number of email notifications might be incorrectly limited to one email per hour.';

	$news['7.9.7'][] = 'New: Authorized users only mode';
	$news['7.9.7'][] = 'New: An ability to block a user account with a custom message';
	$news['7.9.7'][] = 'New: Role-based access to WordPress REST API';
	$news['7.9.7'][] = 'Added ability to search and filter a user on the Activity page';
	$news['7.9.7'][] = 'Improved handling scheduled maintenance tasks on a multi-site WordPress installation';
	$news['7.9.7'][] = 'A new Changelog section on the Tools page';

	$news['8.0'][] = 'A new feature called Cerber.Hub enables you to manage WP Cerber settings, monitor user activity, watch website traffic, and upgrade plugins on an unlimited number of websites.';
	$news['8.0'][] = 'To block multiple WordPress users at a time, use a new bulk action "Block" in the dropdown list on the Users admin page. Requires WordPress 4.7 or newer.';
	$news['8.0'][] = 'We’ve significantly improved the export routine for the Activity and Live Traffic logs. Now it’s capable of exporting more than 500K rows in a single CSV file.';

	$news['8.1'][] = 'New: In a single click you can get a list of active plugins and available plugin updates on a slave website.';
	$news['8.1'][] = 'New: If a newer version of Cerber or WordPress is available to install on slave websites, a red exclamation icons are shown on the My Websites page.';
	$news['8.1'][] = 'New: On a master website, you can select what language to use when a slave admin page is being displayed.';
	$news['8.1'][] = 'Update: Long URLs on the Live Traffic page now are shortened and displayed more neatly.';
	$news['8.1'][] = 'Update: The plugin uninstallation process has been improved and now cleans up the database completely.';
	$news['8.1'][] = 'Update: Multiple translations have been updated. Thanks to: Maxime, Jos Knippen, Fredrik Näslund, Francesco.';
	$news['8.1'][] = 'Bug fixed: The "Add to the Black List" button on the Activity log page does not work.';
	$news['8.1'][] = 'Bug fixed: When the "All suspicious activity" button is clicked on the Dashboard admin page, the "Subscribe" link on the Activity page does not work correctly';

	$news['8.2'][] = 'New: Automatic recovery of infected files. When the malware scanner detects changes in the core WordPress files and plugins, it automatically recovers them.';
	$news['8.2'][] = 'New: A set of quick navigation buttons on the Activity page. They allow you to filter out log records quickly.';
	$news['8.2'][] = 'New: A unique Session ID (SID) is displayed on the Forbidden 403 Page now.';
	$news['8.2'][] = 'New: The advanced search on the Live Traffic page has got a set of new fields.';
	$news['8.2'][] = 'New: To make a website comply with GDPR, a cookie prefix can be set.';
	$news['8.2'][] = 'Update: The lockout notification settings are moved to the Notifications tab.';
	$news['8.2'][] = 'Update: The list of files to be scanned in Quick mode now also includes files with these extensions:  phtm, phtml, phps, php2, php3, php4, php5, php6, php7.';

	$news['8.3'][] = 'Hot: Two-Factor Authentication.';
	$news['8.3'][] = 'New: Block registrations with unwanted (banned) email domains.';
	$news['8.3'][] = 'New: Block access to the WordPress Dashboard on a per-role basis.';
	$news['8.3'][] = 'New: Redirect after login/logout on a per-role basis.';
	$news['8.3'][] = 'Fixed: Switching to the English language in Cerber’s admin interface has no effect.';
	$news['8.3'][] = 'Fixed: Multiple notifications about a new version of the plugin in the WordPress dashboard.';

	$news['8.4'][] = 'New: More flexible role-based GEO access policies.';
	$news['8.4'][] = 'New: A logged in users’ sessions manager.';
	$news['8.4'][] = 'Update: Access to users’ data via WordPress REST API is always granted for administrator accounts now.';
	$news['8.4'][] = 'Improvement: The custom login page feature has been updated to eliminate possible conflicts with themes and other plugins.';
	$news['8.4'][] = 'Improvement: Compatibility with operating systems that natively doesn’t support the PHP GLOB_BRACE constant.';

	if ( ! empty( $news[ $version ] ) ) {
		//$text = '<h3>What\'s new in WP Cerber '.$version.'</h3>';

		$text = '<h3>Highlights from WP Cerber Security '.$version.'</h3>';

		$text .= '<ul><li>'.implode('</li><li>', $news[ $version ]).'</li></ul>';

		$text .= '	<p style="margin-top: 18px; font-weight: bold;"><a href="https://wpcerber.com/?plugin_version='.$version.'" target="_blank">Read more on wpcerber.com</a></p>';

		$text .= '	<p style="margin-top: 24px;"><span class="dashicons-before dashicons-email-alt"></span> &nbsp; <a href="https://wpcerber.com/subscribe-newsletter/">Subscribe to Cerber\'s newsletter</a></p>
					<p><span class="dashicons-before dashicons-twitter"></span> &nbsp; <a href="https://twitter.com/wpcerber">Follow Cerber on Twitter</a></p>
					<p><span class="dashicons-before dashicons-facebook"></span> &nbsp; <a href="https://www.facebook.com/wpcerber/">Follow Cerber on Facebook</a></p>
				';
		cerber_admin_info( $text );
	}
}


function cerber_admin_info($msg, $type = 'normal'){
	//global $crb_assets_url;
	$crb_assets_url = cerber_plugin_dir_url() . 'assets/';
	update_site_option('cerber_admin_info',
		'<table><tr><td><img style="float:left; margin-left:-10px;" src="'.$crb_assets_url.'icon-128x128.png"></td>'.
		'<td>'.$msg.
		'<p style="text-align:right;">
		<input type="button" class="button button-primary cerber-dismiss" value=" &nbsp; '.__('Awesome!','wp-cerber').' &nbsp; "/></p></td></tr></table>');
}


