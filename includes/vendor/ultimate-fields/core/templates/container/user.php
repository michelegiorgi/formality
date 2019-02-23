<% if( 'boxed' != style ) { %>
<h2><%= title %></h2>
<% } %>

<% if( description && description.length ) { %>
<div class="uf-container-description">
	<%= description %>
</div>
<% } %>

<div class="uf-fields uf-fields-label-200"></div>

<input type="hidden" name="uf_user_meta_<%= id %>" value="" class="uf-container-data" />