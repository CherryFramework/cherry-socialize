<?php
/**
 * Template part to display wrapper for user social links.
 *
 * @package Cherry_Socialize
 */
?>
<div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
	<ul class="<?php echo esc_attr( $config['base_class'] ); ?>__list"><?php echo wp_kses_post( $social_links ); ?></ul>
</div>
