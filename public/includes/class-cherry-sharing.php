<?php
/**
 * Cherry_Socialize_Sharing class.
 *
 * @package Cherry_Socialize
 * @since 1.1.0
 */

if ( ! class_exists( 'Cherry_Socialize_Sharing' ) ) {

	/**
	 * Class for adding share buttons.
	 *
	 * @since 1.1.0
	 */
	class Cherry_Socialize_Sharing {

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
		 * Social networks.
		 *
		 * @since 1.1.0
		 */
		private $networks = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			// Hooks a function on to display/return sharing.
			add_filter( 'cherry_socialize_return_sharing', array( $this, 'get_the_sharing' ), 10, 3 );
			add_action( 'cherry_socialize_display_sharing', array( $this, 'the_sharing' ), 10, 3 );

			$this->config = array(
				'http'         => is_ssl() ? 'https' : 'http',
				'type'         => 'icon', // icon, text or both
				'base_class'   => 'cs-share',
				'custom_class' => '',
			);

			/**
			 * Docs:
			 *
			 * $1%s - `id`
			 * $2%s - `type`
			 * $3%s - `url`
			 * $4%s - `title`
			 * $4%s - `summary`
			 * $5%s - `thumbnail`
			 */
			$this->networks = array(
				'facebook' => array(
					'name'      => esc_html__( 'Facebook', 'cherry-socialize' ),
					'share_url' => 'https://www.facebook.com/sharer/sharer.php?u=%3$s&t=%4$s',
				),
				'twitter' => array(
					'name'      => esc_html__( 'Twitter', 'cherry-socialize' ),
					'share_url' => 'https://twitter.com/intent/tweet?url=%3$s&text=%4$s',
				),
				'google-plus' => array(
					'name'      => esc_html__( 'Google+', 'cherry-socialize' ),
					'share_url' => 'https://plus.google.com/share?url=%3$s',
				),
				'linkedin' => array(
					'name'      => esc_html__( 'LinkedIn', 'cherry-socialize' ),
					'share_url' => 'http://www.linkedin.com/shareArticle?mini=true&url=%3$s&title=%4$s&summary=%5$s&source=%3$s',
				),
				'pinterest' => array(
					'name'      => esc_html__( 'Pinterest', 'cherry-socialize' ),
					'share_url' => 'https://www.pinterest.com/pin/create/button/?url=%3$s&description=%4$s&media=%6$s',
				),
			);
		}

		/**
		 * Retrieve a sharing.
		 *
		 * @since  1.1.0
		 * @param  bool   $empty
		 * @param  array  $config
		 * @param  array  $networks
		 * @return string
		 */
		public function get_the_sharing( $empty, $config, $networks ) {
			return $this->build( $config, $networks );
		}

		/**
		 * Display a sharing.
		 *
		 * @since  1.1.0
		 * @param  bool   $empty
		 * @param  array  $config
		 * @param  array  $networks
		 * @return string
		 */
		public function the_sharing( $empty, $config, $networks ) {
			echo $this->get_the_sharing( $empty, $config, $networks );
		}

		/**
		 * Build HTML for a sharing buttons.
		 *
		 * @since  1.1.0
		 * @param  array $config
		 * @param  array $networks
		 * @return string
		 */
		public function build( $config, $networks ) {
			$config   = wp_parse_args( $config, $this->get_config() );
			$networks = wp_parse_args( $networks, $this->get_networks() );

			// Prepare a data for sharing.
			$id           = get_the_ID();
			$type         = get_post_type( $id );
			$url          = get_permalink( $id );
			$title        = get_the_title( $id );
			$summary      = get_the_excerpt();
			$thumbnail_id = get_post_thumbnail_id( $id );
			$thumbnail    = '';

			if ( ! empty( $thumbnail_id ) ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'full' );
				$thumbnail = $thumbnail[0];
			}

			$share_buttons = '';
			$template_item = cherry_socialize()->locate_template( 'sharing/item.php' );

			foreach ( (array) $networks as $id => $network ) :

				if ( empty( $network['share_url'] ) ) {
					continue;
				}

				$share_url = sprintf( $network['share_url'],
					urlencode( $id ),
					urlencode( $type ),
					urlencode( $url ),
					urlencode( $title ),
					urlencode( $summary ),
					urlencode( $thumbnail )
				);

				ob_start();
				include $template_item;
				$share_buttons .= ob_get_clean();

			endforeach;

			$classes = array(
				esc_attr( $config['base_class'] ),
				esc_attr( $config['base_class'] ) . '--' . esc_attr( $config['type'] ),
				esc_attr( $config['custom_class'] ),
			);

			$classes = array_map( 'esc_attr', $classes );
			$classes = array_filter( $classes );

			ob_start();
			include cherry_socialize()->locate_template( 'sharing/wrapper.php' );
			$result = ob_get_clean();

			return apply_filters( 'cherry_socialize_sharing_html', $result, $config, $networks );
		}

		/**
		 * Retrieve a configuration.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_config() {
			return apply_filters( 'cherry_socialize_get_config_sharing', $this->config, $this );
		}

		/**
		 * Retrieve a networks.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public function get_networks() {
			return apply_filters( 'cherry_socialize_get_networks_sharing', $this->networks, $this );
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

	Cherry_Socialize_Sharing::get_instance();
}