<?php
/**
 * Template part to display sharing button.
 *
 * @package Cherry_Socialize
 */
?>
<li class="<?php echo esc_attr( $config['base_class'] ); ?>__item">
	<a class="<?php echo esc_attr( $config['base_class'] ); ?>__link" href="<?php echo htmlentities( $share_url ); ?>" target="_blank" rel="nofollow" title="<?php printf( esc_html__( 'Share on %s', 'cherry-socialize' ), esc_attr( $network['name'] ) ); ?>">
		<span class="<?php echo esc_attr( $config['base_class'] ); ?>__link-text"><?php esc_html_e( $network['name'] ); ?></span>
	</a>
</li>
