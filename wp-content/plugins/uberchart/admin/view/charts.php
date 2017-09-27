<?php

if ( !current_user_can(get_option( $this->shared->get('slug') . "_charts_menu_capability")) )  {
	wp_die( __( 'You do not have sufficient permissions to access this page.', 'dauc' ) );
}

//if the temporary charts are more than 100 clear the older (first inserted) temporary chart
$this->delete_older_temporary_chart();

//delete a chart
if ( isset( $_POST['delete_id'] ) ) {

	global $wpdb;
	$delete_id = intval( $_POST['delete_id'], 10 );

	//verify if this is a model
	$table_name     = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
	$safe_sql       = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE id = %d AND is_model = 1", $delete_id );
	$count = $wpdb->get_var( $safe_sql );

	if($count == 0){

		//delete this chart
		$table_name     = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
		$safe_sql       = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d ", $delete_id );
		$query_result_1 = $wpdb->query( $safe_sql );

		//delete all the data of this chart
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
		$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE chart_id = %d ", $delete_id );

		$query_result_2 = $wpdb->query( $safe_sql );

		if ( $query_result_1 !== false and $query_result_2 !== false ) {
			$process_data_message = '<div class="updated settings-error notice is-dismissible below-h2"><p>' . __('The chart has been successfully deleted.', 'dauc') . '</p></div>';
		}

	}

}

//clone the chart
if ( isset( $_POST['clone_id'] ) ) {

	global $wpdb;
	$clone_id = intval( $_POST['clone_id'], 10 );

	//clone the chart
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
	$wpdb->query( "CREATE TEMPORARY TABLE tmptable_1 SELECT * FROM $table_name WHERE id = $clone_id" );
	$wpdb->query( "UPDATE tmptable_1 SET id = NULL" );
	$wpdb->query( "INSERT INTO $table_name SELECT * FROM tmptable_1" );
	$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS tmptable_1" );
	$last_inserted_id = $wpdb->insert_id;

	//clone the data
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_data";
	$wpdb->query( "CREATE TEMPORARY TABLE tmptable_1 SELECT * FROM $table_name WHERE chart_id = $clone_id" );
	$wpdb->query( "UPDATE tmptable_1 SET chart_id = $last_inserted_id" );
	$wpdb->query( "UPDATE tmptable_1 SET id = NULL" );
	$wpdb->query( "INSERT INTO $table_name SELECT * FROM tmptable_1" );
	$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS tmptable_1" );

}

//get the chart data
$display_form = true;
if ( isset( $_GET['edit_id'] ) ) {
	$edit_id = intval( $_GET['edit_id'], 10 );
	global $wpdb;
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
	$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d ", $edit_id );
	$chart_obj  = $wpdb->get_row( $safe_sql );
	if($chart_obj === null){
		$display_form = false;
	}
}

?>

<!-- output -->

<div class="wrap">

    <?php if ($this->number_of_charts() > 0) : ?>

        <div id="daext-header-wrapper" class="daext-clearfix">

            <h2><?php _e( 'UberChart - Charts', 'dauc' ); ?></h2>

            <form action="admin.php" method="get">
                <input type="hidden" name="page" value="dauc-charts">
                <?php
                if (isset($_GET['s']) and strlen(trim($_GET['s'])) > 0) {
                    $search_string = $_GET['s'];
                } else {
                    $search_string = '';
                }
                ?>
                <input type="text" name="s" placeholder="<?php esc_attr_e('Search...', 'dauc'); ?>"
                       value="<?php echo esc_attr(stripslashes($search_string)); ?>" autocomplete="off" maxlength="255">
                <input type="submit" value="">
            </form>

        </div>

    <?php else: ?>

        <div id="daext-header-wrapper" class="daext-clearfix">

            <h2><?php _e( 'UberChart - Charts', 'dauc' ); ?></h2>

        </div>

    <?php endif; ?>

	<div id="daext-menu-wrapper">

		<?php

        //create the query part used to filter the results when a search is performed
        if (isset($_GET['s']) and strlen(trim($_GET['s'])) > 0) {
            $search_string = $_GET['s'];
            global $wpdb;
            $filter = $wpdb->prepare('AND (name LIKE %s OR description LIKE %s)', '%' . $search_string . '%', '%' . $search_string . '%');
        } else {
            $filter = '';
        }

		//retrieve the total number of charts
		global $wpdb;
		$table_name  = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE temporary = 0 $filter" );

		//Initialize the pagination class
		require_once( $this->shared->get( 'dir' ) . '/admin/inc/class-dauc-pagination.php' );
		$pag = new dauc_pagination();
		$pag->set_total_items( $total_items );//Set the total number of items
		$pag->set_record_per_page( 10 ); //Set records per page
		$pag->set_target_page( "admin.php?page=" . $this->shared->get( 'slug' ) . "-charts" );//Set target page
		$pag->set_current_page();//set the current page number from $_GET

		?>

		<!-- Query the database -->
		<?php
		$query_limit = $pag->query_limit();
		$results     = $wpdb->get_results( "SELECT * FROM $table_name WHERE temporary = 0 $filter ORDER BY id DESC $query_limit ", ARRAY_A ); ?>

		<?php if ( count( $results ) > 0 ) : ?>

			<div class="daext-items-container">

				<!-- list of charts -->
				<table class="daext-items">
					<thead>
					<tr>
						<th>
							<div><?php _e( 'Name', 'dauc' ); ?></div>
						</th>
						<th>
							<div><?php _e( 'Description', 'dauc' ); ?></div>
						</th>
						<th>
							<div><?php _e( 'Type', 'dauc' ); ?></div>
						</th>
						<th>
							<div><?php _e( 'Status', 'dauc' ); ?></div>
						</th>
						<th></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $results as $result ) : ?>
						<tr>
							<td><?php echo esc_attr( stripslashes( $result['name'] ) ); ?></td>
							<td><?php echo esc_attr( stripslashes( $result['description'] ) ); ?></td>
							<td><?php echo esc_attr( $this->chart_type_nice_name($result['type']) ); ?></td>
							<td>
								<?php echo $result['is_model'] ? __('Model', 'dauc') : __('Chart', 'dauc'); ?></php>
							</td>
							<td class="icons-container">
								<form method="POST"
								      action="admin.php?page=<?php echo $this->shared->get( 'slug' ); ?>-charts">
									<input type="hidden" name="clone_id" value="<?php echo $result['id']; ?>">
									<input class="menu-icon clone help-icon" type="submit" value="">
								</form>
								<a class="menu-icon edit"
								   href="admin.php?page=<?php echo $this->shared->get( 'slug' ); ?>-charts&edit_id=<?php echo $result['id']; ?>"></a>
								<?php if(!$result['is_model']) : ?>
									<form method="POST"
									      action="admin.php?page=<?php echo $this->shared->get( 'slug' ); ?>-charts">
										<input type="hidden" value="<?php echo $result['id']; ?>" name="delete_id">
										<input class="menu-icon delete" type="submit" value="">
									</form>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>

					</tbody>

				</table>

			</div>

			<!-- Display the pagination -->
			<?php if ( $pag->total_items > 0 ) : ?>
				<div class="daext-tablenav daext-clearfix">
					<div class="daext-tablenav-pages">
						<span
							class="daext-displaying-num"><?php echo $pag->total_items; ?>&nbsp<?php _e( 'items', 'dauc' ); ?></span>
						<?php $pag->show(); ?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php if( $display_form ) : ?>

			<div class="chart-container">

					<?php if ( isset( $_GET['edit_id'] ) ) : ?>

					<!-- Edit a Chart -->

					<div class="daext-form-container" form-disabled="<?php echo intval($chart_obj->is_model, 10); ?>">

						<h3 class="daext-form-title"><?php _e( 'Edit Chart', 'dauc' ); ?></h3>

						<table class="daext-form daext-form-chart">

							<input type="hidden" id="update-id" value="<?php echo $chart_obj->id; ?>" />

							<!-- Load Model -->
							<tr>
								<th scope="row"><label for="load-model"><?php _e( 'Load Model', 'dauc' ); ?></label></th>
								<td>
									<select id="load-model" <?php $this->disable_model_input($chart_obj->id); ?>>

										<option value="0"><?php _e('None', 'dauc'); ?></option>

										<?php

										global $wpdb;
										$table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
										$sql = "SELECT * FROM $table_name WHERE is_model = 1 ORDER BY name ASC";
										$model_a = $wpdb->get_results($sql, ARRAY_A);

										foreach ($model_a as $key => $model) {
											echo '<option value="' . $model['id'] . '">' . esc_attr(stripslashes($model['name'])) . '</option>';
										}

										?>

									</select>
									<div class="help-icon" title="<?php _e( 'Use this field to import the data of an existing model.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Name -->
							<tr>
								<th scope="row"><label for="name"><?php _e( 'Name', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $chart_obj->name ) ); ?>" type="text"
									       id="name" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The name of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Description -->
							<tr>
								<th scope="row"><label for="description"><?php _e( 'Description', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $chart_obj->description ) ); ?>"
									       type="text" id="description" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The description of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Type -->
							<tr>
								<th scope="row"><label for="type"><?php _e( 'Type', 'dauc' ); ?></label></th>
								<td>
									<select id="type" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="line" <?php selected( $chart_obj->type, 'line' ); ?>><?php _e( 'Line', 'dauc' ); ?></option>
										<option
											value="bar" <?php selected( $chart_obj->type, 'bar' ); ?>><?php _e( 'Bar', 'dauc' ); ?></option>
										<option
											value="horizontalBar" <?php selected( $chart_obj->type, 'horizontalBar' ); ?>><?php _e( 'Horizontal Bar', 'dauc' ); ?></option>
										<option
											value="radar" <?php selected( $chart_obj->type, 'radar' ); ?>><?php _e( 'Radar', 'dauc' ); ?></option>
										<option
											value="polarArea" <?php selected( $chart_obj->type, 'polarArea' ); ?>><?php _e( 'Polar Area', 'dauc' ); ?></option>
										<option
											value="pie" <?php selected( $chart_obj->type, 'pie' ); ?>><?php _e( 'Pie', 'dauc' ); ?></option>
										<option
											value="doughnut" <?php selected( $chart_obj->type, 'doughnut' ); ?>><?php _e( 'Doughnut', 'dauc' ); ?></option>
										<option
											value="bubble" <?php selected( $chart_obj->type, 'bubble' ); ?>><?php _e( 'Bubble', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines the type of the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Rows -->
							<tr>
								<th scope="row"><label for="rows"><?php _e( 'Rows', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->rows, 10 ); ?>" type="text" id="rows" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The rows of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Columns -->
							<tr>
								<th scope="row"><label for="columns"><?php _e( 'Columns', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->columns, 10 ); ?>" type="text" id="columns" maxlength="10" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The columns of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="data"><?php _e( 'Data', 'dauc' ); ?></label></th>
								<td id="dauc-table-td">
									<div id="dauc-table" data-disabled="<?php echo intval($chart_obj->is_model, 10); ?>"></div>
								</td>
							</tr>

							<!-- Chart Preview ************************************************************** -->

							<tr class="group-trigger" data-trigger-target="chart-preview" preview-disabled="<?php echo intval($chart_obj->is_model, 10); ?>">
								<th scope="row" class="group-title"><?php _e( 'Preview', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<tr class="chart-preview" preview-disabled="<?php echo intval($chart_obj->is_model, 10); ?>">
								<td colspan="2" id="chart-preview-iframe-container-td">
									<div id="chart-preview-iframe-container"></div>
									<div id="save-and-refresh" class="help-icon" title="<?php _e( 'Save your changes and refresh the preview.', 'dauc' ); ?>"></div>
									<div id="chart-preview-error"><?php _e('Preview not available', 'dauc'); ?></div>
								</td>
							</tr>

							<!-- Common ******************************************************** -->

							<tr class="group-trigger" data-trigger-target="common-chart-configuration">
								<th scope="row"
								    class="group-title"><?php _e( 'Common', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- canvas-transparent-background -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="canvas-transparent-background"><?php _e( 'Transparent', 'dauc' ); ?></label></th>
								<td>
									<select id="canvas-transparent-background" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->canvas_transparent_background, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->canvas_transparent_background, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines if the canvas should have a transparent background.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- canvas-backgroundColor -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="canvas-backgroundColor"><?php _e( 'Background Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $chart_obj->canvas_backgroundColor ) ); ?>" type="text" id="canvas-backgroundColor"
									       maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="canvas-backgroundColor-spectrum" class="spectrum-input" type="text">
									<div id="canvas-backgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The background color of the canvas. This value is applied only if the &quot;Transparent&quot; option is disabled.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- width -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="width"><?php _e( 'Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->width, 10 ); ?>" type="text" id="width" maxlength="6"
									       size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The width of the chart. This option will be used only if the chart is not responsive.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- height -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="height"><?php _e( 'Height', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->height, 10 ); ?>" type="text" id="height" maxlength="6"
									       size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The height of the chart. This option will be used only if the chart is not responsive.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- margin-top -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="margin-top"><?php _e( 'Margin Top', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->margin_top, 10 ); ?>" type="text" id="margin-top" maxlength="6"
									       size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The top margin of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- margin-bottom -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="margin-bottom"><?php _e( 'Margin Bottom', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->margin_bottom, 10 ); ?>" type="text" id="margin-bottom" maxlength="6"
									       size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The bottom margin of the chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- responsive -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="responsive"><?php _e( 'Responsive', 'dauc' ); ?></label></th>
								<td>
									<select id="responsive" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->responsive, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->responsive, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Resizes when the canvas container does.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- responsiveAnimationDuration -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="responsiveAnimationDuration"><?php _e( 'Responsive Animation Duration', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->responsiveAnimationDuration, 10 ); ?>"
									       type="text" id="responsiveAnimationDuration" maxlength="6" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Duration in milliseconds it takes to animate to new size after a resize event.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- maintainAspectRatio -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="maintainAspectRatio"><?php _e( 'Maintain Aspect Ratio', 'dauc' ); ?></label></th>
								<td>
									<select id="maintainAspectRatio" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->maintainAspectRatio, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->maintainAspectRatio, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Maintain the original canvas aspect ratio when resizing.', 'dauc' ); ?>'></div>
								</td>
							</tr>

                            <!-- fixed-height -->
                            <tr class="common-chart-configuration">
                                <th scope="row"><label for="fixed-height"><?php _e( 'Fixed Height', 'dauc' ); ?></label></th>
                                <td>
                                    <input value="<?php echo intval( $chart_obj->fixed_height, 10 ); ?>" type="text" id="fixed-height" maxlength="6"
                                           size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
                                    <div class="help-icon"
                                         title="<?php esc_attr_e( 'Enter the fixed height of the chart or 0 to not use a fixed height. This option will be considered only if the chart is responsive and the "Maintain Aspect Ratio" option is set to "No".', 'dauc' ); ?>"></div>
                                </td>
                            </tr>

							<!-- Is Model -->
							<tr class="common-chart-configuration">
								<th scope="row"><label for="is-model"><?php _e( 'Status', 'dauc' ); ?></label></th>
								<td>
									<select id="is-model">
										<option
											value="0" <?php selected( $chart_obj->is_model, 0 ); ?>><?php _e( 'Chart', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->is_model, 1 ); ?>><?php _e( 'Model', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines if this chart can be used as a model.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Title *************************************************************** -->

							<tr class="group-trigger" data-trigger-target="title-configuration">
								<th scope="row" class="group-title"><?php _e( 'Title', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- title-display -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="title-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->title_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->title_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon" title='<?php _e( 'Display the title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- title-position -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
								<td>
									<select id="title-position" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="top" <?php selected( $chart_obj->title_position, 'top' ); ?>><?php _e( 'Top', 'dauc' ); ?></option>
										<option
											value="bottom" <?php selected( $chart_obj->title_position, 'bottom' ); ?>><?php _e( 'Bottom', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Position of the title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- title-fullWidth -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-fullWidth"><?php _e( 'Full Width', 'dauc' ); ?></label></th>
								<td>
									<select id="title-fullWidth" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->title_fullWidth, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->title_fullWidth, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines if the title should take the full width of the canvas.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- title-padding -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->title_padding, 10 ); ?>" type="text"
									       id="title-padding" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Number of pixels to add above and below the title text.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- title-fontSize -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->title_fontSize, 10 ); ?>" type="text"
									       id="title-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font size of the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- title-fontColor -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-configuration"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->title_fontColor ) ); ?>"
										type="text" id="title-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="title-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="title-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The font color of the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- title-fontFamily -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $chart_obj->title_fontFamily ) ); ?>"
									       type="text" id="title-fontFamily" maxlength="200" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- title-fontStyle -->
							<tr class="title-configuration">
								<th scope="row"><label for="title-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="title-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="normal" <?php selected( $chart_obj->title_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option
											value="bold" <?php selected( $chart_obj->title_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option
											value="italic" <?php selected( $chart_obj->title_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option
											value="oblique" <?php selected( $chart_obj->title_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font styling of the title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Legend ************************************************************** -->

							<tr class="group-trigger" data-trigger-target="legend-configuration">
								<th scope="row" class="group-title"><?php _e( 'Legend', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- legend-display -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="legend-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->legend_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->legend_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon" title='<?php _e( 'This option determines if the legend should be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- legend-position -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
								<td>
									<select id="legend-position" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="top" <?php selected( $chart_obj->legend_position, 'top' ); ?>><?php _e( 'Top', 'dauc' ); ?></option>
										<option value="right" <?php selected( $chart_obj->legend_position, 'right' ); ?>><?php _e( 'Right', 'dauc' ); ?></option>
										<option value="bottom" <?php selected( $chart_obj->legend_position, 'bottom' ); ?>><?php _e( 'Bottom', 'dauc' ); ?></option>
										<option value="left" <?php selected( $chart_obj->legend_position, 'left' ); ?>><?php _e( 'Left', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'The position of the legend.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- legend-fullWidth -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-fullWidth"><?php _e( 'Full Width', 'dauc' ); ?></label></th>
								<td>
									<select id="legend-fullWidth" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->legend_fullWidth, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->legend_fullWidth, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines if the legend should take the full width of the canvas.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- legend-labels-padding -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-padding"><?php _e( 'Padding', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->legend_labels_padding, 10 ); ?>"
									       type="text" id="legend-labels-padding" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The padding between rows of colored boxes.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- legend-labels-boxWidth -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-boxWidth"><?php _e( 'Box Width', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->legend_labels_boxWidth ) ); ?>"
										type="text" id="legend-labels-boxWidth" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The width of the colored box.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- legend-toggle-dataset -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-toggle-dataset"><?php _e( 'Toggle Dataset', 'dauc' ); ?></label></th>
								<td>
									<select id="legend-toggle-dataset" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->legend_toggle_dataset, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->legend_toggle_dataset, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option activates the ability to enable or disable a dataset by clicking on its legend colored box.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- legend-labels-fontSize -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->legend_labels_fontSize, 10 ); ?>"
									       type="text" id="legend-labels-fontSize" maxlength="6" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font size of the legend label.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- legend-labels-fontColor -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->legend_labels_fontColor ) ); ?>"
										type="text" id="legend-labels-fontColor" maxlength="22" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="legend-labels-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="legend-labels-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The font color of the legend label.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- legend-labels-fontFamily -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->legend_labels_fontFamily ) ); ?>"
										type="text" id="legend-labels-fontFamily" maxlength="200" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the legend label.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- legend-labels-fontStyle -->
							<tr class="legend-configuration">
								<th scope="row"><label for="legend-labels-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="legend-labels-fontStyle"<?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="normal" <?php selected( $chart_obj->legend_labels_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option
											value="bold" <?php selected( $chart_obj->legend_labels_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option
											value="italic" <?php selected( $chart_obj->legend_labels_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option
											value="oblique" <?php selected( $chart_obj->legend_labels_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'The font style of the legend label.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Tooltip ******************************************************** -->

							<tr class="group-trigger" data-trigger-target="tooltip-configuration">
								<th scope="row" class="group-title"><?php _e( 'Tooltip', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- tooltips-enabled -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-enabled"><?php _e( 'Enabled', 'dauc' ); ?></label></th>
								<td>
									<select id="tooltips-enabled" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="0" <?php selected( $chart_obj->tooltips_enabled, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option
											value="1" <?php selected( $chart_obj->tooltips_enabled, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon" title='<?php _e( 'This option determines if the tooltips should be enabled.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- tooltips-mode -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltip-configuration"><?php _e( 'Mode', 'dauc' ); ?></label></th>
								<td>
									<select id="tooltips-mode" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="single" <?php selected( $chart_obj->tooltips_mode, 'single' ); ?>><?php _e( 'Single', 'dauc' ); ?></option>
										<option
											value="label" <?php selected( $chart_obj->tooltips_mode, 'label' ); ?>><?php _e( 'Label', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'This option determines which elements should appear in the tooltip. Single highlights the closest element and Label highlights elements in all datasets at the same X value.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- tooltips-backgroundColor -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-backgroundColor"><?php _e( 'Background Color', 'dauc' ); ?></label>
								</th>
								<td>
									<input
									       value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_backgroundColor ) ); ?>"
									       type="text" id="tooltips-backgroundColor" maxlength="22" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="tooltips-backgroundColor-spectrum" class="spectrum-input" type="text">
									<div id="tooltips-backgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The background color of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-multiKeyBackground -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tolltips-multiKeyBackground"><?php _e( 'Multi Key Background', 'dauc' ); ?></label></th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_multiKeyBackground ) ); ?>"
										type="text" id="tooltips-multiKeyBackground" maxlength="22" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="tooltips-multiKeyBackground-spectrum" class="spectrum-input" type="text">
									<div id="tooltips-multiKeyBackground-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'Color to draw behind the colored boxes when multiple items are in the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-titleMarginBottom -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltip-configuration"><?php _e( 'Title Margin Bottom', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_titleMarginBottom, 10 ); ?>"
									       type="text" id="tooltips-titleMarginBottom" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Margin to add on the bottom of the tooltip title section.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-footerMarginTop -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-footerMarginTop"><?php _e( 'Footer Margin Top', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_footerMarginTop, 10 ); ?>"
									       type="text" id="tooltips-footerMarginTop" maxlength="6" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Margin to add before drawing the tooltip footer.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-xPadding -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-xPadding"><?php _e( 'X Padding', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_xPadding, 10 ); ?>" type="text"
									       id="tooltips-xPadding" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Padding to add on the left and right side of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-yPadding -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-yPadding"><?php _e( 'Y Padding', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_yPadding, 10 ); ?>" type="text"
									       id="tooltips-yPadding" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Padding to add on the top and bottom side of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-caretSize -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tolltips-caretSize"><?php _e( 'Caret Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_caretSize, 10 ); ?>" type="text"
									       id="tooltips-caretSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The size of the tooltip arrow.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-cornerRadius -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-cornerRadius"><?php _e( 'Corner Radius', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_cornerRadius, 10 ); ?>"
									       type="text" id="tooltips-cornerRadius" maxlength="6" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The radius of the tooltip corner curves.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- hover-animationDuration -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="hover-animationDuration"><?php _e( 'Animation Duration', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->hover_animationDuration, 10 ); ?>"
									       type="text" id="hover-animationDuration" maxlength="6" size="30"
									       <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'Duration in milliseconds it takes to animate hover style changes.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-beforeTitle -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-beforeTitle"><?php _e( 'Before Title', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_beforeTitle)); ?>" type="text"
									       id="tooltips-beforeTitle" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed before the title of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-afterTitle -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-afterTitle"><?php _e( 'After Title', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_afterTitle)); ?>" type="text"
									       id="tooltips-afterTitle" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed after the title of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-beforeBody -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-beforeBody"><?php _e( 'Before Body', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_beforeBody)); ?>" type="text"
									       id="tooltips-beforeBody" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed before the body of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-afterBody -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-afterBody"><?php _e( 'After Body', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_afterBody)); ?>" type="text"
									       id="tooltips-afterBody" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed after the body of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-beforeLabel -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-beforeLabel"><?php _e( 'Before Label', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_beforeLabel)); ?>" type="text"
									       id="tooltips-beforeLabel" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed before the label of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-afterLabel -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-afterLabel"><?php _e( 'After Label', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_afterLabel)); ?>" type="text"
									       id="tooltips-afterLabel" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed after the label of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-beforeFooter -->
							<tr class="tooltip-configuration">
								<th scope="row"><Footer for="tooltips-beforeFooter"><?php _e( 'Before Footer', 'dauc' ); ?></Footer></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_beforeFooter)); ?>" type="text"
									       id="tooltips-beforeFooter" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed before the footer of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-footer -->
							<tr class="tooltip-configuration">
								<th scope="row"><Footer for="tooltips-footer"><?php _e( 'Footer', 'dauc' ); ?></Footer></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_footer)); ?>" type="text"
									       id="tooltips-footer" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed in the footer of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-afterFooter -->
							<tr class="tooltip-configuration">
								<th scope="row"><Footer for="tooltips-afterFooter"><?php _e( 'After Footer', 'dauc' ); ?></Footer></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_afterFooter)); ?>" type="text"
									       id="tooltips-afterFooter" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text displayed after the footer of the tooltip.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-titleFontSize -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-titleFontSize"><?php _e( 'Title Font Size', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_titleFontSize, 10 ); ?>"
									       type="text" id="tooltips-titleFontSize" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font size of the tooltip title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-titleFontColor -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-titleFontColor"><?php _e( 'Title Font Color', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_titleFontColor ) ); ?>"
										type="text" id="tooltips-titleFontColor" maxlength="22" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="tooltips-titleFontColor-spectrum" class="spectrum-input" type="text">
									<div id="tooltips-titleFontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The font color of the tooltip title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-titleFontFamily -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-titleFontFamily"><?php _e( 'Title Font Family', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_titleFontFamily ) ); ?>"
										type="text" id="tooltips-titleFontFamily" maxlength="200" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the tooltip title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-titleFontStyle -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-titleFontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="tooltips-titleFontStyle"<?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="normal" <?php selected( $chart_obj->tooltips_titleFontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option
											value="bold" <?php selected( $chart_obj->tooltips_titleFontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option
											value="italic" <?php selected( $chart_obj->tooltips_titleFontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option
											value="oblique" <?php selected( $chart_obj->tooltips_titleFontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon" title='<?php _e( 'The font style of the tooltip title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- tooltips-bodyFontSize -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-bodyFontSize"><?php _e( 'Body Font Size', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_bodyFontSize, 10 ); ?>"
									       type="text" id="tooltips-bodyFontSize" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the tooltip body.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-bodyFontColor -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-bodyFontColor"><?php _e( 'Body Font Color', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_bodyFontColor ) ); ?>"
										type="text" id="tooltips-bodyFontColor" maxlength="22" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="tooltips-bodyFontColor-spectrum" class="spectrum-input" type="text">
									<div id="tooltips-bodyFontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The font color of the tooltip body.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-bodyFontFamily -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-bodyFontFamily"><?php _e( 'Body Font Family', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->tooltips_bodyFontFamily)); ?>"
									       type="text" id="tooltips-bodyFontFamily" maxlength="200" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the tooltip body.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-bodyFontStyle -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-bodyFontStyle"><?php _e( 'Body Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="tooltips-bodyFontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="normal" <?php selected( $chart_obj->tooltips_bodyFontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option
											value="bold" <?php selected( $chart_obj->tooltips_bodyFontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option
											value="italic" <?php selected( $chart_obj->tooltips_bodyFontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option
											value="oblique" <?php selected( $chart_obj->tooltips_bodyFontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon" title='<?php _e( 'The font style of the tooltip body.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- tooltips-footerFontSize -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-footerFontSize"><?php _e( 'Footer Font Size', 'dauc' ); ?></label>
								</th>
								<td>
									<input value="<?php echo intval( $chart_obj->tooltips_footerFontSize, 10 ); ?>"
									       type="text" id="tooltips-footerFontSize" maxlength="6" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font size of the tooltip footer.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-footerFontColor -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-footerFontColor"><?php _e( 'Footer Font Color', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_footerFontColor ) ); ?>"
										type="text" id="tooltips-footerFontColor" maxlength="22" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="tooltips-footerFontColor-spectrum" class="spectrum-input" type="text">
									<div id="tooltips-footerFontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon"
									     title="<?php _e( 'The font color of the tooltip footer.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-footerFontFamily -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-footerFontFamily"><?php _e( 'Footer Font Family', 'dauc' ); ?></label>
								</th>
								<td>
									<input
										value="<?php echo esc_attr( stripslashes( $chart_obj->tooltips_footerFontFamily ) ); ?>"
										type="text" id="tooltips-footerFontFamily" maxlength="200" size="30"
										<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The font family of the tooltip footer.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- tooltips-footerFontStyle -->
							<tr class="tooltip-configuration">
								<th scope="row"><label for="tooltips-footerFontStyle"><?php _e( 'Footer Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="tooltips-footerFontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option
											value="normal" <?php selected( $chart_obj->tooltips_footerFontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option
											value="bold" <?php selected( $chart_obj->tooltips_footerFontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option
											value="italic" <?php selected( $chart_obj->tooltips_footerFontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option
											value="oblique" <?php selected( $chart_obj->tooltips_footerFontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'The font style of the tooltip footer.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Animation *********************************************************** -->

							<tr class="group-trigger" data-trigger-target="animation-configuration">
								<th scope="row" class="group-title"><?php _e( 'Animation', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- animation-duration -->
							<tr class="animation-configuration">
								<th scope="row"><label for="animation-duration"><?php _e( 'Duration', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo intval( $chart_obj->animation_duration, 10 ); ?>" type="text"
									       id="animation-duration" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon"
									     title="<?php _e( 'The number of milliseconds an animation takes.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- animation-easing -->
							<tr class="animation-configuration">
								<th scope="row"><label for="animation-easing"><?php _e( 'Easing', 'dauc' ); ?></label></th>
								<td>
									<select id="animation-easing" <?php $this->disable_model_input($chart_obj->id); ?>>
										<?php
										$easing_list = 'linear,easeInQuad,easeOutQuad,easeInOutQuad,easeInCubic,easeOutCubic,easeInOutCubic,easeInQuart,easeOutQuart,easeInOutQuart,easeInQuint,easeOutQuint,easeInOutQuint,easeInSine,easeOutSine,easeInOutSine,easeInExpo,easeOutExpo,easeInOutExpo,easeInCirc,easeOutCirc,easeInOutCirc,easeInElastic,easeOutElastic,easeInOutElastic,easeInBack,easeOutBack,easeInOutBack,easeInBounce,easeOutBounce,easeInOutBounce';
										$easing_a    = explode( ',', $easing_list );
										foreach ( $easing_a as $key => $single_easing ) {
											echo '<option ' . selected( $chart_obj->animation_easing, $single_easing ) . ' value="' . $single_easing . '">' . $single_easing . '</option>';
										}
										?>
									</select>
									<div class="help-icon" title='<?php _e( 'Easing function to use.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- animation-animateRotate -->
							<tr class="animation-configuration">
								<th scope="row"><label for="animation-animateRotate"><?php _e( 'Animate Rotate', 'dauc' ); ?></label></th>
								<td>
									<select id="animation-animateRotate" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->animation_animateRotate, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->animation_animateRotate, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, will animate the rotation of the chart. This option is applied only with Polar Area, Pie and Doughnut Chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- animation-animateScale -->
							<tr class="animation-configuration">
								<th scope="row"><label for="animation-animateScale"><?php _e( 'Animate Scale', 'dauc' ); ?></label></th>
								<td>
									<select id="animation-animateScale" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->animation_animateScale, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->animation_animateScale, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, will animate the scaling of the chart from the center. This option is applied only with Polar Area, Pie and Doughnut Chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Misc *********************************************************** -->

							<tr class="group-trigger" data-trigger-target="misc-configuration">
								<th scope="row"
								    class="group-title"><?php _e( 'Misc', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- elements-rectangle-borderSkipped -->
							<tr class="misc-configuration">
								<th scope="row"><label for="elements-rectangle-borderSkipped"><?php _e( 'Border Skipped', 'dauc' ); ?></label></th>
								<td>
									<select id="elements-rectangle-borderSkipped" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="top" <?php selected( $chart_obj->elements_rectangle_borderSkipped, 'top' ); ?>><?php _e('Top', 'dauc'); ?></option>
										<option value="right" <?php selected( $chart_obj->elements_rectangle_borderSkipped, 'right' ); ?>><?php _e('Right', 'dauc'); ?></option>
										<option value="bottom" <?php selected( $chart_obj->elements_rectangle_borderSkipped, 'bottom' ); ?>><?php _e('Bottom', 'dauc'); ?></option>
										<option value="left" <?php selected( $chart_obj->elements_rectangle_borderSkipped, 'left' ); ?>><?php _e('Left', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Skipped (excluded) border for the rectangle. This option is applied only to the Bar and Horizontal Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- X Scale Common ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-common-configuration-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Common', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-display -->
							<tr class="scales-common-configuration-x">
								<th scope="row"><label for="scales-xAxes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-position -->
							<tr class="scales-common-configuration-x">
								<th scope="row"><label for="scales-xAxes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-position"
									        <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="top" <?php selected( $chart_obj->scales_xAxes_position, 'top' ); ?>><?php _e('Top', 'dauc'); ?></option>
										<option value="bottom" <?php selected( $chart_obj->scales_xAxes_position, 'bottom' ); ?>><?php _e('Bottom', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-type -->
							<tr class="scales-common-configuration-x">
								<th scope="row"><label for="scales-xAxes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-type"
										<?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="category" <?php selected( $chart_obj->scales_xAxes_type, 'category' ); ?>><?php _e('Category', 'dauc'); ?></option>
										<option value="linear" <?php selected( $chart_obj->scales_xAxes_type, 'linear' ); ?>><?php _e('Linear', 'dauc'); ?></option>
										<option value="logarithmic" <?php selected( $chart_obj->scales_xAxes_type, 'logarithmic' ); ?>><?php _e('Logarithmic', 'dauc'); ?></option>
										<option value="time" <?php selected( $chart_obj->scales_xAxes_type, 'time' ); ?>><?php _e('Time', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-stacked -->
							<tr class="scales-common-configuration-x">
								<th scope="row"><label for="scales-xAxes-stacked"><?php _e( 'Stacked', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-stacked" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_stacked, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_stacked, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, lines or bars are stacked. This option is applied only with the Line and Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- X Scale Grid Line ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Grid Line', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-display -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-gridLines-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_gridLines_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_gridLines_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-color -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_gridLines_color)); ?>" type="text"
									       id="scales-xAxes-gridLines-color" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-xAxes-gridLines-color-spectrum" class="spectrum-input" type="text">
									<div id="scales-xAxes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-lineWidth -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_gridLines_lineWidth)); ?>" type="text"
									       id="scales-xAxes-gridLines-lineWidth" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-drawBorder -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-gridLines-drawBorder" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_gridLines_drawBorder, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_gridLines_drawBorder, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw the border on the edge of the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-drawOnChartArea -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-gridLines-drawOnChartArea" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_gridLines_drawOnChartArea, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_gridLines_drawOnChartArea, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-drawTicks -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-gridLines-drawTicks" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_gridLines_drawTicks, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_gridLines_drawTicks, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-tickMarkLength -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_gridLines_tickMarkLength)); ?>" type="text"
									       id="scales-xAxes-gridLines-tickMarkLength" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-zeroLineColor -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_gridLines_zeroLineColor)); ?>" type="text"
									       id="scales-xAxes-gridLines-zeroLineColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-xAxes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-xAxes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-zeroLineWidth -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_gridLines_zeroLineWidth)); ?>" type="text"
									       id="scales-xAxes-gridLines-zeroLineWidth" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-gridLines-offsetGridLines -->
							<tr class="scales-grid-line-configuration-x">
								<th scope="row"><label for="scales-xAxes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-gridLines-offsetGridLines" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_gridLines_offsetGridLines, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_gridLines_offsetGridLines, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- X Scale Title Configuration ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-title-configuration-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Title', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-display -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-scaleLabel-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_scaleLabel_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_scaleLabel_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-labelString -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-labelString"><?php _e( 'Label', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_scaleLabel_labelString)); ?>" type="text"
									       id="scales-xAxes-scaleLabel-labelString" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-fontSize -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_scaleLabel_fontSize)); ?>" type="text"
									       id="scales-xAxes-scaleLabel-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-fontColor -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_scaleLabel_fontColor)); ?>" type="text"
									       id="scales-xAxes-scaleLabel-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-xAxes-scaleLabel-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-xAxes-scaleLabel-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-fontFamily -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_scaleLabel_fontFamily)); ?>" type="text"
									       id="scales-xAxes-scaleLabel-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-scaleLabel-fontStyle -->
							<tr class="scales-title-configuration-x">
								<th scope="row"><label for="scales-xAxes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-scaleLabel-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_xAxes_scaleLabel_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_xAxes_scaleLabel_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_xAxes_scaleLabel_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_xAxes_scaleLabel_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- X Scale Tick ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-tick-configuration-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Tick', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-display -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-ticks-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_ticks_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_ticks_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-autoskip -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-ticks-autoskip" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_ticks_autoskip, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_ticks_autoskip, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-reverse -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-ticks-reverse" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_ticks_reverse, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_ticks_reverse, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-labelOffset -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-labelOffset"><?php _e( 'Label Offset', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_labelOffset)); ?>" type="text"
									       id="scales-xAxes-ticks-labelOffset" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Distance in pixels to offset the label from the centre point of the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-minRotation -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_minRotation)); ?>" type="text"
									       id="scales-xAxes-ticks-minRotation" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-maxRotation -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_maxRotation)); ?>" type="text"
									       id="scales-xAxes-ticks-maxRotation" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-prefix -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_prefix)); ?>" type="text"
									       id="scales-xAxes-ticks-prefix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-suffix -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_suffix)); ?>" type="text"
									       id="scales-xAxes-ticks-suffix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-round -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_round)); ?>" type="text"
									       id="scales-xAxes-ticks-round" maxlength="2" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-fontSize -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_fontSize)); ?>" type="text"
									       id="scales-xAxes-ticks-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-fontColor -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_fontColor)); ?>" type="text"
									       id="scales-xAxes-ticks-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-xAxes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-xAxes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-fontFamily -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_fontFamily)); ?>" type="text"
									       id="scales-xAxes-ticks-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-fontStyle -->
							<tr class="scales-tick-configuration-x">
								<th scope="row"><label for="scales-xAxes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-ticks-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_xAxes_ticks_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_xAxes_ticks_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_xAxes_ticks_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_xAxes_ticks_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- X Scale Options ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-configuration-options-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Options', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-min -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_min)); ?>" type="text"
									       id="scales-xAxes-ticks-min" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-max -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_max)); ?>" type="text"
									       id="scales-xAxes-ticks-max" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-suggestedMin -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_suggestedMin)); ?>" type="text"
									       id="scales-xAxes-ticks-suggestedMin" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-suggestedMax -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_suggestedMax)); ?>" type="text"
									       id="scales-xAxes-ticks-suggestedMax" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-stepSize -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_stepSize)); ?>" type="text"
									       id="scales-xAxes-ticks-stepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-fixedStepSize -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_fixedStepSize)); ?>" type="text"
									       id="scales-xAxes-ticks-fixedStepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If set, the scale ticks will be enumerated by multiple of this value, having one tick per increment. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-maxTicksLimit -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_ticks_maxTicksLimit)); ?>" type="text"
									       id="scales-xAxes-ticks-maxTicksLimit" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-ticks-beginAtZero -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-ticks-beginAtZero" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_xAxes_ticks_beginAtZero, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_xAxes_ticks_beginAtZero, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-categoryPercentage -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-categoryPercentage"><?php _e( 'Category Percentage', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_categoryPercentage)); ?>" type="text"
									       id="scales-xAxes-categoryPercentage" maxlength="3" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width (the space between the grid lines for small datasets) for each data-point to use for the bars. This option is applied only with the Bar chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-barPercentage -->
							<tr class="scales-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-barPercentage"><?php _e( 'Bar Percentage', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_barPercentage)); ?>" type="text"
									       id="scales-xAxes-barPercentage" maxlength="3" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width each bar should be within the category percentage. 1.0 will take the whole category width and put the bars right next to each other. This option is applied only with the Bar chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- X Scale Time ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-time-configuration-options-x">
								<th scope="row" class="group-title"><?php _e( 'X Scale Time', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-format -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-format"><?php _e( 'Time Format', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_format)); ?>" type="text"
									       id="scales-xAxes-time-format" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the scale. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-tooltipFormat -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-tooltipFormat"><?php _e( 'Tooltip Format', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_tooltipFormat)); ?>" type="text"
									       id="scales-xAxes-time-tooltipFormat" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the tooltip. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-unit-format -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-unit-format"><?php _e( 'Unit Format', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_unit_format)); ?>" type="text"
									       id="scales-xAxes-time-unit-format" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the time unit. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-min -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_min)); ?>" type="text"
									       id="scales-xAxes-time-min" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, this will override the data minimum. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-max -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_max)); ?>" type="text"
									       id="scales-xAxes-time-max" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, this will override the data maximum. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-unit -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-unit"><?php _e( 'Unit', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-xAxes-time-unit" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="millisecond" <?php selected( $chart_obj->scales_xAxes_time_unit, 'millisecond' ); ?>><?php _e( 'Millisecond', 'dauc' ); ?></option>
										<option value="second" <?php selected( $chart_obj->scales_xAxes_time_unit, 'second' ); ?>><?php _e( 'Second', 'dauc' ); ?></option>
										<option value="minute" <?php selected( $chart_obj->scales_xAxes_time_unit, 'minute' ); ?>><?php _e( 'Minute', 'dauc' ); ?></option>
										<option value="hour" <?php selected( $chart_obj->scales_xAxes_time_unit, 'hour' ); ?>><?php _e( 'Hour', 'dauc' ); ?></option>
										<option value="day" <?php selected( $chart_obj->scales_xAxes_time_unit, 'day' ); ?>><?php _e( 'Day', 'dauc' ); ?></option>
										<option value="week" <?php selected( $chart_obj->scales_xAxes_time_unit, 'week' ); ?>><?php _e( 'Week', 'dauc' ); ?></option>
										<option value="month" <?php selected( $chart_obj->scales_xAxes_time_unit, 'month' ); ?>><?php _e( 'Month', 'dauc' ); ?></option>
										<option value="quarter" <?php selected( $chart_obj->scales_xAxes_time_unit, 'quarter' ); ?>><?php _e( 'Quarter', 'dauc' ); ?></option>
										<option value="year" <?php selected( $chart_obj->scales_xAxes_time_unit, 'year' ); ?>><?php _e( 'Year', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'The time unit. This option is applied only to the Time scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-xAxes-time-unitStepSize -->
							<tr class="scales-time-configuration-options-x">
								<th scope="row"><label for="scales-xAxes-time-unitStepSize"><?php _e( 'Unit Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_xAxes_time_unitStepSize)); ?>" type="text"
									       id="scales-xAxes-time-unitStepSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The number of units between grid lines. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Y Scale Common ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-common-configuration-y">
								<th scope="row" class="group-title"><?php _e( 'Y Scale Common', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-yAxes-display -->
							<tr class="scales-common-configuration-y">
								<th scope="row"><label for="scales-yAxes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-position -->
							<tr class="scales-common-configuration-y">
								<th scope="row"><label for="scales-yAxes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-position"
									        <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="right" <?php selected( $chart_obj->scales_yAxes_position, 'right' ); ?>><?php _e('Right', 'dauc'); ?></option>
										<option value="left" <?php selected( $chart_obj->scales_yAxes_position, 'left' ); ?>><?php _e('Left', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-type -->
							<tr class="scales-common-configuration-y">
								<th scope="row"><label for="scales-yAxes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-type"
										<?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="category" <?php selected( $chart_obj->scales_yAxes_type, 'category' ); ?>><?php _e('Category', 'dauc'); ?></option>
										<option value="linear" <?php selected( $chart_obj->scales_yAxes_type, 'linear' ); ?>><?php _e('Linear', 'dauc'); ?></option>
										<option value="logarithmic" <?php selected( $chart_obj->scales_yAxes_type, 'logarithmic' ); ?>><?php _e('Logarithmic', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-stacked -->
							<tr class="scales-common-configuration-y">
								<th scope="row"><label for="scales-yAxes-stacked"><?php _e( 'Stacked', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-stacked" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_stacked, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_stacked, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, lines or bars are stacked. This option is applied only with the Line and Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y Scale Grid Line ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-y">
								<th scope="row" class="group-title"><?php _e( 'Y Scale Grid Line', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-display -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-gridLines-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_gridLines_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_gridLines_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-color -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_gridLines_color)); ?>" type="text"
									       id="scales-yAxes-gridLines-color" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-yAxes-gridLines-color-spectrum" class="spectrum-input" type="text">
									<div id="scales-yAxes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-lineWidth -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_gridLines_lineWidth)); ?>" type="text"
									       id="scales-yAxes-gridLines-lineWidth" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-drawBorder -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-gridLines-drawBorder" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_gridLines_drawBorder, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_gridLines_drawBorder, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw border on the edge of the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-drawOnChartArea -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-gridLines-drawOnChartArea" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_gridLines_drawOnChartArea, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_gridLines_drawOnChartArea, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-drawTicks -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-gridLines-drawTicks" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_gridLines_drawTicks, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_gridLines_drawTicks, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-tickMarkLength -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_gridLines_tickMarkLength)); ?>" type="text"
									       id="scales-yAxes-gridLines-tickMarkLength" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-zeroLineColor -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_gridLines_zeroLineColor)); ?>" type="text"
									       id="scales-yAxes-gridLines-zeroLineColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-yAxes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-yAxes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-zeroLineWidth -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_gridLines_zeroLineWidth)); ?>" type="text"
									       id="scales-yAxes-gridLines-zeroLineWidth" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-gridLines-offsetGridLines -->
							<tr class="scales-grid-line-configuration-y">
								<th scope="row"><label for="scales-yAxes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-gridLines-offsetGridLines" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_gridLines_offsetGridLines, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_gridLines_offsetGridLines, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Horizontal Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y Scale Title ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-title-configuration-y">
								<th scope="row" class="group-title"><?php _e( 'Y Scale Title', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-display -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-scaleLabel-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_scaleLabel_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_scaleLabel_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-labelString -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-labelString"><?php _e( 'Label String', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_scaleLabel_labelString)); ?>" type="text"
									       id="scales-yAxes-scaleLabel-labelString" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-fontSize -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_scaleLabel_fontSize)); ?>" type="text"
									       id="scales-yAxes-scaleLabel-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-fontColor -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_scaleLabel_fontColor)); ?>" type="text"
									       id="scales-yAxes-scaleLabel-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-yAxes-scaleLabel-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-yAxes-scaleLabel-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-fontFamily -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_scaleLabel_fontFamily)); ?>" type="text"
									       id="scales-yAxes-scaleLabel-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-scaleLabel-fontStyle -->
							<tr class="scales-title-configuration-y">
								<th scope="row"><label for="scales-yAxes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-scaleLabel-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_yAxes_scaleLabel_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_yAxes_scaleLabel_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_yAxes_scaleLabel_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_yAxes_scaleLabel_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y Scale Tick ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-tick-configuration-y">
								<th scope="row" class="group-title"><?php _e( 'Y Scale Tick', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-display -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_ticks_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_ticks_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-autoskip -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-autoskip" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_ticks_autoskip, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_ticks_autoskip, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-reverse -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-reverse" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_ticks_reverse, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_ticks_reverse, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-mirror -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-mirror"><?php _e( 'Mirror', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-mirror" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_ticks_mirror, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_ticks_mirror, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Flips tick labels around axis, displaying the labels inside the chart instead of outside.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-minRotation -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_minRotation)); ?>" type="text"
									       id="scales-yAxes-ticks-minRotation" maxlength="6" size="30"/>
									<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-maxRotation -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_maxRotation)); ?>" type="text"
									       id="scales-yAxes-ticks-maxRotation" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-padding -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_padding)); ?>" type="text"
									       id="scales-yAxes-ticks-padding" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Padding between the tick label and the axis.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-prefix -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_prefix)); ?>" type="text"
									       id="scales-yAxes-ticks-prefix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-suffix -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_suffix)); ?>" type="text"
									       id="scales-yAxes-ticks-suffix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-round -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_round)); ?>" type="text"
									       id="scales-yAxes-ticks-round" maxlength="2" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-fontSize -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_fontSize)); ?>" type="text"
									       id="scales-yAxes-ticks-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-fontColor -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_fontColor)); ?>" type="text"
									       id="scales-yAxes-ticks-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-yAxes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-yAxes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-fontFamily -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_fontFamily)); ?>" type="text"
									       id="scales-yAxes-ticks-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-fontStyle -->
							<tr class="scales-tick-configuration-y">
								<th scope="row"><label for="scales-yAxes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_yAxes_ticks_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_yAxes_ticks_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_yAxes_ticks_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_yAxes_ticks_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y Scale Options ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-configuration-options-y">
								<th scope="row" class="group-title"><?php _e( 'Y Scale Options', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-min -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_min)); ?>" type="text"
									       id="scales-yAxes-ticks-min" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-max -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_max)); ?>" type="text"
									       id="scales-yAxes-ticks-max" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-suggestedMin -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_suggestedMin)); ?>" type="text"
									       id="scales-yAxes-ticks-suggestedMin" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-suggestedMax -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_suggestedMax)); ?>" type="text"
									       id="scales-yAxes-ticks-suggestedMax" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-stepSize -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_stepSize)); ?>" type="text"
									       id="scales-yAxes-ticks-stepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-fixedStepSize -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_fixedStepSize)); ?>" type="text"
									       id="scales-yAxes-ticks-fixedStepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If set, the scale ticks will be enumerated by multiple of this value, having one tick per increment. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-maxTicksLimit -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_ticks_maxTicksLimit)); ?>" type="text"
									       id="scales-yAxes-ticks-maxTicksLimit" maxlength="20" size="30"<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-ticks-beginAtZero -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-yAxes-ticks-beginAtZero" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_yAxes_ticks_beginAtZero, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_yAxes_ticks_beginAtZero, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-yAxes-categoryPercentage -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-categoryPercentage"><?php _e( 'Category Percentage', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_categoryPercentage)); ?>" type="text"
									       id="scales-yAxes-categoryPercentage" maxlength="3" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width (the space between the grid lines for small datasets) for each data-point to use for the bars. This option is applied only with the Horizontal Bar chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-yAxes-barPercentage -->
							<tr class="scales-configuration-options-y">
								<th scope="row"><label for="scales-yAxes-barPercentage"><?php _e( 'Bar Percentage', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_yAxes_barPercentage)); ?>" type="text"
									       id="scales-yAxes-barPercentage" maxlength="3" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width each bar should be within the category percentage. 1.0 will take the whole category width and put the bars right next to each other. This option is applied only with the Horizontal Bar chart.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- Y2 Scale Common ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-common-configuration-y2">
								<th scope="row" class="group-title"><?php _e( 'Y2 Scale Common', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-display -->
							<tr class="scales-common-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-position -->
							<tr class="scales-common-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-position"
									        <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="right" <?php selected( $chart_obj->scales_y2Axes_position, 'right' ); ?>><?php _e('Right', 'dauc'); ?></option>
										<option value="left" <?php selected( $chart_obj->scales_y2Axes_position, 'left' ); ?>><?php _e('Left', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-type -->
							<tr class="scales-common-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-type"
										<?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="category" <?php selected( $chart_obj->scales_y2Axes_type, 'category' ); ?>><?php _e('Category', 'dauc'); ?></option>
										<option value="linear" <?php selected( $chart_obj->scales_y2Axes_type, 'linear' ); ?>><?php _e('Linear', 'dauc'); ?></option>
										<option value="logarithmic" <?php selected( $chart_obj->scales_y2Axes_type, 'logarithmic' ); ?>><?php _e('Logarithmic', 'dauc'); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y2 Scale Grid Line ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-y2">
								<th scope="row" class="group-title"><?php _e( 'Y2 Scale Grid Line', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-display -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-gridLines-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_gridLines_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_gridLines_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-color -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_gridLines_color)); ?>" type="text"
									       id="scales-y2Axes-gridLines-color" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-y2Axes-gridLines-color-spectrum" class="spectrum-input" type="text">
									<div id="scales-y2Axes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-lineWidth -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_gridLines_lineWidth)); ?>" type="text"
									       id="scales-y2Axes-gridLines-lineWidth" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-drawBorder -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-gridLines-drawBorder" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawBorder, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawBorder, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw border on the edge of the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-drawOnChartArea -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-gridLines-drawOnChartArea" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawOnChartArea, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawOnChartArea, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-drawTicks -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-gridLines-drawTicks" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawTicks, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_gridLines_drawTicks, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-tickMarkLength -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_gridLines_tickMarkLength)); ?>" type="text"
									       id="scales-y2Axes-gridLines-tickMarkLength" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-zeroLineColor -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_gridLines_zeroLineColor)); ?>" type="text"
									       id="scales-y2Axes-gridLines-zeroLineColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-y2Axes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-y2Axes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-zeroLineWidth -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_gridLines_zeroLineWidth)); ?>" type="text"
									       id="scales-y2Axes-gridLines-zeroLineWidth" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-gridLines-offsetGridLines -->
							<tr class="scales-grid-line-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-gridLines-offsetGridLines" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_gridLines_offsetGridLines, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_gridLines_offsetGridLines, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Horizontal Bar chart.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y2 Scale Title ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-title-configuration-y2">
								<th scope="row" class="group-title"><?php _e( 'Y2 Scale Title', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-display -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-scaleLabel-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-labelString -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-labelString"><?php _e( 'Label String', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_scaleLabel_labelString)); ?>" type="text"
									       id="scales-y2Axes-scaleLabel-labelString" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-fontSize -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_scaleLabel_fontSize)); ?>" type="text"
									       id="scales-y2Axes-scaleLabel-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-fontColor -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_scaleLabel_fontColor)); ?>" type="text"
									       id="scales-y2Axes-scaleLabel-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-y2Axes-scaleLabel-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-y2Axes-scaleLabel-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-fontFamily -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_scaleLabel_fontFamily)); ?>" type="text"
									       id="scales-y2Axes-scaleLabel-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-scaleLabel-fontStyle -->
							<tr class="scales-title-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-scaleLabel-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_y2Axes_scaleLabel_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y2 Scale Tick ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-tick-configuration-y2">
								<th scope="row" class="group-title"><?php _e( 'Y2 Scale Tick', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-display -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_ticks_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_ticks_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-autoskip -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-autoskip" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_ticks_autoskip, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_ticks_autoskip, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-reverse -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-reverse" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_ticks_reverse, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_ticks_reverse, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-mirror -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-mirror"><?php _e( 'Mirror', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-mirror" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_ticks_mirror, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_ticks_mirror, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Flips tick labels around axis, displaying the labels inside the chart instead of outside.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-minRotation -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_minRotation)); ?>" type="text"
									       id="scales-y2Axes-ticks-minRotation" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-maxRotation -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_maxRotation)); ?>" type="text"
									       id="scales-y2Axes-ticks-maxRotation" maxlength="6" size="30"<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-padding -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_padding)); ?>" type="text"
									       id="scales-y2Axes-ticks-padding" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Padding between the tick label and the axis.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-prefix -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_prefix)); ?>" type="text"
									       id="scales-y2Axes-ticks-prefix" maxlength="50" size="30"<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-suffix -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_suffix)); ?>" type="text"
									       id="scales-y2Axes-ticks-suffix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-round -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_round)); ?>" type="text"
									       id="scales-y2Axes-ticks-round" maxlength="2" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-fontSize -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_fontSize)); ?>" type="text"
									       id="scales-y2Axes-ticks-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-fontColor -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_fontColor)); ?>" type="text"
									       id="scales-y2Axes-ticks-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-y2Axes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-y2Axes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-fontFamily -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_fontFamily)); ?>" type="text"
									       id="scales-y2Axes-ticks-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-fontStyle -->
							<tr class="scales-tick-configuration-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_y2Axes_ticks_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_y2Axes_ticks_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_y2Axes_ticks_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_y2Axes_ticks_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- Y2 Scale Options ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-configuration-options-y2">
								<th scope="row" class="group-title"><?php _e( 'Y2 Scale Options', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-min -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_min)); ?>" type="text"
									       id="scales-y2Axes-ticks-min" maxlength="20" size="30"<?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-max -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_max)); ?>" type="text"
									       id="scales-y2Axes-ticks-max" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-suggestedMin -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_suggestedMin)); ?>" type="text"
									       id="scales-y2Axes-ticks-suggestedMin" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-suggestedMax -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_suggestedMax)); ?>" type="text"
									       id="scales-y2Axes-ticks-suggestedMax" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-stepSize -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_stepSize)); ?>" type="text"
									       id="scales-y2Axes-ticks-stepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-fixedStepSize -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_fixedStepSize)); ?>" type="text"
									       id="scales-y2Axes-ticks-fixedStepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If set, the scale ticks will be enumerated by multiple of this value, having one tick per increment. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-maxTicksLimit -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_y2Axes_ticks_maxTicksLimit)); ?>" type="text"
									       id="scales-y2Axes-ticks-maxTicksLimit" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-y2Axes-ticks-beginAtZero -->
							<tr class="scales-configuration-options-y2">
								<th scope="row"><label for="scales-y2Axes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-y2Axes-ticks-beginAtZero" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_y2Axes_ticks_beginAtZero, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_y2Axes_ticks_beginAtZero, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- RL Scale Common ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-common-configuration-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Common', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-display -->
							<tr class="scales-common-configuration-rl">
								<th scope="row"><label for="scales-rl-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the radial linear scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- RL Scale Grid Line ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Grid Line', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-gridLines-display -->
							<tr class="scales-grid-line-configuration-rl">
								<th scope="row"><label for="scales-rl-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-gridLines-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_gridLines_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_gridLines_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-gridLines-color -->
							<tr class="scales-grid-line-configuration-rl">
								<th scope="row"><label for="scales-rl-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_gridLines_color)); ?>" type="text"
									       id="scales-rl-gridLines-color" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-rl-gridLines-color-spectrum" class="spectrum-input" type="text">
									<div id="scales-rl-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-gridLines-lineWidth -->
							<tr class="scales-grid-line-configuration-rl">
								<th scope="row"><label for="scales-rl-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
								<td>
									<input  value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_gridLines_lineWidth)); ?>" value="1" type="text"
									       id="scales-rl-gridLines-lineWidth" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- RL Scale Angle Line ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-angle-line-configuration-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Angle Line', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-angleLines-display -->
							<tr class="scales-angle-line-configuration-rl">
								<th scope="row"><label for="scales-rl-angleLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-angleLines-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_angleLines_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_angleLines_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the angle lines are displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-angleLines-color -->
							<tr class="scales-angle-line-configuration-rl">
								<th scope="row"><label for="scales-rl-angleLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_angleLines_color)); ?>" type="text"
									       id="scales-rl-angleLines-color" maxlength="65535" size="30"/ <?php $this->disable_model_input($chart_obj->id); ?>>
									<input id="scales-rl-angleLines-color-spectrum" class="spectrum-input" type="text">
									<div id="scales-rl-angleLines-color-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'A color that will be used for the angle lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-angleLines-lineWidth -->
							<tr class="scales-angle-line-configuration-rl">
								<th scope="row"><label for="scales-rl-angleLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_angleLines_lineWidth)); ?>" type="text"
									       id="scales-rl-angleLines-lineWidth" maxlength="65535" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Stroke width of the angle lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- RL Scale Point Label ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-point-label-configuration-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Point Label', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-pointLabels-fontSize -->
							<tr class="scales-point-label-configuration-rl">
								<th scope="row"><label for="scales-rl-pointLabels-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_pointLabels_fontSize)); ?>" type="text"
									       id="scales-rl-pointLabels-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the point labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-pointLabels-fontColor -->
							<tr class="scales-point-label-configuration-rl">
								<th scope="row"><label for="scales-rl-pointLabels-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_pointLabels_fontColor)); ?>" type="text"
									       id="scales-rl-pointLabels-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-rl-pointLabels-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-rl-pointLabels-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the point labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-pointLabels-fontFamily -->
							<tr class="scales-point-label-configuration-rl">
								<th scope="row"><label for="scales-rl-pointLabels-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_pointLabels_fontFamily)); ?>" type="text"
									       id="scales-rl-pointLabels-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the point labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-pointLabels-fontStyle -->
							<tr class="scales-point-label-configuration-rl">
								<th scope="row"><label for="scales-rl-pointLabels-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-pointLabels-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_rl_pointLabels_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_rl_pointLabels_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_rl_pointLabels_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_rl_pointLabels_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the point labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- RL Scale Tick ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-tick-configuration-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Tick', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-display -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-display" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_ticks_display, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_ticks_display, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-autoskip -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-autoskip" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_ticks_autoskip, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_ticks_autoskip, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-reverse -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-reverse" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_ticks_reverse, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_ticks_reverse, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-prefix -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_prefix)); ?>" type="text"
									       id="scales-rl-ticks-prefix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-suffix -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_suffix)); ?>" type="text"
									       id="scales-rl-ticks-suffix" maxlength="50" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-round -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_round)); ?>" type="text"
									       id="scales-rl-ticks-round" maxlength="2" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-fontSize -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_fontSize)); ?>" type="text"
									       id="scales-rl-ticks-fontSize" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-fontColor -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_fontColor)); ?>" type="text"
									       id="scales-rl-ticks-fontColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-rl-ticks-fontColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-rl-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-fontFamily -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_fontFamily)); ?>" type="text"
									       id="scales-rl-ticks-fontFamily" maxlength="200" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-fontStyle -->
							<tr class="scales-tick-configuration-rl">
								<th scope="row"><label for="scales-rl-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-fontStyle" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="normal" <?php selected( $chart_obj->scales_rl_ticks_fontStyle, 'normal' ); ?>><?php _e( 'Normal', 'dauc' ); ?></option>
										<option value="bold" <?php selected( $chart_obj->scales_rl_ticks_fontStyle, 'bold' ); ?>><?php _e( 'Bold', 'dauc' ); ?></option>
										<option value="italic" <?php selected( $chart_obj->scales_rl_ticks_fontStyle, 'italic' ); ?>><?php _e( 'Italic', 'dauc' ); ?></option>
										<option value="oblique" <?php selected( $chart_obj->scales_rl_ticks_fontStyle, 'oblique' ); ?>><?php _e( 'Oblique', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- RL Scale Options ---------------------------------------------- -->

							<tr class="group-trigger" data-trigger-target="scales-configuration-options-rl">
								<th scope="row" class="group-title"><?php _e( 'RL Scale Options', 'dauc' ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-min -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_min)); ?>" type="text"
									       id="scales-rl-ticks-min" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-max -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_max)); ?>" type="text"
									       id="scales-rl-ticks-max" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-suggestedMin -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_suggestedMin)); ?>" type="text"
									       id="scales-rl-ticks-suggestedMin" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-suggestedMax -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_suggestedMax)); ?>" type="text"
									       id="scales-rl-ticks-suggestedMax" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-stepSize -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_stepSize)); ?>" type="text"
									       id="scales-rl-ticks-stepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-fixedStepSize -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_fixedStepSize)); ?>" type="text"
									       id="scales-rl-ticks-fixedStepSize" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Fixed step size for the scale. If set, the scale ticks will be enumerated by multiple of Step Size, having one tick per increment. If not set, the ticks are labeled automatically.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-maxTicksLimit -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_maxTicksLimit)); ?>" type="text"
									       id="scales-rl-ticks-maxTicksLimit" maxlength="20" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-beginAtZero -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-beginAtZero" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_ticks_beginAtZero, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_ticks_beginAtZero, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-showLabelBackdrop -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-showLabelBackdrop"><?php _e( 'Show Label Backdrop', 'dauc' ); ?></label></th>
								<td>
									<select id="scales-rl-ticks-showLabelBackdrop" <?php $this->disable_model_input($chart_obj->id); ?>>
										<option value="0" <?php selected( $chart_obj->scales_rl_ticks_showLabelBackdrop, 0 ); ?>><?php _e( 'No', 'dauc' ); ?></option>
										<option value="1" <?php selected( $chart_obj->scales_rl_ticks_showLabelBackdrop, 1 ); ?>><?php _e( 'Yes', 'dauc' ); ?></option>
									</select>
									<div class="help-icon"
									     title='<?php _e( 'If enabled, the label backdrop will be displayed.', 'dauc' ); ?>'></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-backdropColor -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-backdropColor"><?php _e( 'Backdrop Color', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_backdropColor)); ?>" type="text"
									       id="scales-rl-ticks-backdropColor" maxlength="22" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<input id="scales-rl-ticks-backdropColor-spectrum" class="spectrum-input" type="text">
									<div id="scales-rl-ticks-backdropColor-spectrum-toggle" class="spectrum-toggle"></div>
									<div class="help-icon" title="<?php _e( 'The backdrop color.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-backdropPaddingX -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-backdropPaddingX"><?php _e( 'Backdrop Padding X', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_backdropPaddingX)); ?>" type="text"
									       id="scales-rl-ticks-backdropPaddingX" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The backdrop horizontal padding.', 'dauc' ); ?>"></div>
								</td>
							</tr>

							<!-- scales-rl-ticks-backdropPaddingY -->
							<tr class="scales-configuration-options-rl">
								<th scope="row"><label for="scales-rl-ticks-backdropPaddingY"><?php _e( 'Backdrop Padding Y', 'dauc' ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr(stripslashes($chart_obj->scales_rl_ticks_backdropPaddingY)); ?>" type="text"
									       id="scales-rl-ticks-backdropPaddingY" maxlength="6" size="30" <?php $this->disable_model_input($chart_obj->id); ?>/>
									<div class="help-icon" title="<?php _e( 'The backdrop vertical padding.', 'dauc' ); ?>"></div>
								</td>
							</tr>

						</table>

						<!-- submit button -->
						<div class="daext-form-action">
							<input id="save" class="button" type="submit" value="<?php _e( 'Update Chart', 'dauc' ); ?>">
						</div>

						<?php else : ?>

						<!-- Create New Chart -->

						<?php

						//create temporary chart in db table
						global $wpdb;
						$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . "_chart";
						$safe_sql   = $wpdb->prepare( "INSERT INTO $table_name SET
				                              name = %s",
							'[TEMPORARY]' );
						$result     = $wpdb->query( $safe_sql );

						//get the automatic id of the inserted element
						$temporary_chart_id = $wpdb->insert_id;

						//initialize the data based on the current number of rows and columns
						$this->initialize_chart_data( $temporary_chart_id, 10, 10 );

						?>

						<input type="hidden" id="temporary-chart-id"
						       value="<?php echo $temporary_chart_id; ?>"/>

						<div class="daext-form-container">

							<div class="daext-form-title"><?php _e( 'Create New Chart', 'dauc' ); ?></div>

							<!-- submit button -->
							<table class="daext-form daext-form-chart">

								<!-- Load Model -->
								<tr>
									<th scope="row"><label for="load-model"><?php _e( 'Load Model', 'dauc' ); ?></label></th>
									<td>
										<select id="load-model">

											<option value="0"><?php _e('None', 'dauc'); ?></option>

											<?php

											global $wpdb;
											$table_name = $wpdb->prefix . $this->shared->get('slug') . "_chart";
											$sql = "SELECT * FROM $table_name WHERE is_model = 1 ORDER BY name ASC";
											$model_a = $wpdb->get_results($sql, ARRAY_A);

											foreach ($model_a as $key => $model) {
												echo '<option value="' . $model['id'] . '">' . esc_attr(stripslashes($model['name'])) . '</option>';
											}

											?>

										</select>
										<div class="help-icon" title="<?php _e( 'Use this field to import the data of an existing model.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Name -->
								<tr>
									<th scope="row"><label for="name"><?php _e( 'Name', 'dauc' ); ?></label></th>
									<td>
										<input type="text" id="name" maxlength="200" size="30" />
										<div class="help-icon"
										     title="<?php _e( 'The name of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Description -->
								<tr>
									<th scope="row"><label for="description"><?php _e( 'Description', 'dauc' ); ?></label></th>
									<td>
										<input type="text" id="description" maxlength="200" size="30" />
										<div class="help-icon"
										     title="<?php _e( 'The description of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Type -->
								<tr>
									<th scope="row"><label for="type"><?php _e( 'Type', 'dauc' ); ?></label></th>
									<td>
										<select id="type">
											<option value="line"><?php _e( 'Line', 'dauc' ); ?></option>
											<option value="bar"><?php _e( 'Bar', 'dauc' ); ?></option>
											<option value="horizontalBar"><?php _e( 'Horizontal Bar', 'dauc' ); ?></option>
											<option value="radar"><?php _e( 'Radar', 'dauc' ); ?></option>
											<option value="polarArea"><?php _e( 'Polar Area', 'dauc' ); ?></option>
											<option value="pie"><?php _e( 'Pie', 'dauc' ); ?></option>
											<option value="doughnut"><?php _e( 'Doughnut', 'dauc' ); ?></option>
											<option value="bubble"><?php _e( 'Bubble', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines the type of the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Rows -->
								<tr>
									<th scope="row"><label for="rows"><?php _e( 'Rows', 'dauc' ); ?></label></th>
									<td>
										<input type="text" value="10" id="rows"
										       maxlength="6" size="30" />
										<div class="help-icon"
										     title="<?php _e( 'The rows of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Columns -->
								<tr>
									<th scope="row"><label for="columns"><?php _e( 'Columns', 'dauc' ); ?></label></th>
									<td>
										<input type="text" value="10" id="columns"
										       maxlength="10" size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The columns of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row"><label for="data"><?php _e( 'Data', 'dauc' ); ?></label></th>
									<td id="dauc-table-td">
										<div id="dauc-table"></div>
									</td>
								</tr>

								<!-- Chart Preview ************************************************************** -->

								<tr class="group-trigger" data-trigger-target="chart-preview">
									<th scope="row" class="group-title"><?php _e( 'Preview', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<tr class="chart-preview">
									<td colspan="2" id="chart-preview-iframe-container-td">
										<div id="chart-preview-iframe-container"></div>
										<div id="save-and-refresh" class="help-icon" title="<?php _e( 'Save your changes and refresh the preview.', 'dauc' ); ?>"></div>
										<div id="chart-preview-error"><?php _e('Preview not available', 'dauc'); ?></div>
									</td>
								</tr>

								<!-- Common ******************************************************** -->

								<tr class="group-trigger" data-trigger-target="common-chart-configuration">
									<th scope="row"
									    class="group-title"><?php _e( 'Common', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- canvas-transparent-background -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="canvas-transparent-background"><?php _e( 'Transparent', 'dauc' ); ?></label></th>
									<td>
										<select id="canvas-transparent-background">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if the canvas should have a transparent background.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- canvas-backgroundColor -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="canvas-backgroundColor"><?php _e( 'Background Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#fff" type="text" id="canvas-backgroundColor"
										       maxlength="22" size="30"/>
										<input id="canvas-backgroundColor-spectrum" class="spectrum-input" type="text">
										<div id="canvas-backgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The background color of the canvas. This value is applied only if the &quot;Transparent&quot; option is disabled.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- width -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="width"><?php _e( 'Width', 'dauc' ); ?></label></th>
									<td>
										<input value="400" type="text" id="width" maxlength="6"
										       size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The width of the chart. This option will be used only if the chart is not responsive.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- height -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="height"><?php _e( 'Height', 'dauc' ); ?></label></th>
									<td>
										<input value="400" type="text" id="height" maxlength="6"
										       size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The height of the chart. This option will be used only if the chart is not responsive.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- margin-top -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="margin-top"><?php _e( 'Margin Top', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text" id="margin-top" maxlength="6"
										       size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The top margin of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- margin-bottom -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="margin-bottom"><?php _e( 'Margin Bottom', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text" id="margin-bottom" maxlength="6"
										       size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The bottom margin of the chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- responsive -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="responsive"><?php _e( 'Responsive', 'dauc' ); ?></label></th>
									<td>
										<select id="responsive">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Resizes when the canvas container does.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- responsiveAnimationDuration -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="responsiveAnimationDuration"><?php _e( 'Responsive Animation Duration', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text" id="responsiveAnimationDuration" maxlength="6"
										       size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'Duration in milliseconds it takes to animate to new size after a resize event.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- maintainAspectRatio -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="maintainAspectRatio"><?php _e( 'Maintain Aspect Ratio', 'dauc' ); ?></label></th>
									<td>
										<select id="maintainAspectRatio">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Maintain the original canvas aspect ratio when resizing.', 'dauc' ); ?>'></div>
									</td>
								</tr>

                                <!-- fixed-height -->
                                <tr class="common-chart-configuration">
                                    <th scope="row"><label for="fixed-height"><?php _e( 'Fixed Height', 'dauc' ); ?></label></th>
                                    <td>
                                        <input value="0" type="text" id="fixed-height" maxlength="6"
                                               size="30"/>
                                        <div class="help-icon"
                                             title="<?php esc_attr_e( 'Enter the fixed height of the chart or 0 to not use a fixed height. This option will be considered only if the chart is responsive and the "Maintain Aspect Ratio" option is set to "No".', 'dauc' ); ?>"></div>
                                    </td>
                                </tr>

								<!-- Is Model -->
								<tr class="common-chart-configuration">
									<th scope="row"><label for="is-model"><?php _e( 'Status', 'dauc' ); ?></label></th>
									<td>
										<select id="is-model">
											<option value="0"><?php _e( 'Chart', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Model', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if this chart can be used as a model.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Title *************************************************************** -->

								<tr class="group-trigger" data-trigger-target="title-configuration">
									<th scope="row" class="group-title"><?php _e( 'Title', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- title-display -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="title-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Display the title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- title-position -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
									<td>
										<select id="title-position">
											<option value="top"><?php _e( 'Top', 'dauc' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Position of the title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- title-fullWidth -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-fullWidth"><?php _e( 'Full Width', 'dauc' ); ?></label></th>
									<td>
										<select id="title-fullWidth">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if the title should take the full width of the canvas.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- title-padding -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text" id="title-padding" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'Number of pixels to add above and below the title text.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- title-fontSize -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text" id="title-fontSize" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'The font size of the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- title-fontColor -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text" id="title-fontColor"
										       maxlength="22" size="30"/>
										<input id="title-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="title-fontColor-defaultFontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The font color of the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- title-fontFamily -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="title-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon"
										     title="<?php _e( 'The font family of the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- title-fontStyle -->
								<tr class="title-configuration">
									<th scope="row"><label for="title-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="title-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold" selected="selected"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font styling of the title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Legend ************************************************************** -->

								<tr class="group-trigger" data-trigger-target="legend-configuration">
									<th scope="row" class="group-title"><?php _e( 'Legend', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- legend-display -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="legend-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if the legend should be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- legend-position -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
									<td>
										<select id="legend-position">
											<option value="top"><?php _e( 'Top', 'dauc' ); ?></option>
											<option value="right"><?php _e( 'Right', 'dauc' ); ?></option>
											<option value="bottom"><?php _e( 'Bottom', 'dauc' ); ?></option>
											<option value="left"><?php _e( 'Left', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'The position of the legend.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- legend-fullWidth -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-fullWidth"><?php _e( 'Full Width', 'dauc' ); ?></label></th>
									<td>
										<select id="legend-fullWidth">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if the legend should take the full width of the canvas.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- legend-labels-padding -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-padding"><?php _e( 'Padding', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="10" type="text" id="legend-labels-padding" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The padding between rows of colored boxes.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- legend-labels-boxWidth -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-boxWidth"><?php _e( 'Box Width', 'dauc' ); ?></label></th>
									<td>
										<input value="40" type="text" id="legend-labels-boxWidth" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The width of the colored box.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- legend-toggle-dataset -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-toggle-dataset"><?php _e( 'Toggle Dataset', 'dauc' ); ?></label></th>
									<td>
										<select id="legend-toggle-dataset">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option activates the ability to enable or disable a dataset by clicking on its legend colored box.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- legend-labels-fontSize -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text" id="legend-labels-fontSize" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'The font size of the legend label.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- legend-labels-fontColor -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text" id="legend-labels-fontColor"
										       maxlength="22" size="30"/>
										<input id="legend-labels-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="legend-labels-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The font color of the legend label.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- legend-labels-fontFamily -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-fontfamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="legend-labels-fontFamily" maxlength="200" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font family of the legend label.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- legend-labels-fontStyle -->
								<tr class="legend-configuration">
									<th scope="row"><label for="legend-labels-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="legend-labels-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'The font style of the legend label.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Tooltip ******************************************************** -->

								<tr class="group-trigger" data-trigger-target="tooltip-configuration">
									<th scope="row" class="group-title"><?php _e( 'Tooltip', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- tooltips-enabled -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-enabled"><?php _e( 'Enabled', 'dauc' ); ?></label></th>
									<td>
										<select id="tooltips-enabled">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines if the tooltips should be enabled.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- tooltips-mode -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-mode"><?php _e( 'Mode', 'dauc' ); ?></label></th>
									<td>
										<select id="tooltips-mode">
											<option value="single"><?php _e( 'Single', 'dauc' ); ?></option>
											<option value="label"><?php _e( 'Label', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'This option determines which elements should appear in the tooltip. Single highlights the closest element and Label highlights elements in all datasets at the same X value.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- tooltips-backgroundColor -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-backgroundColor"><?php _e( 'Background Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.8)" type="text"
										       id="tooltips-backgroundColor" maxlength="22" size="30"
										      />
										<input id="tooltips-backgroundColor-spectrum" class="spectrum-input" type="text">
										<div id="tooltips-backgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The background color of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-multiKeyBackground -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-multiKeyBackground"><?php _e( 'Multi Key Background', 'dauc' ); ?></label></th>
									<td>
										<input value="#fff" type="text"
										       id="tooltips-multiKeyBackground" maxlength="22" size="30"
										/>
										<input id="tooltips-multiKeyBackground-spectrum" class="spectrum-input" type="text">
										<div id="tooltips-multiKeyBackground-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'Color to draw behind the colored boxes when multiple items are in the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-titleMarginBottom -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-titleMarginBottom"><?php _e( 'Title Margin Bottom', 'dauc' ); ?></label></th>
									<td>
										<input value="6" type="text" id="tooltips-titleMarginBottom" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'Margin to add on the bottom of the tooltip title section.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-footerMarginTop -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-footerMarginTop"><?php _e( 'Footer Margin Top', 'dauc' ); ?></label></th>
									<td>
										<input value="6" type="text" id="tooltips-footerMarginTop" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'Margin to add before drawing the tooltip footer.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-xPadding -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-xPadding"><?php _e( 'X Padding', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="6" type="text" id="tooltips-xPadding" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'Padding to add on the left and right side of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-yPadding -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-yPadding"><?php _e( 'Y Padding', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="6" type="text" id="tooltips-yPadding" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'Padding to add on the top and bottom side of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-caretSize -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-caretSize"><?php _e( 'Caret Size', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="5" type="text" id="tooltips-caretSize" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'The size of the tooltip arrow.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-cornerRadius -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-cornerRadius"><?php _e( 'Corner Radius', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="6" type="text" id="tooltips-cornerRadius" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'The radius of the tooltip corner curves.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- hover-animationDuration -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="hover-animationDuration"><?php _e( 'Animation Duration', 'dauc' ); ?></label></th>
									<td>
										<input value="400" type="text" id="hover-animationDuration" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'Duration in milliseconds it takes to animate hover style changes.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-beforeTitle -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-beforeTitle"><?php _e( 'Before Title', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-beforeTitle" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed before the title of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-afterTitle -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-afterTitle"><?php _e( 'After Title', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-afterTitle" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed after the title of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-beforeBody -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-beforeBody"><?php _e( 'Before Body', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-beforeBody" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed before the body of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-afterBody -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-afterBody"><?php _e( 'After Body', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-afterBody" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed after the body of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-beforeLabel -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-beforeLabel"><?php _e( 'Before Label', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-beforeLabel" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed before the label of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-afterLabel -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-afterLabel"><?php _e( 'After Label', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="tooltips-afterLabel" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed after the label of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-beforeFooter -->
								<tr class="tooltip-configuration">
									<th scope="row"><Footer for="tooltips-beforeFooter"><?php _e( 'Before Footer', 'dauc' ); ?></Footer></th>
									<td>
										<input value="" type="text"
										       id="tooltips-beforeFooter" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed before the footer of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-footer -->
								<tr class="tooltip-configuration">
									<th scope="row"><Footer for="tooltips-footer"><?php _e( 'Footer', 'dauc' ); ?></Footer></th>
									<td>
										<input value="" type="text"
										       id="tooltips-footer" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed in the footer of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-afterFooter -->
								<tr class="tooltip-configuration">
									<th scope="row"><Footer for="tooltips-afterFooter"><?php _e( 'After Footer', 'dauc' ); ?></Footer></th>
									<td>
										<input value="" type="text"
										       id="tooltips-afterFooter" maxlength="200" size="30" />
										<div class="help-icon" title="<?php _e( 'The text displayed after the footer of the tooltip.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-titleFontSize -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-titleFontSize"><?php _e( 'Title Font size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text" id="tooltips-titleFontSize" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font size of the tooltip title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-titleFontColor -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-titleFontColor"><?php _e( 'Title Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#fff" type="text" id="tooltips-titleFontColor"
										       maxlength="22" size="30"/>
										<input id="tooltips-titleFontColor-spectrum" class="spectrum-input" type="text">
										<div id="tooltips-titleFontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The font color of the tooltip title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-titleFontFamily -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-titleFontFamily"><?php _e( 'Title Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="tooltips-titleFontFamily" maxlength="200" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font family of the tooltip title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-titleFontStyle -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-titleFontStyle"><?php _e( 'Title Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="tooltips-titleFontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold" selected="selected"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon" title='<?php _e( 'The font style of the tooltip title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- tooltips-bodyFontSize -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-bodyFontSize"><?php _e( 'Body Font Size', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="12" type="text" id="tooltips-bodyFontSize" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font size of the tooltip body.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-bodyFontColor -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-bodyFontColor"><?php _e( 'Body Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#fff" type="text" id="tooltips-bodyFontColor"
										       maxlength="22" size="30"/>
										<input id="tooltips-bodyFontColor-spectrum" class="spectrum-input" type="text">
										<div id="tooltips-bodyFontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The font color of the tooltip body.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-bodyFontFamily -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-bodyFontFamily"><?php _e( 'Body Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="tooltips-bodyFontFamily" maxlength="200" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font family of the tooltip body.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-bodyFontStyle -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-bodyFontStyle"><?php _e( 'Body Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="tooltips-bodyFontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon" title='<?php _e( 'The font style of the tooltip body.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- tooltips-footerFontSize -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-footerFontSize"><?php _e( 'Footer Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text" id="tooltips-footerFontSize" maxlength="6" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font size of the tooltip footer.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-footerFontColor -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-footerFontColor"><?php _e( 'Footer Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#fff" type="text"
										       id="tooltips-footerFontColor" maxlength="22" size="30"
										/>
										<input id="tooltips-footerFontColor-spectrum" class="spectrum-input" type="text">
										<div id="tooltips-footerFontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon"
										     title="<?php _e( 'The font color for tooltip footer.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-footerFontFamily -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-footerFontFamily"><?php _e( 'Footer Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="tooltips-footerFontFamily" maxlength="200" size="30"
										/>
										<div class="help-icon"
										     title="<?php _e( 'The font family of the tooltip footer.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- tooltips-footerFontStyle -->
								<tr class="tooltip-configuration">
									<th scope="row"><label for="tooltips-footerFontStyle"><?php _e( 'Footer Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="tooltips-footerFontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold" selected="selected"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'The font style of the tooltip footer.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Animation *********************************************************** -->

								<tr class="group-trigger" data-trigger-target="animation-configuration">
									<th scope="row"
									    class="group-title"><?php _e( 'Animation', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- animation-duration -->
								<tr class="animation-configuration">
									<th scope="row"><label for="animation-duration"><?php _e( 'Duration', 'dauc' ); ?></label>
									</th>
									<td>
										<input value="1000" type="text" id="animation-duration" maxlength="6" size="30"
										      />
										<div class="help-icon"
										     title="<?php _e( 'The number of milliseconds an animation takes.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- animation-easing -->
								<tr class="animation-configuration">
									<th scope="row"><label for="animation-easing"><?php _e( 'Easing', 'dauc' ); ?></label></th>
									<td>
										<select id="animation-easing">
											<?php
											$easing_list = 'linear,easeInQuad,easeOutQuad,easeInOutQuad,easeInCubic,easeOutCubic,easeInOutCubic,easeInQuart,easeOutQuart,easeInOutQuart,easeInQuint,easeOutQuint,easeInOutQuint,easeInSine,easeOutSine,easeInOutSine,easeInExpo,easeOutExpo,easeInOutExpo,easeInCirc,easeOutCirc,easeInOutCirc,easeInElastic,easeOutElastic,easeInOutElastic,easeInBack,easeOutBack,easeInOutBack,easeInBounce,easeOutBounce,easeInOutBounce';
											$easing_a    = explode( ',', $easing_list );
											foreach ( $easing_a as $key => $single_easing ) {
												if ( $single_easing == 'easeOutQuart' ) {
													$selected = 'selected="selected"';
												} else {
													$selected = '';
												}
												echo '<option ' . $selected . ' value="' . $single_easing . '">' . $single_easing . '</option>';
											}
											?>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Easing function to use.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- animation-animateRotate -->
								<tr class="animation-configuration">
									<th scope="row"><label for="animation-animateRotate"><?php _e( 'Animate Rotate', 'dauc' ); ?></label></th>
									<td>
										<select id="animation-animateRotate">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, will animate the rotation of the chart. This option is applied only with Polar Area, Pie and Doughnut Chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- animation-animateScale -->
								<tr class="animation-configuration">
									<th scope="row"><label for="animation-animateScale"><?php _e( 'Animate Scale', 'dauc' ); ?></label></th>
									<td>
										<select id="animation-animateScale">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, will animate the scaling of the chart from the center. This option is applied only with Polar Area, Pie and Doughnut Chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Misc *********************************************************** -->

								<tr class="group-trigger" data-trigger-target="misc-configuration">
									<th scope="row"
									    class="group-title"><?php _e( 'Misc', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- elements-rectangle-borderSkipped -->
								<tr class="misc-configuration">
									<th scope="row"><label for="elements-rectangle-borderSkipped"><?php _e( 'Border Skipped', 'dauc' ); ?></label></th>
									<td>
										<select id="elements-rectangle-borderSkipped">
											<option value="top"><?php _e('Top', 'dauc'); ?></option>
											<option value="right"><?php _e('Right', 'dauc'); ?></option>
											<option value="bottom" selected="selected"><?php _e('Bottom', 'dauc'); ?></option>
											<option value="left"><?php _e('Left', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Skipped (excluded) border for the rectangle. This option is applied only to the Bar and Horizontal Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- X Scale Common ------------------------------------------------------ -->

								<tr class="group-trigger" data-trigger-target="scales-common-configuration-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Common', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-display -->
								<tr class="scales-common-configuration-x">
									<th scope="row"><label for="scales-xAxes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-position -->
								<tr class="scales-common-configuration-x">
									<th scope="row"><label for="scales-xAxes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-position"
										       >
											<option value="top"><?php _e('Top', 'dauc'); ?></option>
											<option value="bottom" selected="selected"><?php _e('Bottom', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-type -->
								<tr class="scales-common-configuration-x">
									<th scope="row"><label for="scales-xAxes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-type"
										>
											<option value="category"><?php _e('Category', 'dauc'); ?></option>
											<option value="linear"><?php _e('Linear', 'dauc'); ?></option>
											<option value="logarithmic"><?php _e('Logarithmic', 'dauc'); ?></option>
											<option value="time"><?php _e('Time', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-stacked -->
								<tr class="scales-common-configuration-x">
									<th scope="row"><label for="scales-xAxes-stacked"><?php _e( 'Stacked', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-stacked">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, lines or bars are stacked. This option is applied only with the Line and Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- X Scale Grid Line --------------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Grid Line', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-display -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-gridLines-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-color -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.1)" type="text"
										       id="scales-xAxes-gridLines-color" maxlength="65535" size="30"/>
										<input id="scales-xAxes-gridLines-color-spectrum" class="spectrum-input" type="text">
										<div id="scales-xAxes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-lineWidth -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-xAxes-gridLines-lineWidth" maxlength="65535" size="30"/>
										<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-drawBorder -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-gridLines-drawBorder">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw the border on the edge of the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-drawOnChartArea -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-gridLines-drawOnChartArea">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-drawTicks -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-gridLines-drawTicks">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-tickMarkLength -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-xAxes-gridLines-tickMarkLength" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-zeroLineColor -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.25)" type="text"
										       id="scales-xAxes-gridLines-zeroLineColor" maxlength="22" size="30"/>
										<input id="scales-xAxes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-xAxes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-zeroLineWidth -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-xAxes-gridLines-zeroLineWidth" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-gridLines-offsetGridLines -->
								<tr class="scales-grid-line-configuration-x">
									<th scope="row"><label for="scales-xAxes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-gridLines-offsetGridLines">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- X Scale Title ------------------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-title-configuration-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Title', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-display -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-scaleLabel-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-labelString -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-labelString"><?php _e( 'Label', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-scaleLabel-labelString" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-fontSize -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-xAxes-scaleLabel-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-fontColor -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-xAxes-scaleLabel-fontColor" maxlength="22" size="30"/>
										<input id="scales-xAxes-scaleLabel-spectrum" class="spectrum-input" type="text">
										<div id="scales-xAxes-scaleLabel-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-fontFamily -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-xAxes-scaleLabel-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-scaleLabel-fontStyle -->
								<tr class="scales-title-configuration-x">
									<th scope="row"><label for="scales-xAxes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-scaleLabel-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- X Scale Tick -------------------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-tick-configuration-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Tick', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-display -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-ticks-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-autoskip -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-ticks-autoskip">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-reverse -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-ticks-reverse">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-labelOffset -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-labelOffset"><?php _e( 'Label Offset', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text"
										       id="scales-xAxes-ticks-labelOffset" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Distance in pixels to offset the label from the centre point of the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-minRotation -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text"
										       id="scales-xAxes-ticks-minRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-maxRotation -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="90" type="text"
										       id="scales-xAxes-ticks-maxRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-prefix -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-prefix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-suffix -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-suffix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-round -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-round" maxlength="2" size="30"/>
										<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-fontSize -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-xAxes-ticks-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-fontColor -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-xAxes-ticks-fontColor" maxlength="22" size="30"/>
										<input id="scales-xAxes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-xAxes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-fontFamily -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-xAxes-ticks-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-fontStyle -->
								<tr class="scales-tick-configuration-x">
									<th scope="row"><label for="scales-xAxes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-ticks-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- X Scale Configuration ----------------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-configuration-options-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Options', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-min -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-min" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-max -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-max" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-suggestedMin -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-suggestedMin" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-suggestedMax -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-suggestedMax" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-stepSize -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-stepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-fixedStepSize -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-ticks-fixedStepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Fixed step size for the scale. If set, the scale ticks will be enumerated by multiple of Step Size, having one tick per increment. If not set, the ticks are labeled automatically. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-maxTicksLimit -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-maxTicksLimit"><?php _e( 'Max Limit', 'dauc' ); ?></label></th>
									<td>
										<input value="11" type="text"
										       id="scales-xAxes-ticks-maxTicksLimit" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear Scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-ticks-beginAtZero -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-ticks-beginAtZero">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-categoryPercentage -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-categoryPercentage"><?php _e( 'Category Percentage', 'dauc' ); ?></label></th>
									<td>
										<input value="0.8" type="text"
										       id="scales-xAxes-categoryPercentage" maxlength="3" size="30"/>
										<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width (the space between the grid lines for small datasets) for each data-point to use for the bars. This option is applied only with the Bar chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-barPercentage -->
								<tr class="scales-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-barPercentage"><?php _e( 'Bar Percentage', 'dauc' ); ?></label></th>
									<td>
										<input value="0.9" type="text"
										       id="scales-xAxes-barPercentage" maxlength="3" size="30"/>
										<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width each bar should be within the category percentage. 1.0 will take the whole category width and put the bars right next to each other. This option is applied only with the Bar chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- X Scale Time  ----------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-time-configuration-options-x">
									<th scope="row" class="group-title"><?php _e( 'X Scale Time', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-format -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-format"><?php _e( 'Time Format', 'dauc' ); ?></label></th>
									<td>
										<input value="YYYY" type="text"
										       id="scales-xAxes-time-format" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the scale. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-tooltipFormat -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-tooltipFormat"><?php _e( 'Tooltip Format', 'dauc' ); ?></label></th>
									<td>
										<input value="YYYY" type="text"
										       id="scales-xAxes-time-tooltipFormat" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the tooltip. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-unit-format -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-unit-format"><?php _e( 'Unit Format', 'dauc' ); ?></label></th>
									<td>
										<input value="YYYY" type="text"
										       id="scales-xAxes-time-unit-format" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'The Moment.js format string to use for the time unit. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-min -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-time-min" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, this will override the data minimum. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-max -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-xAxes-time-max" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, this will override the data maximum. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-unit -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-unit"><?php _e( 'Unit', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-xAxes-time-unit">
											<option value="millisecond"><?php _e( 'Millisecond', 'dauc' ); ?></option>
											<option value="second"><?php _e( 'Second', 'dauc' ); ?></option>
											<option value="minute"><?php _e( 'Minute', 'dauc' ); ?></option>
											<option value="hour"><?php _e( 'Hour', 'dauc' ); ?></option>
											<option value="day"><?php _e( 'Day', 'dauc' ); ?></option>
											<option value="week"><?php _e( 'Week', 'dauc' ); ?></option>
											<option value="month"><?php _e( 'Month', 'dauc' ); ?></option>
											<option value="quarter"><?php _e( 'Quarter', 'dauc' ); ?></option>
											<option value="year" selected="selected"><?php _e( 'Year', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'The time unit. This option is applied only to the Time scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-xAxes-time-unitStepSize -->
								<tr class="scales-time-configuration-options-x">
									<th scope="row"><label for="scales-xAxes-time-unitStepSize"><?php _e( 'Unit Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-xAxes-time-unitStepSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'The number of units between grid lines. This option is applied only to the Time scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Y Scale Common ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-common-configuration-y">
									<th scope="row" class="group-title"><?php _e( 'Y Scale Common', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-yAxes-display -->
								<tr class="scales-common-configuration-y">
									<th scope="row"><label for="scales-yAxes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-position -->
								<tr class="scales-common-configuration-y">
									<th scope="row"><label for="scales-yAxes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-position"
										       >
											<option value="right"><?php _e('Right', 'dauc'); ?></option>
											<option value="left" selected="selected"><?php _e('Left', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-type -->
								<tr class="scales-common-configuration-y">
									<th scope="row"><label for="scales-yAxes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-type"
										>
											<option value="category"><?php _e('Category', 'dauc'); ?></option>
											<option value="linear" selected="selected"><?php _e('Linear', 'dauc'); ?></option>
											<option value="logarithmic"><?php _e('Logarithmic', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-stacked -->
								<tr class="scales-common-configuration-y">
									<th scope="row"><label for="scales-yAxes-stacked"><?php _e( 'Stacked', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-stacked">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, lines or bars are stacked. This option is applied only with the Line and Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y Scales Grid Line ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-y">
									<th scope="row" class="group-title"><?php _e( 'Y Scale Grid Line', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-display -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-gridLines-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-color -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.1)" type="text"
										       id="scales-yAxes-gridLines-color" maxlength="65535" size="30"/>
										<input id="scales-yAxes-gridLines-color-spectrum" class="spectrum-input" type="text">
										<div id="scales-yAxes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-lineWidth -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-yAxes-gridLines-lineWidth" maxlength="65535" size="30"/>
										<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-drawBorder -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-gridLines-drawBorder">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw border on the edge of the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-drawOnChartArea -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-gridLines-drawOnChartArea">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-drawTicks -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-gridLines-drawTicks">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-tickMarkLength -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-yAxes-gridLines-tickMarkLength" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-zeroLineColor -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.25)" type="text"
										       id="scales-yAxes-gridLines-zeroLineColor" maxlength="22" size="30"/>
										<input id="scales-yAxes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-yAxes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-zeroLineWidth -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-yAxes-gridLines-zeroLineWidth" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-gridLines-offsetGridLines -->
								<tr class="scales-grid-line-configuration-y">
									<th scope="row"><label for="scales-yAxes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-gridLines-offsetGridLines">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Horizontal Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y Scale Title ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-title-configuration-y">
									<th scope="row" class="group-title"><?php _e( 'Y Scale Title', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-display -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-scaleLabel-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-labelString -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-labelString"><?php _e( 'Label String', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-scaleLabel-labelString" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-fontSize -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-yAxes-scaleLabel-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-fontColor -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-yAxes-scaleLabel-fontColor" maxlength="22" size="30"/>
										<input id="scales-yAxes-scaleLabel-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-yAxes-scaleLabel-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-fontFamily -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-yAxes-scaleLabel-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-scaleLabel-fontStyle -->
								<tr class="scales-title-configuration-y">
									<th scope="row"><label for="scales-yAxes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-scaleLabel-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y Scale Tick ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-tick-configuration-y">
									<th scope="row" class="group-title"><?php _e( 'Y Scale Tick', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-display -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-autoskip -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-autoskip">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-reverse -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-reverse">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-mirror -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-mirror"><?php _e( 'Mirror', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-mirror">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Flips tick labels around axis, displaying the labels inside the chart instead of outside.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-minRotation -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text"
										       id="scales-yAxes-ticks-minRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-maxRotation -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="90" type="text"
										       id="scales-yAxes-ticks-maxRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-padding -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-yAxes-ticks-padding" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Padding between the tick label and the axis.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-prefix -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-prefix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-suffix -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-suffix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-round -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-round" maxlength="2" size="30"/>
										<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-fontSize -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-yAxes-ticks-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-fontColor -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-yAxes-ticks-fontColor" maxlength="22" size="30"/>
										<input id="scales-yAxes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-yAxes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-fontFamily -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-yAxes-ticks-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-fontStyle -->
								<tr class="scales-tick-configuration-y">
									<th scope="row"><label for="scales-yAxes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y Scale Options ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-configuration-options-y">
									<th scope="row" class="group-title"><?php _e( 'Y Scale Options', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-min -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-min" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-max -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-max" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-suggestedMin -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-suggestedMin" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-suggestedMax -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-suggestedMax" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-stepSize -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-stepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-fixedStepSize -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-yAxes-ticks-fixedStepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Fixed step size for the scale. If set, the scale ticks will be enumerated by multiple of Step Size, having one tick per increment. If not set, the ticks are labeled automatically. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-maxTicksLimit -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
									<td>
										<input value="11" type="text"
										       id="scales-yAxes-ticks-maxTicksLimit" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear Scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-ticks-beginAtZero -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-yAxes-ticks-beginAtZero">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'if enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-yAxes-categoryPercentage -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-categoryPercentage"><?php _e( 'Category Percentage', 'dauc' ); ?></label></th>
									<td>
										<input value="0.8" type="text"
										       id="scales-yAxes-categoryPercentage" maxlength="3" size="30"/>
										<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width (the space between the grid lines for small datasets) for each data-point to use for the bars. This option is applied only with the Horizontal Bar chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-yAxes-barPercentage -->
								<tr class="scales-configuration-options-y">
									<th scope="row"><label for="scales-yAxes-barPercentage"><?php _e( 'Bar Percentage', 'dauc' ); ?></label></th>
									<td>
										<input value="0.9" type="text"
										       id="scales-yAxes-barPercentage" maxlength="3" size="30"/>
										<div class="help-icon" title="<?php _e( 'Percent (0-1) of the available width each bar should be within the category percentage. 1.0 will take the whole category width and put the bars right next to each other. This option is applied only with the Horizontal Bar chart.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- Y2 Scale Common ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-common-configuration-y2">
									<th scope="row" class="group-title"><?php _e( 'Y2 Scale Common', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-display -->
								<tr class="scales-common-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-position -->
								<tr class="scales-common-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-position"><?php _e( 'Position', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-position"
										       >
											<option value="right" selected="selected"><?php _e('Right', 'dauc'); ?></option>
											<option value="left"><?php _e('Left', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Position of the scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-type -->
								<tr class="scales-common-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-type"><?php _e( 'Type', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-type"
										>
											<option value="category"><?php _e('Category', 'dauc'); ?></option>
											<option value="linear" selected="selected"><?php _e('Linear', 'dauc'); ?></option>
											<option value="logarithmic"><?php _e('Logarithmic', 'dauc'); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Type of scale being employed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y2 Scale Grid Line ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-y2">
									<th scope="row" class="group-title"><?php _e( 'Y2 Scale Grid Line', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-display -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-gridLines-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-color -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.1)" type="text"
										       id="scales-y2Axes-gridLines-color" maxlength="65535" size="30"/>
										<input id="scales-y2Axes-gridLines-color-spectrum" class="spectrum-input" type="text">
										<div id="scales-y2Axes-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-lineWidth -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-y2Axes-gridLines-lineWidth" maxlength="65535" size="30"/>
										<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-drawBorder -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-drawBorder"><?php _e( 'Draw Border', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-gridLines-drawBorder">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw border on the edge of the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-drawOnChartArea -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-drawOnChartArea"><?php _e( 'Draw on Chart Area', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-gridLines-drawOnChartArea">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines on the chart area inside the axis lines.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-drawTicks -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-drawTicks"><?php _e( 'Draw Ticks', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-gridLines-drawTicks">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, draw lines beside the ticks in the axis area beside the chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-tickMarkLength -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-tickMarkLength"><?php _e( 'Tick Mark Length', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-y2Axes-gridLines-tickMarkLength" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Length in pixels that the grid lines will draw into the axis area.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-zeroLineColor -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-zeroLineColor"><?php _e( 'Zero Line Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.25)" type="text"
										       id="scales-y2Axes-gridLines-zeroLineColor" maxlength="22" size="30"/>
										<input id="scales-y2Axes-gridLines-zeroLineColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-y2Axes-gridLines-zeroLineColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Stroke color of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-zeroLineWidth -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-zeroLineWidth"><?php _e( 'Zero Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-y2Axes-gridLines-zeroLineWidth" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Stroke width of the grid line for the first index.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-gridLines-offsetGridLines -->
								<tr class="scales-grid-line-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-gridLines-offsetGridLines"><?php _e( 'Offset Grid Lines', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-gridLines-offsetGridLines">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, labels are shifted to be between grid lines. This is used in the Horizontal Bar chart.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y2 Scale Title ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-title-configuration-y2">
									<th scope="row" class="group-title"><?php _e( 'Y2 Scale Title', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-display -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-scaleLabel-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale label is displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-labelString -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-labelString"><?php _e( 'Label String', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-scaleLabel-labelString" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'The text for the title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-fontSize -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-y2Axes-scaleLabel-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-fontColor -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-y2Axes-scaleLabel-fontColor" maxlength="22" size="30"/>
										<input id="scales-y2Axes-scaleLabel-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-y2Axes-scaleLabel-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-fontFamily -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-y2Axes-scaleLabel-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the scale title.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-scaleLabel-fontStyle -->
								<tr class="scales-title-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-scaleLabel-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-scaleLabel-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the scale title.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y2 Scale Tick ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-tick-configuration-y2">
									<th scope="row" class="group-title"><?php _e( 'Y2 Scale Tick', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-display -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-autoskip -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-autoskip">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-reverse -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-reverse">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-mirror -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-mirror"><?php _e( 'Mirror', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-mirror">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Flips tick labels around axis, displaying the labels inside the chart instead of outside.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-minRotation -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-minRotation"><?php _e( 'Min Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="0" type="text"
										       id="scales-y2Axes-ticks-minRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Minimum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-maxRotation -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-maxRotation"><?php _e( 'Max Rotation', 'dauc' ); ?></label></th>
									<td>
										<input value="90" type="text"
										       id="scales-y2Axes-ticks-maxRotation" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( "Maximum rotation for tick labels when rotating to condense labels. Note that the rotation doesn't occur until necessary.", 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-padding -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-padding"><?php _e( 'Padding', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-y2Axes-ticks-padding" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Padding between the tick label and the axis.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-prefix -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-prefix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-suffix -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-suffix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-round -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-round" maxlength="2" size="30"/>
										<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-fontSize -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-y2Axes-ticks-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-fontColor -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-y2Axes-ticks-fontColor" maxlength="22" size="30"/>
										<input id="scales-y2Axes-ticks-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-y2Axes-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-fontFamily -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-y2Axes-ticks-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-fontStyle -->
								<tr class="scales-tick-configuration-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- Y2 Scale Options ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-configuration-options-y2">
									<th scope="row" class="group-title"><?php _e( 'Y2 Scale Options', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-min -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-min" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-max -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-max" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-suggestedMin -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-suggestedMin" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-suggestedMax -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-suggestedMax" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-stepSize -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-stepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-fixedStepSize -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-y2Axes-ticks-fixedStepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Fixed step size for the scale. If set, the scale ticks will be enumerated by multiple of Step Size, having one tick per increment. If not set, the ticks are labeled automatically. This option is applied only to the Linear scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-maxTicksLimit -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
									<td>
										<input value="11" type="text"
										       id="scales-y2Axes-ticks-maxTicksLimit" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines. This option is applied only to the Linear Scale.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-y2Axes-ticks-beginAtZero -->
								<tr class="scales-configuration-options-y2">
									<th scope="row"><label for="scales-y2Axes-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-y2Axes-ticks-beginAtZero">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included. This option is applied only to the Linear scale.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- RL Scale Common ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-common-configuration-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Common', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-display -->
								<tr class="scales-common-configuration-rl">
									<th scope="row"><label for="scales-rl-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the radial linear scale including grid lines, ticks and labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- RL Scale Grid Line ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-grid-line-configuration-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Grid Line', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-gridLines-display -->
								<tr class="scales-grid-line-configuration-rl">
									<th scope="row"><label for="scales-rl-gridLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-gridLines-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the grid lines are displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-gridLines-color -->
								<tr class="scales-grid-line-configuration-rl">
									<th scope="row"><label for="scales-rl-gridLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.1)" type="text"
										       id="scales-rl-gridLines-color" maxlength="65535" size="30"/>
										<input id="scales-rl-gridLines-color-spectrum" class="spectrum-input" type="text">
										<div id="scales-rl-gridLines-color-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'A color or a comma separated list of colors that will be used for the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-gridLines-lineWidth -->
								<tr class="scales-grid-line-configuration-rl">
									<th scope="row"><label for="scales-rl-gridLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-rl-gridLines-lineWidth" maxlength="65535" size="30"/>
										<div class="help-icon" title="<?php _e( 'A number or a comma separated list of numbers used to define the stroke width of the grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- RL Scale Angle Line ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-angle-line-configuration-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Angle Line', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-angleLines-display -->
								<tr class="scales-angle-line-configuration-rl">
									<th scope="row"><label for="scales-rl-angleLines-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-angleLines-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the angle lines are displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-angleLines-color -->
								<tr class="scales-angle-line-configuration-rl">
									<th scope="row"><label for="scales-rl-angleLines-color"><?php _e( 'Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(0,0,0,0.1)" type="text"
										       id="scales-rl-angleLines-color" maxlength="65535" size="30"/>
										<input id="scales-rl-angleLines-color-spectrum" class="spectrum-input" type="text">
										<div id="scales-rl-angleLines-color-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'A color that will be used for the angle lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-angleLines-lineWidth -->
								<tr class="scales-angle-line-configuration-rl">
									<th scope="row"><label for="scales-rl-angleLines-lineWidth"><?php _e( 'Line Width', 'dauc' ); ?></label></th>
									<td>
										<input value="1" type="text"
										       id="scales-rl-angleLines-lineWidth" maxlength="65535" size="30"/>
										<div class="help-icon" title="<?php _e( 'Stroke width of the angle lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- RL Scale Point Label ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-point-label-configuration-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Point Label', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-pointLabels-fontSize -->
								<tr class="scales-point-label-configuration-rl">
									<th scope="row"><label for="scales-rl-pointLabels-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="10" type="text"
										       id="scales-rl-pointLabels-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the point labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-pointLabels-fontColor -->
								<tr class="scales-point-label-configuration-rl">
									<th scope="row"><label for="scales-rl-pointLabels-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-rl-pointLabels-fontColor" maxlength="22" size="30"/>
										<input id="scales-rl-pointLabels-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-rl-pointLabels-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the point labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-pointLabels-fontFamily -->
								<tr class="scales-point-label-configuration-rl">
									<th scope="row"><label for="scales-rl-pointLabels-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-rl-pointLabels-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the point labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-pointLabels-fontStyle -->
								<tr class="scales-point-label-configuration-rl">
									<th scope="row"><label for="scales-rl-pointLabels-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-pointLabels-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the point labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- RL Scale Tick ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-tick-configuration-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Tick', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-display -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-display"><?php _e( 'Display', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-display">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, show the ticks.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-autoskip -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-autoskip"><?php _e( 'Autoskip', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-autoskip">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If this option is enabled the number of labels displayed will be determined automatically, if this option is disabled all the labels will be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-reverse -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-reverse"><?php _e( 'Reverse', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-reverse">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Reverses order of tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-prefix -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-prefix"><?php _e( 'Prefix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-prefix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a prefix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-suffix -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-suffix"><?php _e( 'Suffix', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-suffix" maxlength="50" size="30"/>
										<div class="help-icon" title="<?php _e( 'Add a suffix to the tick.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-round -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-round"><?php _e( 'Round', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-round" maxlength="2" size="30"/>
										<div class="help-icon" title="<?php _e( 'Rounds a decimal value to a specified number of decimal places.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-fontSize -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-fontSize"><?php _e( 'Font Size', 'dauc' ); ?></label></th>
									<td>
										<input value="12" type="text"
										       id="scales-rl-ticks-fontSize" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font size for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-fontColor -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-fontColor"><?php _e( 'Font Color', 'dauc' ); ?></label></th>
									<td>
										<input value="#666" type="text"
										       id="scales-rl-ticks-fontColor" maxlength="22" size="30"/>
										<input id="scales-rl-ticks-fontColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-rl-ticks-fontColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'Font color for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-fontFamily -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-fontFamily"><?php _e( 'Font Family', 'dauc' ); ?></label></th>
									<td>
										<input value="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif" type="text"
										       id="scales-rl-ticks-fontFamily" maxlength="200" size="30"/>
										<div class="help-icon" title="<?php _e( 'Font family for the tick labels.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-fontStyle -->
								<tr class="scales-tick-configuration-rl">
									<th scope="row"><label for="scales-rl-ticks-fontStyle"><?php _e( 'Font Style', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-fontStyle">
											<option value="normal"><?php _e( 'Normal', 'dauc' ); ?></option>
											<option value="bold"><?php _e( 'Bold', 'dauc' ); ?></option>
											<option value="italic"><?php _e( 'Italic', 'dauc' ); ?></option>
											<option value="oblique"><?php _e( 'Oblique', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'Font style for the tick labels.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- RL Scale Options ---------------------------------------------- -->

								<tr class="group-trigger" data-trigger-target="scales-configuration-options-rl">
									<th scope="row" class="group-title"><?php _e( 'RL Scale Options', 'dauc' ); ?></th>
									<td>
										<div class="expand-icon"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-min -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-min"><?php _e( 'Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-min" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The minimum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-max -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-max"><?php _e( 'Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-max" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'The maximum item to display.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-suggestedMin -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-suggestedMin"><?php _e( 'Suggested Min', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-suggestedMin" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Minimum number for the scale, overrides minimum value except for if it is higher than the minimum value.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-suggestedMax -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-suggestedMax"><?php _e( 'Suggested Max', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-suggestedMax" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number for the scale, overrides maximum value except for if it is lower than the maximum value.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-stepSize -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-stepSize"><?php _e( 'Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-stepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'If defined, it can be used along with the Min and Max to give a custom number of steps.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-fixedStepSize -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-fixedStepSize"><?php _e( 'Fixed Step Size', 'dauc' ); ?></label></th>
									<td>
										<input value="" type="text"
										       id="scales-rl-ticks-fixedStepSize" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Fixed step size for the scale. If set, the scale ticks will be enumerated by multiple of Step Size, having one tick per increment. If not set, the ticks are labeled automatically.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-maxTicksLimit -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-maxTicksLimit"><?php _e( 'Max Ticks Limit', 'dauc' ); ?></label></th>
									<td>
										<input value="11" type="text"
										       id="scales-rl-ticks-maxTicksLimit" maxlength="20" size="30"/>
										<div class="help-icon" title="<?php _e( 'Maximum number of ticks and grid lines to show. If not defined, it will limit to 11 ticks but will show all grid lines.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-beginAtZero -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-beginAtZero"><?php _e( 'Begin at Zero', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-beginAtZero">
											<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1" selected="selected"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the scale will include 0 if it is not already included.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-showLabelBackdrop -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-showLabelBackdrop"><?php _e( 'Show Label Backdrop', 'dauc' ); ?></label></th>
									<td>
										<select id="scales-rl-ticks-showLabelBackdrop">
											<option value="0" selected="selected"><?php _e( 'No', 'dauc' ); ?></option>
											<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
										</select>
										<div class="help-icon"
										     title='<?php _e( 'If enabled, the label backdrop will be displayed.', 'dauc' ); ?>'></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-backdropColor -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-backdropColor"><?php _e( 'Backdrop Color', 'dauc' ); ?></label></th>
									<td>
										<input value="rgba(255,255,255,0.75)" type="text"
										       id="scales-rl-ticks-backdropColor" maxlength="22" size="30"/>
										<input id="scales-rl-ticks-backdropColor-spectrum" class="spectrum-input" type="text">
										<div id="scales-rl-ticks-backdropColor-spectrum-toggle" class="spectrum-toggle"></div>
										<div class="help-icon" title="<?php _e( 'The backdrop color.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-backdropPaddingX -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-backdropPaddingX"><?php _e( 'Backdrop Padding X', 'dauc' ); ?></label></th>
									<td>
										<input value="2" type="text"
										       id="scales-rl-ticks-backdropPaddingX" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'The backdrop horizontal padding.', 'dauc' ); ?>"></div>
									</td>
								</tr>

								<!-- scales-rl-ticks-backdropPaddingY -->
								<tr class="scales-configuration-options-rl">
									<th scope="row"><label for="scales-rl-ticks-backdropPaddingY"><?php _e( 'Backdrop Padding Y', 'dauc' ); ?></label></th>
									<td>
										<input value="2" type="text"
										       id="scales-rl-ticks-backdropPaddingY" maxlength="6" size="30"/>
										<div class="help-icon" title="<?php _e( 'The backdrop vertical padding.', 'dauc' ); ?>"></div>
									</td>
								</tr>

							</table>

							<!-- Submit Button -->
							<div class="daext-form-action">
								<input id="save" class="button" type="submit" value="<?php _e( 'Add Chart', 'dauc' ); ?>">
							</div>

							<?php endif; ?>

						</div>

				<input id="chart-error-partial-message" type="hidden" value="<?php _e('Please enter valid values in the following fields:', 'dauc'); ?>">
				<div id="chart-error" class="error settings-error notice below-h2"><p></p></div>

			</div>

			<div class="sidebar-container">

				<div class="daext-form-container">

					<h3 class="daext-form-title" id="data-structure-title"><?php _e('Dataset', 'dauc'); ?>&nbsp1</h3>

					<table class="daext-form daext-form-data-structure" <?php if(isset($chart_obj->id)){ echo 'form-disabled="' . intval($chart_obj->is_model, 10) . '"';}else{echo 'form-disabled="0"';} ?>>

						<tbody>

						<!-- Row Index Hidden Field -->
						<input type="hidden" id="data-structure-row-index">

						<!-- Label -->
						<tr class="data-structure-property"
						    data-affected-types="line-bar-horizontalBar-radar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-label"><?php _e('Label', 'dauc'); ?></label></th>
							<td>
	                            <input maxlength="200" type="text" id="data-structure-label" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('The label for the dataset which appears in the legend and tooltips.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- fill -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-fill"><?php _e('Fill', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-fill"<?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
								</select>
								<div class="help-icon" title="<?php _e('If enabled, fill the area under the line.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- lineTension -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-lineTension"><?php _e('Line Tension', 'dauc'); ?></label></th>
							<td>
								<input maxlength="3" type="text" id="data-structure-lineTension" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('Bezier curve tension of the line. Use for example &quot;0.4&quot; to generate a curved line or leave &quot;0&quot; to generate a straight line.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- backgroundColor -->
						<tr class="data-structure-property"
						    data-affected-types="line-bar-horizontalBar-radar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-backgroundColor"><?php _e('Background Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-backgroundColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-backgroundColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-backgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title=""
								     data-title-line="<?php _e('A color that will be used to fill the space under the line.', 'dauc'); ?>"
								     data-title-bar="<?php _e('A color or a comma separated list of colors that will be used to fill the bars.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A color or a comma separated list of colors that will be used to fill the bars.', 'dauc'); ?>"
								     data-title-radar="<?php _e('A color that will be use to fill the space under the line.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A color or a comma separated list of colors that will be used to fill the arcs.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A color or a comma separated list of colors that will be used to fill the arcs.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A color or a comma separated list of colors that will be used to fill the arcs.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A color or a comma separated list of colors that will be used to fill the bubbles.', 'dauc'); ?>"
								></div>
							</td>
						</tr>

						<!-- borderColor -->
						<tr class="data-structure-property"
						    data-affected-types="line-bar-horizontalBar-radar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-borderColor"><?php _e('Border Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-borderColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-borderColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-borderColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="The color of the line."
								     data-title-line="<?php _e('A color that will be used to represent the line.', 'dauc'); ?>"
								     data-title-bar="<?php _e('A color or a comma separated list of colors that will be used to represent the bar border.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A color or a comma separated list of colors that will be used to represent the bar border.', 'dauc'); ?>"
								     data-title-radar="<?php _e('A color that will be used to represent the line.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A color or a comma separated list of colors that will be used to represent the arc border.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A color or a comma separated list of colors that will be used to represent the arc border.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A color or a comma separated list of colors that will be used to represent the arc border.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A color or a comma separated list of colors that will be used to represent the stroke of the bubbles.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- borderWidth -->
						<tr class="data-structure-property"
						    data-affected-types="line-bar-horizontalBar-radar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-borderWidth"><?php _e('Border Width', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-borderWidth" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title=""
								     data-title-line="<?php _e('A number used to define the width of the line.', 'dauc'); ?>"
								     data-title-bar="<?php _e('A number or a comma separated list of numbers used to define the border width of the bars.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A number or a comma separated list of numbers used to define the border width of the bars.', 'dauc'); ?>"
								     data-title-radar="<?php _e('A number used to define the width of the line.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A number or a comma separated list of numbers used to define the border width of the arcs.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A number or a comma separated list of numbers used to define the border width of the arcs.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A number or a comma separated list of numbers used to define the border width of the arcs.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A number or a comma separated list of numbers used to define the stroke width of the bubbles.', 'dauc'); ?>"
								></div>
							</td>
						</tr>

						<!-- borderDash -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-borderDash"><?php _e('Border Dash', 'dauc'); ?></label></th>
							<td>
								<input maxlength="200" type="text" id="data-structure-borderDash" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('A list of numbers separated by comma that specifies distances to alternately draw a line and a gap. Use for example &quot;5,10&quot; to generate a dashed line or leave &quot;0&quot; to generate a straight line.', 'dauc'); ?>"</div>
							</td>
						</tr>

						<!-- borderDashOffset -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-borderDashOffset"><?php _e('Border Dash Offset', 'dauc'); ?></label></th>
							<td>
								<input maxlength="6" type="text" id="data-structure-borderDashOffset" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('Offset for line dashes.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- borderCapStyle -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-borderCapStyle"><?php _e('Border Cap Style', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-borderCapStyle" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="butt"><?php _e('Butt', 'dauc'); ?></option>
									<option value="round"><?php _e('Round', 'dauc'); ?></option>
									<option value="square"><?php _e('Square', 'dauc'); ?></option>
								</select>
								<div class="help-icon"
								     title="<?php _e('Select the cap style of the line.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- borderJoinStyle -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-borderJoinStyle"><?php _e('Border Join Style', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-borderJoinStyle" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="miter"><?php _e('Miter', 'dauc'); ?></option>
									<option value="bevel"><?php _e('Bevel', 'dauc'); ?></option>
									<option value="round"><?php _e('Round', 'dauc'); ?></option>
								</select>
								<div class="help-icon"
								     title="<?php _e('Select the line join style.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointBackgroundColor -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointBackgroundColor"><?php _e('Point Background Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointBackgroundColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-pointBackgroundColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-pointBackgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="<?php _e('A color or a comma separated list of colors that will be used for the background color of the points.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointBorderColor -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointBorderColor"><?php _e('Point Border Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointBorderColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-pointBorderColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-pointBorderColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="<?php _e('A color or a comma separated list of colors that will be used for the border color of the points.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointBorderWidth -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointBorderWidth"><?php _e('Point Border Width', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointBorderWidth" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title="<?php _e('A number or a comma separated list of numbers used to define the border width of points.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointRadius -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointRadius"><?php _e('Point Radius', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointRadius" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('A number or a comma separated list of numbers used to define the radius of the points.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointHitRadius -->
						<tr class="data-structure-property" data-affected-types="line">
							<th scope="row"><label for="data-structure-pointHitRadius"><?php _e('Point Hit Radius', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointHitRadius"<?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('A number or a comma separated list of numbers used to define the pixel size of the non-displayed points that react to mouse events.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointStyle -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointStyle"><?php _e('Point Style', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointStyle" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('A point style or a comma separated list of point styles. The available options are &quot;circle&quot;, &quot;triangle&quot;, &quot;rect&quot;, &quot;rectRot&quot;, &quot;cross&quot;, &quot;crossRot&quot;, &quot;star&quot;, &quot;line&quot;, and &quot;dash&quot;.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointHoverRadius -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointHoverRadius"><?php _e('Point Hover Radius', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointHoverRadius" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title="<?php _e('A number or a comma separated list of numbers used to define the radius of the points when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointHoverBackgroundColor -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointHoverBackgroundColor"><?php _e('Point Hover Background Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointHoverBackgroundColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-pointHoverBackgroundColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-pointHoverBackgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="<?php _e('A color or a comma separated list of colors that will be used for the background color of the points when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointHoverBorderColor -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointHoverBorderColor"><?php _e('Point Hover Border Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointHoverBorderColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-pointHoverBorderColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-pointHoverBorderColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="<?php _e('A color or a comma separated list of colors that will be used for the border color of the points when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- pointHoverBorderWidth -->
						<tr class="data-structure-property" data-affected-types="line-radar">
							<th scope="row"><label for="data-structure-pointHoverBorderWidth"><?php _e('Point Hover Border Width', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-pointHoverBorderWidth" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title="<?php _e('A number or a comma separated list of numbers used to define the border width of the points when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- showLine -->
						<tr class="data-structure-property" data-affected-types="line">
							<th scope="row"><label for="data-structure-showLine"><?php _e('Show Line', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-showLine" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
								</select>
								<div class="help-icon" title="<?php _e('If enabled, the line will be drawn for this dataset.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- spanGaps -->
						<tr class="data-structure-property" data-affected-types="line">
							<th scope="row"><label for="data-structure-spanGaps"><?php _e('Span Gaps', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-spanGaps"<?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
								</select>
								<div class="help-icon"
								     title="<?php _e('If enabled, lines will be drawn between points with no data.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- hoverBackgroundColor -->
						<tr class="data-structure-property"
						    data-affected-types="bar-horizontalBar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-hoverBackgroundColor"><?php _e('Hover Background Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-hoverBackgroundColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-hoverBackgroundColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-hoverBackgroundColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="."
								     data-title-bar="<?php _e('A color or a comma separated list of colors that will be used for the background color of the bars when hovered.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A color or a comma separated list of colors that will be used for the background color of the bars when hovered.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A color or a comma separated list of colors that will be used for the background color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A color or a comma separated list of colors that will be used for the background color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A color or a comma separated list of colors that will be used for the background color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A color or a comma separated list of colors that will be used for the background color of the bubbles when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- hoverBorderColor -->
						<tr class="data-structure-property"
						    data-affected-types="bar-horizontalBar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-hoverBorderColor"><?php _e('Hover Border Color', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-hoverBorderColor" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<input id="data-structure-hoverBorderColor-spectrum" class="spectrum-input" type="text">
								<div id="data-structure-hoverBorderColor-spectrum-toggle" class="spectrum-toggle"></div>
								<div class="help-icon" title="."
								     data-title-bar="<?php _e('A color or a comma separated list of colors that will be used for the border color of the bars when hovered.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A color or a comma separated list of colors that will be used for the border color of the bars when hovered.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A color or a comma separated list of colors that will be used for the border color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A color or a comma separated list of colors that will be used for the border color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A color or a comma separated list of colors that will be used for the border color of the arcs when hovered.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A color or a comma separated list of colors that will be used for the border color of the bubbles when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- hoverBorderWidth -->
						<tr class="data-structure-property"
						    data-affected-types="bar-horizontalBar-polarArea-pie-doughnut-bubble">
							<th scope="row"><label for="data-structure-hoverBorderWidth"><?php _e('Hover Border Width', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-hoverBorderWidth" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title=""
								     data-title-bar="<?php _e('A number or a comma separated list of numbers used to define the border width of bars when hovered.', 'dauc'); ?>"
								     data-title-horizontalBar="<?php _e('A number or a comma separated list of numbers used to define the border width of bars when hovered.', 'dauc'); ?>"
								     data-title-polarArea="<?php _e('A number or a comma separated list of numbers used to define the border width of arcs when hovered.', 'dauc'); ?>"
								     data-title-pie="<?php _e('A number or a comma separated list of numbers used to define the border width of arcs when hovered.', 'dauc'); ?>"
								     data-title-doughnut="<?php _e('A number or a comma separated list of numbers used to define the border width of arcs when hovered.', 'dauc'); ?>"
								     data-title-bubble="<?php _e('A number or a comma separated list of numbers used to define the stroke width of the bubbles when hovered.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- hitRadius -->
						<tr class="data-structure-property" data-affected-types="radar">
							<th scope="row"><label for="data-structure-hitRadius"><?php _e('Hit Radius', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-hitRadius" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon"
								     title="<?php _e('A number or a comma separated list of numbers used to define the pixel size of the non-displayed points that react to mouse events.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- hoverRadius -->
						<tr class="data-structure-property" data-affected-types="bubble">
							<th scope="row"><label for="data-structure-hoverRadius"><?php _e('Hover Radius', 'dauc'); ?></label></th>
							<td>
								<input maxlength="65535" type="text" id="data-structure-hoverRadius" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
								<div class="help-icon" title="<?php _e('A number or a comma separated list of numbers used to define the additional radius added on hover.', 'dauc'); ?>"></div>
							</td>
						</tr>

						<!-- PlotY2 -->
						<tr class="data-structure-property" data-affected-types="line-bar-bubble">
							<th scope="row"><label for="data-structure-plotY2"><?php _e('Plot Y2', 'dauc'); ?></label></th>
							<td>
								<select id="data-structure-plotY2"<?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
									<option value="0"><?php _e( 'No', 'dauc' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'dauc' ); ?></option>
								</select>
								<div class="help-icon" title="<?php _e('This property determines if the dataset is plot on the Y2 axis.', 'dauc'); ?>"></div>
							</td>
						</tr>

						</tbody>

					</table>

					<!-- submit button -->
					<div class="daext-form-action">
						<input id="update-data-structure" class="button" type="submit" value="<?php _e( 'Update', 'dauc' ); ?>" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
						<input id="update-data-structure-and-globalize" class="button" type="submit" value="<?php _e( 'Update and Globalize', 'dauc' ); ?>" <?php if(isset($chart_obj->id)){$this->disable_model_input($chart_obj->id);} ?>>
					</div>

				</div>

				<input id="data-structure-error-partial-message" type="hidden" value="<?php _e('Please enter valid values in the following fields:', 'dauc'); ?>">
				<div id="data-structure-error" class="error settings-error notice below-h2"><p></p></div>
				<div id="data-structure-updated" class="updated settings-error notice below-h2"><p><?php _e('The options have been successfully updated.', 'dauc'); ?></p></div>


			</div>


		<?php endif; ?>

	</div>

    <?php

    /*
     * Set the maximum number of rows and columns based on the value of the 'post_max_size' PHP setting.
     *
     * These values will be used by the JavaScript part to limit the maximum number of rows and columns.
     */
    $post_max_size = intval(ini_get('post_max_size'), 10);

    if($post_max_size > 128){
        $rows_and_columns_limit = 1000;
    }elseif($post_max_size > 64 and $post_max_size <= 128){
        $rows_and_columns_limit = 800;
    }elseif($post_max_size > 32 and $post_max_size <= 64){
        $rows_and_columns_limit = 600;
    }elseif($post_max_size > 16 and $post_max_size <= 32){
        $rows_and_columns_limit = 400;
    }elseif($post_max_size > 8 and $post_max_size <= 16){
        $rows_and_columns_limit = 200;
    }else{
        $rows_and_columns_limit = 100;
    }

    echo '<input type="hidden" id="rows-and-columns-limit" data-rows-and-columns-limit="' . $rows_and_columns_limit . '">';

    ?>

</div>