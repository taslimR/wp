<?php
/**
 * Template Name: Performance
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
	<?php if ($status==1){ ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo avada_render_rich_snippets_for_pages(); ?>
			<?php echo avada_featured_images_for_pages(); ?>
			<div class="post-content">
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
    <?php } else{ echo "<p style='color:#F00;'>You will be able to see the content of this page after submiting your information on top</p>"; }?>
</div>

<?php get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
