<!DOCTYPE html>
<html lang="en">
<?php 
$general_settings = wp_parse_args( get_option('easy_maintenance_mode_general_settings',array()), general_setting());
$text_design = wp_parse_args( get_option('easy_maintenance_mode_text_design_setting', array()), text_design_setting());
$background = wp_parse_args( get_option('easy_maintenance_mode_background_setting_save',array()), background_setting());

?>

  <head>
    <meta charset="utf-8">
    <title><?php
    bloginfo( 'name' );
    $site_description = get_bloginfo( 'description' );
    ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

	<link rel="stylesheet" href="<?php echo WBR_EM_PLUGIN_URL.'themes/assets/bootstrap/css/bootstrap.min.css' ?>">
	
	<script src="<?php echo WBR_EM_PLUGIN_URL.'themes/assets/js/jquery-1.8.2.min.js'?>"></script>
	<script src="<?php echo WBR_EM_PLUGIN_URL.'themes/assets/js/modal.js'?>"></script>

	<script>
		jQuery(document).ready(function($){
		$('#EMModal').modal({
		  keyboard: false
		})
		$('#EMModal').on('shown', function () {
			$('body,.modal-backdrop').unbind();
		});

		});
	</script>
	<style>
	body
	{
		<?php if($background['background_option']=="static-background-color")
			{
			?>
				background:<?php echo $background['background_color']; ?> <?php if($background['background_effect']=="noise"){ ?>  url(<?php echo WBR_EM_PLUGIN_URL.'/images/overlay/overlay-1.png'; ?>) repeat <?php } ?> ;
			<?php
			}
			?>
			
			
			
			<?php if($background['background_option']=="static-background-image")
			{
			?>
				 background: url('<?php echo $background['background_text'] ?>') ;
				background-repeat: no-repeat;
				background-attachment: fixed;
				background-position: top center;
				-webkit-background-size: cover;
				-moz-background-size: cover;
				-o-background-size: cover;
				background-size: cover;
				
			 
			<?php
			}
			?>
	}
	
	/*** headline style ****/
	#easy-maintenance-mode-headline
	{
		margin-bottom:20px;
		color:<?php echo $text_design['headline_color']; ?>;
		font-family:<?php echo $text_design['headline_family_post_title'] ?>;
		font-size:<?php echo  $text_design['headline_size'] ?>px;
		line-height:1.1;
		word-wrap: break-word;
	}
	#easy-maintenance-mode-description{
			color:<?php echo $text_design['description_color']; ?>;
			font-family:<?php echo $text_design['description_family_post_title'] ?>;
			font-size:<?php echo  $text_design['description_size'] ?>px;
			margin-bottom:20px;
			line-height:1.1;
			word-wrap: break-word;
	}
	/*** social links ****/
	.social {
    margin-top: 30px;
    padding-bottom: 30px;
    text-align: center;
}

.social a {
    display: inline-block;
    width: 48px;
    height: 48px;
    margin: 3px;
}

.social a.facebook { background: url(<?php echo WBR_EM_PLUGIN_URL.'/images/social-icons1/facebook.png'; ?>); }
.social a.facebook:hover { 
-webkit-transition: all ease 0.7s;
-moz-transition: all ease 0.7s;
-o-transition: all ease 0.7s;
-ms-transition: all ease 0.7s;
transition: all ease 0.7s;
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-o-transform: rotate(360deg);
-ms-transform: rotate(360deg);
transform: rotate(360deg);
opacity: 0.8;
}
.social a.twitter { background: url(<?php echo WBR_EM_PLUGIN_URL.'/images/social-icons1/twitter.png'; ?>); }
.social a.twitter:hover { 
-webkit-transition: all ease 0.7s;
-moz-transition: all ease 0.7s;
-o-transition: all ease 0.7s;
-ms-transition: all ease 0.7s;
transition: all ease 0.7s;
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-o-transform: rotate(360deg);
-ms-transform: rotate(360deg);
transform: rotate(360deg);
opacity: 0.8;
}
.social a.googleplus { background: url(<?php echo WBR_EM_PLUGIN_URL.'/images/social-icons1/googleplus.png'; ?>); }
.social a.googleplus:hover { 
-webkit-transition: all ease 0.7s;
-moz-transition: all ease 0.7s;
-o-transition: all ease 0.7s;
-ms-transition: all ease 0.7s;
transition: all ease 0.7s;
-webkit-transform: rotate(360deg);
-moz-transform: rotate(360deg);
-o-transform: rotate(360deg);
-ms-transform: rotate(360deg);
transform: rotate(360deg);
opacity: 0.8;
}
			
	</style>
	
	<!-- Google Code add here -->
	<?php echo $general_settings['google_code']; ?>
</head>

<body>
	<!-- Modal -->
	<div class="modal fade dark" id="EMModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<!-- Headline add here -->
					<h1 id="easy-maintenance-mode-headline"><?php echo $general_settings['headline'] ?></h1>
					<!-- Description add here -->
					<p id="easy-maintenance-mode-description"><?php echo $general_settings['description']  ?></p>
					<!--- social links --->
					 <div class="social">
						<?php if($general_settings['fb']!==""){
						?>
						<a href="<?php echo $general_settings['fb']; ?>" class="facebook" target="_new"  title="Facebook"></a>
						<?php } ?>
						<?php if($general_settings['twit']!==""){
						?>
						<a href="<?php echo $general_settings['twit']; ?>" target="_new" class="twitter" title="Twitter"></a>
						<?php } ?>
						<?php if($general_settings['gp']!==""){
						?>
						<a href="<?php echo $general_settings['gp']; ?>" class="googleplus"  target="_new" title="Google Plus"></a>
						<?php } ?>
					</div>
				</div>

			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</body>

</html>

