<?php

// if uninstall.php is not called by WordPress, die
if (! defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete plugin options
delete_option('sanship_client_id');
delete_option('sanship_client_secret');
delete_option('sanship_environment');


// custom query to remove keys from database table
global $wpdb;
$wpdb->query("
    DELETE FROM {$wpdb->postmeta}
    WHERE meta_key IN (
        '_sanship_tracking_number',
        '_sanship_tracking_link'
    )
");

?>