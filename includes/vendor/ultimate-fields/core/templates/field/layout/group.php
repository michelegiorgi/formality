<div class="uf-group-header uf-layout-group-header">
	<div class="uf-group-number uf-layout-group-number">
		<% if( icon ) { %>
		<strong class="dashicons <%= icon %>"></strong>
		<% } else { %>
		<strong class="uf-group-number-inside"><%= number %></strong>
		<% } %>
		<span class="dashicons dashicons-move"></span>
	</div>

	<div class="uf-layout-group-indicator">
		<strong class="wp-ui-highlight">
			<span class="uf-layout-group-width">0</span>/<span class="uf-layout-group-columns">12</span>
		</strong>
	</div>

	<div class="uf-group-controls uf-layout-group-controls">
		<a href="#" class="uf-group-control uf-layout-group-control uf-group-control-remove" title="<?php esc_attr_e( 'Remove', 'ultimate-fields' ); ?>">
			<span class="dashicons dashicons-trash"></span>
		</a>
		<a href="#" class="uf-group-control uf-layout-group-control uf-group-control-popup" title="<?php esc_attr_e( 'Open overlay', 'ultimate-fields' ); ?>">
			<span class="dashicons dashicons-edit"></span>
		</a>
	</div>

	<h3 class="uf-group-title"><%= title %><em class="uf-group-title-preview"></em></h3>
</div>
