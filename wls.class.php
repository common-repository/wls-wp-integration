<?php

/**
 * 
 */
class Wls { 

	const OPTION_NAME_PREFIX = "wls-wp-integration-";

	/**
	 * The stored values for settings wihtin the WLS integration
	 * @var array
	 */
	protected static $_settings = array();
	/**
	 * THe Permalink for the search page. 
	 * @var null
	 */
	protected static $SearchPermalink 	= null;

	/**
	 * The default values for settings within the WLS integration
	 * @var array
	 */
	protected static $_defaults = array(
		'post-limit'		=> 4,
		'search-limit'		=> 6,
		'utm-source'		=> 'blog',
		'search-page'		=> 0,
		'popular-searches'	=> 'washing machine,televisions,gadgets,christmas,digital cameras,smartphones,blu ray,clothing'
	);

	/**
	 * A set of options that are able to be saved from within the ADMIN
	 * 
	 * if the option name to save isnt matched to the _option_var_names then we have an issue and wil not try to save the value
	 * 
	 * @var array
	 */
	protected static $_option_var_names = array(
		'export-url',
		'post-limit',
		'search-limit',
		'utm-source',
		'search-page',		// Boolean if we should have a search page
		'search-page-id',	// the ID of the created search page
		'popular-searches'	// the popular searches as saved by the user
	);

	protected static $log_message_hash = array();

	/**
	 * We add wls-wp-integration- to the integration variable to make sure we do not get any issues with other integrations
	 * make sure this has been added to the option name
	 * 
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	static protected function createOptionName($name) {
		if(strpos($name, self::OPTION_NAME_PREFIX) === false) {
			$name = self::OPTION_NAME_PREFIX . $name;
		}

		if(strpos($name, self::OPTION_NAME_PREFIX) === false) {
			self::log("Invalid Option Name: $name it needs to start with " . self::OPTION_NAME_PREFIX);
		}

		return $name;
	}

	/**
	 * Return an OPtion for this integration 
	 *
	 * An option is a variable saved by wordpress 
	 * 
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	static function getOption($name) {

		$default 	= self::getDefaultSetting($name);
		$vars 		= self::$_option_var_names;

		if(!in_array($name, $vars)) {
			self::log("Unable to get $name from the options as it's not valid for this integration");
			self::log(print_r($vars, true));
			return null;
		}		

		$name 			= self::createOptionName($name);
		$option_value 	= get_option($name, $default);

		self::log("Returning Option Value of $option_value for $name default is: $default");

		return $option_value;
	}

	/**
	 * Set an option for this wordpress integration 
	 * 
	 * @param [type] $name  [description]
	 * @param [type] $value [description]
	 */
	static function setOption($name, $value) {
		$vars = self::$_option_var_names;
		if(!in_array($name, $vars)) {
			self::log("Unable to save $name as it's not within the available option names");
			self::log($vars);
			return null;
		}
		$name = self::createOptionName($name);
		update_option($name, $value);
		self::log("Saving Option: $name with Value: $value");
		return;
	}

	/**
	 * Return Valid Option names for this integration 
	 * 
	 * @return [type] [description]
	 */
	static function getValidOptionNames() {
		return self::$_option_var_names;
	}

	/**
	 * Get the a default setting for a setting passed through
	 *
	 * Sometimes we have settings that are stored in the admin but they havent been setup
	 * So we pull out default values instead. 
	 * 
	 * @param  string $name The default setting to look for
	 * @return mixed       the value of the default setting, or ''
	 */
	static function getDefaultSetting($name) {

		$defaults = self::getDefaultSettings();

		if(array_key_exists($name, $defaults)) {
			return $defaults[$name];
		}

		return '';
	}

	/**
	 * Return the default values from the class
	 * 
	 * @return array the defaults used to return values for settings. 
	 */
	static function getDefaultSettings() {
		return self::$_defaults;
	}
	
	/**
	 * Add a line to the error log
	 * 
	 * @param  [type] $message [description]
	 * @param  boolean $print_once If this is set then the message will not be repeated in the log file. 
	 * @return [type]          [description]
	 */
	static function log($message, $print_once = false) {

		if(WP_DEBUG === true) {
			if($print_once == true) {
				$msg_md5 = md5($message);
				if(array_key_exists($msg_md5, self::$log_message_hash)) {
					return '';
				}
				self::$log_message_hash[$msg_md5] = 1;
			}

			if(is_array($message) || is_object($message)) {
				error_log(print_r($message, true));
			} else {
				error_log($message);
			}
		}
	}

	/**
	 * Get the Search Page URL for this blog 
	 *
	 * This is a fixed page within a blog that we use for our searching needs. 
	 * 
	 * @return [type] [description]
	 */
	static function getSearchPageUrl($parameters = array()) {
		if(!self::$SearchPermalink) {
			$page 					= get_page_by_title(WLS_SEARCH_PAGE_NAME);
			$permalink 				= get_permalink($page->ID);
			self::$SearchPermalink 	= $permalink;
		}

		$link = self::$SearchPermalink;

		if(!empty($parameters)) {
			$qs = http_build_query($parameters);

			if(strpos(self::$SearchPermalink, "?") === false) {
				$link .= "?" . $qs;
			} else {
				$link .= "&" . $qs;
			}
		}

		return $link;
	}
}