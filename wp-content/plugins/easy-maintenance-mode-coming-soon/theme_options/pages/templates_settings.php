<div class="block ui-tabs-panel active" id="option-ui-id-6" >
	<form method="post" id="commingsoon_lite_theme_options_6">
		<div id="heading">
			<table style="width:100%;"><tr>
				<td><h2><?php _e('Templates','easy-maintenance-mode-coming-soon');?></h2></td>
				<td style="width:30%;">
				</td>
				<td style="text-align:right;">					
				</td>
				</tr>
			</table>			
		</div>
<br>	
 <table class="form-table">
 <tr>

 <td>

 <div class="template">
	
		<div class="template-screenshot">
			<img src="<?php echo WBR_EM_PLUGIN_URL.'/images/Firefox_Screenshot_2014-01-31T11-04-53.968Z.png';?>" alt="">
		</div>
	
	<h3 class="template-name"><?php _e('Default','easy-maintenance-mode-coming-soon'); ?></h3>
	

	<div class="template-actions">

	
		
		<button class="btn btn-danger" disabled="disabled" id="template1_active" style="vertical-align:middle;"><?php _e('Activated','easy-maintenance-mode-coming-soon'); ?></button>
	

	</div>

	
</div>
 <div class="template">
	
		<div class="template-screenshot">
			<a   href="http://easycomingsoon.com/"  target= "_new"><img src="<?php echo WBR_EM_PLUGIN_URL.'/images/screenshot-28.png';?>" alt=""></a>
		</div>
	
	<h3 class="template-name"><?php _e('Template1','easy-maintenance-mode-coming-soon'); ?></h3>
	

	<div class="template-actions">

	
		
		<a class="btn btn-danger"  href="http://easycomingsoon.com/"  target= "_new" style="vertical-align:middle;"><?php _e('Available In Pro','easy-maintenance-mode-coming-soon'); ?></a>
	

	</div>

	
</div>


<div class="template">
	
		<div class="template-screenshot">
		<a   href="http://easycomingsoon.com/"  target= "_new">	<img src="<?php echo WBR_EM_PLUGIN_URL.'/images/screenshot-31.png';?>" alt=""></a>
		</div>
	
	<h3 class="template-name"><?php _e('Template2','easy-maintenance-mode-coming-soon'); ?></h3>
	

	<div class="template-actions">

		
		
		<a class="btn btn-danger"  href="http://easycomingsoon.com/"  target= "_new" style="vertical-align:middle;"><?php _e('Available In Pro','easy-maintenance-mode-coming-soon'); ?></a>
		
	

	</div>

	
</div>


<div class="template">
	
		<div class="template-screenshot">
		<a   href="http://easycomingsoon.com/"  target= "_new">	<img src="<?php echo plugins_url('/images/template3-new.png',__FILE__);?>" alt=""></a>
		</div>
	
	<h3 class="template-name"><?php _e('Template3','easy-maintenance-mode-coming-soon'); ?></h3>
	

	<div class="template-actions">

		
		
		<a class="btn btn-danger"  href="http://easycomingsoon.com/"  target= "_new" style="vertical-align:middle;"><?php _e('Available In Pro','easy-maintenance-mode-coming-soon'); ?></a>
		
	

	</div>

	
</div>

<div class="template">
	
		<div class="template-screenshot">
		<a   href="http://easycomingsoon.com/"  target= "_new">	<img src="<?php echo plugins_url('/images/template4.png',__FILE__);?>" alt=""></a>
		</div>
	
	<h3 class="template-name"><?php _e('Template4','easy-maintenance-mode-coming-soon'); ?></h3>
	

	<div class="template-actions">

		
		
		<a class="btn btn-danger"  href="http://easycomingsoon.com/"  target= "_new" style="vertical-align:middle;"><?php _e('Available In Pro','easy-maintenance-mode-coming-soon'); ?></a>
		
	

	</div>

	
</div>

</td>
</tr>

</table>
		

		<!--<div id="button_section">
			<input type="hidden" value="1" id="commingsoon_lite_settings_save_6" name="commingsoon_lite_settings_save_6" />
			<input class="reset-button btn" type="button" name="reset" value="Restore Defaults" onclick="commingsoon_option_data_reset('6');">
			<input class="button button-primary" type="button" value="Save Options" onclick="commingsoon_option_data_save('6')" >
		</div>-->
	</form>
</div>
<style>
.template{
	cursor: pointer;
	float: left;
	margin: 0 4% 4% 0;
	position: relative;
	width: 300px;
	border: 1px solid #dedede;
	-webkit-box-shadow: 0 1px 1px -1px rgba(0,0,0,.1);
	box-shadow: 0 1px 1px -1px rgba(0,0,0,.1);
	-webkit-box-sizing: border-box;
	-moz-box-sizing: border-box;
	box-sizing: border-box;
}

.template-screenshot{
	display: block;
	overflow: hidden;
	position: relative;
	-webkit-transition: opacity .2s ease-in-out;
	transition: opacity .2s ease-in-out;
}
.template-screenshot:hover{
	display: block;
	overflow: hidden;
	position: relative;
	opacity:0.8;
	-webkit-transition: opacity .2s ease-in-out;
	transition: opacity .2s ease-in-out;
}
.template-screenshot img {


width: 100%;

}
.template-name{
	font-size: 15px;
	font-weight: 600;
	margin: 0;
	padding: 15px;
	-webkit-box-shadow: inset 0 1px 0 rgba(0,0,0,.1);
	box-shadow: inset 0 1px 0 rgba(0,0,0,.1);
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
	background: #fff;
	background: rgba(255,255,255,.65);
}
.template-actions{
	opacity: 1;
	-webkit-transition: opacity .1s ease-in-out;
	transition: opacity .1s ease-in-out;
	position: absolute;
	bottom: 0;
	right: 0;
	height: 38px;
	padding: 9px 10px 0;
	background: rgba(244,244,244,.7);
	border-left: 1px solid rgba(0,0,0,.05);
}
#option-tree-settings-api #poststuff .template h3 {
  padding: 9px 15px !important;
  line-height: 35px;
}
</style>