<div class="uf-chooser">
	<div class="uf-chooser-filter <%= show_filters ? '' : ' uf-chooser-filter-mini' %>">
		<input type="text" class="uf-chooser-filter-input" placeholder="<?php esc_attr_e( 'Search...', 'ultimate-fields' ) ?>" />

		<% if( show_filters ) { %>
		<div class="uf-chooser-filter-type">
			<select multiple="multiple" size="1">
				<% _.each( filters, function( options, label ){ %>
				<optgroup label="<%- label %>">
					<% _.each( options, function( text, value ) { %>
						<option value="<%- value %>"><%- text %></option>
					<% }) %>
				</optgroup>
				<% }) %>
			</select>
		</div>
		<% } %>
	</div>

	<div class="uf-chooser-list">
		list here
	</div>

	<div class="uf-chooser-footer"></div>
</div>
