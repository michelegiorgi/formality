(function( $ ){

	var field         = UltimateFields.Field,
		dateTimeField = field.Datetime = {},
		index         = 0;

	/**
	 * Handles the timepicker input of a field.
	 */
	dateTimeField.View = field.Date.View.extend({
		events: {
			'focus input': 'focused',
			'change input': 'changed'
		},

		/**
		 * Renders the date picker/field.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'date' ),
				args = this.getPickerArgs(),
				$input;

			this.$el.html( tmpl({
				id: 'dt' + ( ++index )
			}));

			$input = this.$el.find( '.uf-datepicker-field' ).val( this.model.getValue() );

			args.showTime   = false;
			args.timeFormat = this.model.get( 'time_format' );
			delete args.autoSize;

			$input.datetimepicker( args );
		}
	});

})( jQuery );