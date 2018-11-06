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

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

class KoutaIntranet {
    
    public $plugin;
    
    function __construct() {
        
        $this->plugin = plugin_basename( __FILE__ );
        
        $this->basic_includes();
        $this->add_actions();
        
    }
        
    protected function add_actions() {

        add_action('wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ));        
        add_filter( "plugin_action_links_$this->plugin", array( $this, 'settings_link' ) );
        
    }
    
    function settings_link( $links ) {
        
        $settings_link = '<a href="options-general.php?page=intra_list_options">Asetukset</a>';
        array_push( $links, $settings_link );
        
        return $links;
        
    }
    
    protected function basic_includes() {
        
        require_once plugin_dir_path( __FILE__ ) . '/inc/integrations/basic_kouta_intranet.php';
        require_once plugin_dir_path( __FILE__ ) . '/inc/cpt/class-tiedostot.php';
        require_once plugin_dir_path( __FILE__ ) . '/inc/cpt/class-tapahtumat.php';
        
    }
        
    function enqueue_styles_and_scripts() {
        
        wp_enqueue_style( 'intra-styles', plugins_url( '/assets/css/intra-styles.css', __FILE__ ) );
        
    }
    
    function activate() {
        
        // Flush rewrite rules on activate
        flush_rewrite_rules();
        
    }
    
    function deactivate() {
        
        // Flush rewrite rules on deactivate
        flush_rewrite_rules();
        
    }
    
}


if ( class_exists( 'KoutaIntranet' ) ) {
    
    // Initialize the plugin
    $koutaIntranet = new KoutaIntranet();
    
}

// Activation
//register_activation_hook( __FILE__, array( $koutaIntranet, 'activate' ) );

// Deactivation
//register_deactivation_hook( __FILE__, array( $koutaIntranet, 'deactivate' ) );
