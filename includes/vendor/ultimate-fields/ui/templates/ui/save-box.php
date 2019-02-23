<?php
$support_url = 'https://www.ultimate-fields.com/support/';

$text = __( 'If you are facing any issues or need help, please visit the Ultimate Fields <a href="%s" target="_blank">Support Forum</a>.', 'ultimate-fields' );
$text = sprintf( $text, $support_url );
?>

<div class="submitbox" id="submitpost">
	<div class="uf-help">
		<span class="dashicons dashicons-editor-help uf-help-icon"></span>
		<?php echo wpautop( $text ); ?>
	</div>

	<div id="major-publishing-actions">
		<div id="delete-action">
			<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ) ?>"><?php _e( 'Move to Trash', 'ultimate-fields' ) ?></a>
		</div>

		<div id="publishing-action">
			<span class="spinner"></span>
			<input name="publish" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save', 'ultimate-fields' ) ?>">
		</div>
		<div class="clear"></div>
	</div>

	<input type="hidden" name="hidden_post_status" value="publish" />
	<input name="original_publish" type="hidden" value="Update" />
	<input name="post_status" type="hidden" value="publish" />
</div>
