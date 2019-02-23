<div class="uf-group">
	<div class="uf-group-header uf-group-header-no-number">
		<h3 class="uf-group-title"><%= title %></h3>
	</div>

	<div class="uf-group-inside">
		<% if( description && description.length ) { %>
		<div class="uf-container-description">
			<%= description %>
		</div>
		<% } %>
		
		<div class="uf-fields uf-boxed-fields uf-fields-label-200"></div>
		<input type="hidden" name="uf_term_meta_<%= id %>" value="" class="uf-container-data" />
	</div>
</div>