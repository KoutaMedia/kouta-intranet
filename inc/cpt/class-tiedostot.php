<?php
/*
* @package Kouta_Intranet
*/

class Tiedostot {
    
    function __construct() {
        
        add_action( 'init', array( $this, 'tiedostot_post_type' ) );
        add_action( 'init', array($this, 'kouta_intra_file_categories'), 0 );
        add_filter( 'manage_edit-tiedostot_columns', array($this, 'intra_tiedostot_new_columns') );
        add_action( 'manage_tiedostot_posts_custom_column', array($this, 'intra_populate_custom_tiedostot_columns' ) );
        
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
        
    // Register Custom Taxonomy
    function kouta_intra_file_categories() {

        $labels = array(
            'name'                       => _x( 'Kategoriat', 'Taxonomy General Name', 'intra' ),
            'singular_name'              => _x( 'Kategoria', 'Taxonomy Singular Name', 'intra' ),
            'menu_name'                  => __( 'Kategoria', 'intra' ),
            'all_items'                  => __( 'Kaikki kategoriat', 'intra' ),
            'parent_item'                => __( 'Ylä-kategoria', 'intra' ),
            'parent_item_colon'          => __( 'Ylä-kategoria:', 'intra' ),
            'new_item_name'              => __( 'Uusi kategoria', 'intra' ),
            'add_new_item'               => __( 'Lisää uusi kategoria', 'intra' ),
            'edit_item'                  => __( 'Muokkaa kategoriaa', 'intra' ),
            'update_item'                => __( 'Päivitä kategoria', 'intra' ),
            'view_item'                  => __( 'Näytä kategoria', 'intra' ),
            'separate_items_with_commas' => __( 'Erottele kategoriat pilkulla', 'intra' ),
            'add_or_remove_items'        => __( 'Lisää tai poista kategorioita', 'intra' ),
            'choose_from_most_used'      => __( 'Valitse eniten käytetyistä', 'intra' ),
            'popular_items'              => __( 'Suosituimmat kategoriat', 'intra' ),
            'search_items'               => __( 'Etsi kategoriaa', 'intra' ),
            'not_found'                  => __( 'Ei löytynyt', 'intra' ),
            'no_terms'                   => __( 'Ei kategorioita', 'intra' ),
            'items_list'                 => __( 'Items list', 'intra' ),
            'items_list_navigation'      => __( 'Items list navigation', 'intra' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );
        register_taxonomy( 'tiedosto_kategoria', array( 'tiedostot' ), $args );

    }

    /**
     * Adds new columns to edit tapahtumat screen
     */
    function intra_tiedostot_new_columns( $columns ) {
        
        $columns['remote_file'] = __('Etätiedosto', 'intra');
        $columns['local_file'] = __('Paikallinen tiedosto', 'intra');
        $columns['quick_link'] = __('Pikalinkki', 'intra');
        return $columns;
        
    }

    /**
     * Populate new columns with post meta.
     *
     * @param string[] $column name of column being displayed
     */
    function intra_populate_custom_tiedostot_columns( $column ) {
        
        global $post;
        $file = get_field('tiedosto', $post->ID);

        if ( 'remote_file' === $column ) {
            echo get_field('remote_file'); 
        }     

        if ( 'local_file' === $column ) {
            echo $file['filename']; 
        }    

        if ( 'quick_link' === $column ) {
            echo ( get_field('remote_file') ? 'Kyllä' : 'Ei'); 
        }     

    }
    
}

new Tiedostot();