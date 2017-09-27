<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Settings_Meta_Box {

	public static function output() {
		$charts_type = dw_chart()->chart_type();
		$options = get_post_meta( get_the_ID(), 'dw_chart_options', true );
		$chartType = isset( $options['chartType'] ) ? $options['chartType'] : 'area';
		?>
		<div class="wrap chart_data_settings">
			<div>
				<h2><?php _e( 'Chart Type', 'dwgc' ) ?></h2>
				<select class="chart_type image-picker" name="dw_chart_options[chartType]">
					<?php foreach( $charts_type as $k => $v ) : ?>
						<?php $img_url = file_exists( dw_chart()->assets_dir . 'img/' . $k . '.png' ) ? dw_chart()->assets_uri . 'img/' . $k . '.png' : '' ?>
						<option <?php selected( $chartType, $k ) ?> data-img-src="<?php echo esc_url( $img_url ) ?>" value="<?php echo $k ?>"><?php echo $v ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<table class="form-table">
				<?php echo self::geo_chart_settings( $options ); ?>
				<?php echo self::pie_chart_settings( $options ); ?>
				<?php echo self::scatter_chart_settings( $options ); ?>
				<?php echo self::waterfall_chart_settings( $options ); ?>
				<?php echo self::trendlines_chart_settings( $options ); ?>
			</table>
		</div>
		<?php
	}

	public static function geo_chart_settings( $options ) {
		$displayMode = isset( $options['displayMode'] ) ? $options['displayMode'] : 'auto';
		$region = isset( $options['region'] ) ? $options['region'] : 'world';
		$resolution = isset( $options['resolution'] ) ? $options['resolution'] : 'countries';
		$enableRegionInteractivity = isset( $options['enableRegionInteractivity'] ) ? $options['enableRegionInteractivity'] : 'yes';
		$keepAspectRatio = isset( $options['keepAspectRatio'] ) ? $options['keepAspectRatio'] : 'yes';
		$markerOpacity = isset( $options['markerOpacity'] ) ? $options['markerOpacity'] : '1';
		?>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Display Mode', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[displayMode]">
					<option <?php selected( $displayMode, 'auto' ) ?> value="auto"><?php _e( 'Auto - choose based on the formart of the data', 'dwgc' ) ?></option>
					<option <?php selected( $displayMode, 'regions' ) ?> value="regions"><?php _e( 'Region Map', 'dwgc' ); ?></option>
					<option <?php selected( $displayMode, 'markers' ) ?> value="markers"><?php _e( 'Marker Map' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Region', 'dwgc' ) ?></th>
			<td>
				<input type="text" class="regular-text" value="<?php echo $region ?>" name="dw_chart_options[region]">
			</td>
		</tr>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Resolution', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[resolution]">
					<option <?php selected( $resolution, 'countries' ) ?> value="countries"><?php _e( 'Countries', 'dwgc' ) ?></option>
					<option <?php selected( $resolution, 'provinces' ) ?> value="provinces"><?php _e( 'Provinces', 'dwgc' ) ?></option>
					<option <?php selected( $resolution, 'metros' ) ?> value="metros"><?php _e( 'Metros', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Enable region interactivity', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[enableRegionInteractivity]">
					<option <?php selected( $enableRegionInteractivity, 'yes' ) ?> value="yes"><?php _e( 'Yes', 'dwgc' ) ?></option>
					<option <?php selected( $enableRegionInteractivity, 'no' ) ?> value="no"><?php _e( 'No', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Keep aspect ratio', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[keepAspectRatio]">
					<option <?php selected( $keepAspectRatio, 'yes' ) ?> value="yes"><?php _e( 'Yes', 'dwgc' ) ?></option>
					<option <?php selected( $keepAspectRatio, 'no' ) ?> value="no"><?php _e( 'No', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-geo-chart">
			<th><?php _e( 'Marker opacity', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[markerOpacity]">
					<?php for ( $i = 0; $i <= 10; $i++ ) : ?>
						<option <?php selected( $markerOpacity, (string) ( $i/10 ) ) ?> value="<?php echo ($i/10) ?>"><?php echo ($i/10) ?></option>
					<?php endfor; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	public static function pie_chart_settings( $options ) {
		$pieHole = isset( $options['pieHole'] ) ? $options['pieHole'] : '';
		$slices_explode = isset( $options['slices_explode'] ) ? intval( $options['slices_explode'] ) : 1;
		$is3D = isset( $options['is3D'] ) ? $options['is3D'] : 'yes';
		$pieSliceText = isset( $options['pieSliceText'] ) ? $options['pieSliceText'] : 'none';
		?>
		<tr class="show-if-pie-chart">
			<th><?php _e( 'pieHole', 'dwgc' ) ?></th>
			<td>
				<input type="text" class="regular-text" value="<?php echo $pieHole ?>" name="dw_chart_options[pieHole]">
				<br><span class="description"><?php _e( 'Value from 0.1 to 1', 'dwgc' ) ?></span>
			</td>
		</tr>
		<?php /*
		<tr class="show-if-pie-chart">
			<th><?php _e( 'Exploding Slices', 'dwgc' ) ?></th>
			<td>
				<input type="text" class="regular-text" value="<?php echo $slices_explode ?>" name="dw_chart_options[slices_explode]">
			</td>
		</tr>
		*/ ?>
		<tr class="show-if-pie-chart">
			<th><?php _e( '3d Chart', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[is3D]">
					<option <?php selected( $is3D, 'yes' ) ?> value="yes"><?php _e( 'Yes', 'dwgc' ) ?></option>
					<option <?php selected( $is3D, 'no' ) ?> value="no"><?php _e( 'No', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-pie-chart">
			<th><?php _e( 'Slice text', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[pieSliceText]">
					<option <?php selected( $pieSliceText, 'percentage' ) ?> value="percentage"><?php _e( 'Percentage', 'dwgc' ) ?></option>
					<option <?php selected( $pieSliceText, 'value' ) ?> value="value"><?php _e( 'Value', 'dwgc' ) ?></option>
					<option <?php selected( $pieSliceText, 'label' ) ?> value="label"><?php _e( 'Label', 'dwgc' ) ?></option>
					<option <?php selected( $pieSliceText, 'none' ) ?> value="none"><?php _e( 'None', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	public static function scatter_chart_settings( $options ) {
		$pointShape = isset( $options['pointShape'] ) ? $options['pointShape'] : 'star';
		?>
		<tr class="show-if-scatter-chart show-if-trendlines-chart">
			<th><?php _e( 'Point Shape', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[pointShape]">
					<option <?php selected( $pointShape, 'circle' ) ?> value="circle"><?php _e( 'Circle', 'dwgc' ) ?></option>
					<option <?php selected( $pointShape, 'triangle' ) ?> value="triangle"><?php _e( 'Triangle', 'dwgc' ) ?></option>
					<option <?php selected( $pointShape, 'square' ) ?> value="square"><?php _e( 'Square', 'dwgc' ) ?></option>
					<option <?php selected( $pointShape, 'diamond' ) ?> value="diamond"><?php _e( 'Diamond', 'dwgc' ) ?></option>
					<option <?php selected( $pointShape, 'star' ) ?> value="star"><?php _e( 'Star', 'dwgc' ) ?></option>
					<option <?php selected( $pointShape, 'polygon' ) ?> value="polygon"><?php _e( 'Polygon', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	public static function trendlines_chart_settings( $options ) {
		$trendlines_type = isset( $options['trendlines_type'] ) ? $options['trendlines_type'] : 'linear';
		?>
		<tr class="show-if-trendlines-chart">
			<th><?php _e( 'Type', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[trendlines_type]">
					<option <?php selected( $trendlines_type, 'linear' ) ?> value="linear"><?php _e( 'Linear', 'dwgc' ) ?></option>
					<option <?php selected( $trendlines_type, 'exponential' ) ?> value="exponential"><?php _e( 'Exponential', 'dwgc' ) ?></option>
					<option <?php selected( $trendlines_type, 'polynomial' ) ?> value="polynomial"><?php _e( 'Polynomial', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	public static function waterfall_chart_settings( $options ) {
		$bar_setting = isset( $options['bar_setting'] ) ? $options['bar_setting'] : 'yes';
		$fallingColor = isset( $options['waterfall_falling_color'] ) ? $options['waterfall_falling_color'] : '#a52714';
		$risingColor = isset( $options['waterfall_rising_color'] ) ? $options['waterfall_rising_color'] : '#0f9d58';
		?>
		<tr class="show-if-waterfall-chart">
			<th><?php _e( 'Remove space', 'dwgc' ) ?></th>
			<td>
				<select name="dw_chart_options[bar_setting]">
					<option <?php selected( $bar_setting, 'yes' ) ?> value="yes"><?php _e( 'Yes', 'dwgc' ) ?></option>
					<option <?php selected( $bar_setting, 'no' ) ?> value="no"><?php _e( 'No', 'dwgc' ) ?></option>
				</select>
			</td>
		</tr>
		<tr class="show-if-waterfall-chart">
			<th><?php _e( 'Rising Color', 'dwgc' ) ?></th>
			<td>
				<input class="colorpicker" type="text" name="dw_chart_options[waterfall_rising_color]" value="<?php echo $risingColor ?>">
			</td>
		</tr>
		<tr class="show-if-waterfall-chart">
			<th><?php _e( "Falling Color", 'dwgc' ) ?></th>
			<td>
				<input class="colorpicker" type="text" name="dw_chart_options[waterfall_falling_color]" value="<?php echo $fallingColor ?>">
			</td>
		</tr>
		<?php
	}
}