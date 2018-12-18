<?php
/*
	Copyright (C) 2015-18 CERBER TECH INC., http://cerber.tech
	Copyright (C) 2015-18 CERBER TECH INC., https://wpcerber.com

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

	$news['7.6'][] = 'The quarantine has got a separate admin page in the WordPress dashboard which allows viewing deleted files, restoring or deleting them.';
	$news['7.6'][] = 'Now the malware scanner and integrity checker supports multisite WordPress installations.';
	$news['7.6'][] = 'Bug fixed: Once an address IP has been locked out after reaching the limit to the number of attempts to log in the "We’re sorry, you are not allowed to proceed" forbidden page is being displayed instead of the normal user message "You have exceeded the number of allowed login attempts".';
	$news['7.6'][] = 'Bug fixed: PHP Notice: Only variables should be passed by reference in cerber-load.php on line 5377';

	$news['7.7'][] = 'New: Automatic cleanup of malware and suspicious files. This powerful feature is available in the PRO version and automatically deletes trojans, viruses, backdoors, and other malware. Cerber Security Professional scans the website on an hourly basis and removes malware immediately.';
	$news['7.7'][] = 'Update: Algorithms of the malware scanner have been improved to detect obfuscated malware code more precisely for all types of files.';
	$news['7.7'][] = 'Update: Email reports for scheduled malware scans have been extended with useful performance numbers and a list of automatically deleted malicious files if you’ve enabled automatic malware removal and some files have been deleted.';
	$news['7.7'][] = 'Fix: A possible issue with uploading large JSON and CSV files. When Traffic Inspector scans uploaded files for malware payload, some JSON and CSV files might be erroneously identified as containing a malicious payload.';
	$news['7.7'][] = 'Fix: A possible Divi theme forms incompatibility. If you use the Divi theme (by Elegant Themes), you can come across a problem with submitting some forms.';

	$news['7.8'][] = 'New: An ignore list for the malware scanner.';
	$news['7.8'][] = 'New: Disabling execution of PHP scripts in the WordPress media folder helps to prevent offenders from exploiting security flaws. See the Hardening tab.';
	$news['7.8'][] = 'New: Disabling PHP error as a setting is useful for misconfigured servers. See the Hardening tab.';
	$news['7.8'][] = 'New: English for the admin interface. Enable it if you prefer to have untranslated, original admin interface. See the Main Settings tab.';
	$news['7.8'][] = 'New: Diagnostic logging for the malware scanner. Specify a particular location of the log file by using the CERBER_DIAG_DIR constant.';
	$news['7.8'][] = 'Update: The performance of malware scanning on a slow web server with thousands of issues and tens of thousands of files has been improved.';
	$news['7.8'][] = 'Fix: If a malicious file is detected on a slow shared hosting, the file can be shown twice in the results of the scan.';

	$news['7.8.5'][] = 'New: A new set of heuristics algorithms for detecting obfuscated malicious JavaScript code have been added to the Traffic Inspector firewall rules and malware scanner.';
	$news['7.8.5'][] = 'New: A new file filter on the Quarantine page lets to filter out quarantined files by the date of the scan.';
	$news['7.8.5'][] = 'New: The performance of the malware scanner has been improved. Now the scanner deletes all files in the website session and temporary folders permanently before the scan.';
	$news['7.8.5'][] = 'Update: Now if the plugin is unable to detect the remote IP address, it will use 0.0.0.0 as an IP.';
	$news['7.8.5'][] = 'Update: The anti-spam engine will never block the localhost IP which is 127.0.0.1 in case of IPv4 and ::1 in case of IPv6.';
	$news['7.8.5'][] = 'Update: Improved handling the plugin settings in a buggy or misconfigured hosting environment that could cause the plugin to reset settings to their default values.';
	$news['7.8.5'][] = 'Update: Translations have been updated. Thanks to Francesco, Jos Knippen, Fredrik Näslund, Slobodan Ljubic and MARCELHAP.';
	$news['7.8.5'][] = 'Fix: Fixed an issue with saving settings on the Hardening tab: "Unable to get access to the file…"';

	$news['7.9'][] = 'New: The plugin monitors suspicious requests that cause 4xx and 5xx HTTP errors and blocks IP addresses that aggressively generate such requests.';
	$news['7.9'][] = 'New: A set of WordPress navigation menu links. Login, logout, and register menu items can be automatically generated and shown in any WordPress menu or a widget.';
	$news['7.9'][] = 'New: Software error logging. A handy feature that logs PHP errors and shows them on the Live Traffic page.';
	$news['7.9'][] = 'New: A new export feature for Traffic Inspector. It allows exporting all log entries or a filtered set from the log of HTTP requests.';
	$news['7.9'][] = 'Update: Multiple improvements to Traffic Inspector firewall algorithms. In short, the detection of obfuscated malicious SQL queries and injections has been  improved.';
	$news['7.9'][] = 'Fix: The number of email notifications per hour can exceed the configured limit.';
	$news['7.9'][] = 'Update: Translations have been updated. Thanks to Felipe Turcheti and Eirik Vorland.';

	$news['7.9.3'][] = 'New settings for the Traffic Inspector firewall allow you to fine-tune its behavior. You can enable less or more restrictive firewall rules.';
	$news['7.9.3'][] = 'Troubleshooting of possible issues with scheduled maintenance tasks has been improved.';
	$news['7.9.3'][] = 'To make troubleshooting easier the plugin logs not only a lockout event but also logs and displays the reason for the lockout.';
	$news['7.9.3'][] = 'Compatibility with ManageWP and Gravity Forms has been improved.';
	$news['7.9.3'][] = 'The layout of the Activity and Live Traffic pages has been improved.';
	$news['7.9.3'][] = 'Bug fixed: The malware scanner wrongly prevents PHP files with few specific names in one particular location from being deleted after a manual scan or during the automatic malware removal.';
	$news['7.9.3'][] = 'Bug fixed: The number of email notifications might be incorrectly limited to one email per hour.';

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

	$crb_assets_url = cerber_plugin_dir_url() . 'assets/';

	update_site_option('cerber_admin_info',
		'<table><tr><td><img style="float:left; margin-left:-10px;" src="'.$crb_assets_url.'icon-128x128.png"></td>'.
		'<td>'.$msg.
		'<p style="text-align:right;">
		<input type="button" class="button button-primary cerber-dismiss" value=" &nbsp; '.__('Awesome!','wp-cerber').' &nbsp; "/></p></td></tr></table>');
}


