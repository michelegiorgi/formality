<tr>
	<td class="uf-sidebar-r"><input type="radio" name="<%= uniqueID %>" value="<%- sidebar._builtin ? sidebar.id : sidebar.name %>" /></td>
	<td class="uf-sidebar-t"><p><%= sidebar.name %></p></td>
	<td class="uf-sidebar-d"><%= sidebar.description %></td>
	<td class="center">
		<% if( ! sidebar._builtin ) { %>
		<a href="#" class="button-secondary uf-button uf-button-no-text remove">
			<span class="dashicons dashicons-trash"></span>
		</a>
		<% } else { %>
		&dash;
		<% } %>
	</td>
</tr>
