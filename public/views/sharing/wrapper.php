<?php
/**
 * Template part to display wrapper for sharing buttons.
 *
 * @package Cherry_Socialize
 */
?>
<div class="<?php echo esc_attr( join( ' ', $classes ) ); ?>">
	<ul class="<?php echo esc_attr( $config['base_class'] ); ?>__list"><?php echo $share_buttons; ?></ul>
</div>
