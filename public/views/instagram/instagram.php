<?php
/**
 * Template part to display item for Instagram widget.
 *
 * @package Cherry_Socialize
 * @subpackage Widgets
 */
?>

<div class="cs-instagram__item">
	<?php $this->the_image( $photo ); ?>
	<?php $this->the_caption( $photo ); ?>
	<?php $this->the_date( $photo ); ?>
</div>
