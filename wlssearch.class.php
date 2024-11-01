<?php 

include_once("product.class.php");

/**
 * 
 */
class WlsSearch {

	protected $ExportUrl;
	protected $SearchLimit;
	protected $PostLimit;
	protected $UtmSource;
	protected $SearchTerm;

	/**
	 * Dependency injected Construct
	 * 
	 * @param [type] $export_url [description]
	 * @param array  $args       [description]
	 */
	function __construct($args = array()) {
		$this->ExportUrl 	= Wls::getOption("export-url");
		$this->SearchLimit 	= Wls::getOption("search-limit");
		$this->PostLimit 	= Wls::getOption("post-limit");
		$this->UtmSource	= Wls::getOption("utm-source");
	}

	/**
	 * Perform a search on the export URL
	 * 
	 * @param  [type] $searchterm [description]
	 * @param  [type] $limit      [description]
	 * @return [type]             [description]
	 */
	function query($searchterm, $limit) {

		$this->SearchTerm = $searchterm;

        $qs = http_build_query(array(
            'q' => $searchterm,
            'format' => 'json',
            'limit' => $limit,
            'ip' => getUserIp(),
            'utm_source' => $this->UtmSource
        ));
        $query_url = $this->ExportUrl;

        if(strpos($query_url, "?") === false) {
            $query_url .= "?" . $qs;
        } else {
            $query_url .= "&" . $qs;
        }
		Wls::log("Querying the following Export API: " . $query_url);

		/**
		 * Perform a search on the saved export_url, set the useragent to something other than a WP one. 
		 */
		$data = wp_remote_request(
			$query_url, 
			array(
				"headers" => array(
					'user-agent' => $this->getUseragent()
				)
			)
		);

		if(!$data instanceof WP_Error) {
			if(isset($data['body'])) {
				return $this->parse($data['body']);
			}
		}

		return array();

	}

	/**
	 * Parse the returned results
	 *
	 * Return an array of the products that are returned or a blank array if not 
	 * 
	 * @param  [type] $results [description]
	 * @return [type]          [description]
	 */
	protected function parse($results) {
		$products 		= array();
		$json_results 	= json_decode($results, true);
		$parsed_res		= $json_results['data']['results'];

		foreach($parsed_res as $x => $_p) {
			$p 		= new Wls_Product($_p);
			$offer 	= $p->getFirstOffer();
			
			if(!is_array($offer)) {
				continue; 
			}
			
			$p->addDeeplinkParam("utm_source", $this->UtmSource);
			$p->addDeeplinkParam("utm_term", $this->SearchTerm);
			$products[] = $p;
		}

		Wls::log("Found " . count($products) . " for that query");
		return $products;
	}

	/**
	 * Return the Useragent we should use within the search 
	 * 
	 * @return string the useragent to use
	 */
	protected function getUseragent() {
		$useragents = array();

		$useragents[] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
		$useragents[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/600.8.9 (KHTML, like Gecko) Version/8.0.8 Safari/600.8.9";
		$useragents[] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0";
		$useragents[] = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
		$useragents[] = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36";
		
		shuffle($useragents);

		Wls::log("Searching the Export using Useragent: " . $useragents[0]);
		return $useragents[0];
	}

}