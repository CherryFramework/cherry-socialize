<?php
/**
 * Cherry_Socialize_User_Social_Links class.
 *
 * @package Cherry_Socialize
 * @since 1.1.0
 */

if ( ! class_exists( 'Cherry_Socialize_User_Social_Links' ) ) {

	/**
	 * Class for adding user social links.
	 *
	 * @since 1.1.0
	 */
	class Cherry_Socialize_User_Social_Links {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.1.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Configuration.
		 *
		 * @since 1.1.0
		 */
		private $config = array();

		/**
		 * User social networks.
		 *
		 * @since 1.1.0
		 */
		private $user_networks = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			if ( ! $this->is_support() ) {
				return;
			}

			$this->config = array(
				'type'         => 'icon', // icon, text or both
				'base_class'   => 'cs-user-social',
				'custom_class' => '',
			);

			$this->user_networks = array(
				'facebook'    => array(
					'name' => esc_html__( 'Facebook', 'cherry-socialize' ),
					'icon' => 'fa fa-facebook',
				),
				'twitter'     => array(
					'name' => esc_html__( 'Twitter', 'cherry-socialize' ),
					'icon' => 'fa fa-twitter',
				),
				'google-plus' => array(
					'name' => esc_html__( 'Google+', 'cherry-socialize' ),
					'icon' => 'fa fa-google-plus',
				),
				'instagram'   => array(
					'name' => esc_html__( 'Instagram', 'cherry-socialize' ),
					'icon' => 'fa fa-instagram',
				),
				'linkedin'    => array(
					'name' => esc_html__( 'LinkedIn', 'cherry-socialize' ),
					'icon' => 'fa fa-linkedin',
				),
			);

			// Add user social meta.
			add_filter( 'user_contactmethods', array( $this, 'add_user_social_meta' ) );

			// Hooks a function on to display/return user social links.
			add_filter( 'cherry_socialize_return_user_social_links', array( $this, 'get_the_user_social_links' ) );
			add_action( 'cherry_socialize_display_user_social_links', array( $this, 'the_user_social_links' ) );
		}

		/**
		 * Support user social links.
		 */
		public function is_support() {
			return apply_filters( 'cherry_socialize_support_user_social_links', false );
		}

		/**
		 * Add user social meta.
		 *
		 * @param array $methods
		 *
		 * @return array
		 */
		public function add_user_social_meta( $methods = array() ) {

			$networks = $this->get_networks();

			foreach ( (array) $networks as $id => $network ) {
				$methods[ $id ] = $network['name'];
			}

			return $methods;
		}

		/**
		 * Retrieve a user social links.
		 *
		 * @since  1.1.0
		 * @param  array  $config
		 * @return string
		 */
		public function get_the_user_social_links( $config = array() ) {
			return $this->build( $config );
		}

		/**
		 * Display a user social links.
		 *
		 * @since  1.1.0
		 * @param  array  $config
		 * @return string
		 */
		public function the_user_social_links( $config = array() ) {
			echo $this->get_the_user_social_links( $config );
		}

		/**
		 * Build HTML for a user social links.
		 *
		 * @since  1.1.0
		 * @param  array $config
		 * @return string
		 */
		public function build( $config ) {
			$config   = wp_parse_args( $config, $this->get_config() );
			$networks = $this->get_networks();

			$social_links = '';
			$template_item = cherry_socialize()->locate_template( 'user-social-links/item.php' );

			foreach ( (array) $networks as $id => $network ) :

				$author_social_url = get_the_author_meta( $id );

				if ( empty( $author_social_url ) ) {
					continue;
				}

				$social_url = esc_url( $author_social_url );

				ob_start();
				include $template_item;
				$social_links .= ob_get_clean();

			endforeach;

			if ( ! $social_links ) {
				return false;
			}

			$classes = array(
				esc_attr( $config['base_class'] ),
				esc_attr( $config['base_class'] ) . '--' . esc_attr( $config['type'] ),
				esc_attr( $config['custom_class'] ),
			);

			$classes = array_map( 'esc_attr', $classes );
			$classes = array_filter( $classes );

			ob_start();
			include cherry_socialize()->locate_template( 'user-social-links/wrapper.php' );
			$result = ob_get_clean();

			return apply_filters( 'cherry_socialize_user_social_links_html', $result, $config );
		}

		/**
		 * Retrieve a configuration.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_config() {
			return apply_filters( 'cherry_socialize_get_user_social_config', $this->config, $this );
		}

		/**
		 * Retrieve a networks.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_networks() {
			return apply_filters( 'cherry_socialize_get_user_social_networks', $this->user_networks, $this );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.1.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}

	Cherry_Socialize_User_Social_Links::get_instance();
}
