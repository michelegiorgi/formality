<div class="wrap">
	<h1>
		<?php if( $icon ): ?>
			<span class="dashicons <?php echo $icon ?>"></span>
		<?php endif ?>
		<?php echo $title ?>
	</h1>

	<form method="post" action="<?php echo admin_url( 'admin-ajax.php' ) ?>">

	</form>
</div>
