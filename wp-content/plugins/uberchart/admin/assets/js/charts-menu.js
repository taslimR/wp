jQuery(document).ready(function($) {

    // -----------------------------------------------------------------------------------------------------------------

    //set global variables
    var dsu_timeout_handler;
    var dauc_hot;
    var dauc_data = [];
    var dauc_max_rows;
    var dauc_max_columns;

    // -----------------------------------------------------------------------------------------------------------------

    //document -> ready - EVENT LISTENER
    disable_ctrl_z();
    initialize_chosen();
    display_data_structure(1);
    set_type_based_titles();
    remove_border_last_cell_chart();
    initialize_spectrum();
    initialize_handsontable();
    responsive_sidebar_container();

    //#save -> click - EVENT LISTENER
    $('#save').click(function(){

        var validation_result = save_chart(true);

        //show error message
        if(validation_result !== true){
            $('#chart-error p').html($('#chart-error-partial-message').val() + ' <strong>' + validation_result.join(', ') + '</strong>');
            $('#chart-error').show();
        }

    });

    //#rows, #columns -> change - EVENT LISTENER
    $('#rows, #columns').change(function(){

        update_rows_and_columns();

    });

    //#update-data-structure -> click - EVENT LISTENER
    $('#update-data-structure').click(function(event){

        update_data_structure(false);

    });

    //#update-data-structure-and-globalize -> click - EVENT LISTENER
    $('#update-data-structure-and-globalize').click(function(event){

        update_data_structure(true);

    });

    //#type -> change - EVENT LISTENER
    $('#type').change(function(){

        remove_border_last_cell_chart()
        show_chart_specific_data_structure();
        set_type_based_titles();
        remove_border_last_cell_data_structure();

    });

    //.group-trigger -> click - EVENT LISTENER
    $('.group-trigger').click(function(){

        //open and close the various sections of the chart area
        var target = $(this).attr('data-trigger-target');
        $('.' + target).toggle(0);
        $(this).find('.expand-icon').toggleClass('arrow-down');

        remove_border_last_cell_chart();

    });

    //#load-model -> change - EVENT LISTENER
    $('#load-model').change(function(){

        //get data
        if( $('#temporary-chart-id').length ){
            var chart_id = $('#temporary-chart-id').val();
        }else{
            var chart_id = $('#update-id').val();
        }
        var model_id = parseInt($(this).val(), 10);

        //This is used to terminate the procedure in case "None" is selected
        if(model_id == 0){return;}

        //retrieve the chart data with an ajax request

        //prepare ajax request
        var data = {
            "action": "load_model",
            "security": dauc_nonce,
            "chart_id": chart_id,
            "model_id": model_id
        };

        //send ajax request
        $.post(dauc_ajax_url, data, function(answer_json) {

            var answer_obj = JSON.parse(answer_json);
            var chart = JSON.parse(answer_obj.chart);
            var data = JSON.parse(answer_obj.data);

            //fill the fields of the chart (except the data field) by using the 'chart' object -------------------------

            $('#name').val(chart.name);
            $('#description').val(chart.description);
            update_chosen_field('#type', chart.type);
            $('#rows').val(chart.rows);
            $('#columns').val(chart.columns);

            //Common Chart Configuration
            update_chosen_field('#canvas-transparent-background', chart.canvas_transparent_background);
            $('#canvas-backgroundColor').val(chart.canvas_backgroundColor);
            $('#margin-top').val(chart.margin_top);
            $('#margin-bottom').val(chart.margin_bottom);
            $('#width').val(chart.width);
            $('#height').val(chart.height);
            update_chosen_field('#responsive', chart.responsive);
            $('#responsiveAnimationDuration').val(chart.responsiveAnimationDuration);
            update_chosen_field('#maintainAspectRatio', chart.maintainAspectRatio);

            /*
             * Since fixed_height has been added in version 1.09 (and the old charts have the value of fixed_height
             * empty) set 0 if fixed_height is empty
             */
            if(chart.fixed_height.length == 0){
                $('#fixed-height').val(0);
            }else{
                $('#fixed-height').val(parseInt(chart.fixed_height, 10));
            }

            update_chosen_field('#is-model', 0);

            //Title Configuration
            update_chosen_field('#title-display', chart.title_display);
            update_chosen_field('#title-position', chart.title_position);
            update_chosen_field('#title-fullWidth', chart.title_fullWidth);
            $('#title-fontSize').val(chart.title_fontSize);
            $('#title-fontFamily').val(chart.title_fontFamily);
            $('#title-fontColor').val(chart.title_fontColor);
            update_chosen_field('#title-fontStyle', chart.title_fontStyle);
            $('#title-padding').val(chart.title_padding);

            //Legend Configuration
            update_chosen_field('#legend-display', chart.legend_display);
            update_chosen_field('#legend-position', chart.legend_position);
            update_chosen_field('#legend-fullWidth', chart.legend_fullWidth);
            update_chosen_field('#legend-toggle-dataset', chart.legend_toggle_dataset);
            $('#legend-labels-boxWidth').val(chart.legend_labels_boxWidth);
            $('#legend-labels-fontSize').val(chart.legend_labels_fontSize);
            update_chosen_field('#legend-labels-fontStyle', chart.legend_labels_fontStyle);
            $('#legend-labels-fontColor').val(chart.legend_labels_fontColor);
            $('#legend-labels-fontFamily').val(chart.legend_labels_fontFamily);
            $('#legend-labels-padding').val(chart.legend_labels_padding);

            //Tooltip Configuration
            update_chosen_field('#tooltips-enabled', chart.tooltips_enabled);
            update_chosen_field('#tooltips-mode', chart.tooltips_mode);
            $('#tooltips-backgroundColor').val(chart.tooltips_backgroundColor);
            $('#tooltips-titleFontFamily').val(chart.tooltips_titleFontFamily);
            $('#tooltips-titleFontSize').val(chart.tooltips_titleFontSize);
            update_chosen_field('#tooltips-titleFontStyle', chart.tooltips_titleFontStyle);
            $('#tooltips-titleFontStyle').val(chart.tooltips_titleFontStyle);
            $('#tooltips-titleFontColor').val(chart.tooltips_titleFontColor);
            $('#tooltips-titleMarginBottom').val(chart.tooltips_titleMarginBottom);
            $('#tooltips-bodyFontFamily').val(chart.tooltips_bodyFontFamily);
            $('#tooltips-bodyFontSize').val(chart.tooltips_bodyFontSize);
            update_chosen_field('#tooltips-bodyFontStyle', chart.tooltips_bodyFontStyle);
            $('#tooltips-bodyFontColor').val(chart.tooltips_bodyFontColor);
            $('#tooltips-footerFontFamily').val(chart.tooltips_footerFontFamily);
            $('#tooltips-footerFontSize').val(chart.tooltips_footerFontSize);
            update_chosen_field('#tooltips-footerFontStyle', chart.tooltips_footerFontStyle);
            $('#tooltips-footerFontColor').val(chart.tooltips_footerFontColor);
            $('#tooltips-footerMarginTop').val(chart.tooltips_footerMarginTop);
            $('#tooltips-xPadding').val(chart.tooltips_xPadding);
            $('#tooltips-yPadding').val(chart.tooltips_yPadding);
            $('#tooltips-caretSize').val(chart.tooltips_caretSize);
            $('#tooltips-cornerRadius').val(chart.tooltips_cornerRadius);
            $('#tooltips-multiKeyBackground').val(chart.tooltips_multiKeyBackground);
            $('#hover-animationDuration').val(chart.hover_animationDuration);
            $('#tooltips-beforeTitle').val(chart.tooltips_beforeTitle);
            $('#tooltips-afterTitle').val(chart.tooltips_afterTitle);
            $('#tooltips-beforeBody').val(chart.tooltips_beforeBody);
            $('#tooltips-afterBody').val(chart.tooltips_afterBody);
            $('#tooltips-beforeLabel').val(chart.tooltips_beforeLabel);
            $('#tooltips-afterLabel').val(chart.tooltips_afterLabel);
            $('#tooltips-beforeFooter').val(chart.tooltips_beforeFooter);
            $('#tooltips-footer').val(chart.tooltips_footer);
            $('#tooltips-afterFooter').val(chart.tooltips_afterFooter);

            //Animation Configuration
            $('#animation_duration').val(chart.animation_duration);
            update_chosen_field('#animation-easing', chart.animation_easing);
            update_chosen_field('#animation-animateRotate', chart.animateRotate);
            update_chosen_field('#animation-animateScale', chart.animation_animateScale);

            //Misc Configuration
            update_chosen_field('#elements-rectangle-borderSkipped', chart.elements_rectangle_borderSkipped);

            //Scales Common Configuration X
            update_chosen_field('#scales-xAxes-type', chart.scales_xAxes_type);
            update_chosen_field('#scales-xAxes-display', chart.scales_xAxes_display);
            update_chosen_field('#scales-xAxes-position', chart.scales_xAxes_position);
            update_chosen_field('#scales-xAxes-stacked', chart.scales_xAxes_stacked);

            //Scales Grid Line Configuration X
            update_chosen_field('#scales-xAxes-gridLines-display', chart.scales_xAxes_gridLines_display);
            $('#scales-xAxes-gridLines-color').val(chart.scales_xAxes_gridLines_color);
            $('#scales-xAxes-gridLines-lineWidth').val(chart.scales_xAxes_gridLines_lineWidth);
            update_chosen_field('#scales-xAxes-gridLines-drawBorder', chart.scales_xAxes_gridLines_drawBorder);
            update_chosen_field('#scales-xAxes-gridLines-drawOnChartArea', chart.scales_xAxes_gridLines_drawOnChartArea);
            update_chosen_field('#scales-xAxes-gridLines-drawTicks', chart.scales_xAxes_gridLines_drawTicks);
            $('#scales-xAxes-gridLines-tickMarkLength').val(chart.scales_xAxes_gridLines_tickMarkLength);
            $('#scales-xAxes-gridLines-zeroLineWidth').val(chart.scales_xAxes_gridLines_zeroLineWidth);
            $('#scales-xAxes-gridLines-zeroLineColor').val(chart.scales_xAxes_gridLines_zeroLineColor);
            update_chosen_field('#scales-xAxes-gridLines-offsetGridLines', chart.scales_xAxes_gridLines_offsetGridLines);

            //Scales Title Configuration X
            update_chosen_field('#scales-xAxes-scaleLabel-display', chart.scales_xAxes_scaleLabel_display);
            $('#scales-xAxes-scaleLabel-labelString').val(chart.scales_xAxes_scaleLabel_labelString);
            $('#scales-xAxes-scaleLabel-fontColor').val(chart.scales_xAxes_scaleLabel_fontColor);
            $('#scales-xAxes-scaleLabel-fontFamily').val(chart.scales_xAxes_scaleLabel_fontFamily);
            $('#scales-xAxes-scaleLabel-fontSize').val(chart.scales_xAxes_scaleLabel_fontSize);
            update_chosen_field('#scales-xAxes-scaleLabel-fontStyle', chart.scales_xAxes_scaleLabel_fontStyle);

            //Scales Tick Configuration X
            update_chosen_field('#scales-xAxes-ticks-autoskip', chart.scales_xAxes_ticks_autoskip);
            update_chosen_field('#scales-xAxes-ticks-display', chart.scales_xAxes_ticks_display);
            $('#scales-xAxes-ticks-fontColor').val(chart.scales_xAxes_ticks_fontColor);
            $('#scales-xAxes-ticks-fontFamily').val(chart.scales_xAxes_ticks_fontFamily);
            $('#scales-xAxes-ticks-fontSize').val(chart.scales_xAxes_ticks_fontSize);
            update_chosen_field('#scales-xAxes-ticks-fontStyle', chart.scales_xAxes_ticks_fontStyle);
            $('#scales-xAxes-ticks-labelOffset').val(chart.scales_xAxes_ticks_labelOffset);
            $('#scales-xAxes-ticks-maxRotation').val(chart.scales_xAxes_ticks_maxRotation);
            $('#scales-xAxes-ticks-minRotation').val(chart.scales_xAxes_ticks_minRotation);
            update_chosen_field('#scales-xAxes-ticks-reverse', chart.scales_xAxes_ticks_reverse);
            $('#scales-xAxes-ticks-prefix').val(chart.scales_xAxes_ticks_prefix);
            $('#scales-xAxes-ticks-suffix').val(chart.scales_xAxes_ticks_suffix);
            $('#scales-xAxes-ticks-round').val(chart.scales_xAxes_ticks_round);

            //Scale Configuration Options X
            $('#scales-xAxes-ticks-min').val(chart.scales_xAxes_ticks_min);
            $('#scales-xAxes-ticks-max').val(chart.scales_xAxes_ticks_max);
            update_chosen_field('#scales-xAxes-ticks-beginAtZero', chart.scales_xAxes_ticks_beginAtZero);
            $('#scales-xAxes-ticks-maxTicksLimit').val(chart.scales_xAxes_ticks_maxTicksLimit);
            $('#scales-xAxes-ticks-stepSize').val(chart.scales_xAxes_ticks_stepSize);
            $('#scales-xAxes-ticks-suggestedMax').val(chart.scales_xAxes_ticks_suggestedMax);
            $('#scales-xAxes-ticks-suggestedMin').val(chart.scales_xAxes_ticks_suggestedMin);
            $('#scales-xAxes-ticks-fixedStepSize').val(chart.scales_xAxes_ticks_fixedStepSize);
            $('#scales-xAxes-categoryPercentage').val(chart.scales_xAxes_categoryPercentage);
            $('#scales-xAxes-barPercentage').val(chart.scales_xAxes_barPercentage);

            //Time Scale Configuration Options X
            $('#scales-xAxes-time-format').val(chart.scales_xAxes_time_format);
            $('#scales-xAxes-time-tooltipFormat').val(chart.scales_xAxes_time_tooltipFormat);
            $('#scales-xAxes-time-unit-format').val(chart.scales_xAxes_time_unit_format);
            update_chosen_field('#scales-xAxes-time-unit', chart.scales_xAxes_time_unit);
            $('#scales-xAxes-time-unitStepSize').val(chart.scales_xAxes_time_unitStepSize);
            $('#scales-xAxes-time-max').val(chart.scales_xAxes_time_max);
            $('#scales-xAxes-time-min').val(chart.scales_xAxes_time_min);

            //Scales Common Configuration Y
            update_chosen_field('#scales-yAxes-type', chart.scales_yAxes_type);
            update_chosen_field('#scales-yAxes-display', chart.scales_yAxes_display);
            update_chosen_field('#scales-yAxes-position', chart.scales_yAxes_position);
            update_chosen_field('#scales-yAxes-stacked', chart.scales_yAxes_stacked);

            //Scales Grid Line Configuration Y
            update_chosen_field('#scales-yAxes-gridLines-display', chart.scales_yAxes_gridLines_display);
            $('#scales-yAxes-gridLines-color').val(chart.scales_yAxes_gridLines_color);
            $('#scales-yAxes-gridLines-lineWidth').val(chart.scales_yAxes_gridLines_lineWidth);
            update_chosen_field('#scales-yAxes-gridLines-drawBorder', chart.scales_yAxes_gridLines_drawBorder);
            update_chosen_field('#scales-yAxes-gridLines-drawOnChartArea', chart.scales_yAxes_gridLines_drawOnChartArea);
            update_chosen_field('#scales-yAxes-gridLines-drawTicks', chart.scales_yAxes_gridLines_drawTicks);
            $('#scales-yAxes-gridLines-tickMarkLength').val(chart.scales_yAxes_gridLines_tickMarkLength);
            $('#scales-yAxes-gridLines-zeroLineWidth').val(chart.scales_yAxes_gridLines_zeroLineWidth);
            $('#scales-yAxes-gridLines-zeroLineColor').val(chart.scales_yAxes_gridLines_zeroLineColor);
            update_chosen_field('#scales-yAxes-gridLines-offsetGridLines', chart.scales_yAxes_gridLines_offsetGridLines);
            $('#scales-yAxes-gridLines-offsetGridLines').val(chart.scales_yAxes_gridLines_offsetGridLines);

            //Scales Title Configuration Y
            update_chosen_field('#scales-yAxes-scaleLabel-display', chart.scales_yAxes_scaleLabel_display);
            $('#scales-yAxes-scaleLabel-labelString').val(chart.scales_yAxes_scaleLabel_labelString);
            $('#scales-yAxes-scaleLabel-fontColor').val(chart.scales_yAxes_scaleLabel_fontColor);
            $('#scales-yAxes-scaleLabel-fontFamily').val(chart.scales_yAxes_scaleLabel_fontFamily);
            $('#scales-yAxes-scaleLabel-fontSize').val(chart.scales_yAxes_scaleLabel_fontSize);
            update_chosen_field('#scales-yAxes-scaleLabel-fontStyle', chart.scales_yAxes_scaleLabel_fontStyle);

            //Scales Tick Configuration Y
            update_chosen_field('#scales-yAxes-ticks-autoskip', chart.scales_yAxes_ticks_autoskip);
            update_chosen_field('#scales-yAxes-ticks-display', chart.scales_yAxes_ticks_display);
            $('#scales-yAxes-ticks-fontColor').val(chart.scales_yAxes_ticks_fontColor);
            $('#scales-yAxes-ticks-fontFamily').val(chart.scales_yAxes_ticks_fontFamily);
            $('#scales-yAxes-ticks-fontSize').val(chart.scales_yAxes_ticks_fontSize);
            update_chosen_field('#scales-yAxes-ticks-fontStyle', chart.scales_yAxes_ticks_fontStyle);
            $('#scales-yAxes-ticks-maxRotation').val(chart.scales_yAxes_ticks_maxRotation);
            $('#scales-yAxes-ticks-minRotation').val(chart.scales_yAxes_ticks_minRotation);
            update_chosen_field('#scales-yAxes-ticks-mirror', chart.scales_yAxes_ticks_mirror);
            $('#scales-yAxes-ticks-padding').val(chart.scales_yAxes_ticks_padding);
            update_chosen_field('#scales-yAxes-ticks-reverse', chart.scales_yAxes_ticks_reverse);
            $('#scales-yAxes-ticks-prefix').val(chart.scales_yAxes_ticks_prefix);
            $('#scales-yAxes-ticks-suffix').val(chart.scales_yAxes_ticks_suffix);
            $('#scales-yAxes-ticks-round').val(chart.scales_yAxes_ticks_round);

            //Scale Configuration Options Y
            $('#scales-yAxes-ticks-min').val(chart.scales_yAxes_ticks_min);
            $('#scales-yAxes-ticks-max').val(chart.scales_yAxes_ticks_max);
            update_chosen_field('#scales-yAxes-ticks-beginAtZero', chart.scales_yAxes_ticks_beginAtZero);
            $('#scales-yAxes-ticks-maxTicksLimit').val(chart.scales_yAxes_ticks_maxTicksLimit);
            $('#scales-yAxes-ticks-stepSize').val(chart.scales_yAxes_ticks_stepSize);
            $('#scales-yAxes-ticks-suggestedMax').val(chart.scales_yAxes_ticks_suggestedMax);
            $('#scales-yAxes-ticks-suggestedMin').val(chart.scales_yAxes_ticks_suggestedMin);
            $('#scales-yAxes-ticks-fixedStepSize').val(chart.scales_yAxes_ticks_fixedStepSize);
            $('#scales-yAxes-categoryPercentage').val(chart.scales_yAxes_categoryPercentage);
            $('#scales-yAxes-barPercentage').val(chart.scales_yAxes_barPercentage);

            //Scales Common Configuration Y2
            update_chosen_field('#scales-y2Axes-type', chart.scales_y2Axes_type);
            update_chosen_field('#scales-y2Axes-display', chart.scales_y2Axes_display);
            update_chosen_field('#scales-y2Axes-position', chart.scales_y2Axes_position);

            //Scales Grid Line Configuration Y2
            update_chosen_field('#scales-y2Axes-gridLines-display', chart.scales_y2Axes_gridLines_display);
            $('#scales-y2Axes-gridLines-color').val(chart.scales_y2Axes_gridLines_color);
            $('#scales-y2Axes-gridLines-lineWidth').val(chart.scales_y2Axes_gridLines_lineWidth);
            update_chosen_field('#scales-y2Axes-gridLines-drawBorder', chart.scales_y2Axes_gridLines_drawBorder);
            update_chosen_field('#scales-y2Axes-gridLines-drawOnChartArea', chart.scales_y2Axes_gridLines_drawOnChartArea);
            update_chosen_field('#scales-y2Axes-gridLines-drawTicks', chart.scales_y2Axes_gridLines_drawTicks);
            $('#scales-y2Axes-gridLines-tickMarkLength').val(chart.scales_y2Axes_gridLines_tickMarkLength);
            $('#scales-y2Axes-gridLines-zeroLineWidth').val(chart.scales_y2Axes_gridLines_zeroLineWidth);
            $('#scales-y2Axes-gridLines-zeroLineColor').val(chart.scales_y2Axes_gridLines_zeroLineColor);
            update_chosen_field('#scales-y2Axes-gridLines-offsetGridLines', chart.scales_y2Axes_gridLines_offsetGridLines);

            //Scales Title Configuration Y2
            update_chosen_field('#scales-y2Axes-scaleLabel-display', chart.scales_y2Axes_scaleLabel_display);
            $('#scales-y2Axes-scaleLabel-labelString').val(chart.scales_y2Axes_scaleLabel_labelString);
            $('#scales-y2Axes-scaleLabel-fontColor').val(chart.scales_y2Axes_scaleLabel_fontColor);
            $('#scales-y2Axes-scaleLabel-fontFamily').val(chart.scales_y2Axes_scaleLabel_fontFamily);
            $('#scales-y2Axes-scaleLabel-fontSize').val(chart.scales_y2Axes_scaleLabel_fontSize);
            update_chosen_field('#scales-y2Axes-scaleLabel-fontStyle', chart.scales_y2Axes_scaleLabel_fontStyle);

            //Scales Tick Configuration Y2
            update_chosen_field('#scales-y2Axes-ticks-autoskip', chart.scales_y2Axes_ticks_autoskip);
            update_chosen_field('#scales-y2Axes-ticks-display', chart.scales_y2Axes_ticks_display);
            $('#scales-y2Axes-ticks-fontColor').val(chart.scales_y2Axes_ticks_fontColor);
            $('#scales-y2Axes-ticks-fontFamily').val(chart.scales_y2Axes_ticks_fontFamily);
            $('#scales-y2Axes-ticks-fontSize').val(chart.scales_y2Axes_ticks_fontSize);
            update_chosen_field('#scales-y2Axes-ticks-fontStyle', chart.scales_y2Axes_ticks_fontStyle);
            $('#scales-y2Axes-ticks-maxRotation').val(chart.scales_y2Axes_ticks_maxRotation);
            $('#scales-y2Axes-ticks-minRotation').val(chart.scales_y2Axes_ticks_minRotation);
            update_chosen_field('#scales-y2Axes-ticks-mirror', chart.scales_y2Axes_ticks_mirror);
            $('#scales-y2Axes-ticks-padding').val(chart.scales_y2Axes_ticks_padding);
            update_chosen_field('#scales-y2Axes-ticks-reverse', chart.scales_y2Axes_ticks_reverse);
            $('#scales-y2Axes-ticks-prefix').val(chart.scales_y2Axes_ticks_prefix);
            $('#scales-y2Axes-ticks-suffix').val(chart.scales_y2Axes_ticks_suffix);
            $('#scales-y2Axes-ticks-round').val(chart.scales_y2Axes_ticks_round);

            //Scale Configuration Options Y2
            $('#scales-y2Axes-ticks-min').val(chart.scales_y2Axes_ticks_min);
            $('#scales-y2Axes-ticks-max').val(chart.scales_y2Axes_ticks_max);
            update_chosen_field('#scales-y2Axes-ticks-beginAtZero', chart.scales_y2Axes_ticks_beginAtZero);
            $('#scales-y2Axes-ticks-maxTicksLimit').val(chart.scales_y2Axes_ticks_maxTicksLimit);
            $('#scales-y2Axes-ticks-stepSize').val(chart.scales_y2Axes_ticks_stepSize);
            $('#scales-y2Axes-ticks-suggestedMax').val(chart.scales_y2Axes_ticks_suggestedMax);
            $('#scales-y2Axes-ticks-suggestedMin').val(chart.scales_y2Axes_ticks_suggestedMin);
            $('#scales-y2Axes-ticks-fixedStepSize').val(chart.scales_y2Axes_ticks_fixedStepSize);

            /* RL Scale Common Configuration */
            update_chosen_field('#scales-rl-display', chart.scales_rl_display);

            /* RL Scale Grid Line Configuration */
            update_chosen_field('#scales-rl-gridLines-display', chart.scales_rl_gridLines_display)
            $('#scales-rl-gridLines-color').val(chart.scales_rl_gridLines_color);
            $('#scales-rl-gridLines-lineWidth').val(chart.scales_rl_gridLines_lineWidth);

            /* RL Scale Angle Line Configuration */
            update_chosen_field('#scales-rl-angleLines-display', chart.scales_rl_angleLines_display);
            $('#scales-rl-angleLines-color').val(chart.scales_rl_angleLines_color);
            $('#scales-rl-angleLines-lineWidth').val(chart.scales_rl_angleLines_lineWidth);

            /* RL Scale Point Label Configuration */
            $('#scales-rl-pointLabels-fontSize').val(chart.scales_rl_pointLabels_fontSize);
            $('#scales-rl-pointLabels-fontColor').val(chart.scales_rl_pointLabels_fontColor);
            $('#scales-rl-pointLabels-fontFamily').val(chart.scales_rl_pointLabels_fontFamily);
            $('#scales-rl-pointLabels-fontStyle').val(chart.scales_rl_pointLabels_fontStyle);

            /* RL Scale Tick Configuration */
            update_chosen_field('#scales-rl-ticks-display', chart.scales_rl_ticks_display);
            update_chosen_field('#scales-rl-ticks-autoskip', chart.scales_rl_ticks_autoskip);
            update_chosen_field('#scales-rl-ticks-reverse', chart.scales_rl_ticks_reverse);
            $('#scales-rl-ticks-prefix').val(chart.scales_rl_ticks_prefix);
            $('#scales-rl-ticks-suffix').val(chart.scales_rl_ticks_suffix);
            $('#scales-rl-ticks-round').val(chart.scales_rl_ticks_round);
            $('#scales-rl-ticks-fontSize').val(chart.scales_rl_ticks_fontSize);
            $('#scales-rl-ticks-fontColor').val(chart.scales_rl_ticks_fontColor);
            $('#scales-rl-ticks-fontFamily').val(chart.scales_rl_ticks_fontFamily);
            $('#scales-rl-ticks-fontStyle').val(chart.scales_rl_ticks_fontStyle);

            /* RL Scale Configuration Options */
            $('#scales-rl-ticks-min').val(chart.scales_rl_ticks_min);
            $('#scales-rl-ticks-max').val(chart.scales_rl_ticks_max);
            $('#scales-rl-ticks-suggestedMin').val(chart.scales_rl_ticks_suggestedMin);
            $('#scales-rl-ticks-suggestedMax').val(chart.scales_rl_ticks_suggestedMax);
            $('#scales-rl-ticks-stepSize').val(chart.scales_rl_ticks_stepSize);
            $('#scales-rl-ticks-fixedStepSize').val(chart.scales_rl_ticks_fixedStepSize);
            $('#scales-rl-ticks-maxTicksLimit').val(chart.scales_rl_ticks_maxTicksLimit);
            $('#scales-rl-ticks-beginAtZero').val(chart.scales_rl_ticks_beginAtZero);
            update_chosen_field('#scales-rl-ticks-showLabelBackdrop', chart.scales_rl_ticks_showLabelBackdrop);
            $('#scales-rl-ticks-backdropColor').val(chart.scales_rl_ticks_backdropColor);
            $('#scales-rl-ticks-backdropPaddingX').val(chart.scales_rl_ticks_backdropPaddingX);
            $('#scales-rl-ticks-backdropPaddingY').val(chart.scales_rl_ticks_backdropPaddingY);

            /*
             Fill the fields of the data structure associated with the first row with the data in the first object
             (data[0]) available in the 'data' array of objects (each object represents a row)
             */
            var data_obj = data[0];
            $('#data-structure-title').text('Dataset ' + ( parseInt(data_obj.row_index, 10) ));
            $("#data-structure-row-index").val(data_obj.row_index);
            $("#data-structure-label").val(data_obj.label);
            update_chosen_field("#data-structure-fill", data_obj.fill);
            $("#data-structure-lineTension").val(data_obj.lineTension);
            $("#data-structure-backgroundColor").val(data_obj.backgroundColor);
            $("#data-structure-borderWidth").val(data_obj.borderWidth);
            $("#data-structure-borderColor").val(data_obj.borderColor);
            update_chosen_field("#data-structure-borderCapStyle", data_obj.borderCapStyle);
            $("#data-structure-borderDash").val(data_obj.borderDash);
            $("#data-structure-borderDashOffset").val(data_obj.borderDashOffset);
            update_chosen_field("#data-structure-borderJoinStyle", data_obj.borderJoinStyle);
            $("#data-structure-pointBorderColor").val(data_obj.pointBorderColor);
            $("#data-structure-pointBackgroundColor").val(data_obj.pointBackgroundColor);
            $("#data-structure-pointBorderWidth").val(data_obj.pointBorderWidth);
            $("#data-structure-pointRadius").val(data_obj.pointRadius);
            $("#data-structure-pointHoverRadius").val(data_obj.pointHoverRadius);
            $("#data-structure-pointHitRadius").val(data_obj.pointHitRadius);
            $("#data-structure-pointHoverBackgroundColor").val(data_obj.pointHoverBackgroundColor);
            $("#data-structure-pointHoverBorderColor").val(data_obj.pointHoverBorderColor);
            $("#data-structure-pointHoverBorderWidth").val(data_obj.pointHoverBorderWidth);
            $("#data-structure-pointStyle").val(data_obj.pointStyle);
            update_chosen_field("#data-structure-showLine", data_obj.showLine);
            update_chosen_field("#data-structure-spanGaps", data_obj.spanGaps);
            $("#data-structure-hoverBackgroundColor").val(data_obj.hoverBackgroundColor);
            $("#data-structure-hoverBorderColor").val(data_obj.hoverBorderColor);
            $("#data-structure-hoverBorderWidth").val(data_obj.hoverBorderWidth);
            $("#data-structure-hitRadius").val(data_obj.hitRadius);
            $("#data-structure-hoverRadius").val(data_obj.hoverRadius);
            update_chosen_field("#data-structure-plotY2", data_obj.plotY2);

            show_chart_specific_data_structure();
            remove_border_last_cell_data_structure();

            //set the new maximum number of rows and columns
            dauc_hot.updateSettings({
                maxRows: parseInt(chart.rows, 10) + 1,
                maxCols: chart.columns
            });

            //set the number of rows
            if( dauc_hot.countRows() - 1 < chart.rows ){

                while ( dauc_hot.countRows() - 1 < chart.rows){
                    dauc_hot.alter('insert_row');
                }

            }else if( dauc_hot.countRows() - 1 > chart.rows ){

                while (dauc_hot.countRows() - 1 > chart.rows){
                    dauc_hot.alter('remove_row');
                }

            }

            //set the number of columns
            if( dauc_hot.countCols() < chart.columns ){

                do {
                    dauc_hot.alter('insert_col');
                }
                while (dauc_hot.countCols() < chart.columns);

            }else if( dauc_hot.countCols() > chart.columns ){

                do {
                    dauc_hot.alter('remove_col');
                }
                while (dauc_hot.countCols() > chart.columns);

            }

            //add the labels on the first row of the handsontable table
            var labels = JSON.parse(chart.labels);
            $.each(labels, function( index, value ) {
                dauc_data[0][index] = value;
            });

            //add the data (from the second row to the bottom) to the handsontable table
            $.each(data, function( index, value ) {

                var row_obj = JSON.parse(value.content);
                $.each(row_obj, function( index2, value2 ) {

                    //actual index is used to start from the second row (and skip the first row, used for the labels)
                    var actual_index = parseInt(index, 10) + 1;

                    dauc_data[actual_index][index2] = value2;

                });

            });

            //render the changes on the data made on the data reference
            dauc_hot.render();

        });

    });

    //#save-and-refresh -> click - EVENT LISTENER
    $('#save-and-refresh').click(function(){

        save_and_refresh();

    });

    //tr[data-trigger-target="chart-preview"] -> click - EVENT LISTENER
    $('tr[data-trigger-target="chart-preview"]').click(function(){

        if( $('.chart-preview').css('display') == 'table-row' ){

            save_and_refresh();

        }else{

            $( '#chart-preview-iframe').detach();

        }

    });

    //window -> resize - EVENT LISTENER
    jQuery(window).resize(function(){

        responsive_sidebar_container();

    });

    // -----------------------------------------------------------------------------------------------------------------

    /*
    Update the number of rows and columns on the handsontable and on the "data" db table
     */
    function update_rows_and_columns(){

        //update the number of rows and columns of the table -----------------------------------------------------------

        //get the chart id
        if( $('#temporary-chart-id').length ){
            var chart_id = $('#temporary-chart-id').val();
            var is_update = false;
        }else{
            var chart_id = $('#update-id').val();
            var is_update = true;
        }

        //get the rows and columns limit
        var rows_and_columns_limit = parseInt($('#rows-and-columns-limit').attr('data-rows-and-columns-limit'), 10);

        //change the number of rows
        var current_number_of_rows = dauc_hot.countRows() - 1;
        var new_number_of_rows = parseInt($('#rows').val(), 10);
        if( isNaN(new_number_of_rows) || new_number_of_rows < 1 ){
            new_number_of_rows = 1;
            $('#rows').val(1);
        }

        //Do not allow to enter more rows than the ones defined with in the rows_and_columns_limit
        if( new_number_of_rows > rows_and_columns_limit ){
            new_number_of_rows = rows_and_columns_limit;
            $('#rows').val(rows_and_columns_limit);
        }

        //set the new maximum number of rows
        dauc_hot.updateSettings({
            maxRows: (new_number_of_rows + 1)
        });

        if(new_number_of_rows > current_number_of_rows){

            var cells_to_add = [];
            var row_difference = new_number_of_rows - current_number_of_rows;
            var count_rows_result = dauc_hot.countRows();
            var count_cols_result = dauc_hot.countCols();

            for(i=1;i<=row_difference;i++){

                //initialize with 0 all the cells of the new row
                for(t=1;t<=count_cols_result;t++){
                    cells_to_add.push([count_rows_result+i-1,(t-1), 0]);
                }

            }

            //create the new rows
            dauc_hot.alter('insert_row', null, row_difference);

            //use the setDataAtCell() method one single time with a two dimensional array to avoid performance issues
            dauc_hot.setDataAtCell(cells_to_add);

        }else if(new_number_of_rows < current_number_of_rows){

            var row_difference = current_number_of_rows - new_number_of_rows;

            dauc_hot.alter('remove_row', null, row_difference);

        }

        //create or remove the new rows in the 'data' db table with an asynchronous ajax request -----------------------
        if(new_number_of_rows != current_number_of_rows){

            var data = {
                "action": "add_remove_rows",
                "security": dauc_nonce,
                "chart_id": chart_id,
                "current_number_of_rows": current_number_of_rows,
                "new_number_of_rows": new_number_of_rows,
                "current_number_of_columns": dauc_hot.countCols(),
            };

            //set ajax in synchronous mode
            jQuery.ajaxSetup({async:false});

            //send ajax request
            $.post(dauc_ajax_url, data, function(data_json) {});

            //set ajax in asynchronous mode
            jQuery.ajaxSetup({async:true});

        }

        //change the number of columns ---------------------------------------------------------------------------------
        var current_number_of_columns = dauc_hot.countCols();
        var new_number_of_columns = parseInt($('#columns').val(), 10);
        if( isNaN(new_number_of_columns) || new_number_of_columns < 1 ){
            new_number_of_columns = 1;
            $('#columns').val(1);
        }

        //Do not allow to enter more columns than the ones defined with in the rows_and_columns_limit
        if( new_number_of_columns > rows_and_columns_limit ){
            new_number_of_columns = rows_and_columns_limit;
            $('#columns').val(rows_and_columns_limit);
        }

        //set the new maximum number of columns
        dauc_hot.updateSettings({
            maxCols: new_number_of_columns
        });

        if(new_number_of_columns > current_number_of_columns){

            //add the new columns
            var cells_to_add = [];
            var column_difference = new_number_of_columns-current_number_of_columns;
            var count_rows_result = dauc_hot.countRows();
            var count_cols_result = dauc_hot.countCols();

            for(i=1;i<=column_difference;i++){

                for(t=1;t<=count_rows_result;t++){

                    if(t==1){
                        //in row 1 add the default label text
                        cells_to_add.push([0, (count_cols_result+i-1), 'Label ' + parseInt(count_cols_result+i, 10)]);
                    }else{
                        //from row 2 initialize with 0 all the cells of the new column
                        cells_to_add.push([(t-1), (count_cols_result+i-1), 0]);
                    }

                }

            }

            //create the new columns
            dauc_hot.alter('insert_col', null, column_difference);

            //use the setDataAtCell() method one single time with a two dimensional array to avoid performance issues
            dauc_hot.setDataAtCell(cells_to_add);

        }else if(new_number_of_columns < current_number_of_columns){

            var column_difference = current_number_of_columns-new_number_of_columns;
            dauc_hot.alter('remove_col', null, column_difference);

        }

        //create or remove the new columns in the 'data' db table with an asynchronous ajax request --------------------
        if(new_number_of_columns != current_number_of_columns){

            var data = {
                "action": "add_remove_columns",
                "security": dauc_nonce,
                "chart_id": chart_id,
                "new_number_of_columns": new_number_of_columns
            };

            //set ajax in synchronous mode
            jQuery.ajaxSetup({async:false});

            //send ajax request
            $.post(dauc_ajax_url, data, function(data_json) {

            });

            //set ajax in asynchronous mode
            jQuery.ajaxSetup({async:true});

        }

    }

    /*
    Fill the fields in the dataset section
     */
    function display_data_structure(row){

        if( $('#temporary-chart-id').length ){
            var chart_id = $('#temporary-chart-id').val();
        }else{
            var chart_id = $('#update-id').val();
        }

        //prepare ajax request
        var data = {
            "action": "retrieve_row_data",
            "security": dauc_nonce,
            "chart_id": chart_id,
            "row": row
        };

        //send ajax request
        $.post(dauc_ajax_url, data, function(data_json) {

            var data_obj = JSON.parse(data_json);

            $('#data-structure-title').text('Dataset ' + parseInt(data_obj.row_index, 10) );
            $("#data-structure-row-index").val(data_obj.row_index);
            $("#data-structure-label").val(data_obj.label);
            update_chosen_field("#data-structure-fill", data_obj.fill);
            $("#data-structure-lineTension").val(data_obj.lineTension);
            $("#data-structure-backgroundColor").val(data_obj.backgroundColor);
            $("#data-structure-borderWidth").val(data_obj.borderWidth);
            $("#data-structure-borderColor").val(data_obj.borderColor);
            update_chosen_field("#data-structure-borderCapStyle", data_obj.borderCapStyle);
            $("#data-structure-borderDash").val(data_obj.borderDash);
            $("#data-structure-borderDashOffset").val(data_obj.borderDashOffset);
            update_chosen_field("#data-structure-borderJoinStyle", data_obj.borderJoinStyle);
            $("#data-structure-pointBorderColor").val(data_obj.pointBorderColor);
            $("#data-structure-pointBackgroundColor").val(data_obj.pointBackgroundColor);
            $("#data-structure-pointBorderWidth").val(data_obj.pointBorderWidth);
            $("#data-structure-pointRadius").val(data_obj.pointRadius);
            $("#data-structure-pointHoverRadius").val(data_obj.pointHoverRadius);
            $("#data-structure-pointHitRadius").val(data_obj.pointHitRadius);
            $("#data-structure-pointHoverBackgroundColor").val(data_obj.pointHoverBackgroundColor);
            $("#data-structure-pointHoverBorderColor").val(data_obj.pointHoverBorderColor);
            $("#data-structure-pointHoverBorderWidth").val(data_obj.pointHoverBorderWidth);
            $("#data-structure-pointStyle").val(data_obj.pointStyle);
            update_chosen_field("#data-structure-showLine", data_obj.showLine);
            update_chosen_field("#data-structure-spanGaps", data_obj.spanGaps);
            $("#data-structure-hoverBackgroundColor").val(data_obj.hoverBackgroundColor);
            $("#data-structure-hoverBorderColor").val(data_obj.hoverBorderColor);
            $("#data-structure-hoverBorderWidth").val(data_obj.hoverBorderWidth);
            $("#data-structure-hitRadius").val(data_obj.hitRadius);
            $("#data-structure-hoverRadius").val(data_obj.hoverRadius);
            update_chosen_field("#data-structure-plotY2", data_obj.plotY2);

            show_chart_specific_data_structure();
            remove_border_last_cell_data_structure();
            $('#data-structure').show();

        });

    }

    /*
    Update the data structure of a dataset in the "data" db table
     */
    function update_data_structure(globalize){

        //get data
        if( $('#temporary-chart-id').length ){
            var chart_id = $('#temporary-chart-id').val();
        }else{
            var chart_id = $('#update-id').val();
        }

        var row_index = $("#data-structure-row-index").val();
        var label = $("#data-structure-label").val();
        var fill = $("#data-structure-fill").val();
        var lineTension = $("#data-structure-lineTension").val();
        var backgroundColor = $("#data-structure-backgroundColor").val();
        var borderWidth = $("#data-structure-borderWidth").val();
        var borderColor = $("#data-structure-borderColor").val();
        var borderCapStyle = $("#data-structure-borderCapStyle").val();
        var borderDash = $("#data-structure-borderDash").val();
        var borderDashOffset = $("#data-structure-borderDashOffset").val();
        var borderJoinStyle = $("#data-structure-borderJoinStyle").val();
        var pointBorderColor = $("#data-structure-pointBorderColor").val();
        var pointBackgroundColor = $("#data-structure-pointBackgroundColor").val();
        var pointBorderWidth = $("#data-structure-pointBorderWidth").val();
        var pointRadius = $("#data-structure-pointRadius").val();
        var pointHoverRadius = $("#data-structure-pointHoverRadius").val();
        var pointHitRadius = $("#data-structure-pointHitRadius").val();
        var pointHoverBackgroundColor = $("#data-structure-pointHoverBackgroundColor").val();
        var pointHoverBorderColor = $("#data-structure-pointHoverBorderColor").val();
        var pointHoverBorderWidth = $("#data-structure-pointHoverBorderWidth").val();
        var pointStyle = $("#data-structure-pointStyle").val();
        var showLine = $("#data-structure-showLine").val();
        var spanGaps = $("#data-structure-spanGaps").val();
        var hoverBackgroundColor = $("#data-structure-hoverBackgroundColor").val();
        var hoverBorderColor = $("#data-structure-hoverBorderColor").val();
        var hoverBorderWidth = $("#data-structure-hoverBorderWidth").val();
        var hitRadius = $("#data-structure-hitRadius").val();
        var hoverRadius = $("#data-structure-hoverRadius").val();
        var plotY2 = $("#data-structure-plotY2").val();

        //remove unnecessary spaces in all the data
        if(lineTension.length > 0){lineTension = lineTension.replace(/ /g,'');}
        if(backgroundColor.length > 0){backgroundColor = backgroundColor.replace(/ /g,'');}
        if(borderWidth.length > 0){borderWidth = borderWidth.replace(/ /g,'');}
        if(borderColor.length > 0){borderColor = borderColor.replace(/ /g,'');}
        if(borderDash.length > 0){borderDash = borderDash.replace(/ /g,'');}
        if(borderDashOffset.length > 0){borderDashOffset = borderDashOffset.replace(/ /g,'');}
        if(pointBorderColor.length > 0){pointBorderColor = pointBorderColor.replace(/ /g,'');}
        if(pointBackgroundColor.length > 0){pointBackgroundColor = pointBackgroundColor.replace(/ /g,'');}
        if(pointBorderWidth.length > 0){pointBorderWidth = pointBorderWidth.replace(/ /g,'');}
        if(pointRadius.length > 0){pointRadius = pointRadius.replace(/ /g,'');}
        if(pointHoverRadius.length > 0){pointHoverRadius = pointHoverRadius.replace(/ /g,'');}
        if(pointHitRadius.length > 0){pointHitRadius = pointHitRadius.replace(/ /g,'');}
        if(pointHoverBackgroundColor.length > 0){pointHoverBackgroundColor = pointHoverBackgroundColor.replace(/ /g,'');}
        if(pointHoverBorderColor.length > 0){pointHoverBorderColor = pointHoverBorderColor.replace(/ /g,'');}
        if(pointHoverBorderWidth.length > 0){pointHoverBorderWidth = pointHoverBorderWidth.replace(/ /g,'');}
        if(pointStyle.length > 0){pointStyle = pointStyle.replace(/ /g,'');}
        if(hoverBackgroundColor.length > 0){hoverBackgroundColor = hoverBackgroundColor.replace(/ /g,'');}
        if(hoverBorderColor.length > 0){hoverBorderColor = hoverBorderColor.replace(/ /g,'');}
        if(hoverBorderWidth.length > 0){hoverBorderWidth = hoverBorderWidth.replace(/ /g,'');}
        if(hitRadius.length > 0){hitRadius = hitRadius.replace(/ /g,'');}
        if(hoverRadius.length > 0){hoverRadius = hoverRadius.replace(/ /g,'');}

        //prepare ajax request
        var data = {
            "action": "update_data_structure",
            "security": dauc_nonce,
            "globalize": globalize,
            "chart_id": chart_id,
            "row_index": row_index,
            "label": label,
            "fill": fill,
            "lineTension": lineTension,
            "backgroundColor": backgroundColor,
            "borderWidth": borderWidth,
            "borderColor": borderColor,
            "borderCapStyle": borderCapStyle,
            "borderDash": borderDash,
            "borderDashOffset": borderDashOffset,
            "borderJoinStyle": borderJoinStyle,
            "pointBorderColor": pointBorderColor,
            "pointBackgroundColor": pointBackgroundColor,
            "pointBorderWidth": pointBorderWidth,
            "pointRadius": pointRadius,
            "pointHoverRadius": pointHoverRadius,
            "pointHitRadius": pointHitRadius,
            "pointHoverBackgroundColor": pointHoverBackgroundColor,
            "pointHoverBorderColor": pointHoverBorderColor,
            "pointHoverBorderWidth": pointHoverBorderWidth,
            "pointStyle": pointStyle,
            "showLine": showLine,
            "spanGaps": spanGaps,
            "hoverBackgroundColor": hoverBackgroundColor,
            "hoverBorderColor": hoverBorderColor,
            "hoverBorderWidth": hoverBorderWidth,
            "hitRadius": hitRadius,
            "hoverRadius": hoverRadius,
            "plotY2": plotY2
        };

        if(data_structure_is_valid(data)){

            //send ajax request
            $.post(dauc_ajax_url, data, function(result) {

                if(result.trim() == 'success'){

                    //show success message
                    $('#data-structure-updated').show();
                    clearTimeout(dsu_timeout_handler);
                    dsu_timeout_handler = setTimeout(function(){ $('#data-structure-updated').hide(); }, 3000);

                }

            });

        }


    }

    /*
    In the data structure section show only the field associated with the current type of chart
     */
    function show_chart_specific_data_structure(){

        var type = $('#type').val();

        $('.data-structure-property').each(function(index, element ) {

            var affected_types = $(this).attr('data-affected-types');
            var affected_types_a = affected_types.split('-');

            if( affected_types.indexOf(type) !== -1 ){
                $(this).show();
            }else{
                $(this).hide();
            }

        });

    }

    /*
    Remove the bottom border on the cells of the last row of the chart section
     */
    function remove_border_last_cell_chart(){
        $('table.daext-form-chart tr > *').css('border-bottom-width', '1px');
        $('table.daext-form-chart tr:visible:last > *').css('border-bottom-width', '0');
    }

    /*
    Remove the bottom border on the cells of the last row of the data structure section
     */
    function remove_border_last_cell_data_structure(){
        $('table.daext-form-data-structure tr > *').css('border-bottom-width', '1px');
        $('table.daext-form-data-structure tr:visible:last > *').css('border-bottom-width', '0');
    }

    /*
    The "title" attribute of the fields in the dataset section is modified based on the current type of chart
     */
    function set_type_based_titles(){

        var type = $('#type').val();

        $('.data-structure-property').each(function(index_1, element_1 ) {

            var container = $(this).find('.help-icon');
            var chart_specific_title = container.attr('data-title-' + type);
            if(chart_specific_title !== undefined){

                var affected_types = $(this).attr('data-affected-types');
                var affected_types_a = affected_types.split('-');

                $.each(affected_types_a, function( index_2, value_2 ) {

                    if(value_2 == type){

                        container.attr('title', chart_specific_title);

                    }

                });

            }

        });

    }

    /*
    Verifies if the data of the data structure are valid
     */
    function data_structure_is_valid(data){

        //init variables
        var fields_with_errors_a = [];

        //define patterns ----------------------------------------------------------------------------------------------

        //match an integer or a float value
        var patt_integer_or_float = /^(\d+\.\d+)|\d+$/;

        //match a hex rgb color, a rgba color or a comma separated list of hex rgb colors and rgba colors
        var patt_color_or_colors = /^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))(,(\#([0-9a-fA-F]{3}){1,2}|rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))*$/;

        //match an integer or a comma separated list of integers
        var patt_integer_or_integers = /^(\d+|(\d+(,\d+)*))$/;

        //match an integer
        var patt_integer = /^\d+$/;

        //match a pointStyle or a comma separated list of pointStyles
        var patt_pointStyle_or_pointStyles = /^((circle|triangle|rect|rectRot|cross|crossRot|star|line|dash)|(circle|triangle|rect|rectRot|cross|crossRot|star|line|dash)(,(circle|triangle|rect|rectRot|cross|crossRot|star|line|dash))*)$/;

        if(!patt_integer_or_float.test(data.lineTension)){fields_with_errors_a.push(objectL10n.data_structure_lineTension);}
        if(!patt_color_or_colors.test(data.backgroundColor)){fields_with_errors_a.push(objectL10n.data_structure_backgroundColor);}
        if(!patt_color_or_colors.test(data.borderColor)){fields_with_errors_a.push(objectL10n.data_structure_borderColor);}
        if(!patt_integer_or_integers.test(data.borderWidth)){fields_with_errors_a.push(objectL10n.data_structure_borderWidth);}
        if(!patt_integer_or_integers.test(data.borderDash)){fields_with_errors_a.push(objectL10n.data_structure_borderDash);}
        if(!patt_integer_or_float.test(data.borderDashOffset)){fields_with_errors_a.push(objectL10n.data_structure_borderDashOffset);}
        if(!patt_color_or_colors.test(data.pointBackgroundColor)){fields_with_errors_a.push(objectL10n.data_structure_pointBackgroundColor);}
        if(!patt_color_or_colors.test(data.pointBorderColor)){fields_with_errors_a.push(objectL10n.data_structure_pointBorderColor);}
        if(!patt_integer_or_integers.test(data.pointBorderWidth)){fields_with_errors_a.push(objectL10n.data_structure_pointBorderWidth);}
        if(!patt_integer_or_integers.test(data.pointRadius)){fields_with_errors_a.push(objectL10n.data_structure_pointRadius);}
        if(!patt_integer_or_integers.test(data.pointHitRadius)){fields_with_errors_a.push(objectL10n.data_structure_pointHitRadius);}
        if(!patt_pointStyle_or_pointStyles.test(data.pointStyle)){fields_with_errors_a.push(objectL10n.data_structure_pointStyle);}
        if(!patt_integer_or_integers.test(data.pointHoverRadius)){fields_with_errors_a.push(objectL10n.data_structure_pointHoverRadius);}
        if(!patt_color_or_colors.test(data.pointHoverBackgroundColor)){fields_with_errors_a.push(objectL10n.data_structure_pointHoverBackgroundColor);}
        if(!patt_color_or_colors.test(data.pointHoverBorderColor)){fields_with_errors_a.push(objectL10n.data_structure_pointHoverBorderColor);}
        if(!patt_integer_or_integers.test(data.pointHoverBorderWidth)){fields_with_errors_a.push(objectL10n.data_structure_pointHoverBorderWidth);}
        if(!patt_color_or_colors.test(data.hoverBackgroundColor)){fields_with_errors_a.push(objectL10n.data_structure_hoverBackgroundColor);}
        if(!patt_color_or_colors.test(data.hoverBorderColor)){fields_with_errors_a.push(objectL10n.data_structure_hoverBorderColor);}
        if(!patt_integer_or_integers.test(data.hoverBorderWidth)){fields_with_errors_a.push(objectL10n.data_structure_hoverBorderWidth);}
        if(!patt_integer_or_integers.test(data.hitRadius)){fields_with_errors_a.push(objectL10n.data_structure_hitRadius);}
        if(!patt_integer_or_integers.test(data.hoverRadius)){fields_with_errors_a.push(objectL10n.data_structure_hoverRadius);}

        if( fields_with_errors_a.length > 0){

            //hide the updated message if it's shown
            $('#data-structure-updated').hide();

            //show error message
            $('#data-structure-error p').html($('#data-structure-error-partial-message').val() + ' <strong>' + fields_with_errors_a.join(', ') + '</strong>');
            $('#data-structure-error').show();

            return false;

        }else{

            //hide the error message if it's shown
            $('#data-structure-error').hide();

            return true;

        }

    }

    /*
    Verifies if the data of the chart are valid
     */
    function chart_is_valid(data){

        //init variables
        var fields_with_errors_a = [];

        //define regex patterns ----------------------------------------------------------------------------------------

        //match an integer or a float value
        var patt_integer_or_float = /^(\d+\.\d+)|\d+$/;

        //match an integer or a float value with sign
        var patt_integer_or_float_with_sign = /^(\+|-)?(\d+\.\d+)|\d+$/;

        //match a float value
        var patt_float = /^\d\.\d$/;

        //match a hex rgb color or a rgba color
        var patt_color = /^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))$/;

        //match a hex rgb color, a rgba color or a comma separated list of hex rgb colors and rgba colors
        var patt_color_or_colors = /^((\#([0-9a-fA-F]{3}){1,2})|(rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))(,(\#([0-9a-fA-F]{3}){1,2}|rgba\(\d{1,3},\d{1,3},\d{1,3},(\d{1}|\d{1}\.\d{1,2})\)))*$/;

        //match an integer or a comma separated list of integers
        var patt_integer_or_integers = /^(\d+|(\d+(,\d+)*))$/;

        //match an integer
        var patt_integer = /^\d+$/;

        //match a font family or a comma separated list of font families
        var patt_font_family_or_font_families = /^(('[^'"]+'|[^,"]+)(,('[^'"]+'|[^,"]+))*)$/;

        //match a moment.js format tokens, for details see: http://momentjs.com/docs/#/displaying/format/
        var patt_moment_format_tokens = /^(\s|\/|,|:|M|Mo|MM|MMM|MMMM|Q|Qo|D|Do|DD|DDD|DDDo|DDDD|d|do|dd|ddd|dddd|e|E|w|ww|W|Wo|WW|YY|YYYY|Y|gg|gggg|GG|GGGG|A|a|H|HH|h|hh|k|kk|m|mm|s|ss|S|SS|SSS|z|zz|Z|ZZ|X|x)+$/;

        //match a moment.js format output, for details see: http://momentjs.com/docs/#/displaying/format/
        var patt_moment_format_output = /^(\s|[0-9]|[a-z]|[A-Z]|\+|-|\[|\]|:|\.|\/|,)+$/;

        //validate data ------------------------------------------------------------------------------------------------

        //Basic Info
        if( data.name.trim().length < 1 || data.name.trim().length > 200 ){fields_with_errors_a.push(objectL10n.name);}
        if( data.description.trim().length < 1 || data.description.trim().length > 200 ){fields_with_errors_a.push(objectL10n.description);}

        //Common Chart Configuration
        if(!patt_color.test(data.canvas_backgroundColor)){fields_with_errors_a.push(objectL10n.canvas_backgroundColor)}
        if(!patt_integer.test(data.width)){fields_with_errors_a.push(objectL10n.width)}
        if(!patt_integer.test(data.height)){fields_with_errors_a.push(objectL10n.height)}
        if(!patt_integer.test(data.margin_top)){fields_with_errors_a.push(objectL10n.margin_top)}
        if(!patt_integer.test(data.margin_bottom)){fields_with_errors_a.push(objectL10n.margin_bottom)}
        if(!patt_integer.test(data.responsiveAnimationDuration)){fields_with_errors_a.push(objectL10n.responsiveAnimationDuration);}
        if(!patt_integer.test(data.fixed_height)){fields_with_errors_a.push(objectL10n.fixed_height)}

        //Title Configuration
        if(!patt_integer.test(data.title_padding)){fields_with_errors_a.push(objectL10n.title_padding);}
        if(!patt_integer.test(data.title_fontSize)){fields_with_errors_a.push(objectL10n.title_fontSize);}
        if(!patt_color.test(data.title_fontColor)){fields_with_errors_a.push(objectL10n.title_fontColor);}
        if(!patt_font_family_or_font_families.test(data.title_fontFamily)){fields_with_errors_a.push(objectL10n.title_fontFamily);}

        //Legend Label Configuration
        if(!patt_integer.test(data.legend_labels_padding)){fields_with_errors_a.push(objectL10n.legend_labels_padding);}
        if(!patt_integer.test(data.legend_labels_boxWidth)){fields_with_errors_a.push(objectL10n.legend_labels_boxWidth);}
        if(!patt_integer.test(data.legend_labels_fontSize)){fields_with_errors_a.push(objectL10n.legend_labels_fontSize);}
        if(!patt_color.test(data.legend_labels_fontColor)){fields_with_errors_a.push(objectL10n.legend_labels_fontColor);}
        if(!patt_font_family_or_font_families.test(data.legend_labels_fontFamily)){fields_with_errors_a.push(objectL10n.legend_labels_fontFamily);}

        //Tooltip Configuration
        if(!patt_color.test(data.tooltips_backgroundColor)){fields_with_errors_a.push(objectL10n.tooltips_backgroundColor);}
        if(!patt_color.test(data.tooltips_multiKeyBackground)){fields_with_errors_a.push(objectL10n.tooltips_multiKeyBackground);}
        if(!patt_integer.test(data.tooltips_titleMarginBottom)){fields_with_errors_a.push(objectL10n.tooltips_titleMarginBottom);}
        if(!patt_integer.test(data.tooltips_footerMarginTop)){fields_with_errors_a.push(objectL10n.tooltips_footerMarginTop);}
        if(!patt_integer.test(data.tooltips_xPadding)){fields_with_errors_a.push(objectL10n.tooltips_xPadding);}
        if(!patt_integer.test(data.tooltips_yPadding)){fields_with_errors_a.push(objectL10n.tooltips_yPadding);}
        if(!patt_integer.test(data.tooltips_caretSize)){fields_with_errors_a.push(objectL10n.tooltips_caretSize);}
        if(!patt_integer.test(data.tooltips_cornerRadius)){fields_with_errors_a.push(objectL10n.tooltips_cornerRadius);}
        if(!patt_integer.test(data.hover_animationDuration)){fields_with_errors_a.push(objectL10n.hover_animationDuration);}
        if(!patt_integer.test(data.tooltips_titleFontSize)){fields_with_errors_a.push(objectL10n.tooltips_titleFontSize);}
        if(!patt_color.test(data.tooltips_titleFontColor)){fields_with_errors_a.push(objectL10n.tooltips_titleFontColor);}
        if(!patt_font_family_or_font_families.test(data.tooltips_titleFontFamily)){fields_with_errors_a.push(objectL10n.tooltips_titleFontFamily);}
        if(!patt_integer.test(data.tooltips_bodyFontSize)){fields_with_errors_a.push(objectL10n.tooltips_bodyFontSize);}
        if(!patt_color.test(data.tooltips_bodyFontColor)){fields_with_errors_a.push(objectL10n.tooltips_bodyFontColor);}
        if(!patt_font_family_or_font_families.test(data.tooltips_bodyFontFamily)){fields_with_errors_a.push(objectL10n.tooltips_bodyFontFamily);}
        if(!patt_integer.test(data.tooltips_footerFontSize)){fields_with_errors_a.push(objectL10n.tooltips_footerFontSize);}
        if(!patt_color.test(data.tooltips_footerFontColor)){fields_with_errors_a.push(objectL10n.tooltips_footerFontColor);}
        if(!patt_font_family_or_font_families.test(data.tooltips_footerFontFamily)){fields_with_errors_a.push(objectL10n.tooltips_footerFontFamily);}

        //Animation Configuration
        if(!patt_integer.test(data.animation_duration)){fields_with_errors_a.push(objectL10n.animation_duration);}

        //X Scale Grid Line
        if(!patt_color_or_colors.test(data.scales_xAxes_gridLines_color)){fields_with_errors_a.push(objectL10n.scales_xAxes_gridLines_color);}
        if(!patt_integer_or_integers.test(data.scales_xAxes_gridLines_lineWidth)){fields_with_errors_a.push(objectL10n.scales_xAxes_gridLines_lineWidth);}
        if(!patt_integer.test(data.scales_xAxes_gridLines_tickMarkLength)){fields_with_errors_a.push(objectL10n.scales_xAxes_gridLines_tickMarkLength);}
        if(!patt_color.test(data.scales_xAxes_gridLines_zeroLineColor)){fields_with_errors_a.push(objectL10n.scales_xAxes_gridLines_zeroLineColor);}
        if(!patt_integer.test(data.scales_xAxes_gridLines_zeroLineWidth)){fields_with_errors_a.push(objectL10n.scales_xAxes_gridLines_zeroLineWidth);}

        //X Scale Title
        if(!patt_integer.test(data.scales_xAxes_scaleLabel_fontSize)){fields_with_errors_a.push(objectL10n.scales_xAxes_scaleLabel_fontSize);}
        if(!patt_color.test(data.scales_xAxes_scaleLabel_fontColor)){fields_with_errors_a.push(objectL10n.scales_xAxes_scaleLabel_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_xAxes_scaleLabel_fontFamily)){fields_with_errors_a.push(objectL10n.scales_xAxes_scaleLabel_fontFamily);}

        //X Scale Tick
        if(!patt_integer.test(data.scales_xAxes_ticks_labelOffset)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_labelOffset);}
        if(!patt_integer.test(data.scales_xAxes_ticks_minRotation)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_minRotation);}
        if(!patt_integer.test(data.scales_xAxes_ticks_maxRotation)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_maxRotation);}
        if(!patt_integer.test(data.scales_xAxes_ticks_round) && data.scales_xAxes_ticks_round.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_round);}
        if(!patt_integer.test(data.scales_xAxes_ticks_fontSize)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_fontSize);}
        if(!patt_color.test(data.scales_xAxes_ticks_fontColor)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_xAxes_ticks_fontFamily)){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_fontFamily);}

        //X Scale Options
        if( !patt_integer_or_float_with_sign.test(data.scales_xAxes_ticks_min) && data.scales_xAxes_ticks_min.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_min);}
        if( !patt_integer_or_float_with_sign.test(data.scales_xAxes_ticks_max) && data.scales_xAxes_ticks_max.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_max);}
        if( !patt_integer_or_float_with_sign.test(data.scales_xAxes_ticks_suggestedMax) && data.scales_xAxes_ticks_suggestedMax.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_suggestedMax);}
        if( !patt_integer_or_float_with_sign.test(data.scales_xAxes_ticks_suggestedMin) && data.scales_xAxes_ticks_suggestedMin.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_suggestedMin);}
        if( !patt_integer_or_float.test(data.scales_xAxes_ticks_stepSize) && data.scales_xAxes_ticks_stepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_stepSize);}
        if( !patt_integer_or_float.test(data.scales_xAxes_ticks_fixedStepSize) && data.scales_xAxes_ticks_fixedStepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_fixedStepSize);}
        if( !patt_integer.test(data.scales_xAxes_ticks_maxTicksLimit) && data.scales_xAxes_ticks_maxTicksLimit.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_xAxes_ticks_maxTicksLimit);}
        if(!patt_integer_or_float.test(data.scales_xAxes_categoryPercentage)){fields_with_errors_a.push(objectL10n.scales_xAxes_categoryPercentage);}
        if(!patt_integer_or_float.test(data.scales_xAxes_barPercentage)){fields_with_errors_a.push(objectL10n.scales_xAxes_barPercentage);}

        //X Scale Time
        if(!patt_moment_format_tokens.test(data.scales_xAxes_time_format)){fields_with_errors_a.push(objectL10n.scales_xAxes_time_format);}
        if(!patt_moment_format_tokens.test(data.scales_xAxes_time_tooltipFormat)){fields_with_errors_a.push(objectL10n.scales_xAxes_time_tooltipFormat);}
        if(!patt_moment_format_tokens.test(data.scales_xAxes_time_unit_format)){fields_with_errors_a.push(objectL10n.scales_xAxes_time_unit_format);}
        if( !patt_moment_format_output.test(data.scales_xAxes_time_min) && data.scales_xAxes_time_min.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_xAxes_time_min);}
        if( !patt_moment_format_output.test(data.scales_xAxes_time_max) && data.scales_xAxes_time_max.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_xAxes_time_max);}
        if(!patt_integer.test(data.scales_xAxes_time_unitStepSize)){fields_with_errors_a.push(objectL10n.scales_xAxes_time_unitStepSize);}

        //Y Scale Grid Line
        if(!patt_color_or_colors.test(data.scales_yAxes_gridLines_color)){fields_with_errors_a.push(objectL10n.scales_yAxes_gridLines_color);}
        if(!patt_integer_or_integers.test(data.scales_yAxes_gridLines_lineWidth)){fields_with_errors_a.push(objectL10n.scales_yAxes_gridLines_lineWidth);}
        if(!patt_integer.test(data.scales_yAxes_gridLines_tickMarkLength)){fields_with_errors_a.push(objectL10n.scales_yAxes_gridLines_tickMarkLength);}
        if(!patt_color.test(data.scales_yAxes_gridLines_zeroLineColor)){fields_with_errors_a.push(objectL10n.scales_yAxes_gridLines_zeroLineColor);}
        if(!patt_integer.test(data.scales_yAxes_gridLines_zeroLineWidth)){fields_with_errors_a.push(objectL10n.scales_yAxes_gridLines_zeroLineWidth);}

        //Y Scale Title
        if(!patt_integer.test(data.scales_yAxes_scaleLabel_fontSize)){fields_with_errors_a.push(objectL10n.scales_yAxes_scaleLabel_fontSize);}
        if(!patt_color.test(data.scales_yAxes_scaleLabel_fontColor)){fields_with_errors_a.push(objectL10n.scales_yAxes_scaleLabel_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_yAxes_scaleLabel_fontFamily)){fields_with_errors_a.push(objectL10n.scales_yAxes_scaleLabel_fontFamily);}

        //Y Scale Tick
        if(!patt_integer.test(data.scales_yAxes_ticks_minRotation)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_minRotation);}
        if(!patt_integer.test(data.scales_yAxes_ticks_maxRotation)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_maxRotation);}
        if(!patt_integer.test(data.scales_yAxes_ticks_padding)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_padding);}
        if(!patt_integer.test(data.scales_yAxes_ticks_round) && data.scales_yAxes_ticks_round.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_round);}
        if(!patt_integer.test(data.scales_yAxes_ticks_fontSize)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_fontSize);}
        if(!patt_color.test(data.scales_yAxes_ticks_fontColor)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_yAxes_ticks_fontFamily)){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_fontFamily);}

        //Y Scale Options
        if( !patt_integer_or_float_with_sign.test(data.scales_yAxes_ticks_min) && data.scales_yAxes_ticks_min.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_min);}
        if( !patt_integer_or_float_with_sign.test(data.scales_yAxes_ticks_max) && data.scales_yAxes_ticks_max.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_max);}
        if( !patt_integer_or_float_with_sign.test(data.scales_yAxes_ticks_suggestedMin) && data.scales_yAxes_ticks_suggestedMin.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_suggestedMin);}
        if( !patt_integer_or_float_with_sign.test(data.scales_yAxes_ticks_suggestedMax) && data.scales_yAxes_ticks_suggestedMax.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_suggestedMax);}
        if( !patt_integer_or_float.test(data.scales_yAxes_ticks_stepSize) && data.scales_yAxes_ticks_stepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_stepSize);}
        if( !patt_integer_or_float.test(data.scales_yAxes_ticks_fixedStepSize) && data.scales_yAxes_ticks_fixedStepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_fixedStepSize);}
        if( !patt_integer.test(data.scales_yAxes_ticks_maxTicksLimit) && data.scales_yAxes_ticks_maxTicksLimit.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_yAxes_ticks_maxTicksLimit);}
        if(!patt_integer_or_float.test(data.scales_yAxes_categoryPercentage)){fields_with_errors_a.push(objectL10n.scales_yAxes_categoryPercentage);}
        if(!patt_integer_or_float.test(data.scales_yAxes_barPercentage)){fields_with_errors_a.push(objectL10n.scales_yAxes_barPercentage);}

        //Y2 Scale Grid Line
        if(!patt_color_or_colors.test(data.scales_y2Axes_gridLines_color)){fields_with_errors_a.push(objectL10n.scales_y2Axes_gridLines_color);}
        if(!patt_integer_or_integers.test(data.scales_y2Axes_gridLines_lineWidth)){fields_with_errors_a.push(objectL10n.scales_y2Axes_gridLines_lineWidth);}
        if(!patt_integer.test(data.scales_y2Axes_gridLines_tickMarkLength)){fields_with_errors_a.push(objectL10n.scales_y2Axes_gridLines_tickMarkLength);}
        if(!patt_color.test(data.scales_y2Axes_gridLines_zeroLineColor)){fields_with_errors_a.push(objectL10n.scales_y2Axes_gridLines_zeroLineColor);}
        if(!patt_integer.test(data.scales_y2Axes_gridLines_zeroLineWidth)){fields_with_errors_a.push(objectL10n.scales_y2Axes_gridLines_zeroLineWidth);}

        //Y2 Scale Title
        if(!patt_integer.test(data.scales_y2Axes_scaleLabel_fontSize)){fields_with_errors_a.push(objectL10n.scales_y2Axes_scaleLabel_fontSize);}
        if(!patt_color.test(data.scales_y2Axes_scaleLabel_fontColor)){fields_with_errors_a.push(objectL10n.scales_y2Axes_scaleLabel_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_y2Axes_scaleLabel_fontFamily)){fields_with_errors_a.push(objectL10n.scales_y2Axes_scaleLabel_fontFamily);}

        //Y2 Scale Tick
        if(!patt_integer.test(data.scales_y2Axes_ticks_minRotation)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_minRotation);}
        if(!patt_integer.test(data.scales_y2Axes_ticks_maxRotation)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_maxRotation);}
        if(!patt_integer.test(data.scales_y2Axes_ticks_padding)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_padding);}
        if(!patt_integer.test(data.scales_y2Axes_ticks_round) && data.scales_y2Axes_ticks_round.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_round);}
        if(!patt_integer.test(data.scales_y2Axes_ticks_fontSize)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_fontSize);}
        if(!patt_color.test(data.scales_y2Axes_ticks_fontColor)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_y2Axes_ticks_fontFamily)){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_fontFamily);}

        //Y2 Scale Options
        if( !patt_integer_or_float_with_sign.test(data.scales_y2Axes_ticks_min) && data.scales_y2Axes_ticks_min.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_min);}
        if( !patt_integer_or_float_with_sign.test(data.scales_y2Axes_ticks_max) && data.scales_y2Axes_ticks_max.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_max);}
        if( !patt_integer_or_float_with_sign.test(data.scales_y2Axes_ticks_suggestedMin) && data.scales_y2Axes_ticks_suggestedMin.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_suggestedMin);}
        if( !patt_integer_or_float_with_sign.test(data.scales_y2Axes_ticks_suggestedMax) && data.scales_y2Axes_ticks_suggestedMax.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_suggestedMax);}
        if( !patt_integer_or_float.test(data.scales_y2Axes_ticks_stepSize) && data.scales_y2Axes_ticks_stepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_stepSize);}
        if( !patt_integer_or_float.test(data.scales_y2Axes_ticks_fixedStepSize) && data.scales_y2Axes_ticks_fixedStepSize.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_fixedStepSize);}
        if( !patt_integer.test(data.scales_y2Axes_ticks_maxTicksLimit) && data.scales_y2Axes_ticks_maxTicksLimit.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_y2Axes_ticks_maxTicksLimit);}

        /* RL Scale Grid Line Configuration */
        if(!patt_color_or_colors.test(data.scales_rl_gridLines_color)){fields_with_errors_a.push(objectL10n.scales_rl_gridLines_color);}
        if(!patt_integer_or_integers.test(data.scales_rl_gridLines_lineWidth)){fields_with_errors_a.push(objectL10n.scales_rl_gridLines_lineWidth);}

        /* RL Scale Angle Line Configuration */
        if(!patt_color.test(data.scales_rl_angleLines_color)){fields_with_errors_a.push(objectL10n.scales_rl_angleLines_color);}
        if(!patt_integer.test(data.scales_rl_angleLines_lineWidth)){fields_with_errors_a.push(objectL10n.scales_rl_angleLines_lineWidth);}

        /* RL Scale Point Label Configuration */
        if(!patt_integer.test(data.scales_rl_pointLabels_fontSize)){fields_with_errors_a.push(objectL10n.scales_rl_pointLabels_fontSize);}
        if(!patt_color.test(data.scales_rl_pointLabels_fontColor)){fields_with_errors_a.push(objectL10n.scales_rl_pointLabels_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_rl_pointLabels_fontFamily)){fields_with_errors_a.push(objectL10n.scales_rl_pointLabels_fontFamily);}

        /* RL Scale Tick Configuration */
        if(!patt_integer.test(data.scales_rl_ticks_round) && data.scales_rl_ticks_round.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_round);}
        if(!patt_integer.test(data.scales_rl_ticks_fontSize)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_fontSize);}
        if(!patt_color.test(data.scales_rl_ticks_fontColor)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_fontColor);}
        if(!patt_font_family_or_font_families.test(data.scales_rl_ticks_fontFamily)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_fontFamily);}

        /* RL Scale Configuration Options */
        if( !patt_integer_or_float_with_sign.test(data.scales_rl_ticks_min) && data.scales_rl_ticks_min.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_min);}
        if( !patt_integer_or_float_with_sign.test(data.scales_rl_ticks_max) && data.scales_rl_ticks_max.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_max);}
        if( !patt_integer_or_float_with_sign.test(data.scales_rl_ticks_suggestedMin) && data.scales_rl_ticks_suggestedMin.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_suggestedMin);}
        if( !patt_integer_or_float_with_sign.test(data.scales_rl_ticks_suggestedMax) && data.scales_rl_ticks_suggestedMax.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_suggestedMax);}
        if( !patt_integer_or_float.test(data.scales_rl_ticks_stepSize) && data.scales_rl_ticks_stepSize.trim().length !== 0  ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_stepSize);}
        if( !patt_integer_or_float.test(data.scales_rl_ticks_fixedStepSize) && data.scales_rl_ticks_fixedStepSize.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_fixedStepSize);}
        if( !patt_integer.test(data.scales_rl_ticks_maxTicksLimit) && data.scales_rl_ticks_maxTicksLimit.trim().length !== 0 ){fields_with_errors_a.push(objectL10n.scales_rl_ticks_maxTicksLimit);}
        if(!patt_color.test(data.scales_rl_ticks_backdropColor)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_backdropColor);}
        if(!patt_integer.test(data.scales_rl_ticks_backdropPaddingX)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_backdropPaddingX);}
        if(!patt_integer.test(data.scales_rl_ticks_backdropPaddingY)){fields_with_errors_a.push(objectL10n.scales_rl_ticks_backdropPaddingY);}

        if( fields_with_errors_a.length > 0){

            return fields_with_errors_a;

        }else{

            return true;

        }

    }

    /*
    Initialize the spectrum color picker
     */
    function initialize_spectrum(){

        $(".spectrum-input").spectrum({

            //options --------------------------------------------------------------------------------------------------
            color: 'rgba(0,117,165,1)',
            showAlpha: true,
            cancelText: objectL10n.cancelText,
            chooseText: objectL10n.chooseText,
            preferredFormat: 'rgb',

            //events ---------------------------------------------------------------------------------------------------
            change: function(color) {

                //get the name of the element involved
                var element_name = $(this).attr('id').substr(0, ( $(this).attr('id').length - 9 ) )

                //prepare the values of the color components
                var red = parseInt(color._r, 10);
                var green = parseInt(color._g, 10);
                var blue = parseInt(color._b, 10);
                var alpha = Math.round( color._a * 10 ) / 10;

                //generate the color in the rgba format
                var rgba_color = 'rgba(' + red + ',' + green + ',' + blue + ',' + alpha + ')';

                //if the related field is not empty add a comma and the color, otherwise add only the color
                if( $("#" + element_name).val().trim().length > 0 ){
                    $("#" + element_name).val( $("#" + element_name).val().trim() + ',' + rgba_color );
                }else{
                    $("#" + element_name).val( rgba_color );
                }

            }

        });

        //.spectrum-toggle -> click - EVENT LISTENER (a click on the colored circle that activates the color picker modal window)
        $(".spectrum-toggle").click(function() {

            //get the name of the element involved
            var element_name = $(this).attr('id').substr(0, ( $(this).attr('id').length - 16 ) )

            //toggle the colorpicker
            $("#" + element_name + "-spectrum").spectrum("toggle");

            //get the container of the color picker
            var container = $("#" + element_name + "-spectrum").spectrum("container");

            //add a data attribute to the color picker used during the resize event to identify the element involved
            $(container).attr('data-element-involved', element_name);

            //position the container of the color picker on the right of the toggle
            var toggle_position = $("#" + element_name + "-spectrum-toggle").offset();

            if( $(document).width() - 227 > toggle_position.left ){

                //if there is enough space on the right of the color picker show the color picker on the right of the toggle
                container.css('left', toggle_position.left + 28);

            }else{

                //otherwise show the color picker on the left of the toogle
                container.css('left', toggle_position.left - 202);

            }
            container.css('top', toggle_position.top + -2);

            //save the element name in a global variable, so that will be used during the resize event
            window.dauc_element_name = element_name;

            return false;

        });

        //window -> resize - EVENT LISTENER
        jQuery(window).resize(function(){

            //get the element from a global variable
            var element_name = window.dauc_element_name;

            //disable the color picker
            var container = $("#" + element_name + "-spectrum").spectrum("disable");

        });

        //window -> load - EVENT LISTENER (page completely loaded)
        $(window).bind("load", function() {

            $('.spectrum-toggle, .spectrum-input').css('visibility', 'visible');

        });

    }

    /*
    Save the data of the chart and refresh the preview of the chart
     */
    function save_and_refresh(){

        var validation_result = save_chart(false);

        if(validation_result !== true){

            //detach iframe
            $( '#chart-preview-iframe').detach();

            //show error message
            $('#chart-preview-error').hide();
            $('#chart-preview-error').delay(400).fadeIn(0);

        }else{

            $('#chart-preview-error').hide();

            if( $('#temporary-chart-id').length ){
                var chart_id = $('#temporary-chart-id').val();
            }else{
                var chart_id = $('#update-id').val();
            }

            if( $('#chart-preview-iframe').length ){

                //if the iframe exists detach the old iframe and add the new iframe
                $( '#chart-preview-iframe').detach();
                $('#chart-preview-iframe-container').append('<iframe scrolling="no" id="chart-preview-iframe" src="' + dauc_site_url + '?chart-preview=' + chart_id + '"></iframe>');

            }else{

                //if the iframe doesn't exist add the iframe
                $('#chart-preview-iframe-container').append('<iframe scrolling="no" id="chart-preview-iframe" src="' + dauc_site_url + '?chart-preview=' + chart_id + '"></iframe>');

            }

        }

    }

    /*
    * Save the chart. Used by the #save -> click and the #save-and-refresh -> click event handler
     */
    function save_chart(reload_menu){

        //get form data
        if( $('#temporary-chart-id').length ){
            var chart_id = $('#temporary-chart-id').val();
        }else{
            var chart_id = $('#update-id').val();
        }
        var name = $('#name').val();
        var description = $('#description').val();
        var type = $('#type').val();
        var rows = $('#rows').val();
        var columns = $('#columns').val();
        var is_model = parseInt($('#is-model').val(), 10);

        //Common Chart Configuration
        var canvas_transparent_background = $('#canvas-transparent-background').val();
        var canvas_backgroundColor = $('#canvas-backgroundColor').val().replace(/ /g,'');
        var margin_top = $('#margin-top').val();
        var margin_bottom = $('#margin-bottom').val();
        var width = $('#width').val();
        var height = $('#height').val();
        var responsive = $('#responsive').val();
        var responsiveAnimationDuration = $('#responsiveAnimationDuration').val();
        var maintainAspectRatio = $('#maintainAspectRatio').val();
        var fixed_height = $('#fixed-height').val();

        //Title Configuration
        var title_display = $('#title-display').val();
        var title_position = $('#title-position').val();
        var title_fullWidth = $('#title-fullWidth').val();
        var title_fontSize = $('#title-fontSize').val();
        var title_fontFamily = $('#title-fontFamily').val().trim();
        var title_fontColor = $('#title-fontColor').val().replace(/ /g,'');
        var title_fontStyle = $('#title-fontStyle').val();
        var title_padding = $('#title-padding').val();

        //Legend Configuration
        var legend_display = $('#legend-display').val();
        var legend_position = $('#legend-position').val();
        var legend_fullWidth = $('#legend-fullWidth').val();
        var legend_toggle_dataset = $('#legend-toggle-dataset').val();

        //Legend Label Configuration
        var legend_labels_boxWidth = $('#legend-labels-boxWidth').val();
        var legend_labels_fontSize = $('#legend-labels-fontSize').val();
        var legend_labels_fontStyle = $('#legend-labels-fontStyle').val();
        var legend_labels_fontColor = $('#legend-labels-fontColor').val().replace(/ /g,'');
        var legend_labels_fontFamily = $('#legend-labels-fontFamily').val().trim();
        var legend_labels_padding = $('#legend-labels-padding').val();

        //Tooltip Configuration
        var tooltips_enabled = $('#tooltips-enabled').val();
        var tooltips_mode = $('#tooltips-mode').val();
        var tooltips_backgroundColor = $('#tooltips-backgroundColor').val().replace(/ /g,'');
        var tooltips_titleFontFamily = $('#tooltips-titleFontFamily').val().trim();
        var tooltips_titleFontSize = $('#tooltips-titleFontSize').val();
        var tooltips_titleFontStyle = $('#tooltips-titleFontStyle').val();
        var tooltips_titleFontColor = $('#tooltips-titleFontColor').val().replace(/ /g,'');
        var tooltips_titleMarginBottom = $('#tooltips-titleMarginBottom').val();
        var tooltips_bodyFontFamily = $('#tooltips-bodyFontFamily').val().trim();
        var tooltips_bodyFontSize = $('#tooltips-bodyFontSize').val();
        var tooltips_bodyFontStyle = $('#tooltips-bodyFontStyle').val();
        var tooltips_bodyFontColor = $('#tooltips-bodyFontColor').val().replace(/ /g,'');
        var tooltips_footerFontFamily = $('#tooltips-footerFontFamily').val().trim();
        var tooltips_footerFontSize = $('#tooltips-footerFontSize').val();
        var tooltips_footerFontStyle = $('#tooltips-footerFontStyle').val();
        var tooltips_footerFontColor = $('#tooltips-footerFontColor').val().replace(/ /g,'');
        var tooltips_footerMarginTop = $('#tooltips-footerMarginTop').val();
        var tooltips_xPadding = $('#tooltips-xPadding').val();
        var tooltips_yPadding = $('#tooltips-yPadding').val();
        var tooltips_caretSize = $('#tooltips-caretSize').val();
        var tooltips_cornerRadius = $('#tooltips-cornerRadius').val();
        var tooltips_multiKeyBackground  = $('#tooltips-multiKeyBackground').val().replace(/ /g,'');
        var tooltips_beforeTitle = $('#tooltips-beforeTitle').val();
        var tooltips_afterTitle = $('#tooltips-afterTitle').val();
        var tooltips_beforeBody = $('#tooltips-beforeBody').val();
        var tooltips_afterBody = $('#tooltips-afterBody').val();
        var tooltips_beforeLabel = $('#tooltips-beforeLabel').val();
        var tooltips_afterLabel = $('#tooltips-afterLabel').val();
        var tooltips_beforeFooter = $('#tooltips-beforeFooter').val();
        var tooltips_footer = $('#tooltips-footer').val();
        var tooltips_afterFooter = $('#tooltips-afterFooter').val();

        //Hover Configuration
        var hover_animationDuration = $('#hover-animationDuration').val();

        //Animation Configuration
        var animation_duration = $('#animation-duration').val();
        var animation_easing = $('#animation-easing').val();
        var animation_animateRotate = $('#animation-animateRotate').val();
        var animation_animateScale = $('#animation-animateScale').val();

        //Misc Configuration
        var elements_rectangle_borderSkipped = $('#elements-rectangle-borderSkipped').val();

        //Scales Common Configuration X
        var scales_xAxes_type = $('#scales-xAxes-type').val();
        var scales_xAxes_display = $('#scales-xAxes-display').val();
        var scales_xAxes_position = $('#scales-xAxes-position').val();
        var scales_xAxes_stacked = $('#scales-xAxes-stacked').val();

        //Scales Grid Line Configuration X
        var scales_xAxes_gridLines_display = $('#scales-xAxes-gridLines-display').val();
        var scales_xAxes_gridLines_color = $('#scales-xAxes-gridLines-color').val();
        var scales_xAxes_gridLines_lineWidth = $('#scales-xAxes-gridLines-lineWidth').val();
        var scales_xAxes_gridLines_drawBorder = $('#scales-xAxes-gridLines-drawBorder').val();
        var scales_xAxes_gridLines_drawOnChartArea = $('#scales-xAxes-gridLines-drawOnChartArea').val();
        var scales_xAxes_gridLines_drawTicks = $('#scales-xAxes-gridLines-drawTicks').val();
        var scales_xAxes_gridLines_tickMarkLength = $('#scales-xAxes-gridLines-tickMarkLength').val();
        var scales_xAxes_gridLines_zeroLineWidth = $('#scales-xAxes-gridLines-zeroLineWidth').val();
        var scales_xAxes_gridLines_zeroLineColor = $('#scales-xAxes-gridLines-zeroLineColor').val();
        var scales_xAxes_gridLines_offsetGridLines = $('#scales-xAxes-gridLines-offsetGridLines').val();

        //Scales Title Configuration X
        var scales_xAxes_scaleLabel_display = $('#scales-xAxes-scaleLabel-display').val();
        var scales_xAxes_scaleLabel_labelString = $('#scales-xAxes-scaleLabel-labelString').val();
        var scales_xAxes_scaleLabel_fontColor = $('#scales-xAxes-scaleLabel-fontColor').val();
        var scales_xAxes_scaleLabel_fontFamily = $('#scales-xAxes-scaleLabel-fontFamily').val();
        var scales_xAxes_scaleLabel_fontSize = $('#scales-xAxes-scaleLabel-fontSize').val();
        var scales_xAxes_scaleLabel_fontStyle = $('#scales-xAxes-scaleLabel-fontStyle').val();

        //Scales Tick Configuration X
        var scales_xAxes_ticks_autoskip = $('#scales-xAxes-ticks-autoskip').val();
        var scales_xAxes_ticks_display = $('#scales-xAxes-ticks-display').val();
        var scales_xAxes_ticks_fontColor = $('#scales-xAxes-ticks-fontColor').val();
        var scales_xAxes_ticks_fontFamily = $('#scales-xAxes-ticks-fontFamily').val();
        var scales_xAxes_ticks_fontSize = $('#scales-xAxes-ticks-fontSize').val();
        var scales_xAxes_ticks_fontStyle = $('#scales-xAxes-ticks-fontStyle').val();
        var scales_xAxes_ticks_labelOffset = $('#scales-xAxes-ticks-labelOffset').val();
        var scales_xAxes_ticks_maxRotation = $('#scales-xAxes-ticks-maxRotation').val();
        var scales_xAxes_ticks_minRotation = $('#scales-xAxes-ticks-minRotation').val();
        var scales_xAxes_ticks_reverse = $('#scales-xAxes-ticks-reverse').val();
        var scales_xAxes_ticks_prefix = $('#scales-xAxes-ticks-prefix').val();
        var scales_xAxes_ticks_suffix = $('#scales-xAxes-ticks-suffix').val();
        var scales_xAxes_ticks_round = $('#scales-xAxes-ticks-round').val();

        //Scale Configuration Options X
        var scales_xAxes_ticks_min = $('#scales-xAxes-ticks-min').val();
        var scales_xAxes_ticks_max = $('#scales-xAxes-ticks-max').val();
        var scales_xAxes_ticks_beginAtZero = $('#scales-xAxes-ticks-beginAtZero').val();
        var scales_xAxes_ticks_maxTicksLimit = $('#scales-xAxes-ticks-maxTicksLimit').val();
        var scales_xAxes_ticks_stepSize = $('#scales-xAxes-ticks-stepSize').val();
        var scales_xAxes_ticks_suggestedMax = $('#scales-xAxes-ticks-suggestedMax').val();
        var scales_xAxes_ticks_suggestedMin = $('#scales-xAxes-ticks-suggestedMin').val();
        var scales_xAxes_ticks_fixedStepSize = $('#scales-xAxes-ticks-fixedStepSize').val();
        var scales_xAxes_categoryPercentage = $('#scales-xAxes-categoryPercentage').val();
        var scales_xAxes_barPercentage = $('#scales-xAxes-barPercentage').val();

        //Time Scale Configuration Options X
        var scales_xAxes_time_format = $('#scales-xAxes-time-format').val();
        var scales_xAxes_time_tooltipFormat = $('#scales-xAxes-time-tooltipFormat').val();
        var scales_xAxes_time_unit_format = $('#scales-xAxes-time-unit-format').val();
        var scales_xAxes_time_unit = $('#scales-xAxes-time-unit').val();
        var scales_xAxes_time_unitStepSize = $('#scales-xAxes-time-unitStepSize').val();
        var scales_xAxes_time_max = $('#scales-xAxes-time-max').val();
        var scales_xAxes_time_min = $('#scales-xAxes-time-min').val();

        //Scales Common Configuration Y
        var scales_yAxes_type = $('#scales-yAxes-type').val();
        var scales_yAxes_display = $('#scales-yAxes-display').val();
        var scales_yAxes_position = $('#scales-yAxes-position').val();
        var scales_yAxes_stacked = $('#scales-yAxes-stacked').val();

        //Scales Grid Line Configuration Y
        var scales_yAxes_gridLines_display = $('#scales-yAxes-gridLines-display').val();
        var scales_yAxes_gridLines_color = $('#scales-yAxes-gridLines-color').val();
        var scales_yAxes_gridLines_lineWidth = $('#scales-yAxes-gridLines-lineWidth').val();
        var scales_yAxes_gridLines_drawBorder = $('#scales-yAxes-gridLines-drawBorder').val();
        var scales_yAxes_gridLines_drawOnChartArea = $('#scales-yAxes-gridLines-drawOnChartArea').val();
        var scales_yAxes_gridLines_drawTicks = $('#scales-yAxes-gridLines-drawTicks').val();
        var scales_yAxes_gridLines_tickMarkLength = $('#scales-yAxes-gridLines-tickMarkLength').val();
        var scales_yAxes_gridLines_zeroLineWidth = $('#scales-yAxes-gridLines-zeroLineWidth').val();
        var scales_yAxes_gridLines_zeroLineColor = $('#scales-yAxes-gridLines-zeroLineColor').val();
        var scales_yAxes_gridLines_offsetGridLines = $('#scales-yAxes-gridLines-offsetGridLines').val();

        //Scales Title Configuration Y
        var scales_yAxes_scaleLabel_display = $('#scales-yAxes-scaleLabel-display').val();
        var scales_yAxes_scaleLabel_labelString = $('#scales-yAxes-scaleLabel-labelString').val();
        var scales_yAxes_scaleLabel_fontColor = $('#scales-yAxes-scaleLabel-fontColor').val();
        var scales_yAxes_scaleLabel_fontFamily = $('#scales-yAxes-scaleLabel-fontFamily').val();
        var scales_yAxes_scaleLabel_fontSize = $('#scales-yAxes-scaleLabel-fontSize').val();
        var scales_yAxes_scaleLabel_fontStyle = $('#scales-yAxes-scaleLabel-fontStyle').val();

        //Scales Tick Configuration Y
        var scales_yAxes_ticks_autoskip = $('#scales-yAxes-ticks-autoskip').val();
        var scales_yAxes_ticks_display = $('#scales-yAxes-ticks-display').val();
        var scales_yAxes_ticks_fontColor = $('#scales-yAxes-ticks-fontColor').val();
        var scales_yAxes_ticks_fontFamily = $('#scales-yAxes-ticks-fontFamily').val();
        var scales_yAxes_ticks_fontSize = $('#scales-yAxes-ticks-fontSize').val();
        var scales_yAxes_ticks_fontStyle = $('#scales-yAxes-ticks-fontStyle').val();
        var scales_yAxes_ticks_maxRotation = $('#scales-yAxes-ticks-maxRotation').val();
        var scales_yAxes_ticks_minRotation = $('#scales-yAxes-ticks-minRotation').val();
        var scales_yAxes_ticks_mirror = $('#scales-yAxes-ticks-mirror').val();
        var scales_yAxes_ticks_padding = $('#scales-yAxes-ticks-padding').val();
        var scales_yAxes_ticks_reverse = $('#scales-yAxes-ticks-reverse').val();
        var scales_yAxes_ticks_prefix = $('#scales-yAxes-ticks-prefix').val();
        var scales_yAxes_ticks_suffix = $('#scales-yAxes-ticks-suffix').val();
        var scales_yAxes_ticks_round = $('#scales-yAxes-ticks-round').val();

        //Scale Configuration Options Y
        var scales_yAxes_ticks_min = $('#scales-yAxes-ticks-min').val();
        var scales_yAxes_ticks_max = $('#scales-yAxes-ticks-max').val();
        var scales_yAxes_ticks_beginAtZero = $('#scales-yAxes-ticks-beginAtZero').val();
        var scales_yAxes_ticks_maxTicksLimit = $('#scales-yAxes-ticks-maxTicksLimit').val();
        var scales_yAxes_ticks_stepSize = $('#scales-yAxes-ticks-stepSize').val();
        var scales_yAxes_ticks_suggestedMax = $('#scales-yAxes-ticks-suggestedMax').val();
        var scales_yAxes_ticks_suggestedMin = $('#scales-yAxes-ticks-suggestedMin').val();
        var scales_yAxes_ticks_fixedStepSize = $('#scales-yAxes-ticks-fixedStepSize').val();
        var scales_yAxes_categoryPercentage = $('#scales-yAxes-categoryPercentage').val();
        var scales_yAxes_barPercentage = $('#scales-yAxes-barPercentage').val();

        //Scales Common Configuration Y2
        var scales_y2Axes_type = $('#scales-y2Axes-type').val();
        var scales_y2Axes_display = $('#scales-y2Axes-display').val();
        var scales_y2Axes_position = $('#scales-y2Axes-position').val();

        //Scales Grid Line Configuration Y2
        var scales_y2Axes_gridLines_display = $('#scales-y2Axes-gridLines-display').val();
        var scales_y2Axes_gridLines_color = $('#scales-y2Axes-gridLines-color').val();
        var scales_y2Axes_gridLines_lineWidth = $('#scales-y2Axes-gridLines-lineWidth').val();
        var scales_y2Axes_gridLines_drawBorder = $('#scales-y2Axes-gridLines-drawBorder').val();
        var scales_y2Axes_gridLines_drawOnChartArea = $('#scales-y2Axes-gridLines-drawOnChartArea').val();
        var scales_y2Axes_gridLines_drawTicks = $('#scales-y2Axes-gridLines-drawTicks').val();
        var scales_y2Axes_gridLines_tickMarkLength = $('#scales-y2Axes-gridLines-tickMarkLength').val();
        var scales_y2Axes_gridLines_zeroLineWidth = $('#scales-y2Axes-gridLines-zeroLineWidth').val();
        var scales_y2Axes_gridLines_zeroLineColor = $('#scales-y2Axes-gridLines-zeroLineColor').val();
        var scales_y2Axes_gridLines_offsetGridLines = $('#scales-y2Axes-gridLines-offsetGridLines').val();

        //Scales Title Configuration Y2
        var scales_y2Axes_scaleLabel_display = $('#scales-y2Axes-scaleLabel-display').val();
        var scales_y2Axes_scaleLabel_labelString = $('#scales-y2Axes-scaleLabel-labelString').val();
        var scales_y2Axes_scaleLabel_fontColor = $('#scales-y2Axes-scaleLabel-fontColor').val();
        var scales_y2Axes_scaleLabel_fontFamily = $('#scales-y2Axes-scaleLabel-fontFamily').val();
        var scales_y2Axes_scaleLabel_fontSize = $('#scales-y2Axes-scaleLabel-fontSize').val();
        var scales_y2Axes_scaleLabel_fontStyle = $('#scales-y2Axes-scaleLabel-fontStyle').val();

        //Scales Tick Configuration Y2
        var scales_y2Axes_ticks_autoskip = $('#scales-y2Axes-ticks-autoskip').val();
        var scales_y2Axes_ticks_display = $('#scales-y2Axes-ticks-display').val();
        var scales_y2Axes_ticks_fontColor = $('#scales-y2Axes-ticks-fontColor').val();
        var scales_y2Axes_ticks_fontFamily = $('#scales-y2Axes-ticks-fontFamily').val();
        var scales_y2Axes_ticks_fontSize = $('#scales-y2Axes-ticks-fontSize').val();
        var scales_y2Axes_ticks_fontStyle = $('#scales-y2Axes-ticks-fontStyle').val();
        var scales_y2Axes_ticks_maxRotation = $('#scales-y2Axes-ticks-maxRotation').val();
        var scales_y2Axes_ticks_minRotation = $('#scales-y2Axes-ticks-minRotation').val();
        var scales_y2Axes_ticks_mirror = $('#scales-y2Axes-ticks-mirror').val();
        var scales_y2Axes_ticks_padding = $('#scales-y2Axes-ticks-padding').val();
        var scales_y2Axes_ticks_reverse = $('#scales-y2Axes-ticks-reverse').val();
        var scales_y2Axes_ticks_prefix = $('#scales-y2Axes-ticks-prefix').val();
        var scales_y2Axes_ticks_suffix = $('#scales-y2Axes-ticks-suffix').val();
        var scales_y2Axes_ticks_round = $('#scales-y2Axes-ticks-round').val();

        //Scale Configuration Options Y2
        var scales_y2Axes_ticks_min = $('#scales-y2Axes-ticks-min').val();
        var scales_y2Axes_ticks_max = $('#scales-y2Axes-ticks-max').val();
        var scales_y2Axes_ticks_beginAtZero = $('#scales-y2Axes-ticks-beginAtZero').val();
        var scales_y2Axes_ticks_maxTicksLimit = $('#scales-y2Axes-ticks-maxTicksLimit').val();
        var scales_y2Axes_ticks_stepSize = $('#scales-y2Axes-ticks-stepSize').val();
        var scales_y2Axes_ticks_suggestedMax = $('#scales-y2Axes-ticks-suggestedMax').val();
        var scales_y2Axes_ticks_suggestedMin = $('#scales-y2Axes-ticks-suggestedMin').val();
        var scales_y2Axes_ticks_fixedStepSize = $('#scales-y2Axes-ticks-fixedStepSize').val();

        /* RL Scale Common Configuration */
        var scales_rl_display = $('#scales-rl-display').val();

        /* RL Scale Grid Line Configuration */
        var scales_rl_gridLines_display = $('#scales-rl-gridLines-display').val();
        var scales_rl_gridLines_color = $('#scales-rl-gridLines-color').val();
        var scales_rl_gridLines_lineWidth = $('#scales-rl-gridLines-lineWidth').val();

        /* RL Scale Angle Line Configuration */
        var scales_rl_angleLines_display = $('#scales-rl-angleLines-display').val();
        var scales_rl_angleLines_color = $('#scales-rl-angleLines-color').val();
        var scales_rl_angleLines_lineWidth = $('#scales-rl-angleLines-lineWidth').val();

        /* RL Scale Point Label Configuration */
        var scales_rl_pointLabels_fontSize = $('#scales-rl-pointLabels-fontSize').val();
        var scales_rl_pointLabels_fontColor = $('#scales-rl-pointLabels-fontColor').val();
        var scales_rl_pointLabels_fontFamily = $('#scales-rl-pointLabels-fontFamily').val();
        var scales_rl_pointLabels_fontStyle = $('#scales-rl-pointLabels-fontStyle').val();

        /* RL Scale Tick Configuration */
        var scales_rl_ticks_display = $('#scales-rl-ticks-display').val();
        var scales_rl_ticks_autoskip = $('#scales-rl-ticks-autoskip').val();
        var scales_rl_ticks_reverse = $('#scales-rl-ticks-reverse').val();
        var scales_rl_ticks_prefix = $('#scales-rl-ticks-prefix').val();
        var scales_rl_ticks_suffix = $('#scales-rl-ticks-suffix').val();
        var scales_rl_ticks_round = $('#scales-rl-ticks-round').val();
        var scales_rl_ticks_fontSize = $('#scales-rl-ticks-fontSize').val();
        var scales_rl_ticks_fontColor = $('#scales-rl-ticks-fontColor').val();
        var scales_rl_ticks_fontFamily = $('#scales-rl-ticks-fontFamily').val();
        var scales_rl_ticks_fontStyle = $('#scales-rl-ticks-fontStyle').val();

        /* RL Scale Configuration Options */
        var scales_rl_ticks_min = $('#scales-rl-ticks-min').val();
        var scales_rl_ticks_max = $('#scales-rl-ticks-max').val();
        var scales_rl_ticks_suggestedMin = $('#scales-rl-ticks-suggestedMin').val();
        var scales_rl_ticks_suggestedMax = $('#scales-rl-ticks-suggestedMax').val();
        var scales_rl_ticks_stepSize = $('#scales-rl-ticks-stepSize').val();
        var scales_rl_ticks_fixedStepSize = $('#scales-rl-ticks-fixedStepSize').val();
        var scales_rl_ticks_maxTicksLimit = $('#scales-rl-ticks-maxTicksLimit').val();
        var scales_rl_ticks_beginAtZero = $('#scales-rl-ticks-beginAtZero').val();
        var scales_rl_ticks_showLabelBackdrop = $('#scales-rl-ticks-showLabelBackdrop').val();
        var scales_rl_ticks_backdropColor = $('#scales-rl-ticks-backdropColor').val();
        var scales_rl_ticks_backdropPaddingX = $('#scales-rl-ticks-backdropPaddingX').val();
        var scales_rl_ticks_backdropPaddingY = $('#scales-rl-ticks-backdropPaddingY').val();

        //save the table data as JSON
        var chart_data = JSON.stringify({data: dauc_hot.getData()});

        //prepare ajax request
        var data = {
            "action": "save_data",
            "security": dauc_nonce,
            "chart_id": chart_id,
            "name": name,
            "description": description,
            "type": type,
            "rows": rows,
            "columns": columns,
            "is_model": is_model,

            //Common Chart Configuration
            "canvas_transparent_background": canvas_transparent_background,
            "canvas_backgroundColor": canvas_backgroundColor,
            "margin_top": margin_top,
            "margin_bottom": margin_bottom,
            "width": width,
            "height": height,
            "responsive": responsive,
            "responsiveAnimationDuration": responsiveAnimationDuration,
            "maintainAspectRatio": maintainAspectRatio,
            "fixed_height": fixed_height,

            //Title Configuration
            "title_display": title_display,
            "title_position": title_position,
            "title_fullWidth": title_fullWidth,
            "title_fontSize": title_fontSize,
            "title_fontFamily": title_fontFamily,
            "title_fontColor": title_fontColor,
            "title_fontStyle": title_fontStyle,
            "title_padding": title_padding,

            //Legend Configuration
            "legend_display": legend_display,
            "legend_position": legend_position,
            "legend_fullWidth": legend_fullWidth,
            "legend_toggle_dataset": legend_toggle_dataset,

            //Legend Label Configuration
            "legend_labels_boxWidth": legend_labels_boxWidth,
            "legend_labels_fontSize": legend_labels_fontSize,
            "legend_labels_fontStyle": legend_labels_fontStyle,
            "legend_labels_fontColor": legend_labels_fontColor,
            "legend_labels_fontFamily": legend_labels_fontFamily,
            "legend_labels_padding": legend_labels_padding,

            //Tooltip Configuration
            "tooltips_enabled": tooltips_enabled,
            "tooltips_mode": tooltips_mode,
            "tooltips_backgroundColor": tooltips_backgroundColor,
            "tooltips_titleFontFamily": tooltips_titleFontFamily,
            "tooltips_titleFontSize": tooltips_titleFontSize,
            "tooltips_titleFontStyle": tooltips_titleFontStyle,
            "tooltips_titleFontColor": tooltips_titleFontColor,
            "tooltips_titleMarginBottom": tooltips_titleMarginBottom,
            "tooltips_bodyFontFamily": tooltips_bodyFontFamily,
            "tooltips_bodyFontSize": tooltips_bodyFontSize,
            "tooltips_bodyFontStyle": tooltips_bodyFontStyle,
            "tooltips_bodyFontColor": tooltips_bodyFontColor,
            "tooltips_footerFontFamily": tooltips_footerFontFamily,
            "tooltips_footerFontSize": tooltips_footerFontSize,
            "tooltips_footerFontStyle": tooltips_footerFontStyle,
            "tooltips_footerFontColor": tooltips_footerFontColor,
            "tooltips_footerMarginTop": tooltips_footerMarginTop,
            "tooltips_xPadding": tooltips_xPadding,
            "tooltips_yPadding": tooltips_yPadding,
            "tooltips_caretSize": tooltips_caretSize,
            "tooltips_cornerRadius": tooltips_cornerRadius,
            "tooltips_multiKeyBackground": tooltips_multiKeyBackground,
            "tooltips_beforeTitle": tooltips_beforeTitle,
            "tooltips_afterTitle": tooltips_afterTitle,
            "tooltips_beforeBody": tooltips_beforeBody,
            "tooltips_afterBody": tooltips_afterBody,
            "tooltips_beforeLabel": tooltips_beforeLabel,
            "tooltips_afterLabel": tooltips_afterLabel,
            "tooltips_beforeFooter": tooltips_beforeFooter,
            "tooltips_footer": tooltips_footer,
            "tooltips_afterFooter": tooltips_afterFooter,

            //Hover Configuration
            "hover_animationDuration": hover_animationDuration,

            //Animation Configuration
            "animation_duration": animation_duration,
            "animation_easing": animation_easing,
            "animation_animateRotate": animation_animateRotate,
            "animation_animateScale": animation_animateScale,

            //Misc Configuration
            "elements_rectangle_borderSkipped": elements_rectangle_borderSkipped,

            //Scales Common Configuration X
            scales_xAxes_type: scales_xAxes_type,
            scales_xAxes_display: scales_xAxes_display,
            scales_xAxes_position: scales_xAxes_position,
            scales_xAxes_stacked: scales_xAxes_stacked,

            //Scales Grid Line Configuration X
            scales_xAxes_gridLines_display: scales_xAxes_gridLines_display,
            scales_xAxes_gridLines_color: scales_xAxes_gridLines_color,
            scales_xAxes_gridLines_lineWidth: scales_xAxes_gridLines_lineWidth,
            scales_xAxes_gridLines_drawBorder: scales_xAxes_gridLines_drawBorder,
            scales_xAxes_gridLines_drawOnChartArea: scales_xAxes_gridLines_drawOnChartArea,
            scales_xAxes_gridLines_drawTicks: scales_xAxes_gridLines_drawTicks,
            scales_xAxes_gridLines_tickMarkLength: scales_xAxes_gridLines_tickMarkLength,
            scales_xAxes_gridLines_zeroLineWidth: scales_xAxes_gridLines_zeroLineWidth,
            scales_xAxes_gridLines_zeroLineColor: scales_xAxes_gridLines_zeroLineColor,
            scales_xAxes_gridLines_offsetGridLines: scales_xAxes_gridLines_offsetGridLines,

            //Scales Title Configuration X
            scales_xAxes_scaleLabel_display: scales_xAxes_scaleLabel_display,
            scales_xAxes_scaleLabel_labelString: scales_xAxes_scaleLabel_labelString,
            scales_xAxes_scaleLabel_fontColor: scales_xAxes_scaleLabel_fontColor,
            scales_xAxes_scaleLabel_fontFamily: scales_xAxes_scaleLabel_fontFamily,
            scales_xAxes_scaleLabel_fontSize: scales_xAxes_scaleLabel_fontSize,
            scales_xAxes_scaleLabel_fontStyle: scales_xAxes_scaleLabel_fontStyle,

            //Scales Tick Configuration X
            scales_xAxes_ticks_autoskip: scales_xAxes_ticks_autoskip,
            scales_xAxes_ticks_display: scales_xAxes_ticks_display,
            scales_xAxes_ticks_fontColor: scales_xAxes_ticks_fontColor,
            scales_xAxes_ticks_fontFamily: scales_xAxes_ticks_fontFamily,
            scales_xAxes_ticks_fontSize: scales_xAxes_ticks_fontSize,
            scales_xAxes_ticks_fontStyle: scales_xAxes_ticks_fontStyle,
            scales_xAxes_ticks_labelOffset: scales_xAxes_ticks_labelOffset,
            scales_xAxes_ticks_maxRotation: scales_xAxes_ticks_maxRotation,
            scales_xAxes_ticks_minRotation: scales_xAxes_ticks_minRotation,
            scales_xAxes_ticks_reverse: scales_xAxes_ticks_reverse,
            scales_xAxes_ticks_prefix: scales_xAxes_ticks_prefix,
            scales_xAxes_ticks_suffix: scales_xAxes_ticks_suffix,
            scales_xAxes_ticks_round: scales_xAxes_ticks_round,

            //Scale Configuration Options X
            scales_xAxes_ticks_min: scales_xAxes_ticks_min,
            scales_xAxes_ticks_max: scales_xAxes_ticks_max,
            scales_xAxes_ticks_beginAtZero: scales_xAxes_ticks_beginAtZero,
            scales_xAxes_ticks_maxTicksLimit: scales_xAxes_ticks_maxTicksLimit,
            scales_xAxes_ticks_stepSize: scales_xAxes_ticks_stepSize,
            scales_xAxes_ticks_suggestedMax: scales_xAxes_ticks_suggestedMax,
            scales_xAxes_ticks_suggestedMin: scales_xAxes_ticks_suggestedMin,
            scales_xAxes_ticks_fixedStepSize: scales_xAxes_ticks_fixedStepSize,
            scales_xAxes_categoryPercentage: scales_xAxes_categoryPercentage,
            scales_xAxes_barPercentage: scales_xAxes_barPercentage,

            //Time Scale Configuration Options X
            scales_xAxes_time_format: scales_xAxes_time_format,
            scales_xAxes_time_tooltipFormat: scales_xAxes_time_tooltipFormat,
            scales_xAxes_time_unit_format: scales_xAxes_time_unit_format,
            scales_xAxes_time_unit: scales_xAxes_time_unit,
            scales_xAxes_time_unitStepSize: scales_xAxes_time_unitStepSize,
            scales_xAxes_time_max: scales_xAxes_time_max,
            scales_xAxes_time_min: scales_xAxes_time_min,

            //Scales Common Configuration Y
            scales_yAxes_type: scales_yAxes_type,
            scales_yAxes_display: scales_yAxes_display,
            scales_yAxes_position: scales_yAxes_position,
            scales_yAxes_stacked: scales_yAxes_stacked,

            //Scales Grid Line Configuration Y
            scales_yAxes_gridLines_display: scales_yAxes_gridLines_display,
            scales_yAxes_gridLines_color: scales_yAxes_gridLines_color,
            scales_yAxes_gridLines_lineWidth: scales_yAxes_gridLines_lineWidth,
            scales_yAxes_gridLines_drawBorder: scales_yAxes_gridLines_drawBorder,
            scales_yAxes_gridLines_drawOnChartArea: scales_yAxes_gridLines_drawOnChartArea,
            scales_yAxes_gridLines_drawTicks: scales_yAxes_gridLines_drawTicks,
            scales_yAxes_gridLines_tickMarkLength: scales_yAxes_gridLines_tickMarkLength,
            scales_yAxes_gridLines_zeroLineWidth: scales_yAxes_gridLines_zeroLineWidth,
            scales_yAxes_gridLines_zeroLineColor: scales_yAxes_gridLines_zeroLineColor,
            scales_yAxes_gridLines_offsetGridLines: scales_yAxes_gridLines_offsetGridLines,

            //Scales Title Configuration Y
            scales_yAxes_scaleLabel_display: scales_yAxes_scaleLabel_display,
            scales_yAxes_scaleLabel_labelString: scales_yAxes_scaleLabel_labelString,
            scales_yAxes_scaleLabel_fontColor: scales_yAxes_scaleLabel_fontColor,
            scales_yAxes_scaleLabel_fontFamily: scales_yAxes_scaleLabel_fontFamily,
            scales_yAxes_scaleLabel_fontSize: scales_yAxes_scaleLabel_fontSize,
            scales_yAxes_scaleLabel_fontStyle: scales_yAxes_scaleLabel_fontStyle,

            //Scales Tick Configuration Y
            scales_yAxes_ticks_autoskip: scales_yAxes_ticks_autoskip,
            scales_yAxes_ticks_display: scales_yAxes_ticks_display,
            scales_yAxes_ticks_fontColor: scales_yAxes_ticks_fontColor,
            scales_yAxes_ticks_fontFamily: scales_yAxes_ticks_fontFamily,
            scales_yAxes_ticks_fontSize: scales_yAxes_ticks_fontSize,
            scales_yAxes_ticks_fontStyle: scales_yAxes_ticks_fontStyle,
            scales_yAxes_ticks_maxRotation: scales_yAxes_ticks_maxRotation,
            scales_yAxes_ticks_minRotation: scales_yAxes_ticks_minRotation,
            scales_yAxes_ticks_mirror: scales_yAxes_ticks_mirror,
            scales_yAxes_ticks_padding: scales_yAxes_ticks_padding,
            scales_yAxes_ticks_reverse: scales_yAxes_ticks_reverse,
            scales_yAxes_ticks_prefix: scales_yAxes_ticks_prefix,
            scales_yAxes_ticks_suffix: scales_yAxes_ticks_suffix,
            scales_yAxes_ticks_round: scales_yAxes_ticks_round,

            //Scale Configuration Options Y
            scales_yAxes_ticks_min: scales_yAxes_ticks_min,
            scales_yAxes_ticks_max: scales_yAxes_ticks_max,
            scales_yAxes_ticks_beginAtZero: scales_yAxes_ticks_beginAtZero,
            scales_yAxes_ticks_maxTicksLimit: scales_yAxes_ticks_maxTicksLimit,
            scales_yAxes_ticks_stepSize: scales_yAxes_ticks_stepSize,
            scales_yAxes_ticks_suggestedMax: scales_yAxes_ticks_suggestedMax,
            scales_yAxes_ticks_suggestedMin: scales_yAxes_ticks_suggestedMin,
            scales_yAxes_ticks_fixedStepSize: scales_yAxes_ticks_fixedStepSize,
            scales_yAxes_categoryPercentage: scales_yAxes_categoryPercentage,
            scales_yAxes_barPercentage: scales_yAxes_barPercentage,

            //Scales Common Configuration Y2
            scales_y2Axes_type: scales_y2Axes_type,
            scales_y2Axes_display: scales_y2Axes_display,
            scales_y2Axes_position: scales_y2Axes_position,

            //Scales Grid Line Configuration Y2
            scales_y2Axes_gridLines_display: scales_y2Axes_gridLines_display,
            scales_y2Axes_gridLines_color: scales_y2Axes_gridLines_color,
            scales_y2Axes_gridLines_lineWidth: scales_y2Axes_gridLines_lineWidth,
            scales_y2Axes_gridLines_drawBorder: scales_y2Axes_gridLines_drawBorder,
            scales_y2Axes_gridLines_drawOnChartArea: scales_y2Axes_gridLines_drawOnChartArea,
            scales_y2Axes_gridLines_drawTicks: scales_y2Axes_gridLines_drawTicks,
            scales_y2Axes_gridLines_tickMarkLength: scales_y2Axes_gridLines_tickMarkLength,
            scales_y2Axes_gridLines_zeroLineWidth: scales_y2Axes_gridLines_zeroLineWidth,
            scales_y2Axes_gridLines_zeroLineColor: scales_y2Axes_gridLines_zeroLineColor,
            scales_y2Axes_gridLines_offsetGridLines: scales_y2Axes_gridLines_offsetGridLines,

            //Scales Title Configuration Y2
            scales_y2Axes_scaleLabel_display: scales_y2Axes_scaleLabel_display,
            scales_y2Axes_scaleLabel_labelString: scales_y2Axes_scaleLabel_labelString,
            scales_y2Axes_scaleLabel_fontColor: scales_y2Axes_scaleLabel_fontColor,
            scales_y2Axes_scaleLabel_fontFamily: scales_y2Axes_scaleLabel_fontFamily,
            scales_y2Axes_scaleLabel_fontSize: scales_y2Axes_scaleLabel_fontSize,
            scales_y2Axes_scaleLabel_fontStyle: scales_y2Axes_scaleLabel_fontStyle,

            //Scales Tick Configuration Y2
            scales_y2Axes_ticks_autoskip: scales_y2Axes_ticks_autoskip,
            scales_y2Axes_ticks_display: scales_y2Axes_ticks_display,
            scales_y2Axes_ticks_fontColor: scales_y2Axes_ticks_fontColor,
            scales_y2Axes_ticks_fontFamily: scales_y2Axes_ticks_fontFamily,
            scales_y2Axes_ticks_fontSize: scales_y2Axes_ticks_fontSize,
            scales_y2Axes_ticks_fontStyle: scales_y2Axes_ticks_fontStyle,
            scales_y2Axes_ticks_maxRotation: scales_y2Axes_ticks_maxRotation,
            scales_y2Axes_ticks_minRotation: scales_y2Axes_ticks_minRotation,
            scales_y2Axes_ticks_mirror: scales_y2Axes_ticks_mirror,
            scales_y2Axes_ticks_padding: scales_y2Axes_ticks_padding,
            scales_y2Axes_ticks_reverse: scales_y2Axes_ticks_reverse,
            scales_y2Axes_ticks_prefix: scales_y2Axes_ticks_prefix,
            scales_y2Axes_ticks_suffix: scales_y2Axes_ticks_suffix,
            scales_y2Axes_ticks_round: scales_y2Axes_ticks_round,

            //Scale Configuration Options Y2
            scales_y2Axes_ticks_min: scales_y2Axes_ticks_min,
            scales_y2Axes_ticks_max: scales_y2Axes_ticks_max,
            scales_y2Axes_ticks_beginAtZero: scales_y2Axes_ticks_beginAtZero,
            scales_y2Axes_ticks_maxTicksLimit: scales_y2Axes_ticks_maxTicksLimit,
            scales_y2Axes_ticks_stepSize: scales_y2Axes_ticks_stepSize,
            scales_y2Axes_ticks_suggestedMax: scales_y2Axes_ticks_suggestedMax,
            scales_y2Axes_ticks_suggestedMin: scales_y2Axes_ticks_suggestedMin,
            scales_y2Axes_ticks_fixedStepSize: scales_y2Axes_ticks_fixedStepSize,

            /* RL Scale Common Configuration */
            scales_rl_display: scales_rl_display,

            /* RL Scale Grid Line Configuration */
            scales_rl_gridLines_display: scales_rl_gridLines_display,
            scales_rl_gridLines_color: scales_rl_gridLines_color,
            scales_rl_gridLines_lineWidth: scales_rl_gridLines_lineWidth,

            /* RL Scale Angle Line Configuration */
            scales_rl_angleLines_display: scales_rl_angleLines_display,
            scales_rl_angleLines_color: scales_rl_angleLines_color,
            scales_rl_angleLines_lineWidth: scales_rl_angleLines_lineWidth,

            /* RL Scale Point Label Configuration */
            scales_rl_pointLabels_fontSize: scales_rl_pointLabels_fontSize,
            scales_rl_pointLabels_fontColor: scales_rl_pointLabels_fontColor,
            scales_rl_pointLabels_fontFamily: scales_rl_pointLabels_fontFamily,
            scales_rl_pointLabels_fontStyle: scales_rl_pointLabels_fontStyle,

            /* RL Scale Tick Configuration */
            scales_rl_ticks_display: scales_rl_ticks_display,
            scales_rl_ticks_autoskip: scales_rl_ticks_autoskip,
            scales_rl_ticks_reverse: scales_rl_ticks_reverse,
            scales_rl_ticks_prefix: scales_rl_ticks_prefix,
            scales_rl_ticks_suffix: scales_rl_ticks_suffix,
            scales_rl_ticks_round: scales_rl_ticks_round,
            scales_rl_ticks_fontSize: scales_rl_ticks_fontSize,
            scales_rl_ticks_fontColor: scales_rl_ticks_fontColor,
            scales_rl_ticks_fontFamily: scales_rl_ticks_fontFamily,
            scales_rl_ticks_fontStyle: scales_rl_ticks_fontStyle,

            /* RL Scale Configuration Options */
            scales_rl_ticks_min: scales_rl_ticks_min,
            scales_rl_ticks_max: scales_rl_ticks_max,
            scales_rl_ticks_suggestedMin: scales_rl_ticks_suggestedMin,
            scales_rl_ticks_suggestedMax: scales_rl_ticks_suggestedMax,
            scales_rl_ticks_stepSize: scales_rl_ticks_stepSize,
            scales_rl_ticks_fixedStepSize: scales_rl_ticks_fixedStepSize,
            scales_rl_ticks_maxTicksLimit: scales_rl_ticks_maxTicksLimit,
            scales_rl_ticks_beginAtZero: scales_rl_ticks_beginAtZero,
            scales_rl_ticks_showLabelBackdrop: scales_rl_ticks_showLabelBackdrop,
            scales_rl_ticks_backdropColor: scales_rl_ticks_backdropColor,
            scales_rl_ticks_backdropPaddingX: scales_rl_ticks_backdropPaddingX,
            scales_rl_ticks_backdropPaddingY: scales_rl_ticks_backdropPaddingY,

            "chart_data": chart_data

        };

        var validation_result = chart_is_valid(data);
        if(validation_result === true){

            //set ajax in synchronous mode
            jQuery.ajaxSetup({async:false});

            //send ajax request
            $.post(dauc_ajax_url, data, function(data) {

                if(reload_menu === true){

                    //reload the dashboard menu
                    window.location.replace(dauc_admin_url + 'admin.php?page=dauc-charts');

                }

            });

            //set ajax in asynchronous mode
            jQuery.ajaxSetup({async:true});

            return true;

        }else{

            return validation_result;

        }

    }

    /*
    Moves the data structure section on the bottom of the chart section when the screen width goes below a specific
    value.
     */
    function responsive_sidebar_container(){

        if( $('#wpcontent').width() < 1528 ){

            $('.sidebar-container').addClass('sidebar-container-below-breakpoint');

        }else{

            $('.sidebar-container').removeClass('sidebar-container-below-breakpoint');

        }

    }

    /*
     Update the selection of a specific chosen field (field_selector) based on the provided value (selected_value)
     */
    function update_chosen_field(field_selector, selected_value){

        $( field_selector + " option" ).removeAttr('selected');
        $( field_selector + " option[value=" + selected_value + "]" ).attr("selected", "selected");
        $( field_selector ).trigger("chosen:updated");

    }

    /*
    Initializes Chosen on all the select elements and disables chosen on the select elements that are currently disabled
     */
    function initialize_chosen(){

        //initialize chosen on all the select elements -----------------------------------------------------------------
        var chosen_elements = [];
        chosen_elements.push('#load-model');
        chosen_elements.push('#type');
        chosen_elements.push('#defaultFontStyle');
        chosen_elements.push('#canvas-transparent-background');
        chosen_elements.push('#responsive');
        chosen_elements.push('#maintainAspectRatio');
        chosen_elements.push('#is-model');
        chosen_elements.push('#title-display');
        chosen_elements.push('#title-position');
        chosen_elements.push('#title-fullWidth');
        chosen_elements.push('#title-fontStyle');
        chosen_elements.push('#legend-display');
        chosen_elements.push('#legend-position');
        chosen_elements.push('#legend-fullWidth');
        chosen_elements.push('#legend-toggle-dataset');
        chosen_elements.push('#legend-labels-fontStyle');
        chosen_elements.push('#tooltips-enabled');
        chosen_elements.push('#tooltips-mode');
        chosen_elements.push('#tooltips-titleFontStyle');
        chosen_elements.push('#tooltips-bodyFontStyle');
        chosen_elements.push('#tooltips-footerFontStyle');
        chosen_elements.push('#hover-mode');
        chosen_elements.push('#animation-easing');
        chosen_elements.push('#animation-animateRotate');
        chosen_elements.push('#animation-animateScale');
        chosen_elements.push('#defaultFontStyle');
        chosen_elements.push('#elements-rectangle-borderSkipped');
        chosen_elements.push('#elements-line-borderCapStyle');
        chosen_elements.push('#elements-line-borderJoinStyle');
        chosen_elements.push('#elements-line-capBezierPoints');
        chosen_elements.push('#elements-line-fill');
        chosen_elements.push('#elements-line-stepped');
        chosen_elements.push('#elements-point-pointStyle');
        chosen_elements.push('#scales-xAxes-type');
        chosen_elements.push('#scales-xAxes-display');
        chosen_elements.push('#scales-xAxes-position');
        chosen_elements.push('#scales-xAxes-stacked');
        chosen_elements.push('#scales-xAxes-gridLines-display');
        chosen_elements.push('#scales-xAxes-gridLines-drawBorder');
        chosen_elements.push('#scales-xAxes-gridLines-drawOnChartArea');
        chosen_elements.push('#scales-xAxes-gridLines-drawTicks');
        chosen_elements.push('#scales-xAxes-gridLines-offsetGridLines');
        chosen_elements.push('#scales-xAxes-scaleLabel-display');
        chosen_elements.push('#scales-xAxes-scaleLabel-fontStyle');
        chosen_elements.push('#scales-xAxes-ticks-autoskip');
        chosen_elements.push('#scales-xAxes-ticks-display');
        chosen_elements.push('#scales-xAxes-ticks-fontStyle');
        chosen_elements.push('#scales-xAxes-ticks-reverse');
        chosen_elements.push('#scales-xAxes-ticks-beginAtZero');
        chosen_elements.push('#scales-xAxes-time-unit');
        chosen_elements.push('#scales-yAxes-type');
        chosen_elements.push('#scales-yAxes-display');
        chosen_elements.push('#scales-yAxes-position');
        chosen_elements.push('#scales-yAxes-stacked');
        chosen_elements.push('#scales-yAxes-gridLines-display');
        chosen_elements.push('#scales-yAxes-gridLines-drawBorder');
        chosen_elements.push('#scales-yAxes-gridLines-drawOnChartArea');
        chosen_elements.push('#scales-yAxes-gridLines-drawTicks');
        chosen_elements.push('#scales-yAxes-gridLines-offsetGridLines');
        chosen_elements.push('#scales-yAxes-scaleLabel-display');
        chosen_elements.push('#scales-yAxes-scaleLabel-fontStyle');
        chosen_elements.push('#scales-yAxes-ticks-autoskip');
        chosen_elements.push('#scales-yAxes-ticks-display');
        chosen_elements.push('#scales-yAxes-ticks-fontStyle');
        chosen_elements.push('#scales-yAxes-ticks-mirror');
        chosen_elements.push('#scales-yAxes-ticks-reverse');
        chosen_elements.push('#scales-yAxes-ticks-beginAtZero');
        chosen_elements.push('#scales-y2Axes-type');
        chosen_elements.push('#scales-y2Axes-display');
        chosen_elements.push('#scales-y2Axes-position');
        chosen_elements.push('#scales-y2Axes-gridLines-display');
        chosen_elements.push('#scales-y2Axes-gridLines-drawBorder');
        chosen_elements.push('#scales-y2Axes-gridLines-drawOnChartArea');
        chosen_elements.push('#scales-y2Axes-gridLines-drawTicks');
        chosen_elements.push('#scales-y2Axes-gridLines-offsetGridLines');
        chosen_elements.push('#scales-y2Axes-scaleLabel-display');
        chosen_elements.push('#scales-y2Axes-scaleLabel-fontStyle');
        chosen_elements.push('#scales-y2Axes-ticks-autoskip');
        chosen_elements.push('#scales-y2Axes-ticks-display');
        chosen_elements.push('#scales-y2Axes-ticks-fontStyle');
        chosen_elements.push('#scales-y2Axes-ticks-mirror');
        chosen_elements.push('#scales-y2Axes-ticks-reverse');
        chosen_elements.push('#scales-y2Axes-ticks-beginAtZero');
        chosen_elements.push('#scales-rl-display');
        chosen_elements.push('#scales-rl-gridLines-display');
        chosen_elements.push('#scales-rl-angleLines-display');
        chosen_elements.push('#scales-rl-pointLabels-fontStyle');
        chosen_elements.push('#scales-rl-ticks-display');
        chosen_elements.push('#scales-rl-ticks-autoskip');
        chosen_elements.push('#scales-rl-ticks-reverse');
        chosen_elements.push('#scales-rl-ticks-fontStyle');
        chosen_elements.push('#scales-rl-ticks-showLabelBackdrop');
        chosen_elements.push('#data-structure-fill');
        chosen_elements.push('#data-structure-borderCapStyle');
        chosen_elements.push('#data-structure-borderJoinStyle');
        chosen_elements.push('#data-structure-showLine');
        chosen_elements.push('#data-structure-spanGaps');
        chosen_elements.push('#data-structure-plotY2');

        jQuery(chosen_elements.join(',')).chosen();

        //disable all the chosen elements that have the attribute disabled="disabled" ----------------------------------
        $.each(chosen_elements, function( index, chosen_element ) {
            $(chosen_element + ':disabled').trigger('chosen:updated');
        });

    }

    /*
    Initialize the handsontable table
     */
    function initialize_handsontable(){

        /*
         If the form is in edit mode retrieve the data of the chart based on the chart id, otherwise initialize an empty
         table.
         */
        if(parseInt($('#update-id').val())>0){

            //prepare ajax request
            var data = {
                "action": "retrieve_chart_data",
                "security": dauc_nonce,
                "chart_id": $('#update-id').val()
            };

            //set ajax in synchronous mode
            jQuery.ajaxSetup({async:false});

            //send ajax request
            $.post(dauc_ajax_url, data, function(data_json) {

                //initialize the table with the retrieved data
                var data_obj = JSON.parse(data_json);

                $.each(data_obj, function( index, value ) {
                    dauc_data.push(value);
                });

                dauc_max_rows = parseInt($('#rows').val(), 10) + 1;
                dauc_max_columns = parseInt($('#columns').val(), 10);

            });

            //set ajax in asynchronous mode
            jQuery.ajaxSetup({async:true});

        }else{

            //initialize an empty table
            dauc_data = [
                ['Label 1','Label 2','Label 3','Label 4','Label 5','Label 6','Label 7','Label 8','Label 9','Label 10'],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0,0,0]
            ];

            dauc_max_rows = 11;
            dauc_max_columns = 10;

        }

        //Instantiate the handsontable table
        var dauc_container = document.getElementById('dauc-table');
        dauc_hot = new Handsontable(dauc_container,
            {

                afterSelection: function(start_row_index) {
                    if(start_row_index > 0){
                        display_data_structure(start_row_index);
                    }
                },

                data: dauc_data,

                //set the new maximum number of rows and columns
                maxRows: dauc_max_rows,
                maxCols: dauc_max_columns

            });


        /*
         Set set table in read-only mode if the table is disabled.
         While in read-only mode he table style is also changed.
         */
        if( parseInt( $('#dauc-table').attr('data-disabled'), 10) == 1 ){

            dauc_hot.updateSettings({
                readOnly: true,
                contextMenu: false
            });

            $('#dauc-table td').css('background', '#f7f7f7').css('color', '#ccc');

        }

    }

    /*
     Disables ctrl+z
     The reason why this function is useful is that clicking ctrl+z while editing the handsontable generates problems.
     */
    function disable_ctrl_z(){

        $("body").keydown(function(e){
            var zKey = 90;
            if ((e.ctrlKey || e.metaKey) && e.keyCode == zKey) {
                e.preventDefault();
                return false;
            }
        });

    }

});