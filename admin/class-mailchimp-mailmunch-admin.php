<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.mailmunch.co
 * @since      2.0.0
 *
 * @package    Mailchimp_Mailmunch
 * @subpackage Mailchimp_Mailmunch/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mailchimp_Mailmunch
 * @subpackage Mailchimp_Mailmunch/admin
 * @author     MailMunch <info@mailmunch.co>
 */
class Mailchimp_Mailmunch_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The ID of this plugin's 3rd party integration.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $integration_name    The ID of this plugin's 3rd party integration.
	 */
	private $integration_name;	

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The MailMunch Api object.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $mailmunch_api    The MailMunch Api object.
	 */
	private $mailmunch_api;


	public function __construct( $plugin_name, $integration_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->integration_name = $integration_name;		
		$this->version = $version;
		$this->mailmunch_api = new Mailmunch_Api();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailchimp_Mailmunch_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailchimp_Mailmunch_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mailchimp-mailmunch-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mailchimp_Mailmunch_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mailchimp_Mailmunch_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mailchimp-mailmunch-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function sign_up() {
		$email = $_POST['email'];
		$password = $_POST['password'];
		echo json_encode($this->mailmunch_api->signUpUser($email, $password, $_POST['site_name'], $_POST['site_url']));
		exit;
	}

	public function sign_in() {
		$email = $_POST['email'];
		$password = $_POST['password'];
		echo json_encode($this->mailmunch_api->signInUser($email, $password));
		exit;
	}

	public function delete_widget() {
		echo json_encode($this->mailmunch_api->deleteWidget($_POST['widget_id']));
		exit;
	}

	/**
	 * Register menu for the admin area
	 *
	 * @since    2.0.0
	 */
	public function menu() {
		add_options_page( $this->integration_name, $this->integration_name, 'manage_options', MAILCHIMP_MAILMUNCH_SLUG, array($this, 'get_dashboard_html'));
		add_menu_page( $this->integration_name, $this->integration_name, 'manage_options', MAILCHIMP_MAILMUNCH_SLUG, array($this, 'get_dashboard_html'), plugins_url( 'img/icon.png', __FILE__ ), 103.786);
	}

	/**
	 * Adds settings link for plugin
	 *
	 * @since    2.0.0
	 */
	public function settings_link($links) {
	  $settings_link = '<a href="options-general.php?page='.MAILCHIMP_MAILMUNCH_SLUG.'">Settings</a>';
	  array_unshift($links, $settings_link);
	  return $links;
	}

	/**
	 * Get current step
	 *
	 * @since    2.0.0
	 */
	public function getStep() {
		if (isset($_GET['step'])) { $step = $_GET['step']; }
		elseif ($this->mailmunch_api->skipOnBoarding()) { $step = ''; }
		else {
			$step = 'connect';
			$mcAccessToken = get_option($this->mailmunch_api->getPrefix(). 'mailchimp_access_token');
			$mcListId = get_option($this->mailmunch_api->getPrefix(). 'mailchimp_list_id');
			if (!empty($mcAccessToken)) $step = 'integrate';
			if (!empty($mcListId)) $step = '';
		}
		return $step;
	}

	/**
	 * Get Dashboard HTML
	 *
	 * @since    2.0.0
	 */
	public function get_dashboard_html() {

		switch ($this->getStep()) {
			case 'sign_out':
				$this->mailmunch_api->signOutUser();
				require_once(plugin_dir_path(__FILE__) . 'partials/mailchimp-mailmunch-connect.php');
			break;

			case 'connect':
				require_once(plugin_dir_path(__FILE__) . 'partials/mailchimp-mailmunch-connect.php');
			break;

			case 'integrate':
				if (isset($_POST['access_token'])) {
					update_option($this->mailmunch_api->getPrefix(). 'mailchimp_access_token', $_POST['access_token']);
				}

				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/drewm_mailchimp.php';
				$mailchimpApi = new DrewmMailChimp(get_option($this->mailmunch_api->getPrefix(). 'mailchimp_access_token'));
				$lists = $mailchimpApi->call('lists/list');
				require_once(plugin_dir_path( __FILE__ ) . 'partials/mailchimp-mailmunch-integrate.php');
			break;

			default:
				if (isset($_POST['list_id'])) {
					update_option($this->mailmunch_api->getPrefix(). 'mailchimp_list_id', $_POST['list_id']);

					require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/drewm_mailchimp.php';
					$mailchimpApi = new DrewmMailChimp(get_option($this->mailmunch_api->getPrefix(). 'mailchimp_access_token'));
					$lists = $mailchimpApi->call('lists/list');
					$listName = '';
					if ($lists['total'] > 0) {
						foreach ($lists['data'] as $list) {
							if ($list['id'] == $_POST['list_id'] ) { $listName = $list['name']; }
						}
					}
					$accessToken = get_option($this->mailmunch_api->getPrefix(). 'mailchimp_access_token');

					$this->mailmunch_api->createListAndIntegration($accessToken, $listName, $_POST['list_id']);
				}
				require_once(plugin_dir_path( __FILE__ ) . 'partials/mailchimp-mailmunch-admin-display.php');
		}

		require_once(plugin_dir_path( __FILE__ ) . 'partials/mailchimp-mailmunch-modals.php');
	}

}
