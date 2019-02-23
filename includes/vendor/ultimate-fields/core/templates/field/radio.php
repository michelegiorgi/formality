<ul class="uf-radios orientation-<%= orientation %>">
	<% _.each( options, function( label, key ){ %>
	<li>
		<label>
			<input type="radio" name="<%= name %>" value="<%= key %>" />
			<span><%= label %></span>
		</label>
	</li>
	<% }) %>
</ul>