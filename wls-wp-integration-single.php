<?php 
/**
 * Filter the content (body content for posts) and add shopping products to the footer of the content
 */
add_filter( 'the_content', 'wls_wp_integration_render_single_footer_products');

/**
 * Add the products to the bottom of each post
 * 
 * @param  HTML $content 
 * @return [type]          [description]
 */
function wls_wp_integration_render_single_footer_products($content) {
	if(is_single()) {

		$tags = getPostTags();

		if(empty($tags)) {
			return $content;
		}

		shuffle($tags);
		$search = $tags[0];

		$s 			= new WlsSearch();
		$products 	= $s->query($search, Wls::getOption("post-limit"));

		$product_html 	= wls_wp_integration_render_products($products);
		$content 		.= $product_html;
	}
	return $content;
}