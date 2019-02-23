<?php
/**
 * This template is used for forms int he front-end only.
 */
?><form action="<?php echo esc_attr( $url ) ?>" method="post" class="uf-form">
	<?php if( $success ): ?>
	<div class="uf-form-success">
		<h4><?php echo $success[ 'title' ] ?></h4>
		<?php echo wpautop( $success[ 'message' ] ) ?>
	</div>
	<?php endif ?>

	<?php $fields->callback() ?>

	<div class="uf-form-special">
		<input type="text" name="<?php echo $form_id ?>" value="" />		
	</div>

	<div class="uf-form-footer">
		<button type="submit"><?php echo $button_text ?></button>
		<?php wp_nonce_field( $nonce_action, $nonce_name ) ?>
	</div>
</form>