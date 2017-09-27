<!-- Including php files -->
<!-- ======================================================================== --> 

<!-- tab-1 settings files -->
<!------  include general.php file for tab-1(settings) submenu-1 ------->
<div class="block ui-tabs-panel active" id="option-ui-id-1" >
<?php require_once('general.php');?>
</div>

<!-- include header.php file for tab-3(Design) submenu-2 ------>
<div class="block ui-tabs-panel deactive" id="option-ui-id-3" >
<?php require_once('templates.php');?>
</div>

<!-- include header.php file for tab-3(Design) submenu-3 ------>
<div class="block ui-tabs-panel deactive" id="option-ui-id-11" >
<?php require_once('background.php');?>
</div>
<!-- include header.php file for tab-3(Design) submenu-3 ------>
<div class="block ui-tabs-panel deactive" id="option-ui-id-12" >
<?php require_once('text-design.php');?>
</div>


<!-- include header.php file for tab-3(Design) submenu-4 ------>
<div class="block ui-tabs-panel deactive" id="option-ui-id-18" >
<?php require_once('pro.php');?>
</div>







<!-- Including js files -->
<!-- ======================================================================== --> 
<?php echo wp_enqueue_script('upload-reset',WBR_EM_PLUGIN_URL.'/includes/js/upload-reset.js'); ?>
<?php echo wp_enqueue_script('background',WBR_EM_PLUGIN_URL.'/includes/js/background.js'); ?>
<?php echo wp_enqueue_script('set-default',WBR_EM_PLUGIN_URL.'/includes/js/set-default.js'); ?>
