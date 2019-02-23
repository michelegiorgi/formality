(function( $ ){

	var field      = UltimateFields.Field,
		fileField  = field.File,
		imageField = field.Image = {};

	/**
	 * Extend the normal file model for the image.
	 */
	imageField.Model = fileField.Model.extend({
		/**
		 * Returns an SEO-analyzable value of the field.
		 */
		getSEOValue: function() {
			var id = this.getValue(), obj, image;

			if( ! id ) {
				return false;
			}

			obj = fileField.Cache.get( id );

			return '<img src="%s" height="%s" width="%s" alt="%s" />'
				.replace( '%s', obj.get( 'url' ) )
				.replace( '%s', obj.get( 'width' ) )
				.replace( '%s', obj.get( 'height' ) )
				.replace( '%s', obj.get( 'alt' ).replace( /['"]/g, "&#39;" ) );
		}
	});

	/**
	 * Handles the input of the image field.
	 */
	imageField.View = fileField.View.extend({});

})( jQuery );
