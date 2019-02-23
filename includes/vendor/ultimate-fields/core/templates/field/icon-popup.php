<div class="uf-icon-lists">
	<% _.each( sets, function( set, setKey ){ %>
	<div class="uf-icon-list uf-icon-list-<%= setKey %>">
		<% _.each( set.groups, function( group ) { %>
		<div class="uf-icon-group">
			<h4><%= group.groupName %></h4>

			<div class="uf-icon-group-icons">
				<% _.each( group.icons, function( icon ) { %>
				<div class="uf-icon-group-selector" data-icon="<%=icon %>">
					<input type="radio" name="<%= inputName %>" value="<%= icon %>" id="<%= inputName %>-<%= icon %>" />
					<label for="<%= inputName %>-<%= icon %>">
						<span class="<%= set.prefix %> <%= icon %>"></span>
					</label>
				</div>
				<% }) %>
			</div>
		</div>
		<% }) %>
	</div>
	<% }) %>
</div>

<div class="uf-icon-sidebar">
	<div class="uf-icon-search">
		<input type="text" placeholder="<?php _e( 'Type to search', 'ultimate-fields' ) ?>" class="uf-icon-search-input" />
	</div>

	<div class="uf-icon-current">
		<span class="uf-icon-current-span"></span>
		<span class="uf-icon-current-name"></span>
	</div>
</div>