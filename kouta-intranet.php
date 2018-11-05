<?php
/**
 * Plugin Name:     Kouta Intranet
 * Plugin URI:      https://www.koutamedia.fi
 * Description:     Provides Kouta Intranet Base Functionalities. Should be used with Kouta developed themes.
 * Author:          Kouta Media Oy
 * Author URI:      https://www.koutamedia.fi
 * Text Domain:     intra
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Kouta_Intranet
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class KoutaIntranet {
    
    function __construct() {
        $this->add_actions();
    }
    
    function activate() {
        flush_rewrite_rules();
    }
    
    function deactivate() {
        flush_rewrite_rules();
    }
    
    function tapahtumat_post_type() {

        $labels = array(
            'name'                => _x( 'Tapahtumat', 'Post Type General Name', 'intra' ),
            'singular_name'       => _x( 'Tapahtuma', 'Post Type Singular Name', 'intra' ),
            'menu_name'           => __( 'Tapahtumat', 'intra' ),
            'parent_item_colon'   => __( 'Ylä-tapahtuma:', 'intra' ),
            'all_items'           => __( 'Kaikki tapahtumat', 'intra' ),
            'view_item'           => __( 'Näytä tapahtuma', 'intra' ),
            'add_new_item'        => __( 'Lisää uusi tapahtuma', 'intra' ),
            'add_new'             => __( 'Lisää uusi', 'intra' ),
            'edit_item'           => __( 'Muokkaa', 'intra' ),
            'update_item'         => __( 'Päivitä', 'intra' ),
            'search_items'        => __( 'Hae', 'intra' ),
            'not_found'           => __( 'Ei löytynyt yhtään', 'intra' ),
            'not_found_in_trash'  => __( 'Ei yhtään roskakorissa', 'intra' ),
        );
        $args = array(
            'label'               => __( 'tapahtuma', 'intra' ),
            'description'         => __( 'tapahtumat', 'intra' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'thumbnail', 'editor' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_icon'           => 'dashicons-calendar-alt',
            'menu_position'       => 7,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
        );
        register_post_type( 'tapahtumat', $args );

    }
    
    function tiedostot_post_type() {

        $labels = array(
            'name'                => _x( 'Tiedostot', 'Post Type General Name', 'intra' ),
            'singular_name'       => _x( 'Tiedosto', 'Post Type Singular Name', 'intra' ),
            'menu_name'           => __( 'Tiedostot', 'intra' ),
            'parent_item_colon'   => __( 'Ylä-tiedosto:', 'intra' ),
            'all_items'           => __( 'Kaikki tiedosto', 'intra' ),
            'view_item'           => __( 'Näytä tiedosto', 'intra' ),
            'add_new_item'        => __( 'Lisää uusi tiedosto', 'intra' ),
            'add_new'             => __( 'Lisää uusi', 'intra' ),
            'edit_item'           => __( 'Muokkaa', 'intra' ),
            'update_item'         => __( 'Päivitä', 'intra' ),
            'search_items'        => __( 'Hae', 'intra' ),
            'not_found'           => __( 'Ei löytynyt yhtään', 'intra' ),
            'not_found_in_trash'  => __( 'Ei yhtään roskakorissa', 'intra' ),
        );
        $args = array(
            'label'               => __( 'tiedosto', 'intra' ),
            'description'         => __( 'Tiedostot', 'intra' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'thumbnail', 'editor' ),
            'paged'               => true,
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_icon'           => 'dashicons-format-aside',
            'menu_position'       => 7,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
        );
        register_post_type( 'tiedostot', $args );

    }
    
    protected function add_actions() {
        
        add_action( 'init', array( $this, 'tapahtumat_post_type' ) );
        add_action( 'init', array( $this, 'tiedostot_post_type' ) );
        
    }
    
}


if ( class_exists( 'KoutaIntranet' ) ) {
    // Initialize the plugin
    $koutaIntranet = new KoutaIntranet();
}

// Activation
register_activation_hook( __FILE__, array( $koutaIntranet, 'activate' ) );

// Deactivation
register_deactivation_hook( __FILE__, array( $koutaIntranet, 'deactivate' ) );
