<div class="wrap">
	<h1>
		<?php if( $icon ): ?>
			<span class="dashicons <?php echo $icon ?>"></span>
		<?php endif ?>
		<?php echo $title ?>
	</h1>

	<?php if( isset( $_GET[ 'uf-message' ] ) ): ?>
	<div class="notice notice-success is-dismissible">
		<?php echo wpautop( __( 'Your options were sucessfully saved.', 'ultimate-fields' ) ) ?>
	</div>
	<?php endif ?>

	<form method="post" action="<?php echo $url ?>" id="poststuff">
		<div id="post-body" class="columns-<?php echo $columns ?>">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $id, 'side', $page ); ?>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<?php
				do_meta_boxes( $id, 'normal', $page );
				do_meta_boxes( $id, 'advanced', $page );
				?>
			</div>
		</div>

		<?php
		# Standard nonces for metabox sorting and toggling
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

		echo $nonce;
		?>
	</form>
</div>

<script>
jQuery(function() {
	postboxes.add_postbox_toggles(pagenow);
})
</script>