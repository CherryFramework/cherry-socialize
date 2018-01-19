<?php
/**
 * Template part to display user social link.
 *
 * @package Cherry_Socialize
 */
?>
<li class="<?php echo esc_attr( $config['base_class'] ); ?>__item">
	<a class="<?php echo esc_attr( $config['base_class'] ); ?>__link" href="<?php echo htmlentities( $social_url ); ?>" target="_blank">
		<i class="<?php echo esc_attr( $config['base_class'] ); ?>__link-icon <?php echo esc_attr( $network['icon'] );?>"></i>
		<span class="<?php echo esc_attr( $config['base_class'] ); ?>__link-text"><?php esc_html_e( $network['name'] ); ?></span>
	</a>
</li>
