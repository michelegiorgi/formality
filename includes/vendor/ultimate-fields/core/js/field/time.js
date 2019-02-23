(function( $ ){

	var field     = UltimateFields.Field,
		timeField = field.Time = {},
		index     = 0;

	/**
	 * Handles the timepicker input of a field.
	 */
	timeField.View = field.Date.View.extend({
		events: {
			'focus input': 'focused',
			'change input': 'changed'
		},

		/**
		 * Renders the date picker/field.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'time' ),
				$input;

			this.$el.html( tmpl({
				id: ++index
			}));

			$input = this.$el.find( '.uf-datepicker-field' ).val( this.model.getValue() );

			$input.timepicker({
				showTime:   false,
				timeFormat: this.model.get( 'format' )
			});
		}
	});

})( jQuery );