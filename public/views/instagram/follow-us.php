<?php
/**
 * Template part to display `Follow Us` block for Instagram widget.
 *
 * @package Cherry_Socialize
 * @subpackage Widgets
 */
?>

<a class="cs-instagram__follow-us" href="<?php echo esc_url( $follow_url ); ?>" target="_blank" rel="nofollow">
	<?php echo wp_kses_post( $follow_text ); ?>
</a>
