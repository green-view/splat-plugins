<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://green-view.nl
 * @since      1.0.0
 *
 * @package    Gv_Splat
 * @subpackage Gv_Splat/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gv_Splat
 * @subpackage Gv_Splat/admin
 * @author     GreenView <info@green-view.nl>
 */
class Gv_Splat_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gv_Splat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gv_Splat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gv-splat-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gv_Splat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gv_Splat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gv-splat-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_settings_page() {
		add_options_page(
			'GV Splat Settings',       // Page title
			'GV Splat',                // Menu title
			'manage_options',          // Capability
			'gv_splat_settings',       // Menu slug
			array( $this, 'display_settings_page' ) // Callback to display page content
		);
	}

	public function display_settings_page() {
		?>
        <div class="wrap">
            <h1>GV Splat Settings</h1>
            <form method="post" action="options.php">
				<?php
				settings_fields( 'gv_splat_settings_group' );
				do_settings_sections( 'gv_splat_settings' );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	public function register_settings() {
		register_setting( 'gv_splat_settings_group', 'gv_splat_token' );

		add_settings_section(
			'gv_splat_main_section',
			'Main Settings',
			null,
			'gv_splat_settings'
		);

		add_settings_field(
			'gv_splat_token',
			'Token',
			array( $this, 'token_input_field_callback' ),
			'gv_splat_settings',
			'gv_splat_main_section'
		);
	}

	public function token_input_field_callback() {
		$token = get_option( 'gv_splat_token' );
		echo '<input type="text" id="gv_splat_token" name="gv_splat_token" value="' . esc_attr( $token ) . '" />';
	}

}
