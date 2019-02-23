jQuery.fn.importer = function() {
	var $        = jQuery,
		$area    = $( this ),
		$button  = $area.find( '.uf-button' ),
		$spinner = $area.find( '.spinner' ),
		uploader;

	uploader = new plupload.Uploader({
		browse_button: $button.get( 0 ),
		url: window.location.href.split( '#' ).shift(),
		filters: {
			mime_types : [
				{ title : "Ultimate Fields JSON", extensions : "json" }
			],
			max_file_size: uf_max_file_size
		},
		drop_element: $area.get( 0 ),
		file_data_name: 'uf-import-file',
		multipart_params: {
			uf_import: true
		}
	});

	uploader.bind( 'FilesAdded', function( up, files ) {
		uploader.start();
		$area.removeClass( 'uf-import-over' );
	});

	uploader.bind( 'UploadProgress', function( up ) {
		$area.addClass( 'uf-import-waiting' );
		$spinner.addClass( 'is-active' );
	});

	uploader.bind( 'FileUploaded', function( up, files, info ) {
		var data = $.parseJSON( info.response ),
			$error = $( '.uf-import-error' );

		$spinner.removeClass( 'is-active' );

		// Redirect properly
		if( data.success ) {
			window.location = data.redirect;
			return;
		}

		// Show errors
		$error.empty();
		$error.append( $( '<p />').text( data.message ).wrapInner( '<strong />' ) );

		// Add individual ones
		if( 'errors' in data ) {
			var $ul = $( '<ol />' );

			$.each( data.errors, function( i, error ) {
				$ul.append( $( '<li />' ).text( error ) )	;
			});

			$ul.appendTo( $error );
		}

		$area.before( $error.show() );
	});

	uploader.init();

	$area.on( 'dragenter', function() {
		$area.addClass( 'uf-import-over' );
	});

	$area.on( 'dragleave', function() {
		$area.removeClass( 'uf-import-over' );
	});
}

jQuery(function( $ ) {
	$( '.uf-import' ).importer();
});
