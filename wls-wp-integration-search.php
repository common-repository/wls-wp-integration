<?php

add_shortcode('wls_integration_search_form', 	'wls_integration_shortcode_search_form');
add_shortcode('wls_integration_search_results', 'wls_integration_shortcode_search_results');

/**
 * Filter the content (body content for posts) and add shopping products to the footer of the content
 */
$products 			= array();
$search_performed 	= 0;


function getSearchQuery() {
	if(isset($_GET['searchterm'])) {
		return $_GET['searchterm'];
	}
	return false;
}


if(getSearchQuery()) {
	global $products;
	Wls::log("Querying for " . getSearchQuery() . " hopefully only calling this once");
	$s 			= new WlsSearch();
	$products 	= $s->query(getSearchQuery(), Wls::getOption("search-limit"));
	$search_performed = 1;
}



/**
 * Replace this shortcode with a search form
 *
 * Shortcode to replace: wls_integration_search_form
 * This will render a search form for the user to be able to query the shopping feed
 * 
 * @return [type] [description]
 */
function wls_integration_shortcode_search_form() {
	
	$searchterm = $_GET['searchterm'];

	$html = "
		<div id='wls-integration-shortcode-searchform'>
			<form method='get' action='" . Wls::getSearchPageUrl() . "'>
				<input type='text' name='searchterm' placeholder='Search now' value='" . $searchterm . "' class='wls-integration-shortcode-searchform-text'/>
				<input type='submit' value='Search'  class='wls-integration-shortcode-searchform-submit' />
			</form>
		</div>
		<div style='clear:both'></div>
	";

	echo $html;
}

/**
 * Replace the shortcode with the results of the search 
 *
 * Shortcode to replace: wls_integration_search_results
 * Replace the shortcode with actual results returned from the search, if we have no results show a helpful message
 * 
 * @return [type] [description]
 */
function wls_integration_shortcode_search_results() {
	global $products;

	$popular = explode(",", Wls::getOption("popular-searches"));

	$html = "<div id='wls-integration-search-results'>";

	if(empty($products)) {
		$html .= "
			<div class='wls-integration-search-results-noresults'>
				<h3>We were unable to find you results</h3>
				<p>Please try the following solutions:</p>
				<ul>
					<li>Reduce the length of your search string</li>
					<li>Ensure your search string is longer than 3 chars</li>
					<li>Contact the site owner</li>
				</ul>
			</div>
		";
	} else {
		echo "
			<div class='wls-integration-search-results-desc'>
				We have found <em>" . count($products) . "</em> results when searching for <em>" . getSearchQuery() . "</em>
			</div>
		";
		echo wls_wp_integration_render_products($products);
	}

	$html .= "
		<hr>
		<h3>Popular Searches</h3>
			<ul class='wls-integration-search-results-suggestions'>
	";
		foreach($popular as $x => $y) {
			$link = Wls::getSearchPageUrl(array("searchterm" => trim($y)));
			$html .= "<li><a href='". $link . "'/>$y</a></li>";
		}
	$html .= "</ul>";

	echo $html;
}
