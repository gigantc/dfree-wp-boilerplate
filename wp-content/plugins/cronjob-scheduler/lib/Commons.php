<?php
/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 20/05/2020
 */

namespace chrispage1\lib;

abstract class Commons {

    protected

        /**
         * Array to store all plugin settings
         * @var array
         */
        $_options = array (),

        /**
         * Admin notice ID
         * @var string
         */

        $_adminNoticeID = 'session_notices',
        /**
         * Prefix for option parameters
         * @var string
         */
        $_optionPrefix = null,

        /**
         * Array to store magic getter/setter params
         * @var array
         */
        $__get = array ();




    /**
     * Supr3meBaseAbstract Plugin constructor class
     * @param string $plugin_slug  Plugin slug for settings saved to database
     * @param array  $options      Array of permittable options for this plugin
     */
    public function __construct ($plugin_slug, array $options) {
        // set our plugin slug and generate our option prefix
        $this->_optionPrefix = '_chrispage1_' . sanitize_title($plugin_slug);

        // populate our array of options
        $this->_options = $options;
        $this->_options = $this->get_option();

        // create filter for admin notices
        $this->action(['admin_notices', 'network_admin_notices'], 'notices_display', 10);

        // return current instance
        return $this;
    }



    /**
     * Magic __get() method to get undefined params
     * @param  string $param_name   Parameter name
     * @return mixed                Value of the parameter
     */
    public function __get($param_name) {

        // create $return param
        $return = false;

        // if we have already instantiated this param, return it
        if(array_key_exists($param_name, $this->__get)) {
            return $this->__get[$param_name];
        }

        // return our populated $return and store
        // it in our caching parameter
        return $this->__get[$param_name] = $return;
    }



    /**
     * Magic __set() method to set magic params
     * @param string $param_name    Parameter name
     * @param mixed $param_value    Parameter value
     * @return mixed
     */
    public function __set($param_name, $param_value) {
        // set the param against our $this->__get array
        return $this->__get[$param_name] = $param_value;
    }


    /**
     * Get timezone from WordPress installation
     * @return \DateTimeZone
     */
    protected function getTimezone () {

        // get our timezone string
        $sTimezone = $this->get_option('timezone_string');

        // return DateTimeZone object
        return new \DateTimeZone($sTimezone);
    }



    /**
     * Update plugin options in the database
     * @param  string $key          Key to store the option under
     * @param  mixed $value         Value to store against the option
     * @return bool                 Returns true on success, false on failure
     */
    public function update_option ($key, $value) {

        // check if the option exists
        if(array_key_exists($key, $this->_options)) {

            // attempt to update the option
            if(update_site_option($this->_optionPrefix . $key, $value)) {

                // set option on our options array
                $this->_options[$key] = $value;
                return true;
            }
        }

        // if we get to this point, update failed
        return false;
    }




    /**
     * Gets plugin option / options from the database
     *
     * @param string $key The key to get
     * @return array
     */
    public function get_option ($key = null) {

        // if the key is not null, attempt to return a single value
        if(!is_null($key)) {
            if(isset($this->_options[$key]) && !is_null($this->_options[$key])) {
                // return our cached option
                return $this->_options[$key];
            } else {
                // get it from the database and return it
                return get_site_option($this->_optionPrefix . $key);
            }
        }

        // loop through each option and get it from the database
        foreach($this->_options as $key => $value) {

            // try and get this option from the database
            $db_loaded = get_site_option($this->_optionPrefix . $key);

            // get option with a default
            $this->_options[$key] = ($db_loaded !== false ? $db_loaded : $value);
        }

        // return the resulting array
        return $this->_options;
    }



    /**
     * Basic method to load view file
     * @param  string $name         Name of the view to load
     * @param  array $variables     Array of variables to make available to view
     * @return void
     */
    public function load_view ($name, $variables = array()) {

        // set options as an accesible variable
        $options = $this->_options;
        $options['plugin_dir'] = dirname(__FILE__);

        // extract variables as parameters and then remove $variables
        extract($variables);
        unset($variables);

        // load in our view file
        require_once(CRONJOB_BASE_DIR . '/views/' . $name . '.php');

        // unset our options
        unset($options);
    }



    /**
     * Helper function for WordPress actions
     * @param  string|array $hook   Name of the filter to use
     * @param  string|null $method  The method to call, defaults to $hook
     * @param  int $priority        Action priority
     * @return void
     */
    public function action ($hook, $method = null, $priority = 10) {

        // convert the $hook string into an array
        if(!is_array($hook))
            $hook = array($hook);

        // return private hook method with action flag
        foreach($hook as $hook_single)
            $this->_hook('action', $hook_single, $method, $priority);
    }



    /**
     * Helper function for WordPress filters
     * @param  string $hook         Name of the filter to use
     * @param  string|null $method  The method to call, defaults to $hook
     * @param  int $priority        Filter priority
     * @return void
     */
    public function filter ($hook, $method = null, $priority = 10) {

        // convert the $hook string into an array
        if(!is_array($hook))
            $hook = array($hook);

        // return private hook method with filter flag
        foreach($hook as $hook_single)
            $this->_hook('filter', $hook_single, $method, $priority);
    }



    /**
     * Registers WordPress hooks and actions
     * @param  string $type         Either action or filter type
     * @param  string $hook         The WordPress filter/action
     * @param  string|null $method  The method to call
     * @param  int $priority        Action/filter priority
     * @return mixed
     */
    private function _hook ($type, $hook, $method = null, $priority = 10) {

        // check if we are trying to run a valid type (default to action)
        if(!in_array($type, array('filter', 'action'))) $type = 'action';

        // prefix the type
        $type = 'add_' . $type;

        // set the method to be the hook if not defined
        if(is_null($method)) $method = $hook;

        // return the result of the WordPress filter/action
        return $type($hook, is_object($method) ? $method :  array($this, $method), $priority, 999);
    }



    /**
     * Sets a notice for the current session
     * @param string $message       Message to output
     * @param string $class         Class of the message. Should be one of updated, error or updated-nag
     * @return void
     */
    public function notice_set ($message, $class = 'updated') {

        // @see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices

        // set the session parameter
        $_SESSION[$this->_adminNoticeID][] = array (
            'message'   => $message,
            'class'     => $class,
        );
    }



    /**
     * Outputs admin notices using the admin_notices hook
     * @return void
     */
    public function notices_display () {

        // @see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices

        // dont continue if our notices are not an array
        if(!isset($_SESSION[$this->_adminNoticeID]) || !is_array($_SESSION[$this->_adminNoticeID])) return;

        // if our session is an array, loop through each message
        foreach($_SESSION[$this->_adminNoticeID] as $key => $notice) {

            // output our message
            echo "<div class='{$notice['class']}'><p>{$notice['message']}</p></div>";

            // unset it as its been shown
            unset($_SESSION[$this->_adminNoticeID][$key]);
        }
    }
}