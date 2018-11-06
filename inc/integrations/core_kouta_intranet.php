<?php

class core_kouta_intranet {
	
	protected function __construct() {
		$this->add_actions();
	}
	
	// PRIVATE SITE
	
	public function intra_template_redirect() {
		$options = $this->get_option_intra();
		if (!$options['intra_privatesite']) {
			return;
		}

	    $allow_access = false;
		if (substr($_SERVER['REQUEST_URI'], 0, 16) == '/wp-activate.php' || substr($_SERVER['REQUEST_URI'], 0, 11) == '/robots.txt') {
		    $allow_access = true;
		}

		$allow_access = apply_filters('intra_allow_public_access', $allow_access);

		if ($allow_access) {
		    return;
        }

		// We do want a private site
		if (!is_user_logged_in()) {
			auth_redirect();
		}
		else {
			if (is_multisite()) {
				$this->handle_private_loggedin_multisite($options);
			}
			else {
				// Bar access to users with no role
				$user = wp_get_current_user();
				if (!$user || !is_array($user->roles) || count($user->roles) == 0) {
					wp_logout();
					$output = '<p>'.esc_html__('You attempted to login to the site, but you do not have any permissions. If you believe you should have access, please contact your administrator.', 'intra').'</p>';
					wp_die($output);
				}
			}
		}
	}
	
	// Override to decide what to do for Multisite
	protected function handle_private_loggedin_multisite($options) {
	}
	
	// Handler for robots.txt - just disallow if private
	public function intra_robots_txt($output, $public) {
		$options = $this->get_option_intra();
		if ($options['intra_privatesite']) {	
			return "Disallow: /\n";
		}
		return $output;
	}
	
	// Don't allow ping backs if private
	public function intra_option_ping_sites($sites) {
		$options = $this->get_option_intra();
		if ($options['intra_privatesite']) {
			return '';
		}
		return $sites;
	}

	// Disable REST API
    public function intra_rest_pre_dispatch() {
	    $options = $this->get_option_intra();
	    $allow_access = !$options['intra_privatesite'] || is_user_logged_in();
	    $allow_access = apply_filters('intra_allow_public_access', $allow_access);

	    if (!$allow_access) {
		    return new WP_Error( 'not-logged-in', 'REST API Requests must be authenticated because Kouta Intranet is active', array( 'status' => 401 ) );
	    }
    }
	
	// LOGIN REDIRECT
	
	public function intra_login_redirect($redirect_to, $requested_redirect_to='', $user=null) {
		if (!is_null($user) && isset($user->user_login)) {
			$options = $this->get_option_intra();
			if ($options['intra_loginredirect'] != '' && admin_url() == $redirect_to) {
				return $options['intra_loginredirect']; 
			}
		}
		return $redirect_to;
	}
	
	// AUTO-LOGOUT
	
	// Reset timer on login
	public function intra_wp_login($username, $user) {
		try {
			if ($user->ID) {
				update_user_meta($user->ID, 'intra_last_activity_time', time());
			}
		} catch (Exception $ex) {
		}
	}
	
	// Check whether user should be auto-logged out this time
	public function intra_check_activity() {
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$last_activity_time = (int)get_user_meta($user_id, 'intra_last_activity_time', true);
			$logout_time_in_sec = $this->get_autologout_time_in_seconds();
			if ($logout_time_in_sec > 0 && $last_activity_time + $logout_time_in_sec < time()) {
				$current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
				wp_logout();
				wp_redirect($current_url); // Should hit the Login wall if site is private
				exit;
			} else {
				update_user_meta($user_id, 'intra_last_activity_time', time());
			}
		}
	}
	
	protected function get_autologout_time_in_seconds() {
		$options = $this->get_option_intra();
		if ($options['intra_autologout_time'] == 0) {
			return 0;
		}
		
		switch ($options['intra_autologout_units']) {
			case 'days':
				return $options['intra_autologout_time'] * 60 * 60 * 24;
				break;

			case 'hours':
				return $options['intra_autologout_time'] * 60 * 60;
				break;
			
			case 'minutes':
			default:
				return $options['intra_autologout_time'] * 60;
				break;
		}
	}
	
	
	// PUT SETTINGS MENU ON PLUGINS PAGE
	
	public function intra_plugin_action_links( $links, $file ) {
		if ($file == $this->my_plugin_basename()) {
			$settings_link = '<a href="'.$this->get_settings_url().'">'.__('Settings', 'intra').'</a>';
			array_unshift( $links, $settings_link );
		}
	
		return $links;
	}
	
	// ADMIN OPTIONS
	// *************
	
	protected function get_options_menuname() {
		return 'intra_list_options';
	}
	
	protected function get_options_pagename() {
		return 'intra_options';
	}
	
	protected function get_settings_url() {
		return is_multisite()
		? network_admin_url( 'settings.php?page='.$this->get_options_menuname() )
		: admin_url( 'options-general.php?page='.$this->get_options_menuname() );
	}
	
	// Add Kouta Intranet to the Settings menu in admin panel
	public function intra_admin_menu() {
		if (is_multisite()) {
			add_submenu_page( 'settings.php', __('Kouta Intranet settings', 'intra'),
                __('Kouta Intranet', 'intra'),
			'manage_network_options', $this->get_options_menuname(),
			array($this, 'intra_options_do_page'));
		}
		else {
			add_options_page( __('Kouta Intranet settings', 'intra'),
            __('Kouta Intranet', 'intra'),
			'manage_options', $this->get_options_menuname(),
			array($this, 'intra_options_do_page'));
		}
	}
	
	// Entry point of admin settings page
	public function intra_options_do_page() {
		
		wp_enqueue_script( 'intra_admin_js', $this->my_plugin_url().'js/intra-admin.js', array('jquery') );
	
		$submit_page = is_multisite() ? 'edit.php?action='.$this->get_options_menuname() : 'options.php';
	
		if (is_multisite()) {
			$this->intra_options_do_network_errors();
		}
		?>
			
		<h2><?php esc_html_e('Kouta Intranet setup', 'intra'); ?></h2>
		
		<hr />
		<br />
		
		<form action="<?php echo $submit_page; ?>" method="post">
		
		<?php 
		settings_fields($this->get_options_pagename());
		$this->intra_privacysection_text();
		$this->intra_memberssection_text();
		$this->intra_loginredirectsection_text();
		$this->intra_autologoutsection_text();
		$this->intra_licensesection_text();
		?>
		<p class="submit">
			<input type="submit" value="<?php esc_attr_e('Save Changes', 'intra') ?>" class="button button-primary" id="submit" name="submit">
		</p>
		
		</form>

		<?php
	}
	
	protected function intra_privacysection_text() {
		$options = $this->get_option_intra();
				
		echo "<h3>".esc_html__('Privacy','intra')."</h3>";
		
		echo "<input id='input_intra_privatesite' name='".$this->get_options_name()."[intra_privatesite]' type='checkbox' ".($options['intra_privatesite'] ? 'checked' : '')." class='checkbox' />";
		echo '<label for="input_intra_privatesite" class="checkbox plain">';
		esc_html_e('Force site to be entirely private', 'intra');
		echo '</label>';
		
		echo "<br />";
		
		if (is_multisite()) {
			echo "<input id='input_intra_ms_requiremember' name='".$this->get_options_name()."[intra_ms_requiremember]' type='checkbox' ".($options['intra_ms_requiremember'] ? 'checked' : '')." class='checkbox' />";
			echo '<label for="input_intra_ms_requiremember" class="checkbox plain">';
			esc_html_e('Require logged-in users to be members of a sub-site to view it', 'intra' );
			echo '</label>';
			
			echo "<br />";
		}
		
		echo "<p>".esc_html__('Note that your media uploads (e.g. photos) will still be accessible to anyone who knows their direct URLs.', 'intra')."</p>";
		
		$this->display_registration_warning();
		echo "<br />";
	}
	
	protected function display_registration_warning() {
		if (get_option('users_can_register')) {
			echo '<p>'
                 . '<b>'.esc_html__('Warning:', 'intra').'</b> '
                 . esc_html__('Your site is set so that &quot;Anyone can register&quot; themselves. ', 'intra');
			echo '<a href="'
					.admin_url( 'options-general.php' )
					.'">'.esc_html__('Turn off here', 'intra').'</a>';
			echo '</p>';
		}
	}
	
	// Override to deal with members of sub-sites in a multisite
	protected function intra_memberssection_text() {
	}
	
	protected function intra_loginredirectsection_text() {
		$options = $this->get_option_intra();
	
		echo "<h3>".esc_html__('Login Redirect', 'intra')."</h3>";
	
		echo '<label for="input_intra_loginredirect" class="textbox plain">';
		esc_html_e( 'Redirect after login to URL: ', 'intra');
		echo '</label>';
	
		echo "<input id='input_intra_loginredirect' name='".$this->get_options_name()."[intra_loginredirect]' type='input' value='".esc_attr($options['intra_loginredirect'])."' size='60' />";
		
		echo "<br />";
		
		echo "<p>".esc_html__('Effective when users login via /wp-login.php directly. Otherwise, they will be taken to the page they were trying to access before being required to login.', 'intra')."</p>";
		
		echo "<br />";
		echo "<br />";
	}
	
	protected function intra_autologoutsection_text() {
		$options = $this->get_option_intra();
		
		echo "<h3>".esc_html('Auto Logout', 'intra')."</h3>";
		
		echo '<label for="input_intra_autologout_time" class="textbox plain">';
		esc_html_e('Auto logout inactive users after ', 'intra');
		echo '</label>';
		
		echo "<input id='input_intra_autologout_time' name='".$this->get_options_name()."[intra_autologout_time]' type='input' value='".esc_attr($options['intra_autologout_time'] == 0 ? '' : $options['intra_autologout_time'])."' size='10' />";
		
		echo "<select name='".$this->get_options_name()."[intra_autologout_units]'>";
		echo $this->list_options(Array('minutes', 'hours', 'days'), $options['intra_autologout_units']);
		echo "</select> ".esc_html__("(leave blank to turn off auto-logout)", 'intra');

		echo "<br />";
		echo "<br />";
	}

	// Override in Premium
	protected function intra_licensesection_text() {
    }

	protected function list_options($list, $current) {
		$output = '';
		$trans_map = Array(
			'minutes' => esc_html__('Minutes', 'intra'),
			'hours' => esc_html__('Hours', 'intra'),
            'days' => esc_html__('Days', 'intra')
        );
		foreach ($list as $opt) {
			$output .= '<option value="'.esc_attr($opt).'" '.($current == $opt ? 'selected="selected"' : '').'>'.$trans_map[$opt].'</option>';
		}
		return $output;
	}
	public function intra_options_validate($input) {
		$newinput = Array();
		$newinput['intra_version'] = $this->PLUGIN_VERSION;
		$newinput['intra_privatesite'] = isset($input['intra_privatesite']) ? (boolean)$input['intra_privatesite'] : false;
		$newinput['intra_ms_requiremember'] = isset($input['intra_ms_requiremember']) ? (boolean)$input['intra_ms_requiremember'] : false;
		
		$newinput['intra_autologout_time'] = isset($input['intra_autologout_time']) ? trim($input['intra_autologout_time']) : '';
		if(!preg_match('/^[0-9]*$/i', $newinput['intra_autologout_time'])) {
			add_settings_error(
			'intra_autologout_time',
			'nan_texterror',
			self::get_error_string('intra_autologout_time|nan_texterror'),
			'error'
			);
			$newinput['intra_autologout_time'] = 0;
		}
		else {
			$newinput['intra_autologout_time'] = intval($newinput['intra_autologout_time']);
		}
		
		$newinput['intra_autologout_units'] = isset($input['intra_autologout_units']) ? $input['intra_autologout_units'] : '';
		if (!in_array($newinput['intra_autologout_units'], Array('minutes', 'hours', 'days'))) {
			$newinput['intra_autologout_units'] = 'minutes';
		}
		
		$newinput['intra_loginredirect'] = isset($input['intra_loginredirect']) ? $input['intra_loginredirect'] : '';
		
		return $newinput;
	}
	
	protected function get_error_string($fielderror) {
		$local_error_strings = Array(
				'intra_autologout_time|nan_texterror' => __('Auto logout time should be blank or a whole number', 'intra')
		);
		if (isset($local_error_strings[$fielderror])) {
			return $local_error_strings[$fielderror];
		}
		return __('Unspecified error', 'intra');
	}
	
	public function intra_save_network_options() {
		check_admin_referer( $this->get_options_pagename().'-options' );
	
		if (isset($_POST[$this->get_options_name()]) && is_array($_POST[$this->get_options_name()])) {
			$inoptions = $_POST[$this->get_options_name()];
			$outoptions = $this->intra_options_validate($inoptions);
				
			$error_code = Array();
			$error_setting = Array();
			foreach (get_settings_errors() as $e) {
				if (is_array($e) && isset($e['code']) && isset($e['setting'])) {
					$error_code[] = $e['code'];
					$error_setting[] = $e['setting'];
				}
			}
	
			update_site_option($this->get_options_name(), $outoptions);
				
			// redirect to settings page in network
			wp_redirect(
			add_query_arg(
			array( 'page' => $this->get_options_menuname(),
			'updated' => true,
			'error_setting' => $error_setting,
			'error_code' => $error_code ),
			network_admin_url( 'admin.php' )
			)
			);
			exit;
		}
	}
	
	protected function intra_options_do_network_errors() {
		if (isset($_REQUEST['updated']) && $_REQUEST['updated']) {
			?>
					<div id="setting-error-settings_updated" class="updated settings-error">
					<p>
					<strong><?php esc_html_e('Settings saved', 'intra'); ?></strong>
					</p>
					</div>
				<?php
			}
	
			if (isset($_REQUEST['error_setting']) && is_array($_REQUEST['error_setting'])
				&& isset($_REQUEST['error_code']) && is_array($_REQUEST['error_code'])) {
				$error_code = $_REQUEST['error_code'];
				$error_setting = $_REQUEST['error_setting'];
				if (count($error_code) > 0 && count($error_code) == count($error_setting)) {
					for ($i=0; $i<count($error_code) ; ++$i) {
						?>
					<div id="setting-error-settings_<?php echo $i; ?>" class="error settings-error">
					<p>
					<strong><?php echo htmlentities2($this->get_error_string($error_setting[$i].'|'.$error_code[$i])); ?></strong>
					</p>
					</div>
						<?php
				}
			}
		}
	}
	
	// OPTIONS
	
	protected function get_default_options() {
		return Array('intra_version' => $this->PLUGIN_VERSION,
					 'intra_privatesite' => true,
					 'intra_ms_requiremember' => true,
					 'intra_autologout_time' => 0,
					 'intra_autologout_units' => 'minutes',
					 'intra_loginredirect' => '');
	}
	
	protected $intra_options = null;
	protected function get_option_intra() {
		if ($this->intra_options != null) {
			return $this->intra_options;
		}
	
		$option = get_site_option($this->get_options_name(), Array());
	
		$default_options = $this->get_default_options();
		foreach ($default_options as $k => $v) {
			if (!isset($option[$k])) {
				$option[$k] = $v;
			}
		}
	
		$this->intra_options = $option;
		return $this->intra_options;
	}
	
	// ADMIN
	
	public function intra_admin_init() {
		register_setting( $this->get_options_pagename(), $this->get_options_name(), Array($this, 'intra_options_validate') );

		global $pagenow;	
	}
	
	protected function add_actions() {

		add_action('plugins_loaded', array($this, 'intra_plugins_loaded'));
		
		if (is_admin()) {
			add_action( 'admin_init', array($this, 'intra_admin_init'), 5, 0 );
			
			add_action(is_multisite() ? 'network_admin_menu' : 'admin_menu', array($this, 'intra_admin_menu'));
			
			if (is_multisite()) {
				add_action('network_admin_edit_'.$this->get_options_menuname(), array($this, 'intra_save_network_options'));
				add_filter('network_admin_plugin_action_links', array($this, 'intra_plugin_action_links'), 10, 2 );
			}
			else {
				add_filter( 'plugin_action_links', array($this, 'intra_plugin_action_links'), 10, 2 );
			}
		}

		add_action( 'template_redirect', array($this, 'intra_template_redirect') );
		add_filter( 'robots_txt', array($this, 'intra_robots_txt'), 0, 2);
		add_filter( 'option_ping_sites', array($this, 'intra_option_ping_sites'), 0, 1);
		add_filter( 'rest_pre_dispatch', array($this, 'intra_rest_pre_dispatch'), 0, 1);
		
		add_filter( 'login_redirect', array($this, 'intra_login_redirect'), 10, 3);
		
		add_action( 'wp_login', array($this, 'intra_wp_login'), 10, 2);
		add_action( 'init', array($this, 'intra_check_activity'), 1);
	}

	public function intra_plugins_loaded() {
		load_plugin_textdomain( 'intra', false, dirname($this->my_plugin_basename()).'/lang/' );
	}


}
