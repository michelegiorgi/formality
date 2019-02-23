<div class="uf-group-number">
	<% if( icon ) { %>
	<strong class="dashicons <%= icon %>"></strong>
	<% } else { %>
	<strong class="uf-group-number-inside"><%= number %></strong>
	<% } %>
	<span class="dashicons dashicons-sort"></span>
</div>

<div class="uf-fields uf-boxed-fields uf-group-fields"></div>

<a href="#" class="uf-group-control uf-group-control-remove" title="<?php esc_attr_e( 'Remove', 'ultimate-fields' ); ?>">
	<span class="dashicons dashicons-trash"></span>
</a>
