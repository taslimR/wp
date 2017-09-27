<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class DW_Chart_Data_Meta_Box {
	public static function output() {
		$options = get_post_meta( get_the_ID(), 'dw_chart_options', true );
		$input_data = isset( $options['input_data'] ) ? $options['input_data'] : '';
		?>
		<table class="form-table">
			<tr>
				<th><?php _e( 'Data', 'dwgc' ) ?></th>
				<td>
					<textarea id="input_data" rows="10" name="dw_chart_options[input_data]" style="width: 100%;"><?php echo $input_data ?></textarea>
					<br>
					<span class="description"><a target="_blank" href="https://www.designwall.com/guide/dw-chart/chart-types/"><?php _e( 'View example data', 'dwgc' ) ?></a></span>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Upload your CSV file', 'dwgc' ) ?></th>
				<td>
					<input id="input_file" type="file">
					<br>
					<span class="description">
						<?php printf( __( '%sDownload sample file%s', 'dwgc' ), '<a id="dw_download_sample_file" href="#">', '</a>' ) ?>
					</span>
				</td>
			</tr>
		</table>
		<?php
	}
}