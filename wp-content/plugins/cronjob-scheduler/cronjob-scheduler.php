<?php namespace chrispage1;

/**
 * Plugin Name: Cronjob Scheduler
 * Description: Plugin to manage, edit and remove WordPress cron job tasks
 * Version: 1.40.1
 * Author: chrispage1
 * Author URI: https://profiles.wordpress.org/chrispage1/
 *
 * @author chrispage1 <https://profiles.wordpress.org/chrispage1/>
 * @license http://www.gnu.org/licenses/gpl.html GNU v3
 *
 */

define('CRONJOB_BASE_DIR', __DIR__);

require_once CRONJOB_BASE_DIR . '/cronjobs.php';
require_once CRONJOB_BASE_DIR . '/lib/Commons.php';
require_once CRONJOB_BASE_DIR . '/lib/CronjobScheduler.php';

// create a new instance
new lib\CronjobScheduler();