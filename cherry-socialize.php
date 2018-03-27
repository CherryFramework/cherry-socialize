<?php
/**
 * Plugin Name: Cherry Socialize
 * Plugin URI:  https://wordpress.org/plugins/cherry-socialize/
 * Description: A social plugin for WordPress.
 * Version:     1.1.1
 * Author:      Jetimpex
 * Author URI:  https://jetimpex.com/wordpress/
 * Text Domain: cherry-socialize
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @link    http://www.cherryframework.com/plugins/
 * @since   1.1.1
 * @package Cherry_Socialize
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Cherry_Socialize` doesn't exists yet.
if ( ! class_exists( 'Cherry_Socialize' ) ) {

	/**
	 * Sets up and initializes the Cherry Socialize plugin.
	 */
	class Cherry_Socialize {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of Cherry_Core class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private $core = null;

		/**
		 * Plugin folder URL.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $plugin_url = '';

		/**
		 * Plugin folder path.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $plugin_dir = '';

		/**
		 * Plugin version.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $version = '1.1.1';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Loads plugin dependencies.
			add_action( 'plugins_loaded', array( $this, 'load_deps' ), 1 );

			// Internationalize the text strings used.
			add_action( 'plugins_loaded', array( $this, 'lang' ), 2 );

			// Load the installer core.
			add_action( 'after_setup_theme', require( trailingslashit( dirname( __FILE__ ) ) . 'cherry-framework/setup.php' ), 0 );

			// Load the core functions/classes required by the rest of the plugin.
			add_action( 'after_setup_theme', array( $this, 'get_core' ), 1 );

			// Load the modules.
			add_action( 'after_setup_theme', array( 'Cherry_Core', 'load_all_modules' ), 2 );

			// Initialization of modules.
			add_action( 'current_screen', array( $this, 'init_modules' ) );

			// Load the include files.
			add_action( 'after_setup_theme', array( $this, 'includes' ), 10 );

			// Register a public javascripts and stylesheets.
			add_action( 'wp_enqueue_scripts', array( $this, 'register_public_assets' ), 1 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ),  9 );

			// Register activation, deactivation and uninstall hooks.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
			register_uninstall_hook( __FILE__, array( 'Cherry_Socialize', 'uninstall' )  );
		}

		/**
		 * Loads plugin dependencies.
		 *
		 * @since 1.0.0
		 */
		public function load_deps() {

			if ( ! class_exists( 'TLC_Transient_Update_Server' ) ) {
				require_once $this->plugin_dir( 'includes/wp-tlc-transients/class-tlc-transient-update-server.php' );
			}

			new TLC_Transient_Update_Server;

			if ( ! class_exists( 'TLC_Transient' ) ) {
				require_once $this->plugin_dir( 'includes/wp-tlc-transients/class-tlc-transient.php' );
			}

			require_once $this->plugin_dir( 'includes/wp-tlc-transients/functions.php' );
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		public function lang() {
			load_plugin_textdomain( 'cherry-socialize', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Loads the core functions. These files are needed before loading anything else in the
		 * plugin because they have required functions for use.
		 *
		 * @since 1.0.0
		 */
		public function get_core() {

			/**
			 * Fires before loads the plugin's core.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cherry_socialize_core_before' );

			global $chery_core_version;

			if ( null !== $this->core ) {
				return $this->core;
			}

			if ( 0 < sizeof( $chery_core_version ) ) {
				$core_paths = array_values( $chery_core_version );
				require_once( $core_paths[0] );

			} else {
				die( 'Class Cherry_Core not found' );
			}

			$this->core = new Cherry_Core( array(
				'base_dir' => $this->plugin_dir( 'cherry-framework' ),
				'base_url' => $this->plugin_url( 'cherry-framework' ),
				'modules'  => array(
					'cherry-js-core' => array(
						'autoload' => false,
					),
					'cherry-ui-elements' => array(
						'autoload' => false,
					),
					'cherry-widget-factory' => array(
						'autoload' => true,
					),
					'cherry-interface-builder' => array(
						'autoload' => false,
					),
				),
			) );

			return $this->core;
		}

		/**
		 * Initalize UI elements in admin area widgets page.
		 *
		 * @since 1.0.0
		 */
		public function init_modules() {

			if ( ! is_admin() ) {
				return;
			}

			$current_screen = get_current_screen();

			if ( ( $current_screen && 'widgets' == $current_screen->id ) || is_customize_preview() ) {
				$this->get_core()->init_module( 'cherry-js-core' );
				$this->get_core()->init_module( 'cherry-interface-builder' );
			}
		}

		/**
		 * Loads public files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			require_once $this->plugin_dir( 'public/includes/class-cherry-instagram-widget.php' );
			require_once $this->plugin_dir( 'public/includes/class-cherry-sharing.php' );
			require_once $this->plugin_dir( 'public/includes/class-cherry-user-social-links.php' );
		}

		/**
		 * Get plugin URL (or some plugin dir/file URL)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = plugin_dir_url( __FILE__ );
			}

			if ( null !== $path ) {
				$path = wp_normalize_path( $path );

				return $this->plugin_url . ltrim( $path, '/' );
			}

			return $this->plugin_url;
		}

		/**
		 * Get plugin dir path (or some plugin dir/file path)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_dir( $path = null ) {

			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = plugin_dir_path( __FILE__ );
			}

			if ( null !== $path ) {
				$path = wp_normalize_path( $path );

				return $this->plugin_dir . $path;
			}

			return $this->plugin_dir;
		}

		/**
		 * Register public assets.
		 *
		 * @since 1.0.0
		 */
		public function register_public_assets() {
			wp_register_style( 'font-awesome', $this->plugin_url( 'assets/css/font-awesome.min.css' ), array(), '4.7.0' );
			wp_register_style( 'cherry-socialize-public', $this->plugin_url( 'assets/css/public.css' ), array( 'font-awesome' ), $this->version );
		}

		/**
		 * Enqueue public assets.
		 *
		 * @since 1.1.0
		 */
		public function enqueue_public_assets() {
			$is_conditional_check = apply_filters( 'cherry_socialize_dequeue_style', false );

			if ( false === $is_conditional_check ) {
				wp_enqueue_style( 'cherry-socialize-public' );
			}
		}
		/**
		 * Retrieve the name of the highest priority template file that exists, optionally loading that file.
		 *
		 * @since  1.0.0
		 * @param  string $name Template name
		 * @return string
		 */
		public function locate_template( $name ) {
			$template = locate_template( "cherry-socialize/{$name}", false, false );

			if ( empty( $template ) ) {
				$template = $this->plugin_dir( "public/views/$name" );
			}

			return $template;
		}

		/**
		 * On plugin activation.
		 *
		 * @since 1.0.0
		 */
		public function activation() {
			/**
			 * Fire when plugin are activate.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cherry_socialize_activate' );
		}

		/**
		 * On plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		public function deactivation() {
			/**
			 * Fire when plugin are deactivate.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cherry_socialize_deactivate' );
		}

		/**
		 * On plugin uninstall.
		 *
		 * @since 1.0.0
		 */
		public static function uninstall() {
			$cache_keys = get_option( 'cherry_instagram_widget_cache_keys', array() );

			if ( ! empty( $cache_keys ) ) {
				foreach ( $cache_keys as $widget_id => $key ) {
					delete_transient( $key );
				}
			}

			delete_option( 'cherry_instagram_widget_cache_keys' );

			/**
			 * Fire when plugin are uninstall.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cherry_socialize_uninstall' );
		}

		/**
		 * Returns the instance.
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

if ( ! function_exists( 'cherry_socialize' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function cherry_socialize() {
		return Cherry_Socialize::get_instance();
	}
}

cherry_socialize();
