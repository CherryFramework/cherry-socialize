<?php
/**
 * Widget API: Cherry_Socialize_Instagram_Widget class
 *
 * @package Cherry_Socialize
 * @subpackage Widgets
 * @since 1.0.0
 */

if ( ! class_exists( 'Cherry_Socialize_Instagram_Widget' ) ) {

	/**
	 * Custom class used to implement a Instagram widget.
	 *
	 * @since 1.0.0
	 */
	class Cherry_Socialize_Instagram_Widget extends Cherry_Abstract_Widget {

		/**
		 * Instagram API-server URL.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $api_url = 'https://www.instagram.com/';

		/**
		 * Instagram CDN-server URL.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $cdn_url = 'https://scontent.cdninstagram.com/';

		/**
		 * Widget config.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $config = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$photo_sizes = $this->get_photo_size_options();

			$this->widget_cssclass    = 'cs-instagram cs-instagram--widget';
			$this->widget_description = esc_html__( 'Display a list of photos from Instagram network.', 'cherry-socialize' );
			$this->widget_id          = 'cherry_socialize_widget_instagram';
			$this->widget_name        = esc_html__( 'Cherry Socialize: Instagram', 'cherry-socialize' );
			$this->settings           = array(
				'title'  => array(
					'type'  => 'text',
					'value' => esc_html__( 'Follow Us', 'cherry-socialize' ),
					'label' => esc_html__( 'Title', 'cherry-socialize' ),
				),
				'endpoint' => array(
					'type'    => 'radio',
					'value'   => 'hashtag',
					'options' => array(
						'hashtag' => array(
							'label' => esc_html__( 'Tagged photos', 'cherry-socialize' ),
							'slave' => 'hashtag_relationship',
						),
						'self' => array(
							'label' => esc_html__( 'My Photos', 'cherry-socialize' ),
							'slave' => 'self_relationship',
						),
					),
					'label' => esc_html__( 'Content type', 'cherry-socialize' ),
				),
				'hashtag' => array(
					'type'   => 'text',
					'value'  => '',
					'label'  => esc_html__( 'Hashtag (enter without `#` symbol)', 'cherry-socialize' ),
					'master' => 'hashtag_relationship',
				),
				'self' => array(
					'type'   => 'text',
					'value'  => '',
					'label'  => esc_html__( 'User Name', 'cherry-socialize' ),
					'master' => 'self_relationship',
				),
				'photo_counter' => array(
					'type'       => 'stepper',
					'value'      => '6',
					'max_value'  => '12',
					'min_value'  => '1',
					'step_value' => '1',
					'label'      => esc_html__( 'Number of photos', 'cherry-socialize' ),
				),
				'photo_size' => array(
					'type'    => 'select',
					'value'   => key( $photo_sizes ),
					'options' => $photo_sizes,
					'label'   => esc_html__( 'Photo size', 'cherry-socialize' ),
				),
				'photo_link' => array(
					'type'  => 'switcher',
					'value' => 'true',
					'style' => 'normal',
					'label' => esc_html__( 'Enable / Disable linking photos', 'cherry-socialize' ),
				),
				'display_caption' => array(
					'type'  => 'checkbox',
					'value' => array(
						'display_caption_check' => 'false',
					),
					'options' => array(
						'display_caption_check' => array(
							'label' => esc_html__( 'Caption', 'cherry-socialize' ),
							'slave' => 'caption_relationship',
						),
					),
				),
				'caption_length' => array(
					'type'       => 'stepper',
					'value'      => '10',
					'max_value'  => '50',
					'min_value'  => '1',
					'step_value' => '1',
					'label'      => esc_html__( 'Caption length (in characters)', 'cherry-socialize' ),
					'master'     => 'caption_relationship',
				),
				'display_date' => array(
					'type'  => 'checkbox',
					'value' => array(
						'display_date_check' => 'false',
					),
					'options' => array(
						'display_date_check' => esc_html__( 'Date', 'cherry-socialize' ),
					),
				),
				'follow_us' => array(
					'type'  => 'checkbox',
					'value' => array(
						'follow_us_check' => 'false',
					),
					'options' => array(
						'follow_us_check' => array(
							'label' => esc_html__( 'Follow Us', 'cherry-socialize' ),
							'slave' => 'follow_us_relationship',
						),
					),
				),
				'follow_us_label' => array(
					'type'   => 'text',
					'value'  => esc_html__( 'Follow Us', 'cherry-socialize' ),
					'label'  => esc_html__( 'Follow Us label', 'cherry-socialize' ),
					'master' => 'follow_us_relationship',
				),
			);

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 9 );
			add_action( 'setted_transient', array( $this, 'cache_keys' ), 10, 3 );
			parent::__construct();
		}

		/**
		 * Widget function.
		 *
		 * @see WP_Widget
		 * @since 1.0.0
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {

			if ( $this->get_cached_widget( $args ) ) {
				return;
			}

			if ( 'hashtag' == $instance['endpoint'] && empty( $instance['hashtag'] ) ) {
				return print $args['before_widget'] . esc_html__( 'Please, enter #hashtag.', 'cherry-socialize' ) . $args['after_widget'];
			}

			if ( 'self' == $instance['endpoint'] && empty( $instance['self'] ) ) {
				return print $args['before_widget'] . esc_html__( 'Please, enter User Name.', 'cherry-socialize' ) . $args['after_widget'];
			}

			$this->setup_widget_data( $args, $instance );

			// Title.
			$title = apply_filters(
				'widget_title',
				empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base
			);

			// Endpoint.
			$endpoint = $this->sanitize_endpoint();
			$this->config['endpoint'] = $endpoint;
			$this->config['target']   = sanitize_text_field( $this->instance[ $endpoint ] );

			// Photo.
			$this->config['photo_counter'] = $this->sanitize_photo_counter();
			$this->config['photo_size']    = $this->sanitize_photo_size();
			$this->config['photo_link']    = $this->sanitize_photo_link();

			// Date.
			$this->config['date_format'] = apply_filters(
				'cherry_socialize_instagram_widget_date_format',
				get_option( 'date_format' ), $args, $instance
			);
			$date_enabled = ! empty( $instance['display_date'] ) ? $instance['display_date'] : 'false';

			if ( is_array( $date_enabled ) && 'true' === $date_enabled['display_date_check'] ) {
				$date_enabled = true;
			} else {
				$date_enabled = false;
			}

			$this->config['date'] = $date_enabled;

			// Caption.
			$caption_enabled = ! empty( $instance['display_caption'] ) ? $instance['display_caption'] : 'false';
			$caption_length  = $this->sanitize_caption_length();

			if ( is_array( $caption_enabled ) && 'true' === $caption_enabled['display_caption_check'] ) {
				$caption_enabled = true;
			} else {
				$caption_enabled = false;
			}

			$this->config['caption']        = $caption_enabled;
			$this->config['caption_length'] = $caption_length;

			// Folow Us block.
			$follow_us_enabled = ! empty( $instance['follow_us'] ) ? $instance['follow_us'] : 'false';
			$follow_us_label   = $this->use_wpml_translate( 'follow_us_label' );

			if ( is_array( $follow_us_enabled ) && 'true' === $follow_us_enabled['follow_us_check'] ) {
				$follow_us_enabled = true;
			} else {
				$follow_us_enabled = false;
			}

			$transient_key = $this->get_transient_key();
			$cache_timeout = apply_filters(
				'cherry_socialize_instagram_widget_cache_timeout',
				HOUR_IN_SECONDS, $args, $instance
			);

			// Grab that photos.
			$data = tlc_transient( $transient_key )
				->expires_in( $cache_timeout )
				->extend_on_fail( $cache_timeout )
				->background_only()
				->updates_with( array( $this, 'get_photos' ), array( $this->config ) )
				->get();

			if ( empty( $data ) ) {
				return;
			}

			$this->widget_start( $args, $instance );

			printf( '<div class="%s">',
				join( ' ', apply_filters( 'cherry_socialize_instagram_widget_wrapper_class', array(
					'cs-instagram__items',
					"cs-instagram__items--photo-amount-{$this->config['photo_counter']}",
					"cs-instagram__items--photo-size-{$this->config['photo_size']}",
				), $this->config, $args, $instance ) )
			);

			$template_item = cherry_socialize()->locate_template( 'instagram/instagram.php' );

			foreach ( (array) $data['photos'] as $photo ) {
				include $template_item;
			}

			if ( $follow_us_enabled ) {

				$follow_url      = $this->get_grab_url( $this->config );
				$follow_symbol   = 'hashtag' === $this->config['endpoint'] ? ' #' : ' @';
				$follow_text     = $follow_us_label . $follow_symbol . $this->config['target'];
				$template_follow = cherry_socialize()->locate_template( 'instagram/follow-us.php' );

				include $template_follow;
			}

			echo '</div>';

			$this->widget_end( $args );
			$this->reset_widget_data();
		}

		/**
		 * Retrieve a photos.
		 *
		 * @since  1.0.0
		 * @param  array $config Set of configuration.
		 * @return array
		 */
		public function get_photos( $config ) {
			$response = $this->remote_get( $config );

			if ( is_wp_error( $response ) ) {
				throw new Exception( 'error' );
			}

			$key = 'hashtag' == $config['endpoint'] ? 'tag' : 'user';

			if ( empty( $response[ $key ] ) ) {
				throw new Exception( 'error' );
			}

			if ( empty( $response[ $key ]['media']['nodes'] ) ) {
				throw new Exception( 'error' );
			}

			$data  = array();
			$nodes = array_slice(
				$response[ $key ]['media']['nodes'],
				0,
				$config['photo_counter'],
				true
			);

			foreach ( $nodes as $photo ) {
				$_photo            = array();
				$_photo['link']    = $photo['code'];
				$_photo['image']   = $photo['thumbnail_src'];
				$_photo['date']    = sanitize_text_field( $photo['date'] );
				$_photo['caption'] = wp_html_excerpt(
					$photo['caption'],
					$this->config['caption_length'],
					apply_filters( 'cherry_socialize_instagram_widget_caption_more', '&hellip;' )
				);

				array_push( $data, $_photo );
			}

			return array(
				'widget_id' => $this->id,
				'photos'    => $data,
			);
		}

		/**
		 * Retrieve the raw response from the HTTP request using the GET method.
		 *
		 * @since  1.0.0
		 * @return array|WP_Error
		 */
		public function remote_get( $config ) {
			$url = add_query_arg(
				array( '__a' => 1 ),
				$this->get_grab_url( $config )
			);

			$response      = wp_remote_get( $url );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( '' === $response_code ) {
				return new WP_Error;
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $result ) ) {
				return new WP_Error;
			}

			return $result;
		}

		/**
		 * Display a HTML tag with date.
		 *
		 * @since  1.0.0
		 * @param  array $photo Item photo data.
		 */
		public function the_date( $photo ) {

			if ( ! $this->config['date'] || empty( $photo['date'] ) ) {
				return;
			}

			$format = '<time class="cs-instagram__date" datetime="%s">%s</time>';
			$format = apply_filters(
				'cherry_socialize_instagram_widget_the_date_format',
				$format, $this->args, $this->instance
			);

			printf( $format, date( 'Y-m-d\TH:i:sP', $photo['date'] ), date( $this->config['date_format'], $photo['date'] ) );
		}

		/**
		 * Display a caption.
		 *
		 * @since  1.0.0
		 * @param  array  $photo Item photo data.
		 * @return string
		 */
		public function the_caption( $photo ) {

			if ( ! $this->config['caption'] || empty( $photo['caption'] ) ) {
				return;
			}

			$format = '<div class="cs-instagram__caption">%s</div>';
			$format = apply_filters(
				'cherry_socialize_instagram_widget_the_caption_format',
				$format, $this->args, $this->instance
			);

			printf( $format, $photo['caption'] );
		}

		/**
		 * Display a HTML link with image.
		 *
		 * @since  1.0.0
		 * @param  array $item Item photo data.
		 */
		public function the_image( $item ) {
			$size = $this->_get_relation_photo_size( $this->config['photo_size'] );

			// Get photo filename (name.jpg).
			$parse_url  = parse_url( $item['image'] );
			$parts      = explode( '/', $parse_url['path'] );
			$photo_name = $parts[ sizeof( $parts ) - 1 ];

			if ( ! empty( $size ) ) {
				$width     = $size[0];
				$height    = $size[1];
				$photo_url = sprintf(
					'%st/s%dx%d/%s',
					$this->cdn_url, absint( $width ), absint( $height ), $photo_name
				);

				$photo_format = "<img src='%s' class='cs-instagram__img' width='{$width}' height='{$height}' alt=''>";

			} else {
				$photo_url    = sprintf( '%st/%s', $this->cdn_url, $photo_name );
				$photo_format = '<img src="%s" class="cs-instagram__img" alt="">';
			}

			$photo_format = apply_filters(
				'cherry_socialize_instagram_widget_photo_format',
				$photo_format, $size, $this->args, $this->instance
			);

			$photo = sprintf( $photo_format, esc_url( $photo_url ) );

			if ( ! $this->config['photo_link'] ) {
				print $photo;
				return;
			}

			$link = sprintf( $this->get_post_url(), $item['link'] );
			$link_format = '<a class="cs-instagram__link" href="%s" target="_blank" rel="nofollow">%s<span class="cs-instagram__cover"></span></a>';

			$link_format = apply_filters(
				'cherry_socialize_instagram_widget_link_format',
				$link_format, $this->args, $this->instance
			);

			printf( $link_format, esc_url( $link ), $photo );
		}

		/**
		 * Get an array of the available photo size options.
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_photo_size_options() {
			return apply_filters( 'cherry_socialize_instagram_widget_get_photo_size_options', array(
				'thumbnail' => esc_html__( 'Thumbnail (150x150)', 'cherry-socialize' ),
				'low'       => esc_html__( 'Low (320x320)', 'cherry-socialize' ),
				'standard'  => esc_html__( 'Standard (640x640)', 'cherry-socialize' ),
				'high'      => esc_html__( 'High (original)', 'cherry-socialize' ),
			) );
		}

		/**
		 * Retrieve a photo sizes (in px) by option name.
		 *
		 * @since  1.0.0
		 * @param  string $photo_size Photo size.
		 * @return array
		 */
		public function _get_relation_photo_size( $photo_size ) {
			switch ( $photo_size ) {

				case 'high':
					$size = array();
					break;

				case 'standard':
					$size = array( 640, 640 );
					break;

				case 'low':
					$size = array( 320, 320 );
					break;

				default:
					$size = array( 150, 150 );
					break;
			}

			return apply_filters( 'cherry_socialize_instagram_widget_get_relation_photo_size', $size, $photo_size );
		}

		/**
		 * Retrieve a grab URL.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_grab_url( $config ) {

			if ( 'hashtag' == $config['endpoint'] ) {
				$url = sprintf( $this->get_tags_url(), $config['target'] );

			} else {
				$url = sprintf( $this->get_self_url(), $config['target'] );
			}

			return $url;
		}

		/**
		 * Retrieve a URL for photos by hashtag.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_tags_url() {
			return apply_filters( 'cherry_socialize_instagram_widget_get_tags_url', $this->api_url . 'explore/tags/%s/' );
		}

		/**
		 * Retrieve a URL for self photos.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_self_url() {
			return apply_filters( 'cherry_socialize_instagram_widget_get_self_url', $this->api_url . '%s/' );
		}

		/**
		 * Retrieve a URL for post.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_post_url() {
			return apply_filters( 'cherry_socialize_instagram_widget_get_post_url', $this->api_url . 'p/%s/' );
		}

		/**
		 * Set transient key.
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_transient_key() {
			return sprintf( 'cherry_socialize_instagram_%s_%s_photo-%s_caption-%s',
				$this->config['endpoint'],
				$this->config['target'],
				$this->config['photo_counter'],
				$this->config['caption_length']
			);
		}

		/**
		 * Enqueue public assets.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_assets() {
			if ( is_active_widget( false, false, $this->id_base, true ) ) {
				wp_enqueue_style( 'cherry-socialize-public' );
			}
		}

		/**
		 * Save widget's ID and transient's key to option.
		 *
		 * @since 1.0.0
		 * @param string $transient  The name of the transient.
		 * @param mixed  $value      Transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 */
		public function cache_keys( $transient, $value, $expiration ) {

			if ( 0 !== strpos( $transient, 'tlc__' ) ) {
				return;
			}

			if ( empty( $value[1]['widget_id'] ) ) {
				return;
			}

			$id         = $value[1]['widget_id'];
			$new_key    = $transient;
			$cache_keys = get_option( 'cherry_socialize_instagram_widget_cache_keys', array() );

			if ( ! empty( $cache_keys[ $id ] ) ) {

				$old_key = $cache_keys[ $id ];

				if ( $new_key !== $old_key ) {
					delete_transient( $old_key );
					unset( $cache_keys[ $id ] );
				}
			}

			$new_cache = array_merge( $cache_keys, array(
				$id => $new_key,
			) );

			update_option( 'cherry_socialize_instagram_widget_cache_keys', $new_cache );
		}

		public function sanitize_endpoint() {
			return in_array( $this->instance['endpoint'], array( 'hashtag', 'self' ) ) ? $this->instance['endpoint'] : 'hashtag';
		}

		public function sanitize_photo_size() {
			$size      = $this->instance['photo_size'];
			$default   = $this->get_default( 'photo_size' );
			$whitelist = array_keys( $this->get_photo_size_options() );

			return ! empty( $size ) && in_array( $size, $whitelist ) ? $size : $default;
		}

		public function sanitize_photo_counter() {
			$counter = $this->instance['photo_counter'];
			$default = $this->get_default( 'photo_counter' );

			return ! empty( $counter ) ? absint( $counter ) : $default;
		}

		public function sanitize_photo_link() {
			$value   = $this->instance['photo_link'];
			$default = $this->get_default( 'photo_link' );
			$is_link = ! empty( $value ) ? $value : $default;

			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		public function sanitize_caption_length() {
			$length  = $this->instance['caption_length'];
			$default = $this->get_default( 'caption_length' );

			return ! empty( $length ) ? absint( $length ) : $default;
		}

		public function get_default( $key ) {
			return ! empty( $this->settings[ $key ]['value'] ) ? $this->settings[ $key ]['value'] : false;
		}

	}

	add_action( 'widgets_init', 'cherry_socialize_register_instagram_widget' );
	function cherry_socialize_register_instagram_widget() {
		register_widget( 'Cherry_Socialize_Instagram_Widget' );
	}
}
