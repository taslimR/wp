<?php

if ( !current_user_can(get_option( $this->shared->get('slug') . "_export_menu_capability")) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'dauc' ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php _e( 'UberChart - Export', 'dauc' ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php _e( 'Click the Export button to generate an XML file that includes all your charts.', 'dauc' ); ?></p>

		<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->
		<form method="POST" action="admin.php?page=dauc-export">

			<div class="daext-widget-submit">
				<input name="dauc_export" class="button" type="submit" value="<?php _e( 'Export', 'dauc' ); ?>" <?php if ( $this->number_of_charts() == 0 ) { echo 'disabled="disabled"'; } ?>>
			</div>

		</form>

	</div>

</div>