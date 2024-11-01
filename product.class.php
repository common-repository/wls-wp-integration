<?php 


class Wls_Product {

	protected $Data = array();
	protected $DeeplinkParams = array();

	function __construct(array $data) {
		foreach($data as $x => $y) {
			$this->set($x, $y);
		}
	}

	public function addDeeplinkParam($key, $value) {
		$this->DeeplinkParams[$key] = $value;
		return $this;
	}

	function set($k, $v) {
		$this->Data[$k] = $v;
		return $this;
	}

	function get($k) {
		if(!isset($this->Data[$k])) {
			return '';
		}
		return $this->Data[$k];
	}

	function getInfo() {
		return $this->Data;
	}

	function getDeeplink($args = array()) {
		$o = $this->getFirstOffer();

		$args = array_merge($args, $this->DeeplinkParams);

		Wls::log("Args for the Product Deeplink: " . print_r($args, true), true);

		// Add in some UTM stuff
		$args['utm_campaign'] 	= 'na';
		$args['utm_medium']		= 'wls-wp-integration';

		if($o['deeplink']) {
			return $o['deeplink'] . "&" . http_build_query($args);
		}
		return '';
	}

	function renderImage() {
		$i = $this->get("image");
		if(!$i) {
			$i = plugins_url("/images/noimage_160.jpg", dirname(__FILE__) . "/images/");
		}
		return "<img src='" . $i . "' class='wls-wp-integration-product-image' />";
		
	}

	function renderRetLogo() {
		
		$offer = $this->getFirstOffer();
		if(empty($offer)) {
			return "";
		}

		if(!$offer['retailer_logo']) {
			return "
				<a rel='nofollow' href='" . $this->getDeeplink(array("clktype" => "ret_no_logo")) . "'>
					<span class='wls-wp-integration-retailer-noimage'>" . $offer['retailer_name'] . "</span>
				</a>
			";
		}
		return "
			<a rel='nofollow' href='" . $this->getDeeplink(array("clktype" => "ret_logo")) . "'>
				<img src='" . $offer['retailer_logo'] . "' class='wls-wp-integration-retailer-logo' />
			</a>
		";

	}

	function renderBuyNow() {
		return "
			<div class='wls-wp-offer-go'>
				<a rel='nofollow' href='" . $this->getDeeplink(array("clktype" => "buynow")) . "'>Buy Today</a>
			</div>
		";
	}

	function renderPrice($cur_symbol = "&pound;") {
		$offer = $this->getFirstOffer();
		return "
			<span class='wls-wp-integration-price'>
				<a rel='nofollow' href='" . $this->getDeeplink(array("clktype" => "price")) . "'>$cur_symbol" . $offer["price"] . "</a>
			</span>
		";
	}

	function getFirstOffer() {
		$os = $this->get("offers");
		return array_shift($os);
	}

}