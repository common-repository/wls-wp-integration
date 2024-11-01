<?php
defined( 'ABSPATH' ) or die( 'Invalid Access!' );

/**
 * Plugin Name: WLS WP Integration
 * Plugin URI: http://www.whitelabelshopping.net
 * Description: A integration that allows a blog owner to query the whitelabelshopping API to return CPC offers for posts and search results. 
 * Version: 1.5.4
 * Author: Whitelabelshopping.net
 * Author URI: http://www.whitelabelshopping.net
 * License: GPL2
 */
define("WLS_WP_INTEGRATION_VERSION", "1.5.4");

// Setup any functions that we might need. 
include('include.php');

Wls::log(str_repeat("=", 40));

add_action('init', 'wls_wp_integration_admin_init');

function wls_wp_integration_admin_init(){ 
	include('wls-wp-integration-admin.php');
}

add_action('widgets_init', 'wls_wp_integration_init');

function wls_wp_integration_init() {
	register_widget("Widget_Mpu");
}

// Hook for when the WP functions are available
add_action('wp', 'wls_wp_integration_wp_action');

function wls_wp_integration_wp_action() {

	/**
	 * Check to see if we are on the search page
	 */
	if(is_page(Wls::getOption("search-page-id"))) {
		include('wls-wp-integration-search.php');
	} else { 
		if(is_single()) {
			// The functionality to deal with a single post/page
			include("wls-wp-integration-single.php");
		}
	}

	// Load in the CSS for this integration, try and load it last
	add_action ('wp_enqueue_scripts', 'wls_wp_integration_scripts', 1000);

}


function wls_wp_integration_scripts() {
	$path = realpath(dirname(__FILE__)) . "/css/";
	wp_enqueue_style('product-css', plugins_url("styles.css?mt=" . time(), $path) , array());
}


/**
 * Deactivate the integration hook. 
 */
register_deactivation_hook(__FILE__, "wls_integration_deactivate");