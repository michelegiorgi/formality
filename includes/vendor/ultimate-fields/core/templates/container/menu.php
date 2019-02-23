<% if( description && description.length ) { %>
<div class="uf-container-description">
	<%= description %>
</div>
<% } %>

<div class="uf-fields"></div>

<input type="hidden" name="menu-item-uf-<%= item %>-<%= id %>" value="" class="uf-container-data" />
