(function( $ ){

	var field      = UltimateFields.Field,
		colorField = field.Color = {};

	/**
	 * Handles the input of the image field.
	 */
	colorField.View = field.View.extend({
		/**
		 * Renders the color picker/field.
		 */
		render: function() {
			var that = this,
				$input, args, color;

			color = this.model.getValue() || '#000000';

			args = {
				defaultColor: this.model.get( 'default_value' ) || '#000000',
				change: function( e, ui ) {
					that.model.setValue( ui.color.toString() );
				}
			}

			$input = $( '<input type="text" />' )
				.val( color )
				.appendTo( this.$el );

			if( $.fn.wpColorPicker ) {
				$input.wpColorPicker( args );
			}
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			this.$el.find( '.wp-color-result' ).focus();
		}
	});

})( jQuery );
