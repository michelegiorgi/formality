<div class="uf-fields-box-wrapper hide-if-no-js">
	<div class="postbox uf-fields-box">
		<h2 class="hndle"><span><?php _e( 'Fields', 'ultimate-fields' ) ?></span></h2>

		<div class="wp-ui-highlight uf-fields-editor-wrapper">
			<div class="uf-fields-editor">
				<p class="uf-fields-loading"><?php _e( "Your fields will appear here once you create them.\n\nYou can start with the &quot;Add Field&quot; button below.", 'ultimate-fields' ) ?></p>
			</div>
		</div>
	</div>

	<div class="uf-fields-box-footer">
		<button type="button" class="button-primary uf-button uf-add-field-button">
			<span class="dashicons dashicons-plus uf-button-icon"></span>
			<?php _e( 'Add field', 'ultimate-fields' ) ?>
		</button>
	</div>

	<input type="hidden" name="uf-group-fields" class="uf-group-fields" value="<?php echo esc_attr( $existing ) ?>" />
</div>

<?php Ultimate_Fields\Template::instance()->include_template( 'container/no-js' ) ?>
