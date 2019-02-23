<p><span class="dashicons dashicons-update"></span> <?php echo $heading ?></p>
<div class="uf-tip uf-tip-red">
	<span class="dashicons dashicons-warning uf-tip-icon"></span>
	<?php echo wpautop( $text ) ?>
</div>

<a href="<?php echo $sync_url ?>" class="button-primary uf-button">
	<span class="dashicons dashicons-update uf-button-icon"></span>
	<?php _e( 'Synchronize', 'ultimate-fields' ) ?>
</a>

<a href="https://www.google.com" target="_blank" class="button-secondary uf-button">
	<span class="dashicons dashicons-admin-site uf-button-icon"></span>
	<?php _e( 'Learn more', 'ultimate-fields' ) ?>
</a>
