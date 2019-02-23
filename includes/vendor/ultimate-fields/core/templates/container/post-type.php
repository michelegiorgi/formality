<% if( description && description.length ) { %>
<div class="uf-container-description">
	<%= description %>
</div>
<% } %>

<div class="uf-fields <% if( boxed ) { %>uf-boxed-fields<% } %>"></div>

<input type="hidden" name="uf_post_type_<%= id %>" value="" class="uf-container-data" />
