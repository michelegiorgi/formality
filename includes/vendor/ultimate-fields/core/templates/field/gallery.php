<div class="uf-gallery">
	<div class="uf-gallery-loading"><?php _e( 'Loading images...', 'ultimate-fields' ) ?></div>
	<div class="uf-gallery-images"></div>
	<div class="uf-gallery-footer">
		<div class="uf-gallery-order">
			<span class="dashicons dashicons-randomize"></span>
			<select>
				<option value=""><?php _e( 'Change order', 'ultimate-fields' ) ?></option>
				<option value="title-asc">&#x2193; <?php _e( 'Title', 'ultimate-fields' ) ?></option>
				<option value="title-desc">&#x2191; <?php _e( 'Title', 'ultimate-fields' ) ?></option>
				<option value="filename-asc">&#x2193; <?php _e( 'File name', 'ultimate-fields' ) ?></option>
				<option value="filename-desc">&#x2191; <?php _e( 'File name', 'ultimate-fields' ) ?></option>
				<option value="date-asc">&#x2193; <?php _e( 'Date Uploaded', 'ultimate-fields' ) ?></option>
				<option value="date-desc">&#x2191; <?php _e( 'Date Uploaded', 'ultimate-fields' ) ?></option>
				<option value="default">&#x2193; <?php _e( 'Default Order', 'ultimate-fields' ) ?></option>
				<option value="default-reversed">&#x2191; <?php _e( 'Default Order - Reversed', 'ultimate-fields' ) ?></option>
				<option value="random"><?php _e( 'Randomize', 'ultimate-fields' ) ?></option>
			</select>
			<input type="button" class="uf-button button-secondary uf-video-sort" value="<?php echo esc_attr( __( 'Sort', 'ultimate-fields' ) ) ?>" />
		</div>
	</div>
</div>