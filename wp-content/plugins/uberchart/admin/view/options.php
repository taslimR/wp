<?php

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient capabilities to access this page.', 'dauc' ) );
}

?>

<div class="wrap">

	<h2><?php _e( 'UberChart - Options', 'dauc' ); ?></h2>

	<?php

	//settings errors
	if ( isset( $_GET['settings-updated'] ) and $_GET['settings-updated'] == 'true' ) {
		settings_errors();
	}

	?>

	<div id="daext-options-wrapper">

		<?php

		//get current tab value
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

		?>

		<div class="nav-tab-wrapper">
			<a href="?page=dauc-options&tab=general"
			   class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General', 'dauc' ); ?></a>
		</div>

		<form method='post' action='options.php'>

			<?php

			if ( $active_tab == 'general' ) {

				settings_fields( $this->shared->get( 'slug' ) . '_general_options' );
				do_settings_sections( $this->shared->get( 'slug' ) . '_general_options' );

			}

			?>

			<div class="daext-options-action">
				<input type="submit" name="submit" id="submit" class="button"
				       value="<?php _e( 'Save Changes', 'dauc' ); ?>">
			</div>

		</form>

	</div>

</div>