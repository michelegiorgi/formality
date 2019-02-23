<div class="uf-link">
	<div class="uf-link-top">
		<div class="uf-link-chooser">
			<div class="uf-basic-input uf-link-url">
				<span class="uf-field-prefix">
					<span class="dashicons dashicons-admin-site"></span>
				</span>

				<input type="text" class="uf-link-url-input" />
			</div>

			<span class="uf-link-or"><?php _e( 'or', 'ultimate-fields' ) ?></span>

			<!-- The select button will be here -->
		</div>
	</div>

	<% if( target_control ) { %>
	<label class="uf-link-new-tab">
		<input type="checkbox" class="uf-link-new-tab-input" />
		<span><?php _e( 'Open link in a new tab' ) ?></span>
	</label>
	<% } %>
</div>
