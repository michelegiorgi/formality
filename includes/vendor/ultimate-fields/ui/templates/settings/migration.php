<div class="wrap migrate-wrap">
	<form action="<?php echo esc_url( $url ) ?>" method="post">
		<p><?php _e( 'Version two of Ultimate Fields stores its fields and containers in a different way, which requires you to migrate your containers from version one, in order to use them.', 'ultimate-fields' ) ?></p>

		<p><?php _e( 'The process will not delete your old containers, so you can go back to the previous version if you experience compatibility issues.', 'ultimate-fields' ) ?></p>

		<p><strong><?php _e( 'The following containers will be migrated:', 'ultimate-fields' ) ?></strong></p>

		<ul class="containers-list">
			<?php foreach( $containers as $container ): ?>
			<li>
				<input type="hidden" name="containers[]" value="<?php echo $container->ID ?>" />
				<em><?php echo esc_html( $container->post_title ) ?></em>
			</li>
			<?php endforeach ?>
		</ul>

		<button type="submit" class="button-primary uf-button">
			<span class="dashicons dashicons-slides uf-button-icon"></span>
			<span class="uf-button-text"><?php _e( 'Migrate', 'ultimate-fields' ) ?></span>
		</button>

		<?php wp_nonce_field( 'uf-migrate-containers' ) ?>
		<input type="hidden" name="uf-ui-migrate" value="1" />
	</form>
</div>