(function( $ ){

	var field       = UltimateFields.Field,
		numberField = field.Number = {};

	/**
	 * Basic model for the number field.
	 */
	numberField.Model = field.Model.extend({

	});

	/**
	 * Handles the input of the number field.
	 */
	numberField.View = field.View.extend({
		events: {
			'change input': 'changeValue'
		},

		/**
		 * Renders the input.
		 */
		render: function() {
			var that = this;

			// Create an input and add it to the DOM
			this.$input = $( '<input type="' + ( this.model.get( 'slider_enabled' ) ? 'number' : 'number' ) + '" />' )
				.appendTo( this.$el )
				.val( this.model.getValue() );

			// Add properties when needed.
			if( false !== this.model.get( 'minimum' ) ) {
				this.$input.attr( 'min', this.model.get( 'minimum' ) );
			}

			if( false !== this.model.get( 'maximum' ) )
				this.$input.attr( 'max', this.model.get( 'maximum' ) );

			if( this.model.get( 'slider_enabled' ) ) {
				this.$input.attr( 'step', this.model.get( 'step' ) );

				this.slider();
			} else {
				// Indicate that it's a basic input
				this.$el.addClass( 'uf-basic-input' );
			}
		},

		/**
		 * When the value is changed, it should be sent to the datastore.
		 *
		 * @param <Event> e The event that is being triggered.
		 */
		changeValue: function( e ) {
			this.model.setValue( this.$el.find( 'input' ).val() );
		},

		/**
		 * Creates a jQuery UI slider when needed.
		 */
		slider: function() {
			var that    = this,
				$input  = this.$el.find( 'input' ),
				$slider = $( '<div class="uf-number" />' ),
				value   = this.model.getValue() || this.model.get( 'minimum' );

			// Hide the base input
			$input.addClass( 'uf-number-indicator' );

			// Add the slider and the indicator after the input, then hide it
			this.$el.append( $slider );

			// Create the slider
			$slider.slider({
				min:     this.model.get( 'minimum' ),
				max:     this.model.get( 'maximum' ),
				step:    this.model.get( 'step' ),
				value:   value,
				range:   'min',
				animate: 'fast',
				slide: function( event, ui ) {
					that.model.setValue( ui.value );
					$input.val( ui.value );
				}
			});

			$slider.find( '.ui-slider-handle' ).addClass( 'wp-ui-highlight' );

			$input.change(function(){
				$slider.slider( 'value', $input.val() );
			});
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			this.$el.find( 'input' ).focus();
		}
	});

})( jQuery );
