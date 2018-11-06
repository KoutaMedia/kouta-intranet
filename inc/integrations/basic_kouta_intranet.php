<?php

if (!class_exists('core_kouta_intranet')) {
	require_once( plugin_dir_path(__FILE__).'core_kouta_intranet.php' );
}

class intra_basic_kouta_intranet extends core_kouta_intranet {
    
    protected $PLUGIN_VERSION = '1.5';

	// Singleton
	private static $instance = null;
	
	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	// ADMIN
	
	protected function get_options_name() {
		return 'intra_dsl';
	}
	
	// AUX
	
	protected function my_plugin_basename() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			$basename = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		}
		return $basename;
	}
	
	protected function my_plugin_url() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			return plugins_url().'/'.basename(dirname(__FILE__)).'/';
		}
		// Normal case (non symlink)
		return plugin_dir_url( __FILE__ );
	}
	
}

// Global accessor function to singleton
function BasicKoutaIntranet() {
	return intra_basic_kouta_intranet::get_instance();
}

// Initialise at least once
BasicKoutaIntranet();
