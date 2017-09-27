<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php do_action( 'avada_after_main_content' ); ?>

				</div>  <!-- fusion-row -->
			</div>  <!-- #main -->
			<?php do_action( 'avada_after_main_container' ); ?>

			<?php global $social_icons; ?>

			<?php if ( false !== strpos( Avada()->settings->get( 'footer_special_effects' ), 'footer_sticky' ) ) : ?>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * Get the correct page ID.
			 */
			$c_page_id = Avada()->get_page_id();
			?>

			<?php
			/**
			 * Only include the footer.
			 */
			?>
			<?php if ( ! is_page_template( 'blank.php' ) ) : ?>
				<?php $footer_parallax_class = ( 'footer_parallax_effect' == Avada()->settings->get( 'footer_special_effects' ) ) ? ' fusion-footer-parallax' : ''; ?>

				<div class="fusion-footer<?php echo $footer_parallax_class; ?>">

					<?php
					/**
					 * Check if the footer widget area should be displayed.
					 */
					?>
					<?php if ( ( Avada()->settings->get( 'footer_widgets' ) && 'no' != get_post_meta( $c_page_id, 'pyre_display_footer', true ) ) || ( ! Avada()->settings->get( 'footer_widgets' ) && 'yes' == get_post_meta( $c_page_id, 'pyre_display_footer', true ) ) ) : ?>
						<?php $footer_widget_area_center_class = ( Avada()->settings->get( 'footer_widgets_center_content' ) ) ? ' fusion-footer-widget-area-center' : ''; ?>

						<footer class="fusion-footer-widget-area fusion-widget-area<?php echo $footer_widget_area_center_class; ?>">
							<div class="fusion-row">
								<div class="fusion-columns fusion-columns-<?php echo Avada()->settings->get( 'footer_widgets_columns' ); ?> fusion-widget-area">
									<?php
									/**
									 * Check the column width based on the amount of columns chosen in Theme Options.
									 */
									$column_width = ( '5' == Avada()->settings->get( 'footer_widgets_columns' ) ) ? 2 : 12 / Avada()->settings->get( 'footer_widgets_columns' );
									?>

									<?php
									/**
									 * Render as many widget columns as have been chosen in Theme Options.
									 */
									?>
									<?php for ( $i = 1; $i < 7; $i++ ) : ?>
										<?php if ( $i <= Avada()->settings->get( 'footer_widgets_columns' ) ) : ?>
											<div class="fusion-column<?php echo ( Avada()->settings->get( 'footer_widgets_columns' ) == $i ) ? ' fusion-column-last' : ''; ?> col-lg-<?php echo $column_width; ?> col-md-<?php echo $column_width; ?> col-sm-<?php echo $column_width; ?>">
												<?php if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'avada-footer-widget-' . $i ) ) : ?>
													<?php
													/**
													 * All is good, dynamic_sidebar() already called the rendering.
													 */
													?>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									<?php endfor; ?>

									<div class="fusion-clearfix"></div>
								</div> <!-- fusion-columns -->
							</div> <!-- fusion-row -->
						</footer> <!-- fusion-footer-widget-area -->
					<?php endif; // End footer wigets check. ?>

					<?php
					/**
					 * Check if the footer copyright area should be displayed.
					 */
					?>
					<?php if ( ( Avada()->settings->get( 'footer_copyright' ) && 'no' != get_post_meta( $c_page_id, 'pyre_display_copyright', true ) ) || ( ! Avada()->settings->get( 'footer_copyright' ) && 'yes' == get_post_meta( $c_page_id, 'pyre_display_copyright', true ) ) ) : ?>
						<?php $footer_copyright_center_class = ( Avada()->settings->get( 'footer_copyright_center_content' ) ) ? ' fusion-footer-copyright-center' : ''; ?>

						<footer id="footer" class="fusion-footer-copyright-area<?php echo $footer_copyright_center_class; ?>">
							<div class="fusion-row">
								<div class="fusion-copyright-content">
									<?php
									/**
									 * Footer Content (Copyright area) avada_footer_copyright_content hook.
									 *
									 * @hooked avada_render_footer_copyright_notice - 10 (outputs the HTML for the Theme Options footer copyright text)
									 * @hooked avada_render_footer_social_icons - 15 (outputs the HTML for the footer social icons)..
									 */
									do_action( 'avada_footer_copyright_content' );
									?>
                                    

								</div> <!-- fusion-fusion-copyright-content -->
							</div> <!-- fusion-row -->
						</footer> <!-- #footer -->
					<?php endif; // End footer copyright area check. ?>
					<?php
					// Displays WPML language switcher inside footer if parallax effect is used.
					if ( defined( 'ICL_SITEPRESS_VERSION' ) &&  'footer_parallax_effect' === Avada()->settings->get( 'footer_special_effects' ) ) {
						global $wpml_language_switcher;
						$slot = $wpml_language_switcher->get_slot( 'statics', 'footer' );
						if( $slot->is_enabled() ) {
							echo $wpml_language_switcher->render( $slot );
						}
					}
					?>
				</div> <!-- fusion-footer -->
			<?php endif; // End is not blank page check. ?>
            
		</div> <!-- wrapper -->

		<?php
		/**
		 * Check if boxed side header layout is used; if so close the #boxed-wrapper container.
		 */
		?>
		<?php if ( ( ( 'Boxed' == Avada()->settings->get( 'layout' ) && 'default' == get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) || 'boxed' == get_post_meta( $c_page_id, 'pyre_page_bg_layout', true ) ) && 'Top' != Avada()->settings->get( 'header_position' ) ) : ?>
			</div> <!-- #boxed-wrapper -->
		<?php endif; ?>

		<a class="fusion-one-page-text-link fusion-page-load-link"></a>

		<!-- W3TC-include-js-head -->

		<?php wp_footer(); ?>

		<?php
		/**
		 * Echo the scripts added to the "before </body>" field in Theme Options
		 */
		echo Avada()->settings->get( 'space_body' );
		?>

<!--<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>-->
        
        
        
        <script type="text/javascript">
			$(document).ready(function(){
				setTimeout(function(){
				   $(".self-hosted-video-bg video").append('<source src="http://www.bristolgate.com/wp-content/uploads/2017/08/BG_IM_BG_Video_low_size_1_converted.mp4" type="video/mp4" />');
			   }, 1000);
			});
        	$(".radio-inline").click(function(){
				
				countryname = ($('input[name=country]:checked').val());
				pro = ($('input[name=professionality]:checked').val());
				
				if (countryname == undefined || pro == undefined){
						
					}
				else {
					jQuery('.submitinfo').click();
					}
			});
			
			$(".side-icon img, .side-icon i").click(function(){
				$("#Individual").show(1500);
			});
			
			
			
			//Homepage video bg
			/*setTimeout(function(){
			   $(".self-hosted-video-bg video").html('<source src="http://www.bristolgate.com/wp-content/uploads/2017/07/BG_IM_BG_Video_low_size_1_converted.mp4" type="video/mp4">');
		   }, 1000);*/
		   //$(".self-hosted-video-bg video").html('<source src="http://www.bristolgate.com/wp-content/uploads/2017/07/BG_IM_BG_Video_low_size_1_converted.mp4" type="video/mp4">');
			
			//Homepage video bg end
			
			$(".view-tab-show-video").click(function(){
				$("#vidincome").append('<source src="http://www.bristolgate.com/wp-content/uploads/2017/07/Income.mp4" type="video/mp4" />');
				$("#vidprotection").append('<source src="http://www.bristolgate.com/wp-content/uploads/2017/07/Protection_v3.mp4" type="video/mp4" />');
				$("#vidquality").append('<source src="http://www.bristolgate.com/wp-content/uploads/2014/10/Quality_v2.mp4" type="video/mp4" />');
				$("#vidpersistence").append('<source src="http://www.bristolgate.com/wp-content/uploads/2017/07/Persistence_v2.mp4" type="video/mp4" />');
				
				$("view-tab-show-video").attr('id','viewvideo-btn');
				$(".view-tab-show-video").removeClass("view-tab-show-video");
				
				$("#view-video").toggle(1000);
				 var vid = document.getElementById("vidincome");
				 vid.currentTime=0;
				 vid.play();
				 
				$('#viewvideo-btn').click(function() {
					 $("#view-video").toggle(1000);
					 var vid = document.getElementById("vidincome");
					 vid.currentTime=0;
					 vid.play();
				});
			});
			
			
			
			
			

			/*$(window).scroll(function() {
				var offset = $("#fusion-tab-income").offset().top;
			
				if ($(window).scrollTop() >= offset) {
					var vid = document.getElementById("vidincome");
							//vid.currentTime=0;
							vid.play();
				}
			});*/
			
			$("#fusion-tab-income").click(function(){
				var vid = document.getElementById("vidincome");
				vid.currentTime=0;
				vid.play();
			});

			$("#fusion-tab-income").click(function(){
				var vid = document.getElementById("vidincome");
				vid.currentTime=0;
				vid.play();
			});
			$("#fusion-tab-protection").click(function(){
				var vid = document.getElementById("vidprotection");
				vid.currentTime=0;
				vid.play();
			});
			$("#fusion-tab-quality").click(function(){
				var vid = document.getElementById("vidquality");
				vid.currentTime=0;
				vid.play();
			});
			$("#fusion-tab-persistence").click(function(){
				var vid = document.getElementById("vidpersistence");
				vid.currentTime=0;
				vid.play();
			});
			
			
			$('#skip').click(function(event) {
				 event.preventDefault();
				 $('html, body').animate({
					scrollTop: $("#mysection").offset().top
				}, 1000);
			});
			$('#invest-btn').click(function(event) {
				 event.preventDefault();
				 $('html, body').animate({
					scrollTop: $("#investment").offset().top
				}, 1000);
			});
			$('#research-btn').click(function(event) {
				 event.preventDefault();
				 $('html, body').animate({
					scrollTop: $("#research").offset().top
				}, 1000);
			});
			$('#operations-btn').click(function(event) {
				 event.preventDefault();
				 $('html, body').animate({
					scrollTop: $("#operations").offset().top
				}, 1000);
			});
			$('#marketing-btn').click(function(event) {
				 event.preventDefault();
				 $('html, body').animate({
					scrollTop: $("#marketing").offset().top
				}, 1000);
			});
			
			$('#takedata').click(function(event) {
				 $("#collaps").toggle(1000);
			});
			var flag =1;
			$('.region-btn').click(function() {
				if(flag == 1){
					$(".region-view").animate({height: "50px"});
					flag = 2;
				}else{
					$(".region-view").animate({height: "0px"});
					flag = 1;
				}
				 //$(".region-view").toggle(100);
			});
			/*$(".region-btn").toggle(function(){
    $('.region-view').animate({height:40},500);
  },function(){
    $('.region-view').animate({height:5},500);
  });*/
			/*$('.region-btn').click(function(event) {
				$('.region-view').animate({height:'50px'}, 500);
			});*/
			/*$('body').click(function(event) {
				$('.region-view').animate({height:'0px'}, 500);
			});*/
			//$('.region-view').hide();
			
			
			
			
			
			$(document).ready(function(){
				$(".atw_link_text").attr("download", "");
				$("#open-chatbox").attr("onclick", "Intercom('showNewMessage', 'I might want to host or attend a workshop')");
				$("#show-chatbox").attr("onclick", "Intercom('showNewMessage', '')");
				$(".show-intercom").attr("onclick", "Intercom('showNewMessage', '')");
				$("#ask-chatbox").attr("onclick", "Intercom('showNewMessage', 'why do you avoid areas that seem to offer high yield?')");
				$("#do-chatbox").attr("onclick", "Intercom('showNewMessage', 'Is it easy to get your data viz stuff on my site?')");
				$(".dek-pick").attr("onclick", "Intercom('showNewMessage', 'Point me to the pitch deck')");
				$(".about").attr("onclick", "Intercom('showNewMessage', 'I’d like to inquire about how to access Bristol Gate')");
				$(".accredited").attr("onclick", "Intercom('showNewMessage', 'What does Accredited or Exempt mean?')");
			});
			
			

			if(window.location.href === "http://www.bristolgate.com/about/#7d70f3cf0c3e13c2d"){
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#7d70f3cf0c3e13c2d").addClass('in');
			} else if (window.location.href === "http://www.bristolgate.com/about/#467f2428c2dfbbec6") {
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#467f2428c2dfbbec6").addClass('in');
			} else if (window.location.href === "http://www.bristolgate.com/about/#f0c3e39572ccb5f22") {
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#f0c3e39572ccb5f22").addClass('in');
			} else if (window.location.href === "http://www.bristolgate.com/about/#b41706b18fea98baf") {
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#b41706b18fea98baf").addClass('in');
			} else if (window.location.href === "http://www.bristolgate.com/disclaimer-and-terms-of-use/#ad3bca10832986c25") {
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#ad3bca10832986c25").addClass('in');
			} else if (window.location.href === "http://www.bristolgate.com/disclaimer-and-terms-of-use/#6f0143d9852a8a557") {
				$('html,body').animate({ scrollTop: 0 }, 'slow');
				$("#6f0143d9852a8a557").addClass('in');
			}
			
			
			
			$('.more-btn').focus(function() {
				$(this).parent().addClass('more-view');
			});
			$('.panel-title').click(function(event) {
				 $('.more-view').removeClass('more-view');
			});
			
			


			
				/*$('#show-doc').on('click', function() {
					e.preventDefault();
				  $("#fusion-tab-documents").click();
				});*/
			
			//$("#show-doc").attr("data-toggle","tab");
			
			
        </script>

		<style type="text/css">
		.more-view .more-btn, .more-content{
			display:none;
		}
		.more-view .more-content{
			display:block;
		}
		#view-video{
			display:none;}
		.sectionwhy{
			font-size:18px;}
		.more-btn{
			color:#6796BF;}
		.among{
			min-height:200px;}
		.source{
			min-height:298px;
			/*background:#f5f5f5;*/}
		.finance .fusion-column-wrapper, .all-sectors-colom .fusion-column-wrapper{
			min-height:335px;}
		ul.footer-menu li{
			float:left;
			list-style:none;}
		ul.footer-menu li a{
			font-size:18px;
			padding:10px 50px;}
		ul.footer-menu li:first-child a{
			padding:5px 50px !important;
			font-family:"PT Serif", Garamond, serif;
			font-size:24px;
			font-weight:400;
			border-right:1px solid #ddd;}
		.home-four-column .fusion-column-wrapper{
		min-height:300px !important;}
		.flip-box-front, .flip-box-back{
			min-height:220px;}
		.team-column{
			width:48% !important;}
		.region-view{
			height:0;
			overflow:hidden;}
		.team-head{
			position:fixed;
			z-index:1000;
			top:0;
			margin-left:-30px;
			width:100%;}
		.team-head .fusion-layout-column.fusion-one-fourth{
			width:20%;}
		#tablepress-2 tbody.row-hover td, #tablepress-6 tbody.row-hover td{
			text-align:center;}
		#tablepress-2 tbody.row-hover tr td:last-child, #tablepress-6 tbody.row-hover tr td:last-child{
			text-align:left;}
		#tablepress-2 tbody.row-hover td img, #tablepress-6 tbody.row-hover td img{
			margin:0 auto;}
		@media(max-width:1065px){
		.team-head .fusion-layout-column.fusion-one-fourth{
			width:19% !important;}
			}
		@media(max-width:1023px){
		.team-head .fusion-layout-column.fusion-one-fourth{
			width:25% !important;}
			}
		@media(max-width:645px){
		.team-head .fusion-layout-column.fusion-one-fourth{
			width:50% !important;}
			}
		@media(max-width:1210px){
			ul.footer-menu li a{
				padding:8px 15px;}
		}
		@media(max-width:767px){
			ul.footer-menu li{
				float:none;}
			ul.footer-menu li a{
				padding:8px 15px;}
			ul.footer-menu li:first-child a{
				border-right:none;
				padding:10px 15px !important;}
		}
		
        </style>
<?php $pid = get_the_ID();
	if ($pid==13845){
	?>
<style type="text/css">
@media(max-width:1023px){
	.fusion-logo{
		margin-top:35px !important;}
}
@media(max-width:645px){
	.fusion-logo{
		margin-top:80px !important;}
}
</style>    
<?php } ?>

	</body>
</html>