<div class="uf-repeater-tags">
	<h4 class="uf-repeater-tags-text"><%= text %></h4>

	<div class="uf-repeater-tags-options">
		<% _.each( groups, function( group ) { %>
		<a href="#" class="uf-repeater-tags-tag" data-group="<%= group.id %>" title="<?php echo esc_attr_e( 'Click to add', 'ultimate-fields' ) ?>">
			<%= group.title %>
		</a>
		<% }) %>
	</div>
</div>
