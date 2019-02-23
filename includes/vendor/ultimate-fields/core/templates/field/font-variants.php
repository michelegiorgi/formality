<div class="uf-variants-header">
	<h1><%= family %></h1>
	<p><?php _e( 'Choose the font variants which you&apos;d like to use and click the &quot;Select&quot; button.', 'ultimate-fields' ) ?></p>
</div>

<% _.each( variants, function( variant ) { %>
<label class="uf-variant">
	<input type="checkbox" value="<%= variant %>" <%= selected.indexOf( variant ) == -1 ? '' : ' checked="checked"' %> />
	<span class="uf-variant-preview" style="<%= getVariantStyle( variant ) %>"><%= text %></span>
	<small class="uf-variant-description"><%= getVariantDescription( variant ) %></small>
</label>
<% }) %>