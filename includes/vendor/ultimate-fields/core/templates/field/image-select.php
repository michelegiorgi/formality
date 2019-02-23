<% _.each( options, function( option, key ){ %>
<label>
	<input type="radio" value="<%= key %>" name="<%= inputId %>" />
	<span><img src="<%= option.image %>" alt="<%= option.label %>" label="<%= option.title %>" /></span>
</label>
<% }) %>