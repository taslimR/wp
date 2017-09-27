<?php

if ( !current_user_can(get_option( $this->shared->get('slug') . "_import_menu_capability")) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'dauc' ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php _e( 'UberChart - Import', 'dauc' ); ?></h2>

	<div id="daext-menu-wrapper">

		<?php

		//process the csv file upload
		if ( isset( $_FILES['file_to_upload'] ) and
		     isset( $_FILES['file_to_upload']['name'] ) and
		     preg_match('/^.+\.xml$/', $_FILES['file_to_upload']['name'], $matches) === 1 ) {

			$counter = 0;

			if ( file_exists( $_FILES['file_to_upload']['tmp_name'] ) ) {

				global $wpdb;

				//read xml file
				$xml = simplexml_load_file($_FILES['file_to_upload']['tmp_name']);

				$chart_a = $xml->chart;

				foreach($chart_a as $single_chart){

					//convert object to array
					$single_chart_a = get_object_vars($single_chart);

					//remove the id key
					unset($single_chart_a['id']);

					//save the dataset key for later use and remove the dataset key from the main array
					$dataset_a = get_object_vars($single_chart_a['dataset']);
					unset($single_chart_a['dataset']);

					$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
					$wpdb->insert(
						$table_name,
						$single_chart_a
					);
					$inserted_chart_id = $wpdb->insert_id;

					//add the datasets
					$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
					$data_a = $dataset_a['data'];

					if(is_array($data_a)){

						/*
						 * If this chart has multiple rows $data_a is an array filled with objects of type
						 * [SimpleXMLElement]. Each object is converted to an array and is then passed to the
						 * $wpdb->insert method and inserted in the database
						 */
						foreach($data_a as $single_data){

							$single_data_a = get_object_vars($single_data);

							//remove the id key
							unset($single_data_a['id']);

							//set the chart_id based on the id inserted during the creation of the chart
							$single_data_a['chart_id'] = $inserted_chart_id;

							$wpdb->insert(
								$table_name,
								$single_data_a
							);

						}

					}else{

						/*
						 * If this chart has a single row $data_a is an object of type [SimpleXMLElement] and is
						 * converted to an array and passed to the $wpdb->insert method and inserted in the database
						 */
						$single_data_a = get_object_vars($data_a);

						//remove the id key
						unset($single_data_a['id']);

						//set the chart_id based on the id inserted during the creation of the chart
						$single_data_a['chart_id'] = $inserted_chart_id;

						$wpdb->insert(
							$table_name,
							$single_data_a
						);

					}

					$counter++;

				}

				echo '<div class="updated settings-error notice is-dismissible below-h2"><p>' . $counter . ' ' . __( 'charts have been added.', 'dauc' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'dauc' ) . '</span></button></div>';

			}

		}

		?>


		<p><?php _e( 'Import the charts stored in your XML file by clicking the Upload file and import button.', 'dauc' ); ?></p>
		<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="">
			<p>
				<label for="upload"><?php _e( 'Choose a file from your computer:', 'dauc' ); ?></label>
				<input type="file" id="upload" name="file_to_upload">
			</p>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
			                         value="<?php _e( 'Upload file and import', 'dauc' ); ?>"></p>
		</form>

	</div>

</div>