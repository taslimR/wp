<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Advanced_Settings_Meta_Box {
	public static function output() {
		$options = get_post_meta( get_the_ID(), 'dw_chart_options', true );
		$legend = isset( $options['legend'] ) ? $options['legend'] : 'none';
		$tooltip = isset( $options['tooltip_trigger'] ) ? $options['tooltip_trigger'] : 'yes';
		?>
		<table class="form-table">
			<tr>
				<th><?php _e( 'Container Width', 'dwgc' ) ?></th>
				<td>
					<input type="text" class="regular-text" name="dw_chart_options[width]" value="<?php echo isset( $options['width'] ) ? $options['width'] : '100%' ?>">
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Container Height', 'dwgc' ) ?></th>
				<td>
					<input type="text" class="regular-text" name="dw_chart_options[height]" value="<?php echo isset( $options['height'] ) ? $options['height'] : '400' ?>">
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Legend Position', 'dwgc' ) ?></th>
				<td>
					<select name="dw_chart_options[legend]">
						<option <?php selected( $legend, 'right' ) ?> value="right"><?php _e( 'Right', 'dwgc' ); ?></option>
						<option <?php selected( $legend, 'top' ) ?> value="top"><?php _e( 'Above The Chart', 'dwgc' ); ?></option>
						<option <?php selected( $legend, 'bottom' ) ?> value="bottom"><?php _e( 'Below The Chart', 'dwgc' ); ?></option>
						<option <?php selected( $legend, 'in' ) ?> value="in"><?php _e( 'Inside the chart, by the top left corner', 'dwgc' ); ?></option>
						<option <?php selected( $legend, 'none' ) ?> value="none"><?php _e( 'No legend is displayed', 'dwgc' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>Show Tooltip</th>
				<td>
					<select name="dw_chart_options[tooltip_trigger]">
						<option <?php selected( $tooltip, 'yes' ) ?> value="yes"><?php _e( 'Yes', 'dwgc' ) ?></option>
						<option <?php selected( $tooltip, 'no' ) ?> value="no"><?php _e( 'No', 'dwgc' ) ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}
}