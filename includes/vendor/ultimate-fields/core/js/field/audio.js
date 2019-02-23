(function( $ ){

	var field      = UltimateFields.Field,
		fileField  = field.File,
		audioField = field.Audio = {};

	/**
	 * Basic model for files.
	 */
	audioField.Model = fileField.Model.extend({
		defaults: $.extend( {}, fileField.Model.prototype.defaults, {
			multiple: true
		})
	});

	/**
	 * Handles the input of the audio field.
	 */
	audioField.View = fileField.View.extend({
		generatePreview: function( data ) {
			var that = this,
				$preview;

			$preview = $( '<audio controls="controls" />' );

			_.each( data, function( file ) {
				if( ! ( file instanceof Backbone.Model ) ) {
					file = new wp.media.model.Attachment( file );
				}

				$preview
					.append( $( '<source />' ).attr( 'src', file.get( 'url' ) ) );
			})

			that.$el.find( '.uf-file-preview' ).show().append( $preview );

			$preview.mediaelementplayer();
		}
	});

})( jQuery );
