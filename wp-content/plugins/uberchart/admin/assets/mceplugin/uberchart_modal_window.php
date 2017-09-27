<?php

//this file includes the contents of the 'UberChart' modal window

//load the wp core
require_once('../../../../../../wp-load.php');

//prevent direct access to users without the 'manage_option" capability
if(!current_user_can('manage_options')){die();}

?>
<!DOCTYPE html>
<html>
<head>
<title><?php _e('UberChart', 'dauc'); ?></title>

    <script type="text/javascript" src="jquery-2.1.4.min.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <script language="javascript" type="text/javascript" src="chosen-min.js"></script>

    <link rel="stylesheet" href="daext-modal-shortcode.css" type='text/css' media='all' />
    <link rel="stylesheet" href="chosen-min.css" type='text/css' media='all' />
    
    <script type="text/javascript">

        jQuery(document).ready(function($) {

            /*
             * on document ready enable the chosen plugin with the #chart-id select box
             */
            jQuery("#chart-id").chosen();

        });

        var ButtonDialog = {
                local_ed : 'ed',
                init : function(ed) {
                        ButtonDialog.local_ed = ed;
                        tinyMCEPopup.resizeToInnerSize();
                },
                insert : function insertButton(ed) {

                        // Try and remove existing style / blockquote
                        tinyMCEPopup.execCommand('mceRemoveNode', false, null);

                        // set up variables to contain our input values
                        var chart_id = jQuery('#chart-id').val();

                        var output = '';

                        // setup the output of our shortcode
                        output = '[uberchart id="' + parseInt(chart_id, 10) + '"]';

                        tinyMCEPopup.execCommand('mceReplaceContent', false, output);

                        // Return
                        tinyMCEPopup.close();
                }
        };
        tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);

    </script>

</head>
<body id="daext-modal-shortcode">

    <div id="daext-modal-shortcode-content">
    
        <!-- chart-id -->
        <h3><?php _e('Select the Chart', 'dauc'); ?></h3>
        <select id="chart-id" data-placeholder="<?php _e('Choose a Chart', 'dauc'); ?>">

            <?php

            global $wpdb;
            $table_name = $wpdb->prefix . "dauc_chart";
            $sql = "SELECT id, name FROM $table_name WHERE is_model = 0 AND temporary = 0 ORDER BY id DESC";
            $chart_a = $wpdb->get_results($sql, ARRAY_A);

            foreach ($chart_a as $key => $chart) {
                echo '<option value="' . $chart['id'] .'">' . esc_attr(stripslashes($chart['name'])) . '</option>';
            }

            ?>

        </select>

        <div class="separator-20"></div>
        
    </div>
    
    <!-- submit button -->
    <div id="daext-modal-shortcode-footer" class="daext-clearfix">
        <a class="submit-button" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px;"><?php _e('Add Chart', 'dauc'); ?></a>
    </div>
        
</body>
</html>