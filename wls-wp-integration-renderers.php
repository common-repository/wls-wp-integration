<?php 

/**
 * Function to render the products
 * 
 * @param  array  $products [description]
 * @return [type]           [description]
 */
function wls_wp_integration_render_products(array $products) {
	if(empty($products)) { return ""; }

	$html = "
		<div class='wls-wp-product-results'>
			<ul class='wls-wp-product-results-wrap'>
	";

	$loop 	= 0;
	$images = array();

	// lets get some images, we always need one anyway so we want to have a default one or two
	foreach($products as $n => $p) {
		if($p->get("image")) { $images[] = $p->get("image"); }
	}

	foreach($products as $x => $product) {
		$html .= "<li>" . wls_wp_render_product($product) . "</li>";
	}

	$html .= "
			</ul>
			<div style='clear: both;'></div>
		</div>
	";
	return $html;
}

/**
 * Render a product
 * 
 * @param  [type] $product [description]
 * @return [type]          [description]
 */
function wls_wp_render_product(Wls_Product $product) {

	$product->addDeeplinkParam("pagetype", "post");
	$product->addDeeplinkParam("module", "post");

	return $html = "
		<div class='wls-wp-sponsored-message'>Sponsored Result</div>
		<table class='wls-wp-product-result'>
			<tr class='wls-wp-product-header'>
				<td colspan='3'>
					<p class='wls-wp-product-title'>" . $product->get("name") . "</h3>
				</td>
			</tr>
			<tr class='wls-wp-product-image'>
				<td colspan='3'>
					<div class='wls-wp-product-image-div'>
						" . $product->renderImage() . "
					</div>
				</td>
			</tr>
			<tr class='wls-wp-product-footer'>
				<td class='wls-wp-offer-retlogo'>
					" . $product->renderRetLogo() . "
				</td>
				<td class='wls-wp-offer-price'>
					<a href='" . $product->getDeeplink(array('clktpe' => 'price')) . "' target='_blank' rel='nofollow'>" . $product->renderPrice("&pound;") . "</a>
				</td>
				<td class='wls-wp-offer-go'>
					<a href='" . $product->getDeeplink(array('clktype' => 'buynow')) . "' target='_blank' rel='nofollow'>Buy Now</a>
				</td>
			</tr>
		</table>
	";
}

/**
 * Render the HTML for a widget prouct 
 *
 * Widgets will render the product HTML in a list and not a full table as per a full search 
 * 
 * @param  [type] $product [description]
 * @return [type]          [description]
 */
function wls_wp_render_product_list(Wls_Product $product) {

	$product->addDeeplinkParam("module", "widget");
	
	$html = "
		<table class='wls-wp-product-list-result'>
			<tr>
				<td colspan='2' class='wls-wp-product-list-desc-td' valign='top'>
					<p class='title'>" . $product->get("name") . "</p>
				</td>
			</tr>
			<tr>
				<td class='wls-wp-product-list-image-td' valign='top'>
					" . $product->renderImage() . "
				</td>
				<td class='wls-wp-product-list-price-td' valign='top'>
					<ul>
						<li>" . $product->renderPrice() . "</li>
						<li>" . $product->renderRetLogo() . "</li>
						<li>" . $product->renderBuyNow() . "</li>
					</ul>
				</td>
			</tr>
		</table>
	";

	return $html;

}