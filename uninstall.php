<?php
/**
 * Uninstall Procedure for Stack Exchange Posts Widget
 */

// Make sure that we are uninstalling
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// Leave no trail
$option_name = 'b5f_widgets_options';

if ( !is_multisite() ) 
{
    delete_option( $option_name );
} 
else 
{
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id ) 
    {
        switch_to_blog( $blog_id );
        delete_option( $option_name );    
    }
    switch_to_blog( $original_blog_id );
}