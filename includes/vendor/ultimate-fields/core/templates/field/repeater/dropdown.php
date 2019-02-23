<div class="uf-repeater-dropdown">
	<select>
		<% _.each( groups, function( group ) { %>
		<option value="<%= group.id %>"><%= group.title %></option>
		<% }) %>
	</select>

	<button type="button" class="button-primary uf-button uf-repeater-add-button">
		<span class="dashicons dashicons-plus uf-button-icon"></span>
		<%= text %>
	</button>
</div>
