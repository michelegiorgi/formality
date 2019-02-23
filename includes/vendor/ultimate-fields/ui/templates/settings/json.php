<div class="wrap uf-settings uf-json">
	<div class="uf-json-top">
		<div class="uf-settings-header uf-json-header">
			<h1 class="uf-json-title"><?php _e( 'JSON Synchronization', 'ultimate-fields' ) ?></h1>
			<?php echo wpautop( __( 'JSON Synchronization allows Ultimate Fields to save containers as JSON in your theme in order to greatly improve loading performance and allow you to version your fields into GIT or SVN.', 'ultimate-fields' ) ) ?>
		</div>

		<div class="uf-json-state uf-json-<?php echo $enabled ? ( 'enabled uf-json-' . ( $writeable ? 'writeable' : 'unwriteable' ) ) : 'disabled' ?>">
			<h2>
				<div class="uf-json-state-icon">
					<?php if( ! $enabled ): ?>
					<span class="dashicons dashicons-no"></span>
					<?php elseif( ! $writeable ): ?>
					<span class="dashicons dashicons-lock"></span>
					<?php else: ?>
					<span class="dashicons dashicons-yes"></span>
					<?php endif; ?>
				</div>

				<?php _e( 'Current state:', 'ultimate-fields' ) ?>

				<?php
				if( ! $enabled ) {
					_e( 'Disabled', 'ultimate-fields' );
				} elseif( ! $writeable ) {
					_e( 'Not writeable', 'ultimate-fields' );
				} else {
					_e( 'Enabled', 'ultimate-fields' );
				}
				?>
			</h2>

			<div class="uf-json-state-body">
				<?php
				if( ! $enabled ) {
					$text = __( 'Synchronization is inactive, because a directory for storing JSON files does not exist. In order to enable synchronisation, please create the following directory:', 'ultimate-fields' );
				} elseif( ! $writeable ) {
					$text = __( 'Synchronization is active, but if you make any changes, they will not be saved because the JSON directory is not writeable! Please change the persmissions of the following directory to 0755:', 'ultimate-fields' );
				} else {
					$text = __( "The directory in your theme exists and is writable. All your changes will be synchronized!", 'ultimate-fields' );
				}

				echo wpautop( $text );
				?>

				<?php if( $show_path ): ?>
				<pre><?php echo $directory ?></pre>
				<?php endif ?>
			</div>
		</div>

		<div class="uf-clearfix"></div>
	</div>

	<?php
	if( $show_list && ! empty( $containers ) ): ?>
	<form action="<?php echo $url ?>" method="post">
		<table class="wp-list-table widefat striped uf-json-table">
			<thead>
				<?php ob_start() ?>
				<tr>
					<td class="check-column"><input type="checkbox" /></td>
					<th><?php _e( 'Title', 'ultimate-fields' ) ?></th>
					<th><?php _e( 'Synchronized', 'ultimate-fields' ) ?></th>
					<th><?php _e( 'Exists in the database', 'ultimate-fields' ) ?></th>
					<th><?php _e( 'Exists in JSON', 'ultimate-fields' ) ?></th>
					<th><?php _e( 'Last Modified', 'ultimate-fields' ) ?></th>
				</tr>
				<?php echo $headers = ob_get_clean() ?>
			</thead>

			<tbody>
				<?php
				$index = 0;
				foreach( $containers as $id => $c ): ?>
				<tr class="<?php echo $index % 2 ? 'odd' : 'even' ?>">
					<th scole="row" class="check-column">
						<input type="checkbox" name="container[]" value="<?php echo $c[ 'hash' ] ?>" />
					</th>
					<td>
						<span class="row-title">
							<?php echo $c[ 'title' ] ?>
						</span>

						<div class="row-actions">
							<?php
							$i = 0;
							foreach( $c[ 'actions' ] as $name => $link ) {
								echo "<span class='$name'>";
									echo $link;
								echo "</span>";

								$i++;

								if( $i !== count( $c[ 'actions' ] ) ) {
									echo ' | ';
								}
							}
							?>
						</div>
					</td>
					<?php json_yesno( $c[ 'database' ] && $c[ 'json' ] && ! isset( $c[ 'diff' ] ) ) ?>
					<?php json_yesno( $c[ 'database' ] ) ?>
					<?php json_yesno( $c[ 'json' ] ) ?>
					<td>
						<?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $c[ 'modified' ] ) ?>
						<?php if( isset( $c[ 'diff' ] ) ) echo '<br /><strong>' . $c[ 'diff' ] . '</strong>'  ?>
					</td>
					<?php $index++ ?>
				</tr>
				<?php endforeach; ?>
			</tbody>

			<tfoot>
				<?php echo $headers ?>
			</tfoot>
		</table>

		<div class="tablenav bottom">
			<div class="alignleft actions bulkactions">
				<select name="action">
					<option value="-1"><?php _e( 'Bulk Actions' ) ?></option>

					<?php foreach( $bulk_actions as $action => $label ) {
						printf( '<option value="%s">%s</option>', $action, $label );
					} ?>
				</select>
				<input type="submit" class="button" value="<?php _e( 'Apply' ) ?>" />
			</div>

			<div class="tablenav-pages one-page">
				<span class="displaying-num"><?php
					$count = count( $containers );
					printf( _n( '%s container', '%s containers', $count, 'ultimate-fields' ), $count );
				?></span>
			</div>

			<br class="clear">
		</div>

		<?php wp_nonce_field( 'uf-json-bulk-bulk', 'json-nonce' ) ?>
		<input type="hidden" name="json-action" value="bulk" />
		<input type="hidden" name="json-item" value="bulk" />
	</form>
	<?php endif ?>
</div>
<?php
function json_yesno( $v ) {
	if( $v ) {
		echo '<td class="uf-json-status uf-json-status-yes">
			<span class="dashicons dashicons-yes"></span> ' . __( 'Yes', 'ultimate-fields' ) . '
		</td>';
	} else {
		echo '<td class="uf-json-status uf-json-status-no">
			<span class="dashicons dashicons-no"></span> ' . __( 'No', 'ultimate-fields' ) . '
		</td>';
	}
}
