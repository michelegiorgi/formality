(function( $ ){

	var field     = UltimateFields.Field,
		dateField = field.Date = {},
		index     = 0;

	/**
	 * Handles the datepicker input of a field.
	 */
	dateField.View = field.View.extend({
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
				$input;

			this.$el.html( tmpl({
				id: ++index
			}));

			$input = this.$el.find( '.uf-datepicker-field' ).val( this.model.getValue() );

			$input.datepicker( this.getPickerArgs() );
		},

		/**
		 * Returns the artuments for the picker.
		 */
		getPickerArgs: function() {
			return {
				autoSize:        true,
				showButtonPanel: true,
				showOtherMonths: true,
				changeMonth:     true,
				changeYear:      true,
				dateFormat:      this.model.get( 'format' ),
				currentText:     UltimateFields.L10N.localize( 'datepicker-today' ),
				closeText:       UltimateFields.L10N.localize( 'datepicker-close' )
			};
		},

		/**
		 * When the input is fucused, the datepicker will be already displayed.
		 *
		 * At that point, the datepicker will be already visible and available
		 * for manipulations like classes, etc.
		 */
		focused: function() {
			setTimeout(function(){
				$( '.ui-datepicker-buttonpane button' ).addClass( 'button-secondary' );
				$( '#ui-datepicker-div' ).addClass( 'wp-core-ui' );
			}, 10);
		},

		/**
		 * When the input is changed, it's value will be saved.
		 */
		changed: function() {
			this.model.setValue( this.$el.find( '.uf-datepicker-field' ).val() );
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			this.$el.find( 'input' ).focus();
		}
	});

})( jQuery );
