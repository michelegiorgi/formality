<div class="uf-bubble">
	<a href="#" class="button-primary uf-button uf-bubble-button">
		<span class="dashicons dashicons-plus uf-button-icon"></span>
		<%= text %>
	</a>

	<ul class="uf-bubble-list">
		<li class="uf-bubble-item">
			<% _.each( groups, function( group ) { %>
			<a href="#" class="uf-bubble-link" data-group="<%= group.id %>"><%= group.title %></a>
			<% }) %>
		</li>
	</ul>
</div>
