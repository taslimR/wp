<?php

/*
 * this class should be used to work with the public side of wordpress
 */
class Dauc_Public {

	protected static $instance = null;
	private $shared = null;

	private $charts = null;

	//Store all the shortcode IDs used in the post/page
	private static $shortcode_id_a = array();

	private function __construct() {

		//assign an instance of the plugin info
		$this->shared = Dauc_Shared::get_instance();

		//Load public js
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		//instantiate the charts
		add_action('get_footer', array( $this, 'instantiate_charts'));

		//uberchart shortcode
		add_shortcode('uberchart', array( $this, 'display_uberchart') );

		//Generate Chart Preview
		add_action( 'template_redirect', array( $this, 'generate_chart_preview' ), 0 );

	}

	/*
	 * create an instance of this class
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->shared->get('slug') . '-chart-js', esc_url(get_option( $this->shared->get( 'slug' ) . '_chartjs_library_url' )), 'jquery', $this->shared->get('ver') );
	}

	/*
	 * Handler of the [uberchart] shortcode
	 */
	public function display_uberchart( $atts ){

		if( !is_feed() and ( is_single() or is_page() ) ){

			//get the chart id
			extract( shortcode_atts( array('id' => '0'), $atts ) );
			$chart_id = intval( $id, 10 );

			//get event data
			global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
			$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ", $chart_id);
			$chart_obj = $wpdb->get_row($safe_sql);

			//terminate if there is no chart with the specified id
			if($chart_obj === NULL){return '<p>' . __('There is no chart associated with this shortcode.', 'dauc') . '</p>';}

			//terminate if this chart id has already been used
			if(in_array($chart_id, self::$shortcode_id_a, true)){
				return '<p>' . __("You can't use multiple time the same shortcode.", 'dauc') . '</p>';
			}

			//store the shortcode id
			self::$shortcode_id_a[] = $chart_id;

			//the charts property saves all the charts included in this post
			$this->charts[] = $chart_obj;

			/*
			 * If the "Responsive" option is disabled the width and height values will be applied to the canvas
			 */
			if( intval($chart_obj->responsive, 10) == 0 ){
				$canvas_size = 'width="' . intval($chart_obj->width, 10) . '" height="' . intval($chart_obj->height, 10) . '" ';
			}else{
				$canvas_size = '';
			}

			//get the canvas background color if the transparent background option is disabled
			if( intval($chart_obj->canvas_transparent_background, 10) == 0){
				$background_color = 'background-color: ' . esc_attr(stripslashes($chart_obj->canvas_backgroundColor)) . ';';
			}else{
				$background_color = '';
			}

			//Generate the HTML of the cavas element
            $canvas = '<canvas ' . $canvas_size . 'id="uberchart-' . $chart_id . '" style="margin: ' . intval($chart_obj->margin_top, 10) . 'px 0 ' . intval($chart_obj->margin_bottom, 10) . 'px 0;' . $background_color . '"></canvas>';

            /*
             * If:
             *
             * - the fixed_height option is > than 0
             * - the maintain_aspect_ratio is set to 0
             * - the responsive option is set to 1
             *
             * include the canvas in a container with the height equal to the value of the fixed_height option.
             * Otherwise send to the output only the canvas HTML
             */
            if( intval($chart_obj->fixed_height, 10) > 0 and intval($chart_obj->maintainAspectRatio, 10) == 0 and intval($chart_obj->responsive, 10) == 1 ){
                $output = '<div style="height: ' . intval($chart_obj->fixed_height, 10) . 'px;">' . $canvas . '</div>';
            }else{
                $output = $canvas;
            }

			return $output;

		}

	}

	/*
	 * Set all the options of the chart and instantiate the chart
	 */
	public function instantiate_charts($is_preview = false){

		//if there are no charts in this post return
		if(count($this->charts) == 0){return;}

		//turn on output buffer
		ob_start();

		//generate the options and instantiale all the charts available in this post/page
		foreach($this->charts as $key => $chart){

			?>

			<script>

				//Common Chart Configuration
				<?php if($is_preview) : ?>
					Chart.defaults.global.responsive = true;
					Chart.defaults.global.maintainAspectRatio = true;
				<?php else : ?>
					Chart.defaults.global.responsive = <?php echo $this->boolean_string($chart->responsive); ?>;
					Chart.defaults.global.maintainAspectRatio = <?php echo $this->boolean_string($chart->maintainAspectRatio); ?>;
				<?php endif; ?>
				Chart.defaults.global.responsiveAnimationDuration = <?php echo intval($chart->responsiveAnimationDuration, 10); ?>;

				//Title Configuration
				Chart.defaults.global.title.display = <?php echo $this->boolean_string($chart->title_display); ?>;
				Chart.defaults.global.title.position = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->title_position)); ?>';
				Chart.defaults.global.title.fullWidth = <?php echo $this->boolean_string($chart->title_fullWidth); ?>;
				Chart.defaults.global.title.fontSize = <?php echo intval($chart->title_fontSize, 10); ?>;
				Chart.defaults.global.title.fontFamily = "<?php echo htmlspecialchars(stripslashes($chart->title_fontFamily), ENT_COMPAT); ?>";
				Chart.defaults.global.title.fontColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->title_fontColor)); ?>';
				Chart.defaults.global.title.fontStyle = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->title_fontStyle)); ?>';
				Chart.defaults.global.title.padding = <?php echo intval($chart->title_padding, 10); ?>;
				Chart.defaults.global.title.text = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->name)); ?>';

				//Legend Configuration
				Chart.defaults.global.legend.display = <?php echo $this->boolean_string($chart->legend_display); ?>;
				Chart.defaults.global.legend.position = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->legend_position)); ?>';
				Chart.defaults.global.legend.fullWidth = <?php echo $this->boolean_string($chart->legend_fullWidth); ?>;

				//Legend Label Configuration
				Chart.defaults.global.legend.labels.boxWidth = <?php echo intval($chart->legend_labels_boxWidth, 10); ?>;
				Chart.defaults.global.legend.labels.fontSize = <?php echo intval($chart->legend_labels_fontSize, 10); ?>;
				Chart.defaults.global.legend.labels.fontStyle = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->legend_labels_fontStyle)); ?>';
				Chart.defaults.global.legend.labels.fontColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->legend_labels_fontColor)); ?>';
				Chart.defaults.global.legend.labels.fontFamily = "<?php echo htmlspecialchars(stripslashes($chart->legend_labels_fontFamily), ENT_COMPAT); ?>";
				Chart.defaults.global.legend.labels.padding = <?php echo intval($chart->legend_labels_padding, 10); ?>;

				//Tooltip Configuration
				Chart.defaults.global.tooltips.enabled = <?php echo $this->boolean_string($chart->tooltips_enabled); ?>;
				Chart.defaults.global.tooltips.mode = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_mode)); ?>';
				Chart.defaults.global.tooltips.backgroundColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_backgroundColor)); ?>';
				Chart.defaults.global.tooltips.titleFontFamily = "<?php echo htmlspecialchars(stripslashes($chart->tooltips_titleFontFamily), ENT_COMPAT); ?>";
				Chart.defaults.global.tooltips.titleFontSize = <?php echo intval($chart->tooltips_titleFontSize, 10); ?>;
				Chart.defaults.global.tooltips.titleFontStyle = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_titleFontStyle)); ?>';
				Chart.defaults.global.tooltips.titleFontColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_titleFontColor)); ?>';
				Chart.defaults.global.tooltips.titleMarginBottom = <?php echo intval($chart->tooltips_titleMarginBottom, 10); ?>;
				Chart.defaults.global.tooltips.bodyFontFamily = "<?php echo htmlspecialchars(stripslashes($chart->tooltips_bodyFontFamily), ENT_COMPAT); ?>";
				Chart.defaults.global.tooltips.bodyFontSize = <?php echo intval($chart->tooltips_bodyFontSize, 10); ?>;
				Chart.defaults.global.tooltips.bodyFontStyle = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_bodyFontStyle)); ?>';
				Chart.defaults.global.tooltips.bodyFontColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_bodyFontColor)); ?>'
				Chart.defaults.global.tooltips.footerFontFamily = "<?php echo htmlspecialchars(stripslashes($chart->tooltips_footerFontFamily), ENT_COMPAT); ?>";
				Chart.defaults.global.tooltips.footerFontSize = <?php echo intval($chart->tooltips_footerFontSize, 10); ?>;
				Chart.defaults.global.tooltips.footerFontStyle = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_footerFontStyle)); ?>';
				Chart.defaults.global.tooltips.footerFontColor = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_footerFontColor)); ?>';
				Chart.defaults.global.tooltips.footerMarginTop = <?php echo intval($chart->tooltips_footerMarginTop, 10); ?>;
				Chart.defaults.global.tooltips.xPadding = <?php echo intval($chart->tooltips_xPadding, 10); ?>;
				Chart.defaults.global.tooltips.yPadding = <?php echo intval($chart->tooltips_yPadding, 10); ?>;
				Chart.defaults.global.tooltips.caretSize = <?php echo intval($chart->tooltips_caretSize, 10); ?>;
				Chart.defaults.global.tooltips.cornerRadius = <?php echo intval($chart->tooltips_cornerRadius, 10); ?>;
				Chart.defaults.global.tooltips.multiKeyBackground = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_multiKeyBackground)); ?>';

				//Hover Configuration
				Chart.defaults.global.hover.animationDuration = <?php echo intval($chart->hover_animationDuration, 10); ?>;

				//Animation Configuration
				Chart.defaults.global.animation.duration = <?php echo intval($chart->animation_duration, 10); ?>;
				Chart.defaults.global.animation.easing = '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->animation_easing)); ?>';

				var ctx = document.getElementById("uberchart-<?php echo $chart->id; ?>");
				var myChart = new Chart(ctx, {
					type: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->type)); ?>',
					data: {
						labels: <?php echo $chart->labels; ?>,
						datasets: [
							<?php

							global $wpdb; $table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
                            $safe_sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE chart_id = %d ORDER BY row_index ASC", $chart->id );
							$data_a = $wpdb->get_results( $safe_sql, ARRAY_A );

							foreach ( $data_a as $key1 => $row_data ) {

								//convert json to array
								$row_data_a = json_decode( $row_data['content'] );

								echo '{';

								//data structure -----------------------------------------------------------------------

								switch ($chart->type){

									case 'line':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//yAxisID
										if( intval($row_data['plotY2'], 10) === 1 ){
											echo "yAxisID: 'y2-identifier',";
										}

										//fill
										echo 'fill: ' . $this->boolean_string($row_data['fill']) . ',';

										//lineTension
										echo 'lineTension: ' . floatval($row_data['lineTension']) . ',';

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'line', 'backgroundColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'line', 'borderWidth' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'line', 'borderColor' ) . ',';

										//borderCapStyle
										echo "borderCapStyle: '" . $this->shared->prepare_javascript_string( stripslashes( $row_data['borderCapStyle'] ) ) . "',";

										//borderDash
										if(preg_match($this->shared->regex_borderDash, $row_data['borderDash'])){
											echo 'borderDash: [' . $this->shared->prepare_javascript_string( stripslashes( $row_data['borderDash'] ) ) . '],';
										}

										//borderDashOffset
										echo 'borderDashOffset: ' . floatval($row_data['borderDashOffset']) . ',';

										//borderJoinStyle
										echo "borderJoinStyle: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['borderJoinStyle'])) . "',";

										//pointBorderColor
										echo 'pointBorderColor: ' . $this->output_color( $row_data['pointBorderColor'], 'line', 'pointBorderColor' ) . ',';

										//pointBackgroundColor
										echo 'pointBackgroundColor: ' . $this->output_color( $row_data['pointBackgroundColor'], 'line', 'pointBackgroundColor' ) . ',';

										//pointBorderWidth
										echo "pointBorderWidth: " . $this->output_number( $row_data['pointBorderWidth'], 'line', 'pointBorderWidth' ) . ",";

										//pointRadius
										echo "pointRadius: " . $this->output_number( $row_data['pointRadius'], 'line', 'pointRadius' ) . ",";

										//pointHoverRadius
										echo "pointHoverRadius: " . $this->output_number( $row_data['pointHoverRadius'], 'line', 'pointHoverRadius' ) . ",";

										//pointHitRadius
										echo "pointHitRadius: " . $this->output_number( $row_data['pointHitRadius'], 'line', 'pointHitRadius' ) . ",";

										//pointHoverBackgroundColor
										echo 'pointHoverBackgroundColor: ' . $this->output_color( $row_data['pointHoverBackgroundColor'], 'line', 'pointHoverBackgroundColor' ) . ',';

										//pointHoverBorderColor
										echo 'pointHoverBorderColor: ' . $this->output_color( $row_data['pointHoverBorderColor'], 'line', 'pointBorderColor' ) . ',';

										//pointHoverBorderWidth
										echo 'pointHoverBorderWidth: ' . $this->output_number( $row_data['pointHoverBorderWidth'], 'line', 'pointHoverBorderWidth' ) . ',';

										//pointStyle
										echo 'pointStyle: ' . $this->output_pointStyle( $row_data['pointStyle'] ) . ',';

										//showLine
										echo 'showLine: ' . $this->boolean_string($row_data['showLine']) . ',';

										//spanGaps
										echo 'spanGaps: ' . $this->boolean_string($row_data['spanGaps']) . ',';

										break;

									case 'bar':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//yAxisID
										if( intval($row_data['plotY2'], 10) === 1 ){
											echo "yAxisID: 'y2-identifier',";
										}

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'bar', 'backgroundColor' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'bar', 'borderColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'bar', 'borderWidth' ) . ',';

										//hoverBackgroundColor
										echo 'hoverBackgroundColor: ' . $this->output_color( $row_data['hoverBackgroundColor'], 'bar', 'hoverBackgroundColor' ) . ',';

										//hoverBorderColor
										echo 'hoverBorderColor: ' . $this->output_color( $row_data['hoverBorderColor'], 'bar', 'hoverBorderColor' ) . ',';

										//hoverBorderWidth
										echo 'hoverBorderWidth: ' . $this->output_number( $row_data['hoverBorderWidth'], 'bar', 'hoverBorderWidth' ) . ',';

										break;

									case 'horizontalBar':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'bar', 'backgroundColor' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'bar', 'borderColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'bar', 'borderWidth' ) . ',';

										//hoverBackgroundColor
										echo 'hoverBackgroundColor: ' . $this->output_color( $row_data['hoverBackgroundColor'], 'bar', 'hoverBackgroundColor' ) . ',';

										//hoverBorderColor
										echo 'hoverBorderColor: ' . $this->output_color( $row_data['hoverBorderColor'], 'bar', 'hoverBorderColor' ) . ',';

										//hoverBorderWidth
										echo 'hoverBorderWidth: ' . $this->output_number( $row_data['hoverBorderWidth'], 'bar', 'hoverBorderWidth' ) . ',';

										break;

									case 'radar':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//fill
										echo 'fill: ' . $this->boolean_string($row_data['fill']) . ',';

										//lineTension
										echo 'lineTension: ' . floatval($row_data['lineTension']) . ',';

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'radar', 'backgroundColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'radar', 'borderWidth' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'radar', 'borderColor' ) . ',';

										//borderCapStyle
										echo "borderCapStyle: '" . $this->shared->prepare_javascript_string( stripslashes( $row_data['borderCapStyle'] ) ) . "',";

										//borderDash
										if(preg_match($this->shared->regex_borderDash, $row_data['borderDash'])){
											echo 'borderDash: [' . $this->shared->prepare_javascript_string( stripslashes( $row_data['borderDash'] ) ) . '],';
										}

										//borderDashOffset
										echo 'borderDashOffset: ' . floatval($row_data['borderDashOffset']) . ',';

										//borderJoinStyle
										echo "borderJoinStyle: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['borderJoinStyle'])) . "',";

										//pointBorderColor
										echo 'pointBorderColor: ' . $this->output_color( $row_data['pointBorderColor'], 'radar', 'pointBorderColor' ) . ',';

										//pointBackgroundColor
										echo 'pointBackgroundColor: ' . $this->output_color( $row_data['pointBackgroundColor'], 'radar', 'pointBackgroundColor' ) . ',';

										//pointBorderWidth
										echo "pointBorderWidth: " . $this->output_number( $row_data['pointBorderWidth'], 'radar', 'pointBorderWidth' ) . ",";

										//pointRadius
										echo "pointRadius: " . $this->output_number( $row_data['pointRadius'], 'radar', 'pointRadius' ) . ",";

										//pointHoverRadius
										echo "pointHoverRadius: " . $this->output_number( $row_data['pointHoverRadius'], 'radar', 'pointHoverRadius' ) . ",";

										//hitRadius
										echo "hitRadius: '" . intval($row_data['hitRadius'], 10) . "',";

										//pointHoverBackgroundColor
										echo 'pointHoverBackgroundColor: ' . $this->output_color( $row_data['pointHoverBackgroundColor'], 'radar', 'pointHoverBackgroundColor' ) . ',';

										//pointHoverBorderColor
										echo 'pointHoverBorderColor: ' . $this->output_color( $row_data['pointHoverBorderColor'], 'radar', 'pointHoverBorderColor' ) . ',';

										//pointHoverBorderWidth
										echo 'pointHoverBorderWidth: "' . $this->output_number( $row_data['pointHoverBorderWidth'], 'radar', 'pointHoverBorderWidth' ) . '",';

										//pointStyle
										echo 'pointStyle: ' . $this->output_pointStyle( $row_data['pointStyle'] ) . ',';

										break;

									case 'polarArea':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'polarArea', 'backgroundColor' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'polarArea', 'borderColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'polarArea', 'borderWidth' ) . ',';

										//hoverBackgroundColor
										echo 'hoverBackgroundColor: ' . $this->output_color( $row_data['hoverBackgroundColor'], 'polarArea', 'hoverBackgroundColor' ) . ',';

										//hoverBorderColor
										echo 'hoverBorderColor: ' . $this->output_color( $row_data['hoverBorderColor'], 'polarArea', 'hoverBorderColor' ) . ',';

										//hoverBorderWidth
										echo 'hoverBorderWidth: ' . $this->output_number( $row_data['hoverBorderWidth'], 'polarArea', 'hoverBorderWidth' ) . ',';

										break;

									case 'pie':
									case 'doughnut':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'pie', 'backgroundColor' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'pie', 'borderColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'pie', 'borderWidth' ) . ',';

										//hoverBackgroundColor
										echo 'hoverBackgroundColor: ' . $this->output_color( $row_data['hoverBackgroundColor'], 'pie', 'hoverBackgroundColor' ) . ',';

										//hoverBorderColor
										echo 'hoverBorderColor: ' . $this->output_color( $row_data['hoverBorderColor'], 'pie', 'hoverBorderColor' ) . ',';

										//hoverBorderWidth
										echo 'hoverBorderWidth: ' . $this->output_number( $row_data['hoverBorderWidth'], 'pie', 'hoverBorderWidth' ) . ',';

										break;

									case 'bubble':

										//label
										echo "label: '" . $this->shared->prepare_javascript_string(stripslashes($row_data['label'])) . "',";

										//yAxisID
										if( intval($row_data['plotY2'], 10) === 1 ){
											echo "yAxisID: 'y2-identifier',";
										}

										//backgroundColor
										echo 'backgroundColor: ' . $this->output_color( $row_data['backgroundColor'], 'bubble', 'backgroundColor' ) . ',';

										//borderColor
										echo 'borderColor: ' . $this->output_color( $row_data['borderColor'], 'bubble', 'borderColor' ) . ',';

										//borderWidth
										echo 'borderWidth: ' . $this->output_number( $row_data['borderWidth'], 'bubble', 'borderWidth' ) . ',';

										//hoverBackgroundColor
										echo 'hoverBackgroundColor: ' . $this->output_color( $row_data['hoverBackgroundColor'], 'bubble', 'hoverBackgroundColor' ) . ',';

										//hoverBorderColor
										echo 'hoverBorderColor: ' . $this->output_color( $row_data['hoverBorderColor'], 'bubble', 'hoverBorderColor' ) . ',';

										//hoverBorderWidth
										echo 'hoverBorderWidth: ' . $this->output_number( $row_data['hoverBorderWidth'], 'bubble', 'hoverBorderWidth' ) . ',';

										//hoverRadius
										echo 'hoverRadius: ' . $this->output_number( $row_data['hoverRadius'], 'bubble', 'hoverRadius' ) . ',';

										break;

								}

								// -------------------------------------------------------------------------------------

								echo 'data: [';

								foreach ( $row_data_a as $key2 => $row_data ) {

									echo $this->prepare_data($row_data);

									if ( ( $key2 + 1 ) < count( $row_data_a ) ) {
										echo ',';
									}

									if ( ( $key2 + 1 ) == count( $row_data_a ) ) {
										echo ']}';
									}

								}

								if ( ( $key1 + 1 ) < count( $data_a ) ) {
									echo ',';
								}

							}

							?>

					]},

					options: {

						/*
						Prevents the click on the legend to disable and enable the dataset
						 */
						<?php if( intval($chart->legend_toggle_dataset, 10) === 0 ) : ?>
							legend: {
								onClick: function() {
									return;
								}
							},
						<?php endif; ?>

						tooltips: {
							callbacks: {
								<?php if( strlen(trim($chart->tooltips_beforeTitle)) > 0 ) : ?>
									beforeTitle: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_beforeTitle)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_afterTitle)) > 0 ) : ?>
									afterTitle: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_afterTitle)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_beforeBody)) > 0 ) : ?>
									beforeBody: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_beforeBody)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_afterBody)) > 0 ) : ?>
									afterBody: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_afterBody)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_beforeLabel)) > 0 ) : ?>
									beforeLabel: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_beforeLabel)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_afterLabel)) > 0 ) : ?>
									afterLabel: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_afterLabel)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_beforeFooter)) > 0 ) : ?>
									beforeFooter: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_beforeFooter)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_footer)) > 0 ) : ?>
									footer: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_footer)); ?>';
									},
								<?php endif; ?>
								<?php if( strlen(trim($chart->tooltips_afterFooter)) > 0 ) : ?>
									afterFooter: function() {
										return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->tooltips_afterFooter)); ?>';
									},
								<?php endif; ?>
							}
						},

						<?php if($chart->type == 'polarArea' or $chart->type == 'doughnut') : ?>

							animation:{
								animateRotate: <?php echo $this->boolean_string($chart->animation_animateRotate); ?>,
								animateScale: <?php echo $this->boolean_string($chart->animation_animateScale); ?>
							},

						<?php endif; ?>

						//Affect bar and horizontalBar chart
						<?php if($chart->type == 'bar' or $chart->type == 'horizontalBar') : ?>

							elements:{
								rectangle:{
									borderSkipped: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->elements_rectangle_borderSkipped)); ?>'
								}
							},

						<?php endif; ?>

						<?php if($chart->type == 'radar' or $chart->type == 'polarArea') : ?>

							scale: {

								<?php

								//scales_rl_display
								if(intval($chart->scales_rl_display, 10)){

									//RL Scale Common
									echo 'display: true,';

									?>

									//R Scale Grid Line
									gridLines: {
										display: <?php echo $this->boolean_string($chart->scales_rl_gridLines_display); ?>,
										color: <?php echo $this->output_color( $chart->scales_rl_gridLines_color, $chart->type, 'scales_rl_gridLines_color' ); ?>,
										lineWidth: <?php echo $this->output_number( $chart->scales_rl_gridLines_lineWidth, $chart->type, 'scales_rl_gridLines_lineWidth' ); ?>
									},

									//RL Scale Angle Line
									angleLines:{
										display: <?php echo $this->boolean_string($chart->scales_rl_angleLines_display); ?>,
										color: <?php echo $this->output_color( $chart->scales_rl_angleLines_color, $chart->type, 'scales_rl_angleLines_color' ); ?>,
										lineWidth: <?php echo $this->output_number( $chart->scales_rl_angleLines_lineWidth, $chart->type, 'scales_rl_angleLines_lineWidth' ); ?>
									},

									//RL Scale Point Label
									pointLabels:{
										fontSize: <?php echo intval($chart->scales_rl_pointLabels_fontSize, 10); ?>,
										fontColor: <?php echo $this->output_color( $chart->scales_rl_pointLabels_fontColor, $chart->type, 'scales_rl_pointLabels_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_rl_pointLabels_fontFamily), ENT_COMPAT); ?>",
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_rl_pointLabels_fontStyle)); ?>'
									},

									//RL Scale Tick
									reverse: <?php echo $this->boolean_string($chart->scales_rl_ticks_reverse); ?>,
									ticks: {

										display: <?php echo $this->boolean_string( $chart->scales_rl_ticks_display ); ?>,
										autoskip: <?php echo $this->boolean_string( $chart->scales_rl_ticks_autoskip ); ?>,
										fontSize: <?php echo intval( $chart->scales_rl_ticks_fontSize, 10 ); ?>,
										fontColor: <?php echo $this->output_color( $chart->scales_rl_ticks_fontColor, $chart->type, 'scales_rl_ticks_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars( stripslashes( $chart->scales_rl_ticks_fontFamily ), ENT_COMPAT ); ?>",
										fontStyle: '<?php echo $this->shared->prepare_javascript_string( stripslashes( $chart->scales_rl_ticks_fontStyle ) ); ?>',

										//RL Scale Options (specific for the radial linear scale)
										<?php if ( strlen( trim( $chart->scales_rl_ticks_min ) ) > 0 ) {echo 'min: ' . floatval( $chart->scales_rl_ticks_min ) . ',';} ?>
										<?php if ( strlen( trim( $chart->scales_rl_ticks_max ) ) > 0 ) {echo 'max: ' . floatval( $chart->scales_rl_ticks_max ) . ',';} ?>
										<?php if ( strlen( trim( $chart->scales_rl_ticks_suggestedMin ) ) > 0 ) {echo 'suggestedMin: ' . floatval( $chart->scales_rl_ticks_suggestedMin ) . ',';} ?>
										<?php if ( strlen( trim( $chart->scales_rl_ticks_suggestedMax ) ) > 0 ) {echo 'suggestedMax: ' . floatval( $chart->scales_rl_ticks_suggestedMax ) . ',';} ?>
										<?php if ( strlen( trim( $chart->scales_rl_ticks_stepSize ) ) > 0 ) {echo 'stepSize: ' . floatval( $chart->scales_rl_ticks_stepSize ) . ',';} ?>
										<?php if ( strlen( trim( $chart->scales_rl_ticks_fixedStepSize ) ) > 0 ) {echo 'fixedStepSize: ' . floatval( $chart->scales_rl_ticks_fixedStepSize ) . ',';} ?>
										<?php if( strlen( trim( $chart->scales_rl_ticks_maxTicksLimit ) ) > 0 ) : ?>
											maxTicksLimit: <?php echo intval( $chart->scales_rl_ticks_maxTicksLimit, 10 ); ?>,
										<?php endif; ?>
										beginAtZero: <?php echo $this->boolean_string( $chart->scales_rl_ticks_beginAtZero ); ?>,
										showLabelBackdrop: <?php echo $this->boolean_string( $chart->scales_rl_ticks_showLabelBackdrop ); ?>,
										backdropColor: <?php echo $this->output_color( $chart->scales_rl_ticks_backdropColor, $chart->type, 'scales_rl_ticks_backdropColor' ); ?>,
										backdropPaddingX: <?php echo intval( $chart->scales_rl_ticks_backdropPaddingX, 10 ); ?>,
										backdropPaddingY: <?php echo intval( $chart->scales_rl_ticks_backdropPaddingY, 10 ); ?>,

										userCallback: function (tick) {

											<?php if( strlen( trim( $chart->scales_rl_ticks_round ) ) > 0 ) : ?>
											var tick_output = tick.toFixed(<?php echo intval( $chart->scales_rl_ticks_round, 10 ); ?>).toString();
											<?php else : ?>
											var tick_output = tick.toString();
											<?php endif; ?>

											return '<?php echo $this->shared->prepare_javascript_string( stripslashes( $chart->scales_rl_ticks_prefix ) ); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string( stripslashes( $chart->scales_rl_ticks_suffix ) ); ?>';

										},

									}

								<?php

								}else{

									echo 'display: false,';

								}

								?>

							},

						<?php endif; ?>

						scales: {

							xAxes: [{

								//Scale Common Configuration scales_xAxes ----------------------------------------------

								<?php

								//scales_xAxes_display
								if(intval($chart->scales_xAxes_display, 10)){

									echo 'display: true,';

									?>

									//scales_xAxes_type
									type: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_type)); ?>',

									//scales_xAxes_position
									position: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_position)); ?>',

									//scales_xAxes_stacked
									stacked: <?php echo $this->boolean_string($chart->scales_xAxes_stacked); ?>,

									<?php if( $chart->type == 'bar' ) : ?>
										categoryPercentage: <?php echo floatval($chart->scales_xAxes_categoryPercentage); ?>,
										barPercentage: <?php echo floatval($chart->scales_xAxes_barPercentage); ?>,
									<?php endif; ?>

									//Scale Grid Line Configuration scales_xAxes_gridLines -----------------------------
									gridLines: {
										display: <?php echo $this->boolean_string($chart->scales_xAxes_gridLines_display); ?>,
										color: <?php echo $this->output_color( $chart->scales_xAxes_gridLines_color, $chart->type, 'scales_xAxes_gridLines_color' ); ?>,
										lineWidth: <?php echo $this->output_number( $chart->scales_xAxes_gridLines_lineWidth, $chart->type, 'scales_xAxes_gridLines_lineWidth' ); ?>,
										drawBorder: <?php echo $this->boolean_string($chart->scales_xAxes_gridLines_drawBorder); ?>,
										drawOnChartArea: <?php echo $this->boolean_string($chart->scales_xAxes_gridLines_drawOnChartArea); ?>,
										drawTicks: <?php echo $this->boolean_string($chart->scales_xAxes_gridLines_drawTicks); ?>,
										tickMarkLength: <?php echo intval($chart->scales_xAxes_gridLines_tickMarkLength, 10); ?>,
										zeroLineWidth: <?php echo intval( $chart->scales_xAxes_gridLines_zeroLineWidth, 10 ); ?>,
										zeroLineColor: 	<?php echo $this->output_color( $chart->scales_xAxes_gridLines_zeroLineColor, $chart->type, 'scales_xAxes_gridLines_zeroLineColor' ); ?>,
										offsetGridLines: <?php echo $this->boolean_string($chart->scales_xAxes_gridLines_offsetGridLines); ?>
									},

									//Scale Title Configuration scales_xAxes_scaleLabel --------------------------------
									scaleLabel: {
										display: <?php echo $this->boolean_string($chart->scales_xAxes_scaleLabel_display); ?>,
										labelString: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_scaleLabel_labelString)); ?>',
										fontColor: <?php echo $this->output_color( $chart->scales_xAxes_scaleLabel_fontColor, $chart->type, 'scales_xAxes_scaleLabel_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_xAxes_scaleLabel_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_xAxes_scaleLabel_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_scaleLabel_fontStyle)); ?>',
									},

									//Scale Tick Configuration scales_xAxes_ticks --------------------------------------
									ticks: {

										/*
										 If there is a logarithmic scale active this function is used to display the real
										 value on the xAxis.
										 Note that this function with this specific content should be used only with the
										 logarithmic scale. Other versions of this function might be used to customize the
										 string representation of the tick value
										 */
										<?php if( $chart->scales_xAxes_type == 'logarithmic' ) : ?>

											userCallback: function(tick) {

												var remain = tick / (Math.pow(10, Math.floor(Chart.helpers.log10(tick))));

												//this determines how many ticks are shown
												if (remain === 1 || remain === 2 || remain === 5) {

													<?php if( strlen(trim($chart->scales_xAxes_ticks_round)) > 0 ) : ?>
														var tick_output = tick.toFixed(<?php echo intval($chart->scales_xAxes_ticks_round, 10); ?>).toString();
													<?php else : ?>
														var tick_output = tick.toString();
													<?php endif; ?>

													return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_ticks_suffix)); ?>';

												}

												return '';

											},

										<?php else : ?>

											userCallback: function(tick) {

												<?php if( strlen(trim($chart->scales_xAxes_ticks_round)) > 0 ) : ?>
													var tick_output = tick.toFixed(<?php echo intval($chart->scales_xAxes_ticks_round, 10); ?>).toString();
												<?php else : ?>
													var tick_output = tick.toString();
												<?php endif; ?>

												return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_ticks_suffix)); ?>';

											},

										<?php endif; ?>

										//Scale Tick Configuration -----------------------------------------------------
										autoSkip: <?php echo $this->boolean_string($chart->scales_xAxes_ticks_autoskip); ?>,
										display: <?php echo $this->boolean_string($chart->scales_xAxes_ticks_display); ?>,
										fontColor: <?php echo $this->output_color( $chart->scales_xAxes_ticks_fontColor, $chart->type, 'scales_xAxes_ticks_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_xAxes_ticks_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_xAxes_ticks_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_ticks_fontStyle)); ?>',
										labelOffset: <?php echo intval($chart->scales_xAxes_ticks_labelOffset, 10); ?>,
										maxRotation: <?php echo intval($chart->scales_xAxes_ticks_maxRotation, 10); ?>,
										minRotation: <?php echo intval($chart->scales_xAxes_ticks_minRotation, 10); ?>,
										reverse: <?php echo $this->boolean_string($chart->scales_xAxes_ticks_reverse); ?>,

										//Scale Configuration Options --------------------------------------------------
										<?php if(strlen(trim($chart->scales_xAxes_ticks_min)) > 0){echo 'min: '. floatval($chart->scales_xAxes_ticks_min) . ',';} ?>
										<?php if(strlen(trim($chart->scales_xAxes_ticks_max)) > 0){echo 'max: '. floatval($chart->scales_xAxes_ticks_max) . ',';} ?>

										//Scale Linear Configuration Options -------------------------------------------
										<?php if( $chart->scales_xAxes_type == 'linear' ) : ?>

											<?php if(strlen(trim($chart->scales_xAxes_ticks_fixedStepSize)) > 0){echo 'fixedStepSize: '. floatval($chart->scales_xAxes_ticks_fixedStepSize) . ',';} ?>
											beginAtZero: <?php echo $this->boolean_string($chart->scales_xAxes_ticks_beginAtZero); ?>,
											<?php if( strlen(trim($chart->scales_xAxes_ticks_maxTicksLimit)) > 0 ) : ?>
												maxTicksLimit: <?php echo intval($chart->scales_xAxes_ticks_maxTicksLimit, 10); ?>,
											<?php endif; ?>
											<?php if(strlen(trim($chart->scales_xAxes_ticks_stepSize)) > 0){echo 'stepSize: '. floatval($chart->scales_xAxes_ticks_stepSize) . ',';} ?>
											<?php if(strlen(trim($chart->scales_xAxes_ticks_suggestedMax)) > 0){echo 'suggestedMax: '. floatval($chart->scales_xAxes_ticks_suggestedMax) . ',';} ?>
											<?php if(strlen(trim($chart->scales_xAxes_ticks_suggestedMin)) > 0){echo 'suggestedMin: '. floatval($chart->scales_xAxes_ticks_suggestedMin) . ',';} ?>

										<?php endif; ?>

									},

									//Scale Time Configuration Options ------------------------------------------------------
									<?php if($chart->scales_xAxes_type == 'time' ) : ?>

										time: {
											format: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_format)); ?>',
											tooltipFormat: <?php echo "'" . $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_tooltipFormat)) . "'"; ?>,
											unitStepSize: <?php echo intval($chart->scales_xAxes_time_unitStepSize, 10); ?>,
											<?php if(strlen(trim($chart->scales_xAxes_time_max)) > 0){echo "max: '". $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_max)) . "',";} ?>
											<?php if(strlen(trim($chart->scales_xAxes_time_min)) > 0){echo "min: '" . $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_min)) . "',";} ?>
											displayFormats: {
												<?php echo esc_attr(stripslashes($chart->scales_xAxes_time_unit)); ?>: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_unit_format)); ?>',

											},
											unit: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_xAxes_time_unit)); ?>'
										}

									<?php endif; ?>

									<?php

								}else{

									echo 'display: false,';

								}
								?>

							}],

							yAxes: [{

								//Scale Common Configuration scales_yAxes ----------------------------------------------

								<?php

								//scales_yAxes_display
								if(intval($chart->scales_yAxes_display, 10)){

									echo 'display: true,';

									?>

									//scales_yAxes_type
									type: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_type)); ?>',

									//scales_yAxes_position
									position: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_position)); ?>',

									//scales_yAxes_stacked
									stacked: <?php echo $this->boolean_string($chart->scales_yAxes_stacked); ?>,//default: false

									<?php if( $chart->type == 'horizontalBar' ) : ?>

										categoryPercentage: <?php echo floatval($chart->scales_yAxes_categoryPercentage); ?>,
										barPercentage: <?php echo floatval($chart->scales_yAxes_barPercentage); ?>,

									<?php endif; ?>

									//Scale Grid Line Configuration scales_yAxes_gridLines -----------------------------
									gridLines: {
										display: <?php echo $this->boolean_string($chart->scales_yAxes_gridLines_display); ?>,
										color: <?php echo $this->output_color( $chart->scales_yAxes_gridLines_color, $chart->type, 'scales_yAxes_gridLines_color' ); ?>,
										lineWidth: <?php echo $this->output_number( $chart->scales_yAxes_gridLines_lineWidth, $chart->type, 'scales_yAxes_gridLines_lineWidth' ); ?>,
										drawBorder: <?php echo $this->boolean_string($chart->scales_yAxes_gridLines_drawBorder); ?>,
										drawOnChartArea: <?php echo $this->boolean_string($chart->scales_yAxes_gridLines_drawOnChartArea); ?>,
										drawTicks: <?php echo $this->boolean_string($chart->scales_yAxes_gridLines_drawTicks); ?>,
										tickMarkLength: <?php echo intval($chart->scales_yAxes_gridLines_tickMarkLength, 10); ?>,
										zeroLineWidth: <?php echo intval($chart->scales_yAxes_gridLines_zeroLineWidth, 10); ?>,
										zeroLineColor: 	<?php echo $this->output_color( $chart->scales_yAxes_gridLines_zeroLineColor, $chart->type, 'scales_yAxes_gridLines_zeroLineColor' ); ?>,
										offsetGridLines: <?php echo $this->boolean_string($chart->scales_yAxes_gridLines_offsetGridLines); ?>
									},

									//Scale Title Configuration scales_yAxes_scaleLabel --------------------------------
									scaleLabel: {
										display: <?php echo $this->boolean_string($chart->scales_yAxes_scaleLabel_display); ?>,
										labelString: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_scaleLabel_labelString)); ?>',
										fontColor: <?php echo $this->output_color( $chart->scales_yAxes_scaleLabel_fontColor, $chart->type, 'scales_yAxes_scaleLabel_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_yAxes_scaleLabel_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_yAxes_scaleLabel_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_scaleLabel_fontStyle)); ?>',
									},

									//Scale Tick Configuration scales_yAxes_ticks --------------------------------------
									ticks: {

										/*
										 If there is a logarithmic scale active this function is used to display the real
										 value on the yAxis.
										 Note that this function with this specific content should be used only with the
										 logarithmic scale. Other versions of this function might be used to customize the
										 string representation of the tick value
										 */
										<?php if( $chart->scales_yAxes_type == 'logarithmic' ) : ?>

											userCallback: function(tick) {

												var remain = tick / (Math.pow(10, Math.floor(Chart.helpers.log10(tick))));

												//this determines how many ticks are shown
												if (remain === 1 || remain === 2 || remain === 5) {

													<?php if( strlen(trim($chart->scales_yAxes_ticks_round)) > 0 ) : ?>
														var tick_output = tick.toFixed(<?php echo intval($chart->scales_yAxes_ticks_round, 10); ?>).toString();
													<?php else : ?>
														var tick_output = tick.toString();
													<?php endif; ?>

													return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_ticks_suffix)); ?>';

												}

												return '';

											},

										<?php else : ?>

											userCallback: function(tick) {

												<?php if( strlen(trim($chart->scales_yAxes_ticks_round)) > 0 ) : ?>
													var tick_output = tick.toFixed(<?php echo intval($chart->scales_yAxes_ticks_round, 10); ?>).toString();
												<?php else : ?>
													var tick_output = tick.toString();
												<?php endif; ?>

												return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_ticks_suffix)); ?>';

											},

										<?php endif; ?>

										//Scale Tick Configuration -----------------------------------------------------
										autoSkip: <?php echo $this->boolean_string($chart->scales_yAxes_ticks_autoskip); ?>,
										display: <?php echo $this->boolean_string($chart->scales_yAxes_ticks_display); ?>,
										fontColor: <?php echo $this->output_color( $chart->scales_yAxes_ticks_fontColor, $chart->type, 'scales_yAxes_ticks_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_yAxes_ticks_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_yAxes_ticks_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_yAxes_ticks_fontStyle)); ?>',
										maxRotation: <?php echo intval($chart->scales_yAxes_ticks_maxRotation, 10); ?>,
										minRotation: <?php echo intval($chart->scales_yAxes_ticks_minRotation, 10); ?>,
										mirror: <?php echo $this->boolean_string($chart->scales_yAxes_ticks_mirror); ?>,
										padding: <?php echo intval($chart->scales_yAxes_ticks_padding, 10); ?>,
										reverse: <?php echo $this->boolean_string($chart->scales_yAxes_ticks_reverse); ?>,

										//Scale Configuration Options --------------------------------------------------
										<?php if(strlen(trim($chart->scales_yAxes_ticks_min)) > 0){echo 'min: '. floatval($chart->scales_yAxes_ticks_min) . ',';} ?>
										<?php if(strlen(trim($chart->scales_yAxes_ticks_max)) > 0){echo 'max: '. floatval($chart->scales_yAxes_ticks_max) . ',';} ?>

										//Scale Linear Configuration Options -------------------------------------------
										<?php if( $chart->scales_yAxes_type == 'linear' ) : ?>

											<?php if(strlen(trim($chart->scales_yAxes_ticks_fixedStepSize)) > 0){echo 'fixedStepSize: '. floatval($chart->scales_yAxes_ticks_fixedStepSize) . ',';} ?>
											beginAtZero: <?php echo $this->boolean_string($chart->scales_yAxes_ticks_beginAtZero); ?>,
											<?php if( strlen(trim($chart->scales_yAxes_ticks_maxTicksLimit)) > 0 ) : ?>
												maxTicksLimit: <?php echo intval($chart->scales_yAxes_ticks_maxTicksLimit, 10); ?>,
											<?php endif; ?>
											<?php if(strlen(trim($chart->scales_yAxes_ticks_stepSize)) > 0){echo 'stepSize: '. floatval($chart->scales_yAxes_ticks_stepSize) . ',';} ?>
											<?php if(strlen(trim($chart->scales_yAxes_ticks_suggestedMax)) > 0){echo 'suggestedMax: '. floatval($chart->scales_yAxes_ticks_suggestedMax) . ',';} ?>
											<?php if(strlen(trim($chart->scales_yAxes_ticks_suggestedMin)) > 0){echo 'suggestedMin: '. floatval($chart->scales_yAxes_ticks_suggestedMin) . ',';} ?>

										<?php endif; ?>

									}

									<?php

								}else{

									echo 'display: false,';

								}
								?>

							},{

								//Scale Common Configuration scales_y2Axes ---------------------------------------------

								<?php

								//scales_y2Axes_display
								if(intval($chart->scales_y2Axes_display, 10)){

									echo 'display: true,';

									?>

									//scales_y2Axes_type
									type: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_type)); ?>',

									//scales_y2Axes_position
									position: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_position)); ?>',

									//identifier of the Y2 axis
									id: 'y2-identifier',

									//Scale Grid Line Configuration scales_y2Axes_gridLines ----------------------------
									gridLines: {
										display: <?php echo $this->boolean_string($chart->scales_y2Axes_gridLines_display); ?>,
										color: <?php echo $this->output_color( $chart->scales_y2Axes_gridLines_color, $chart->type, 'scales_y2Axes_gridLines_color' ); ?>,
										lineWidth: <?php echo $this->output_number( $chart->scales_y2Axes_gridLines_lineWidth, $chart->type, 'scales_y2Axes_gridLines_lineWidth' ); ?>,
										drawBorder: <?php echo $this->boolean_string($chart->scales_y2Axes_gridLines_drawBorder); ?>,
										drawOnChartArea: <?php echo $this->boolean_string($chart->scales_y2Axes_gridLines_drawOnChartArea); ?>,
										drawTicks: <?php echo $this->boolean_string($chart->scales_y2Axes_gridLines_drawTicks); ?>,
										tickMarkLength: <?php echo intval($chart->scales_y2Axes_gridLines_tickMarkLength, 10); ?>,
										zeroLineWidth: <?php echo intval( $chart->scales_y2Axes_gridLines_zeroLineWidth, 10 ); ?>,
										zeroLineColor: 	<?php echo $this->output_color( $chart->scales_y2Axes_gridLines_zeroLineColor, $chart->type, 'scales_y2Axes_gridLines_zeroLineColor' ); ?>,
										offsetGridLines: <?php echo $this->boolean_string($chart->scales_y2Axes_gridLines_offsetGridLines); ?>
									},

									//Scale Title Configuration scales_y2Axes_scaleLabel -------------------------------
									scaleLabel: {
										display: <?php echo $this->boolean_string($chart->scales_y2Axes_scaleLabel_display); ?>,
										labelString: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_scaleLabel_labelString)); ?>',
										fontColor: <?php echo $this->output_color( $chart->scales_y2Axes_scaleLabel_fontColor, $chart->type, 'scales_y2Axes_scaleLabel_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_y2Axes_scaleLabel_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_y2Axes_scaleLabel_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_scaleLabel_fontStyle)); ?>',
									},

									//Scale Tick Configuration scales_y2Axes_ticks -------------------------------------
									ticks: {

										/*
										 If there is a logarithmic scale active this function is used to display the real
										 value on the yAxis.
										 Note that this function with this specific content should be used only with the
										 logarithmic scale. Other versions of this function might be used to customize the
										 string representation of the tick value
										 */
										<?php if( $chart->scales_y2Axes_type == 'logarithmic' ) : ?>

											userCallback: function(tick) {
												var remain = tick / (Math.pow(10, Math.floor(Chart.helpers.log10(tick))));

												//this determines how many ticks are shown
												if (remain === 1 || remain === 2 || remain === 5) {

													<?php if( strlen(trim($chart->scales_y2Axes_ticks_round)) > 0 ) : ?>
														var tick_output = tick.toFixed(<?php echo intval($chart->scales_y2Axes_ticks_round, 10); ?>).toString();
													<?php else : ?>
														var tick_output = tick.toString();
													<?php endif; ?>

													return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_ticks_suffix)); ?>';

												}
												return '';
											},

										<?php else : ?>
										userCallback: function(tick) {

											<?php if( strlen(trim($chart->scales_y2Axes_ticks_round)) > 0 ) : ?>
												var tick_output = tick.toFixed(<?php echo intval($chart->scales_y2Axes_ticks_round, 10); ?>).toString();
											<?php else : ?>
												var tick_output = tick.toString();
											<?php endif; ?>

											return '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_ticks_prefix)); ?>' + tick_output + '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_ticks_suffix)); ?>';

										},
										<?php endif; ?>

										//Scale Tick Configuration -----------------------------------------------------
										autoSkip: <?php echo $this->boolean_string($chart->scales_y2Axes_ticks_autoskip); ?>,
										display: <?php echo $this->boolean_string($chart->scales_y2Axes_ticks_display); ?>,
										fontColor: <?php echo $this->output_color( $chart->scales_y2Axes_ticks_fontColor, $chart->type, 'scales_y2Axes_ticks_fontColor' ); ?>,
										fontFamily: "<?php echo htmlspecialchars(stripslashes($chart->scales_y2Axes_ticks_fontFamily), ENT_COMPAT); ?>",
										fontSize: <?php echo intval($chart->scales_y2Axes_ticks_fontSize, 10); ?>,
										fontStyle: '<?php echo $this->shared->prepare_javascript_string(stripslashes($chart->scales_y2Axes_ticks_fontStyle)); ?>',
										maxRotation: <?php echo intval($chart->scales_y2Axes_ticks_maxRotation, 10); ?>,
										minRotation: <?php echo intval($chart->scales_y2Axes_ticks_minRotation, 10); ?>,
										mirror: <?php echo $this->boolean_string($chart->scales_y2Axes_ticks_mirror); ?>,
										padding: <?php echo intval($chart->scales_y2Axes_ticks_padding, 10); ?>,
										reverse: <?php echo $this->boolean_string($chart->scales_y2Axes_ticks_reverse); ?>,

										//Scale Configuration Options --------------------------------------------------
										<?php if(strlen(trim($chart->scales_y2Axes_ticks_min)) > 0){echo 'min: '. floatval($chart->scales_y2Axes_ticks_min) . ',';} ?>
										<?php if(strlen(trim($chart->scales_y2Axes_ticks_max)) > 0){echo 'max: '. floatval($chart->scales_y2Axes_ticks_max) . ',';} ?>

										//Scale Linear Configuration Options -------------------------------------------
										<?php if( $chart->scales_y2Axes_type == 'linear' ) : ?>

											<?php if(strlen(trim($chart->scales_y2Axes_ticks_fixedStepSize)) > 0){echo 'fixedStepSize: '. floatval($chart->scales_y2Axes_ticks_fixedStepSize) . ',';} ?>
											beginAtZero: <?php echo $this->boolean_string($chart->scales_y2Axes_ticks_beginAtZero); ?>,
											<?php if( strlen(trim($chart->scales_y2Axes_ticks_maxTicksLimit)) > 0 ) : ?>
												maxTicksLimit: <?php echo intval($chart->scales_y2Axes_ticks_maxTicksLimit, 10); ?>,
											<?php endif; ?>
											<?php if(strlen(trim($chart->scales_y2Axes_ticks_stepSize)) > 0){echo 'stepSize: '. floatval($chart->scales_y2Axes_ticks_stepSize) . ',';} ?>
											<?php if(strlen(trim($chart->scales_y2Axes_ticks_suggestedMax)) > 0){echo 'suggestedMax: '. floatval($chart->scales_y2Axes_ticks_suggestedMax) . ',';} ?>
											<?php if(strlen(trim($chart->scales_y2Axes_ticks_suggestedMin)) > 0){echo 'suggestedMin: '. floatval($chart->scales_y2Axes_ticks_suggestedMin) . ',';} ?>

										<?php endif; ?>

									}

									<?php

								}else{

									echo 'display: false,';

								}

								?>

							}]

						}
					}
				});

			</script>

			<?php

		}

		$out = ob_get_clean();

		//compress javascript if the specific option is enabled
		if ( intval(get_option( $this->shared->get('slug') . "_compress_output"), 10) == 1 )  {
			$out = \JShrink\Minifier::minify($out);
		}

		echo($out);

	}

	/*
	 * Based on the $data returns a single color or a javascript array of colors
	 *
	 * @param $data string
	 * @param $chart_type string
	 * @param $property string
	 * @return string
	 */
	private function output_color($data, $chart_type, $property){

		$data = str_replace(' ', '', stripslashes($data));

		/*
		 * The multiple lookbehinds prevent to use as a separator the comma included inside the definition of an rgba
		 * color
		 *
		 * Multiple lookbehinds are required because lookbehinds need to be zero-width, thus quantifiers are not allowed
		 */
		$color_a = preg_split('/
			(?<!(rgba\(\d{1}))
			(?<!(rgba\(\d{2}))
			(?<!(rgba\(\d{3}))
			(?<!(rgba\(\d{1}\,\d{1}))
			(?<!(rgba\(\d{1}\,\d{2}))
			(?<!(rgba\(\d{1}\,\d{3}))
			(?<!(rgba\(\d{2}\,\d{1}))
			(?<!(rgba\(\d{2}\,\d{2}))
			(?<!(rgba\(\d{2}\,\d{3}))
			(?<!(rgba\(\d{3}\,\d{1}))
			(?<!(rgba\(\d{3}\,\d{2}))
			(?<!(rgba\(\d{3}\,\d{3}))
			(?<!(rgba\(\d{1}\,\d{1},\d{1}))
			(?<!(rgba\(\d{1}\,\d{2},\d{1}))
			(?<!(rgba\(\d{1}\,\d{3},\d{1}))
			(?<!(rgba\(\d{1}\,\d{1},\d{2}))
			(?<!(rgba\(\d{1}\,\d{2},\d{2}))
			(?<!(rgba\(\d{1}\,\d{3},\d{2}))
			(?<!(rgba\(\d{1}\,\d{1},\d{3}))
			(?<!(rgba\(\d{1}\,\d{2},\d{3}))
			(?<!(rgba\(\d{1}\,\d{3},\d{3}))
			(?<!(rgba\(\d{2}\,\d{1},\d{1}))
			(?<!(rgba\(\d{2}\,\d{2},\d{1}))
			(?<!(rgba\(\d{2}\,\d{3},\d{1}))
			(?<!(rgba\(\d{2}\,\d{1},\d{2}))
			(?<!(rgba\(\d{2}\,\d{2},\d{2}))
			(?<!(rgba\(\d{2}\,\d{3},\d{2}))
			(?<!(rgba\(\d{2}\,\d{1},\d{3}))
			(?<!(rgba\(\d{2}\,\d{2},\d{3}))
			(?<!(rgba\(\d{2}\,\d{3},\d{3}))
			(?<!(rgba\(\d{3}\,\d{1},\d{1}))
			(?<!(rgba\(\d{3}\,\d{2},\d{1}))
			(?<!(rgba\(\d{3}\,\d{3},\d{1}))
			(?<!(rgba\(\d{3}\,\d{1},\d{2}))
			(?<!(rgba\(\d{3}\,\d{2},\d{2}))
			(?<!(rgba\(\d{3}\,\d{3},\d{2}))
			(?<!(rgba\(\d{3}\,\d{1},\d{3}))
			(?<!(rgba\(\d{3}\,\d{2},\d{3}))
			(?<!(rgba\(\d{3}\,\d{3},\d{3}))
			,
			/x', $data);

		switch($property){

			case 'backgroundColor':
			case 'borderColor':

				if( count($color_a) > 1 and ( $chart_type == 'bar' or $chart_type == 'polarArea' or $chart_type == 'pie' or $chart_type == 'bubble' ) ){

					//return a javascript array
					$output = $this->generate_js_array($color_a);

				}else{

					//return a single color
					$output = '"' . $color_a[0] . '"';

				}

				break;

			case 'pointBorderColor':
			case 'pointBackgroundColor':
			case 'pointHoverBorderColor':
			case 'pointHoverBackgroundColor':
			case 'scales_xAxes_gridLines_color':
			case 'scales_yAxes_gridLines_color':
			case 'scales_y2Axes_gridLines_color':
			case 'scales_rl_gridLines_color':


				if( count($color_a) > 1 ){

					//return a javascript array
					$output = $this->generate_js_array($color_a);

				}else{

					//return a single color
					$output = '"' . $color_a[0] . '"';

				}

				break;

			case 'hoverBackgroundColor':
			case 'hoverBorderColor':

				if( count($color_a) > 1 and ( $chart_type == 'bar' or $chart_type == 'polarArea' or $chart_type == 'pie' or $chart_type == 'bubble' ) ){

					//return a javascript array
					$output = $this->generate_js_array($color_a);

				}else{

					//return a single color
					$output = '"' . $color_a[0] . '"';

				}

				break;

			case 'scales_xAxes_gridLines_zeroLineColor':
			case 'scales_xAxes_scaleLabel_fontColor':
			case 'scales_xAxes_ticks_fontColor':
			case 'scales_xAxes_ticks_backdropColor':
			case 'scales_xAxes_angleLines_color':
			case 'scales_xAxes_pointLabels_fontColor':
			case 'scales_yAxes_gridLines_zeroLineColor':
			case 'scales_yAxes_scaleLabel_fontColor':
			case 'scales_yAxes_ticks_fontColor':
			case 'scales_yAxes_ticks_backdropColor':
			case 'scales_yAxes_angleLines_color':
			case 'scales_yAxes_pointLabels_fontColor':
			case 'scales_y2Axes_gridLines_zeroLineColor':
			case 'scales_y2Axes_scaleLabel_fontColor':
			case 'scales_y2Axes_ticks_fontColor':
			case 'scales_y2Axes_ticks_backdropColor':
			case 'scales_y2Axes_angleLines_color':
			case 'scales_y2Axes_pointLabels_fontColor':
			case 'scales_rl_pointLabels_fontColor':
			case 'scales_rl_angleLines_color':
			case 'scales_rl_ticks_fontColor':
			case 'scales_rl_ticks_backdropColor':

				//return a single color
				$output = '"' . $color_a[0] . '"';

				break;

		}

		return $output;
	
	}

	/*
	 * Based on the $data returns a single number or a javascript array of numbers
	 *
	 * @param $data string
	 * @param $chart_type string
	 * @param $property string
	 * @return string
	 */
	private function output_number($data, $chart_type, $property){

		$data = str_replace(' ', '', stripslashes($data));
		$number_a = explode(',', $data);

		switch($property){

			case 'borderWidth':
			case 'hoverBorderWidth':

				if( count($number_a) > 1 and ( $chart_type == 'bar' or $chart_type == 'polarArea' or $chart_type == 'pie' or $chart_type == 'bubble' ) ){

					//return a javascript array
					$output = $this->generate_js_array($number_a);

				}else{

					//return a single color
					$output = $number_a[0];

				}

				break;

			case 'pointBorderWidth':
			case 'pointRadius':
			case 'pointHoverRadius':
			case 'pointHitRadius':
			case 'pointHoverBorderWidth':
			case 'hoverRadius':
			case 'scales_xAxes_gridLines_lineWidth':
			case 'scales_yAxes_gridLines_lineWidth':
			case 'scales_y2Axes_gridLines_lineWidth':
			case 'scales_rl_gridLines_lineWidth':
			case 'scales_rl_angleLines_lineWidth':

				if( count($number_a) > 1 ){

					//return a javascript array
					$output = $this->generate_js_array($number_a);

				}else{

					//return a single color
					$output = $number_a[0];

				}

				break;

		}

		return $output;

	}

	/*
	 * Based on $data returns a point style or a javascript array of point styles
	 *
	 * @param $data string
	 * @return string
	 */
	private function output_pointStyle($data) {

		$data     = str_replace( ' ', '', stripslashes( $data ) );
		$pointStyle_a = explode( ',', $data );

		if( count($pointStyle_a) > 1 ){

			//return a javascript array
			$output = $this->generate_js_array($pointStyle_a);

		}else{

			//return a single color
			$output = '"' . $pointStyle_a[0] . '"';

		}

		return $output;

	}

	/*
	 * Given a PHP array, a string that represents the related javascript array is returned
	 *
	 * @param $data_a Array
	 * @return String
	 */
	public function generate_js_array($data_a){

		$output = '[';
		foreach($data_a as $key => $value){

			$output .= '"' . $value . '"';
			if( $key < (count($data_a)-1) ){
				$output .= ',';
			}

		}
		$output .= ']';

		return $output;

	}

	/*
	 * Returns athe string 'true' or 'false' based on the provided integer or string
	 *
	 * @param String|Int
	 * @return String 'true' or 'false'
	 */
	public function boolean_string($value){

		$value = intval($value, 10);

		if($value === 1){
			return 'true';
		}else{
			return 'false';
		}

	}

	/*
	 * This is the behavior of this method:
	 *
	 * - If $data includes a single value return the value:
	 *   Ex. 5 -> 5
	 *
	 * - If $data includes two values separated by a slash output a javascript object with the two value assigned to
	 *   the x and y object properties: (this format is used in scatter chart)
	 *   Ex. 5/7 -> {x:5,y:7}
	 *
	 * - If $data includes three values separated by a slash output a javascript object with the three values
	 *   assigned to the x and y object properties: (this format is used in bubble chart)
	 *   Ex. 5/7/3 -> {x:5,y:7,r:3}
	 *
	 * @param $data String|Int
	 * @return String
	 */
	function prepare_data($data){

		$data_a = explode('/', $data);

		switch (count($data_a)){

			case 1:

				if( strlen(trim($data_a[0])) === 0 ){
					return 'null';
				}else{
					return floatval($data_a[0]);
				}

				break;

			case 2:
				return '{x:' . floatval($data_a[0]) . ',y:' . floatval($data_a[1]) . '}';
				break;

			case 3:
				return '{x:' . floatval($data_a[0]) . ',y:' . floatval($data_a[1]) . ',r:' . floatval($data_a[2]) . '}';
				break;

		}

	}

	/*
	 * Generate the chart preview based on the 'chart-preview' GET parameter value
	 */
	public function generate_chart_preview(){

		if( !isset($_GET['chart-preview']) or !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) ){
			return;
		}

		$chart_id = intval($_GET['chart-preview'], 10);

		?>

		<!doctype html>

		<html lang="en">
		<head>
			<meta charset="utf-8">
			<script type='text/javascript' src='<?php echo includes_url() . 'js/jquery/jquery.js'; ?>'></script>
			<script type="text/javascript" src='<?php echo esc_url(get_option( $this->shared->get( 'slug' ) . '_chartjs_library_url' )); ?>'></script>
			<script>
				window.onerror=function() {

					//remove canvas
					jQuery('canvas').remove();

					//show error message
					jQuery('body').append('<div id="chart-preview-error"><?php _e("Preview not available", "dauc"); ?></div>');

				}
			</script>
			<style type="text/css">
				#chart-preview-error{
					position: absolute;
					left: 0;
					top: 0;
					width: 984px;
					height: 492px;
					line-height: 492px;
					color: #777;
					font-size: 46px !important;
					text-align: center;
					font-family: Arial, sans-serif;
					text-align: center;
				}
			</style>
		</head>

		<body style="margin: 0;">


		<?php

		//get chart data
		global $wpdb; $table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
		$safe_sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d ", $chart_id);
		$chart_obj = $wpdb->get_row($safe_sql);

		//terminate if there is no chart with the specified id
		if($chart_obj !== NULL){

			//the charts property saves the chart data
			$this->charts[] = $chart_obj;

			//get the canvas background color if the transparent background option is disabled
			if( intval($chart_obj->canvas_transparent_background, 10) == 0){
				$background_color = 'background-color: ' . esc_attr(stripslashes($chart_obj->canvas_backgroundColor)) . ';';
			}else{
				$background_color = '';
			}

			echo '<canvas id="uberchart-' . $chart_id . '" style="' . $background_color . '"></canvas>';

			$this->instantiate_charts(true);

		}

		?>

		</body>
		</html>

		<?php
		die();

	}

}