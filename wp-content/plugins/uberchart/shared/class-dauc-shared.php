<?php

/*
 * this class should be used to stores properties and methods shared by the
 * admin and public side of wordpress
 */

class Dauc_Shared {

	//regex
    public $regex_capability = '/^\s*[A-Za-z0-9_]+\s*$/';
	public $regex_list_of_post_types = '/^(\s*([A-Za-z0-9_-]+\s*,\s*)+[A-Za-z0-9_-]+\s*|\s*[A-Za-z0-9_-]+\s*)$/';
	public $regex_borderDash = '/^(\s*([0-9]+\s*,\s*)+[0-9]+\s*|\s*[0-9]+\s*)$/';
    public $digits_regex = '/^\s*\d+\s*$/';

	protected static $instance = null;

	private $data = array();

	private function __construct() {

		//Set plugin textdomain
		load_plugin_textdomain( 'dauc', false, 'uberchart/lang/' );

		$this->data['slug'] = 'dauc';
		$this->data['ver']  = '1.10';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, - 7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, - 7 );

	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	//retrieve data
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/*
	 * Insert the default data for a single data structure of a row of a chart
	 *
	 * @param $chart_id The id of the chart
	 * @param $row_index The index of the data structure row
	 * @param $row_data_json The data of a single data structure row in the json format
	 */
	public function data_insert_default_record($chart_id, $row_index, $row_data_json){

		//save in the db table
		global $wpdb;
		$table_name = $wpdb->prefix . $this->get( 'slug' ) . "_data";
		$safe_sql   = $wpdb->prepare( "INSERT INTO $table_name SET
            chart_id = %d,
            row_index = %d,
            content = %s,
            label = %s,
            fill = %d,
            lineTension = %f,
            backgroundColor = %s,
            borderWidth = %d,
            borderColor = %s,
            borderCapStyle = %s,
            borderDash = %s,
            borderDashOffset = %f,
            borderJoinStyle = %s,
            pointBorderColor = %s,
            pointBackgroundColor = %s,
            pointBorderWidth = %d,
            pointRadius = %d,
            pointHoverRadius = %d,
            pointHitRadius = %d,
            pointHoverBackgroundColor = %s,
            pointHoverBorderColor = %s,
            pointHoverBorderWidth = %d,
            pointStyle = %s,
            showLine = %d,
            spanGaps = %d,
            hoverBackgroundColor = %s,
            hoverBorderColor = %s,
            hoverBorderWidth = %d,
            hitRadius = %d,
            hoverRadius = %d,
            plotY2 = %d",
			$chart_id,
			$row_index,
			$row_data_json,
			'Label ' . $row_index,
			false,
			0,
			'rgba(0,0,0,0.1)',
			3,
			'rgba(0,0,0,0.1)',
			'butt',
			0,
			0.0,
			'miter',
			'rgba(0,0,0,0.1)',
			'rgba(0,0,0,0.1)',
			1,
			1,
			1,
			5,
			'rgba(0,0,0,0.1)',
			'rgba(0,0,0,0.1)',
			2,
			'circle',
			true,
			false,
			'rgba(0,0,0,0.1)',
			'rgba(0,0,0,0.1)',
			1,
			1,
			1,
			0
		);

		$query_result = $wpdb->query( $safe_sql );

	}

	/*
	 * Given a string this method returns and escaped version suitable to be used in javascript strings that make use
	 * of a single quote as the delimiter of the string.
	 *
	 * @param string
	 * @return string The escaped version
	 */
	public function prepare_javascript_string($string) {

		$all = array(

			"\\" => "\\\\",
			"'" => "\\'"

		);

		$string = str_replace( array_keys( $all ), $all, $string );

		return $string;

	}

	/*
	 * Applies stripslashes to all the properties of an object
	 */
	public function object_stripslashes($obj){

		$property_a = get_object_vars($obj);

		foreach($property_a as $key => $value){

			$obj->{$key} = stripslashes($value);

		}

		return $obj;

	}

}