<?php 


function getUserIp() {

	// Display User IP in WordPress
	if (!empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$ip = apply_filters( 'wpb_get_ip', $ip );	

	Wls::log("User IP for searching is: $ip");

	return $ip;
}

/**
 * If we're on a Single page pull out the tags, we will use this for searching
 *
 * @return [type] [description]
 */
function getPostTags() {

	global $post;

	if($post instanceof WP_Post) {
		$tags 		= wp_get_post_tags($post->ID);
		$ret_tags 	= array();
		foreach($tags as $x => $t) {
			$ret_tags[$t->term_id] = $t->name;
		}
		return $ret_tags;
	}

	return array();
}

/**
 * Remove stuff that this integration creates
 * 
 * @return [type] [description]
 */
function wls_integration_deactivate() {
	
	Wls::log("Sorry to see you go, here we clean up your blog so it's not tainted with crap");

	// Delete the Shopping Search page that was created
	$page 	= get_page_by_title(WLS_SEARCH_PAGE_NAME);

	if($page instanceof WP_Post) {

		wp_delete_post($page->ID, true);

		$post_id = Wls::getOption("search-page-id");
		if(is_int($post_id)) {
			wp_delete_post($post_id, true);
		}

		Wls::log("Deleting Post: " . $page->ID);
		// Delete the search-page boolean
		// we have just deleted the page, so we dont have one to search, set to 0
		Wls::setOption("search-page", 0);
	}

	// NOw get all options from WLS and delete them all from the DB
	$options = Wls::getValidOptionNames();

	foreach($options as $x => $option) {
		echo "deleting: $option : " . delete_blog_option(null, $option) . "<br>";
		Wls::log("Deleting option: " . $option . " now that we're deactivating the integration");
	}

}