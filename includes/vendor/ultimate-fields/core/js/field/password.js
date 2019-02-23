(function( $ ){

	var uf        = window.UltimateFields,
		field     = uf.Field,
		passField = field.Password = {};

	/**
	 * A basic model for the field with some default values.
	 */
	passField.Model = field.Model.extend({
		defaults: _.extend({
			placeholder: ''
		}, field.Model.prototype.defaults )
	});

	passField.View = field.View.extend({
		events: {
			'change input': 'change',
			'keyup input': 'keyup'
		},

		/**
		 * Renders the input of the field.
		 */
		render: function() {
			var that = this, $input, prefix, suffix;

			// Indicate that it's a basic input
			this.$el.addClass( 'uf-basic-input' );

			if( prefix = this.model.get( 'prefix' ) ) {
				$( '<span class="uf-field-prefix" />' )
					.text( prefix )
					.appendTo( this.$el );
			}

			$input = $( '<input type="password" />' )
				.appendTo( this.$el )
				.attr( 'placeholder', this.model.get( 'placeholder' ) )
				.val( this.model.getValue() );

			if( suffix = this.model.get( 'suffix' ) ) {
				$( '<span class="uf-field-suffix" />' )
					.text( suffix )
					.appendTo( this.$el );
			}

			// Listen for external changes
			this.model.on( 'external-value', function() {
				$input.val( that.model.getValue() );
			});

			// Assign a manual keyup handler
			this.$el.find( 'input' ).on( 'keyup', _.throttle( _.bind( this.keyUp, this ), 100 ) );
		},

		/**
		 * Saves the value of the field when it gets changed.
		 */
		change: function() {
			var value  = this.model.getValue(),
				$input = this.$el.find( 'input' );

			if( value != $input.val() ) {
				this.model.setValue( $input.val() );
				this.model.trigger( 'text-changed' );
			}
		},

		/**
		 * Immediately saves the value of the field on keyup.
		 */
		keyUp: function() {
			var value  = this.model.getValue(),
				$input = this.$el.find( 'input' );

			if( value != $input.val() ) {
				this.model.setValue( $input.val() );
			}
		}
	});

})( jQuery );
