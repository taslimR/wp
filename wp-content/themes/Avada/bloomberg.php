<?php
/**
 * Template Name: Bloomberg
 * A full-width template.
 */

?>

<?php
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}
?>
<?php get_header(); ?>
<?php
if($_COOKIE) {
    $status = $_COOKIE['status'];
	$country = $_COOKIE['country'];
	$professionality = $_COOKIE['professionality'];
}
?>
<div id="content" class="full-width">
	<?php while ( have_posts() ) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo avada_render_rich_snippets_for_pages(); ?>
			<?php echo avada_featured_images_for_pages(); ?>
			<div class="post-content">
            	<div style="display:flex;">
                    <div class="col-sm-8 col-md-8 col-lg-8 view-bg">
                        <div class="bloom-view">
                            <h2>Bloomberg <span>View</span></h2>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="col-sm-6 col-md-6 col-lg-6 col-sm-offset-6" style="position:absolute; top:0;">
                                <div class="row">
                                    <div class="item-1">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for Granted</a></h3>
                                        <p>Lorem Ipsum is simply dummy text of the printing</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <div class="item-1 bottom-item">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for Granted</a></h3>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <div class="item-1 bottom-item">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 blue-bg">
                        <div class="bloom-view bluenew">
                            <h2>Bloomberg <span>Gadfly</span></h2>
                            <div class="item-1 bottom-item">
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="row" style="display:flex;">
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="img-overlay"></div>
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="img-overlay"></div>
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                   	<div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="img-overlay"></div>
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:flex;">
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item next-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item next-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item next-item">
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:flex;">
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                        	<div class="border-bg"></div>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/488x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                        	<div class="border-bg"></div>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/488x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="news-item">
                        	<div class="border-bg"></div>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/488x-1.jpg" width="400" alt="">
                            <div class="overlay-item">
                            	<h3><a href="">U.S. Probes Panasonic Unit for Alleged Bribery Violations</a></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;">
                    <div class="col-sm-8 col-md-8 col-lg-8 small-green-bg">
                        <div class="bloom-view">
                            <h2>Bloomberg <span>View</span></h2>
                            <img src="<?php echo get_template_directory_uri(); ?>/images/1200x-1.jpg" width="400" alt="">
                            <div class="col-sm-6 col-md-6 col-lg-6 col-sm-offset-6" style="position:absolute; top:0;">
                                <div class="row">
                                    <div class="item-1">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for Granted</a></h3>
                                        <p>Lorem Ipsum is simply dummy text of the printing</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <div class="item-1 bottom-item">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for Granted</a></h3>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-6 col-lg-6">
                                    <div class="item-1 bottom-item">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 small-blue-bg">
                        <div class="bloom-view bluenew">
                            <h2>Bloomberg <span>Gadfly</span></h2>
                            <div class="item-1 bottom-item">
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                            </div>
                        </div>
                    </div> 
                </div>
                <div style="display:flex;">
                    <div class="col-sm-12 col-md-12 col-lg-12 small-blue-bg">
                        <div class="bloom-view">
                            <h2>Bloomberg <span>Business</span></h2>
                            <div class="row">
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <div class="item-1" style="min-height:300px; margin-top:10px;">
                                        <h2 style="margin-top:20px !important;"><a href="">This Is What Dimon and Kashkari Are Really Fighting About </a></h2>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-md-4 col-lg-4">
                                    <div class="item-1" style="min-height:300px; margin-top:10px;">
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                        <h3><a href="">Impressive Market Resilience Shouldn't Be Taken for</a></h3>
                                    </div>
                                </div>
                            	<div class="col-sm-3 col-md-3 col-lg-3">
                                    <div class="item-1" style="min-height:300px; margin-top:-10px;">
                                        <img src="<?php echo get_template_directory_uri(); ?>/images/4d88x-1.jpg" alt="" />
                                    </div>
                                </div>
                            </div>
                            
                            
                            
                        </div>
                    </div>
                     
                </div>
				<?php the_content(); ?>
				<?php avada_link_pages(); ?>
			</div>
			<?php if ( ! post_password_required( $post->ID ) ) : ?>
				<?php if ( Avada()->settings->get( 'comments_pages' ) ) : ?>
					<?php wp_reset_query(); ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>
</div>

<?php get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
