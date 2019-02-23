(function($){

	UltimateFields.initializeContainers();
	$( document ).trigger( 'uf-initialize-loaded' );

	$(function() {
		$( document ).trigger( 'uf-extend' );
		$( document ).trigger( 'uf-pre-init' );
		UltimateFields.L10N.init();
		UltimateFields.initializeContainers();
		$( document ).trigger( 'uf-init' );
	});

})( jQuery );
