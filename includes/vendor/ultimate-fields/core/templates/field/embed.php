<div class="uf-embed">
	<div class="uf-embed-top <%= code ? '' : 'uf-embed-empty' %>">
		<div class="uf-embed-preview <%= -1 != code.indexOf( 'iframe' ) ? 'uf-embed-preview-iframe' : '' %>">
			<%= code %>
		</div>

		<div class="uf-embed-placeholder">
			<span class="dashicons dashicons-admin-media"></span>
		</div>
	</div>

	<div class="uf-embed-footer">
		<input type="text" class="uf-embed-url" placeholder="<?php esc_attr_e( 'Enter the URL of the item that you want to embed here', 'ultimate-fields' ) ?>" value="<%- url %>" />
	</div>
</div>
