<div class="uf-font">
	<div class="uf-font-top">
		<div class="uf-font-name"><%= font.family %></div>
		<div class="uf-font-variants">
			<% _.each( font.variants, function( variant ){ %>
			<div class="uf-font-variant">
				<span style="<%= getVariantStyle( variant ) %>; font-family: <%= font.family %>"><?php _e( 'The quick brown fox jumps over the lazy dog', 'ultimate-fields' ) ?></span>
				<small>(<%= getVariantDescription( variant ) %>)</small>
			</div>
			<% }) %>
		</div>
	</div>
	<div class="uf-font-footer"></div>
</div>