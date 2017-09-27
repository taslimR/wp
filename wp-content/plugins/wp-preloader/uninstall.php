<?php 

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

delete_option( 'img-file' );
delete_option( 'wppreloader_meta' );
 
// For site options in Multisite
delete_site_option( 'img-file' ); 
delete_site_option( 'wppreloader_meta' ); 