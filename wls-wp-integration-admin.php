<?php 

include_once("include.php");

/**
 * Info: 
 *
 * This file contains the Admin functions and UI changes for the admin section within Wordpress. 
 * This will allow us to set the default limit and the export URL. 
 */

// add the menu item for the settings
add_action('admin_menu', 'wls_wp_integration_admin');

// Within INIT to ensure all WP functions are setup
add_action('init', 'wls_wp_integration_admin_process_post', 100);

/**
 * Add a Menu Page to the Admin 
 * @return [type] [description]
 */
function wls_wp_integration_admin() {
	add_menu_page(
		"WLS WP Integration Settings", 
		"WLS Settings", 
		"administrator", 
		"wls-wp-integration-admin", 
		"wls_wp_integration_render_settings_page", 
		"dashicons-admin-generic"
	);
}



/**
 * Render the Settings Page
 * @return [type] [description]#!/usr/bin/env 
 */
function wls_wp_integration_render_settings_page() {

	$yes_checked		= '';
	$no_checked			= '';
	$export_url 		= Wls::getOption("export-url");
	$post_limit 		= Wls::getOption("post-limit");
	$search_limit 		= Wls::getOption("search-limit");
	$utm_source 		= Wls::getOption("utm-source");
	$search_page		= Wls::getOption("search-page");
	$popular_searches	= Wls::getOption("popular-searches");
	
	if($search_page) {
		$yes_checked = "checked=true";
	} else {
		$no_checked = "checked=true";
	}

	$html = "
		<style>
			td { vertical-align: top !important; }
		</style>
		<div class='wrap'>
			<h2>WLS Export Information</h2>
			<p>This integration adds in additional search functionality through a new search page and will also add search results at the bottom of article pages.</p>
			<p>We have a Widget called: WLS MPU Widget, this is a 300 wide widget with a search bar and some default keywords. On a page load it will perform a search and render some results</p>
			<p>The search page is a simple page within WP that has a number of shortcodes. These shortcodes are replaced with a search bar and also some results</p>
			<form method='post' action=''>
				<table class='form-table'>
					<tr valign='top'>
						<th scope='row'>Export URL</th>
						<td><input type='text' name='wls-integration[export-url]' value='". $export_url . "' style='width:450px;'/></td>
						<td class='notes'>The URL used to query the Export API for WLS Domains</td>
					</tr>
					<tr valign='top'>
						<th scope='row'>Post Limit</th>
						<td><input type='text' name='wls-integration[post-limit]' value='" . $post_limit . "' /></td>
						<td class='notes'>Number of results to display on a post - after the content</td>
					</tr>
					<tr valign='top'>
						<th scope='row'>Default UTM_Source</th>
						<td><input type='text' name='wls-integration[utm-source]' value='" . $utm_source . "' /></td>
						<td class='notes'>The default source to use for all deeplinks. This is for tracking purposes.</td>
					</tr>
					<tr valign='top'>
						<th scope='row'>Enable the Search Page</th>
						<td valign='top'>
							<input type='radio' name='wls-integration[search-page]' value='1' $yes_checked/> Yes we want search <br />
							<input type='radio' name='wls-integration[search-page]' value='0' $no_checked/> No we dont want search
						</td>
						<td class='notes'>If selected we will create a page here: <a href='" . Wls::getSearchPageUrl() . "'>" . Wls::getSearchPageUrl() . "</a> that allows a user to perform a search and get shopping results</td>
					</tr>
					<tr valign='top'>
						<th scope='row'>Search Limit</th>
						<td><input type='text' name='wls-integration[search-limit]' value='" . $search_limit . "' /></td>
						<td class='notes'>Number of results to display when we've performed a search into the 'Search Page Name'</td>
					</tr>
					<tr valign='top'>
						<th scope='row'>Popular Searches</th>
						<td valign='top'><textarea cols=60 rows=10 name='wls-integration[popular-searches]'>$popular_searches</textarea></td>
						<td class='notes' valign=top>These searches will be displayed on all shopping search results pages</td>
				</table>
	" . get_submit_button() . "
			</form>
		</div>
	";

	echo $html;

}


/**
 * Take the Admin Form and process the POST'd values
 * 
 * @return [type] [description]
 */
function wls_wp_integration_admin_process_post() {

	if(!empty($_POST)) {
		if(isset($_POST['wls-integration'])) {
			foreach($_POST['wls-integration'] as $key => $value) {
				Wls::setOption($key, $value);

				if($key == "search-page") {

					// Here's where we create the page if it doesnt exist
					$page 	= get_page_by_title(WLS_SEARCH_PAGE_NAME);

					if(!$page instanceof WP_Post) {

						$new_page = array();
						$new_page['post_name'] 		= $value;
						$new_page['post_title'] 	= WLS_SEARCH_PAGE_NAME;
						$new_page['post_content'] 	= "[wls_integration_search_form] <hr> [wls_integration_search_results]";
						$new_page['post_status']	= "publish";
						$new_page['post_type']		= "page";
						$new_page['comment_status']	= "closed";
						$new_page['ping_status']	= "closed";
						$new_post['tag_input']		= "search";
						
						$page_id = wp_insert_post($new_page);
						Wls::setOption("search-page-id", $page_id);

						Wls::log("Created a new Page ($page_id) using Title: " . $value);

					} else {
						// update the Title for this
						wp_update_post($page, array("post_name", $value));
						Wls::log("Updated the post setting the title to : $value");
					}
				}
			}
		}	
	}
}