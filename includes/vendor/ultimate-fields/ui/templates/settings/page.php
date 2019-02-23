<div class="wrap uf-settings">
	<div class="wp-filter uf-settings-toolbar">
		<a href="https://www.ultimate-fields.com/" target="_blank" class="uf-settings-website-link">
			<?php echo file_get_contents( ULTIMATE_FIELDS_UI_DIR . 'assets/icon.svg' ) ?>
		</a>

		<ul class="filter-links uf-settings-tabs">
			<?php foreach( $screens as $screen ) {
				printf(
					'<li><a href="%s" %s>%s</a></li>',
					$screen->url,
					$screen->active ? ' class="current"' : '',
					$screen->get_title()
				);
			} ?>
		</ul>
	</div>
</div><!-- /.uf-options -->
