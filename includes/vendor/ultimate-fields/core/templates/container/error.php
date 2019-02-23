<div class="error uf-error">
	<p><strong><%= title %></strong></p>
	<ul>
		<% _.each( problems, function( problem ) { %>
		<li><%= problem %></li>
		<% }) %>
	</ul>
</div>