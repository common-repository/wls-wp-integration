<?php 

class Widget_Mpu extends WP_Widget {

	/**
	 * The identifier for the WLS Widget
	 * 
	 * @var string
	 */
	const LIMIT_VAR_NAME 		= "limit";
	const DEFAULT_LIMIT_VALUE 	= 4;
	const KEYWORD_VAR_NAME		= "keyword";
	const DEFAULT_KEYWORD_VALUE = "speakers,keyboards,mouse,nerf,shoes,dresses,jeans,jumpers,sweaters,suits,headphones,monitors,microwaves,xbox,playstation,nintendo,blu ray,sat nav,watch,televisions,digital cameras,washing machines,toys,camcorders,womens clothing,mens clothing,gadgets,printers,scanners,smartphone,tablets";

	public function __construct() {
		parent::__construct(
			'wls_mpu_widget',
			__('WLS MPU Widget','text_domain'),
			array(
				'description' => __('A widget allowing the user to perform a search and return results in a sidebar')
			)
		);

	}

	/**
	 * Render the Widget 
	 *
	 * @see  WP_Widget::widget()
	 * 
	 * @param  [type] $args     [description]
	 * @param  [type] $instance [description]
	 * @return [type]           [description]
	 */
	public function widget($args, $instance) {

		$limit 		= $instance[self::LIMIT_VAR_NAME];

		if(isset($_GET['wls_mpu_kw'])) {
			$keyword = trim($_GET['wls_mpu_kw']);
		} else {
			// Load the Defaults from the widget settings, randomise and pick one
			$keywords 	= explode(",", $instance[self::KEYWORD_VAR_NAME]);
			shuffle($keywords);
			$keyword = $keywords[0];
		}

		$search 	= new WlsSearch();
		$products 	= $search->query($keyword, $limit);


		$html = "
			<a name='wls-widget'></a>
			<div class='wls-wp-widget-mpu-wrapper widget'>
				<h3 class='widgettitle'>Sponsored Shopping</h3>
				<div class='wls-widget-searchform'>
					<form action='#wls-widget' method='get'>
						<input type='text' value='" . $keyword . "' name='wls_mpu_kw' class='wls-wp-widget-form-searchterm'/>
						<input type='submit' value='go' class='wls-wp-widget-form-searchbutton'>
						<div style='clear:both;'></div>
					</form>
				</div>
		";

		if($products) {
			$html .= "
				<table class='wls-wp-widget-results'>
			";
			$loop = 0;
			foreach($products as $x => $p) {

				$html .= "<tr><td>" . wls_wp_render_product_list($p) . "</td></tr>";

				$loop++;
			}


			$html .= "
				</table>
			";
		} else {
			$html .= "
				<table class='wls-wp-widget-results'>
					<tr><td class='wls-wp-widget-top-tip'>Tip: Use the search bar in this widget to query our shopping API</td></tr>
				</table>
			";
		}

		$html .= "</div>";

		echo $html;

	}

	/**
	 * Outputs the option form within the ADMIN
	 *
	 * Return the number of results that can be returned
	 *
	 * @see  WP_Widget::form()
	 * 
	 * @param  [type] $instance [description]
	 * @return [type]           [description]
	 */
	public function form($instance) {

		$limit 		= self::DEFAULT_LIMIT_VALUE;
		$keywords	= self::DEFAULT_KEYWORD_VALUE;

		if($instance) {
			if(array_key_exists(self::LIMIT_VAR_NAME, $instance)) {
				$limit = $instance[self::LIMIT_VAR_NAME];
			}

			if(array_key_exists(self::KEYWORD_VAR_NAME, $instance)) {
				$keywords = $instance[self::KEYWORD_VAR_NAME];
			}
		}

		$html = "
			<p>
				<label for='" . $this->get_field_id(self::LIMIT_VAR_NAME) . "'>Number of Results to display</label>
			</p>
			<p>
				<input type='text' class='widefat' id='" . $this->get_field_id(self::LIMIT_VAR_NAME) . "' name='" . $this->get_field_name(self::LIMIT_VAR_NAME) . "' value='" . esc_attr($limit) . "' />
			</p>
			<p>
				Include the keywords you'd like to search within this widget, a user can search, but pick a random one of the keywords and we'll preform a search so you have something on every page
				<br><small>NOTE: Comma Seperated</small>
			</p>
			<p>
				<textarea class='widefat' style='height:200px;' id='" . $this->get_field_id(self::KEYWORD_VAR_NAME) . "' name='".$this->get_field_name(self::KEYWORD_VAR_NAME)."'>$keywords</textarea>
			</p>
		";
		echo $html;
	}

	/**
	 * Processing widget options on save 
	 *
	 * @see  WP_Widget::update()
	 * 
	 * @param  [type] $new_instance the new options
	 * @param  [type] $old_instance the old options
	 * @return [type]               [description]
	 */
	public function update($new_instance, $old_instance) {

		// Used when saving the options for this widget
		$instance 	= array();

		// Placeholder for the limit, set to 0 for now, to allow for the if($limit == 0)
		$limit 		= 0;

		// Placeholder for the keywords, if it's '' then save the default
		$keywords 	= '';

		// Check to make sure that limit has been sent through with the new instance
		// if it has make sure it's an int (casting)
		if(array_key_exists(self::LIMIT_VAR_NAME, $new_instance)) {
			$limit = (int) $new_instance[self::LIMIT_VAR_NAME];	
		}

		//. If the limit is stil 0 then use the default value (as set above)
		if($limit == 0) {
			$limit = self::DEFAULT_LIMIT_VALUE;
		}

		if(array_key_exists(self::KEYWORD_VAR_NAME, $new_instance)) {
			$keywords = $new_instance[self::KEYWORD_VAR_NAME];
		}

		if(strlen($keywords) < 1) {
			$keywords = self::DEFAULT_KEYWORD_VALUE;
		}

		// Setup the value for the instance and return it, this will save the option 
		// set by the admin user. 
		$instance[self::LIMIT_VAR_NAME] 	= $limit;
		$instance[self::KEYWORD_VAR_NAME] 	= $keywords;

		return $instance;

	}

}