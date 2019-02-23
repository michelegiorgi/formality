<div class="wrap uf-settings uf-export">
	<header class="uf-settings-header uf-export-header">
		<?php if( 1 === count( $groups ) ): ?>
		<h1><?php _e( 'Import or export containers', 'ultimate-fields' ) ?>
		<p><?php _e( 'Exporting and importing containers allows you to move them between websites, create local backups and distribute them.', 'ultimate-fields' ) ?></p>
		<?php else: ?>
		<h1><?php _e( 'Import or export your data', 'ultimate-fields' ) ?>
		<p><?php _e( 'Exporting and importing containers, post types and taxonomies allows you to move them between websites, create local backups and distribute them.', 'ultimate-fields' ) ?></p>
		<?php endif ?>
	</header>

	<form class="uf-group" action="<?php echo $screen->url ?>" method="post">
		<div class="uf-group-header">
			<div class="uf-group-number">
				<span class="dashicons dashicons-download"></span>
			</div>

			<h3 class="uf-group-title"><?php _e( 'Export', 'ultimate-fields' ) ?></h3>
		</div>

		<div class="uf-group-inside">
			<div class="uf-fields uf-fields-layout-rows uf-boxed-fields uf-fields-label-200">
				<?php if( ! empty( $groups ) ): ?>
					<?php foreach( $groups as $group ): ?>
					<div class="uf-field uf-field-layout-row uf-export-containers">
						<div class="uf-field-label">
							<label><?php echo $group[ 'label' ] ?></label>
						</div>
						<div class="uf-field-input-wrap">
							<ul class="uf-radio">
								<?php foreach( $group[ 'options' ] as $id => $title ): ?>
									<li>
										<label>
											<input type="checkbox" name="uf_export_id[]" value="<?php echo $id ?>" />
											<span><?php echo $title ?></span>
										</label>
									</li>
								<?php endforeach ?>
							</ul>

							<?php if( count( $group[ 'options' ] ) > 1 ): ?>
							<div class="uf-export-all">
								<a href="#" class="check"><?php _e( 'Check all', 'ultimate-fields' ) ?></a> / <a href="#" class="uncheck"><?php _e( 'Uncheck all', 'ultimate-fields' ) ?></a>
							</div>
							<?php endif ?>
						</div>
					</div>
					<?php endforeach ?>
				<?php else: ?>
				<div class="uf-field uf-field-layout-row uf-export-container">
					<div class="uf-field-label">
						<label><?php _e( 'Containers', 'ultimate-fields' ) ?></label>
					</div>

					<div class="uf-field-input-wrap">
						<?php _e( 'There are no containers to export.', 'ultimate-fields' ) ?>
					</div>
				</div>
				<?php endif ?>

				<div class="uf-field uf-field-layout-row">
					<div class="uf-field-label">
						<label><?php _e( 'Export type', 'ultimate-fields' ) ?></label>
					</div>

					<div class="uf-field-input-wrap">
						<ul class="uf-radio">
							<li>
								<label>
									<input type="radio" name="uf_export_type" value="json" checked="checked" />
									<strong>JSON:</strong> <em><?php _e( 'Can be imported back.', 'ultimate-fields' ) ?></em>
								</label>
							</li>

							<li>
								<label>
									<input type="radio" name="uf_export_type" value="php" />
									<strong>PHP:</strong> <em><?php _e( 'Faster and localizable. Cannot be imported.', 'ultimate-fields' ) ?></em>
								</label>
							</li>
						</ul>
					</div>
				</div>

				<div class="uf-field uf-field-layout-row">
					<div class="uf-field-label">
						<label><?php _e( 'Textdomain', 'ultimate-fields' ) ?></label>
						<div class="uf-field-description"><?php _e( 'Optional. Allows the exported files to be translated.', 'ultimate-fields' ) ?></div>
					</div>

					<div class="uf-field-input-wrap">
						<input type="text" name="uf_export_textdomain" />
					</div>
				</div>
			</div>
		</div>

		<div class="uf-export-footer">
			<button type="submit" class="button-primary uf-button" disabled="disabled"><?php _e( 'Export', 'ultimate-fields' ) ?></button>
		</div>

		<?php wp_nonce_field( 'uf-export' ) ?>
	</form>

	<form class="uf-group" action="<?php echo $screen->url ?>" method="post">
		<div class="uf-group-header">
			<div class="uf-group-number">
				<span class="dashicons dashicons-upload"></span>
			</div>

			<h3 class="uf-group-title"><?php _e( 'Import', 'ultimate-fields' ) ?></h3>
		</div>

		<div class="uf-group-inside">
			<div class="notice error uf-import-error"></div>

			<div class="uf-import">
				<p><?php _e( 'Drop a file here to import it', 'ultimate-fields' ) ?></p>
				<button type="button" class="button-primary uf-button">
					<?php _e( 'Select file', 'ultimate-fields' )  ?>
				</button>
				<span class="spinner"></span>
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
jQuery(function( $ ) {
	$( '.uf-export-containers' ).each(function() {
		var $wrap   = $( this ),
			$inputs = $wrap.find( 'input:checkbox' ),
			$links  = $wrap.find( '.uf-export-all a' ),
			$button = $( '.uf-export-footer button' );

		$inputs.change(function() {
			$button.attr( 'disabled', $inputs.filter( ':checked' ).length ? false : 'disabled' );
		});

		$links.click(function() {
			var $link = $( this );

			$inputs.prop( 'checked', $link.is( '.check' ) ? 'checked' : false ).trigger( 'change' );
			$link.blur();

			return false;
		});
	});
});

uf_max_file_size = '<?php echo wp_max_upload_size() ?>';
</script>
