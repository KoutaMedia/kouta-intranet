<?php

/**
* Trigger this file on plugin uninstall
* @package         Kouta_Intranet
*/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

global $wpdb;

$wpdb->query( "DELETE FROM wp_posts WHERE post_type = 'tapahtumat'" );
$wpdb->query( "DELETE FROM wp_postmeta WHERE post_ID NOT IN (SELECT id FROM wp_posts)" );