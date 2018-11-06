<?php
/*
* @package Kouta_Intranet
*/

class Tapahtumat {
    
    function __construct() {
        
        add_action( 'init', array( $this, 'tapahtumat_post_type' ) );
        add_filter( 'manage_edit-tapahtumat_columns', array( $this, 'intra_tapahtumat_new_columns' ) );
        add_action( 'manage_tapahtumat_posts_custom_column', array( $this, 'intra_populate_custom_tapahtumat_columns' ) );
        
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

    /**
     * Adds new columns to edit tapahtumat screen
     */
    function intra_tapahtumat_new_columns( $columns ) {

        $columns['start_date'] = 'Alkamis pvm.';
        $columns['end_date'] = 'Lopetus pvm.';
        $columns['organizer'] = 'Järjestäjä';
        $columns['price'] = 'Hinta';
        $columns['quick_link'] = 'Pikalinkki';

        return $columns;

    }

    /**
     * Populate new columns with post meta.
     *
     * @param string[] $column name of column being displayed
     */
    function intra_populate_custom_tapahtumat_columns( $column ) {
        global $post;
        $date = get_field('time_date', $post->ID);
        $loc = get_field('sijainti', $post->ID);
        $info = get_field('lisatietoa', $post->ID);
        $start = ( $date['start'] ? date("d.m.Y, H:i", strtotime($date['start'])) : '' );
        $end = ( $date['end'] ? date("d.m.Y, H:i", strtotime($date['end'])) : '' );

        if ( 'start_date' === $column ) {
            echo $start; 
        }     

        if ( 'end_date' === $column ) {
            echo $end; 
        }    

        if ( 'organizer' === $column ) {
            echo $info['jarjestaja']; 
        }     

        if ( 'price' === $column ) {
            if (!empty($info['tapahtuman_hinta'])) {
                echo $info['tapahtuman_hinta'] . ' €'; 
            }
        }      

        if ( 'quick_link' === $column ) {
            echo ( get_field('pikalinkki') ? 'Kyllä' : 'Ei'); 
        }   

    }

    
}

new Tapahtumat();