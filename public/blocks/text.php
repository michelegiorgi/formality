<?php
/**
 * Block Name: Text
 *
 * This is the template that displays the testimonial block.
 */

// get image field (array)
$avatar = get_field('image');

// create id attribute for specific styling
$id = 'testimonial-' . $block['id'];

// create align class ("alignwide") from block setting ("wide")
$align_class = $block['align'] ? 'align' . $block['align'] : '';

?>
<input type="text" id="<?php echo $id; ?>" class="testimonial <?php echo $align_class; ?>" placeholder="<?php the_field('label'); ?>">
<style type="text/css">
	#<?php echo $id; ?> {
		background: <?php the_field('background_color'); ?>;
		color: <?php the_field('text_color'); ?>;
	}
</style>