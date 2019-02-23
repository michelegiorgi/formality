(function( $ ){

	var uf          = window.UltimateFields,
		field       = uf.Field,
		selectField = field.Select = {};

	selectField.lastListName = 0;

	selectField.Model = field.Model.extend({
		/**
		 * Returns the options for the field.
		 */
		getOptions: function() {
			return this.get( 'options' );
		},

		/**
		 * Checks if there is a selected element.
		 */
		checkForValidSelection: function() {
			var that    = this,
				options = this.getOptions(),
				current = this.getValue(),
				set     = false,
				allOptions = [];

			// Get all options
			_.each( options, function( label, option ) {
				if( 'object' == typeof label ) {
					_.each( label, function( subLlabel, key ) {
						allOptions.push( key.toString() );
					});
				} else {
					allOptions.push( option.toString() );
				}
			});

			// Load the first option
			if( -1 == allOptions.indexOf( current.toString() ) ) {
				_.each( allOptions, function( key ) {
					if( set ) {
						return;
					}

					set = true;
					that.setValue( key );
				});
			}
		}
	});

	selectField.View = field.View.extend({
		events: {
			'change select':            'selectChanged',
			'change input[type=radio]': 'radioChanged'
		},

		/**
		 * Listen for option changes.
		 */
		initialize: function() {
			this.model.on( 'options-changed', _.bind( this.render, this ) );
		},

		/**
		 * Renders the input of the field.
		 */
		render: function() {
			this.$el.empty();

			this.model.checkForValidSelection();

			if( _.isEmpty( this.model.getOptions() ) ) {
				this.$el.text( UltimateFields.L10N.localize( 'select-no-options' ) );
			} else {
				if( 'radio' == this.model.get( 'input_type' ) ) {
					this.renderRadios();
				} else {
					this.renderSelect();
				}
			}
		},

		/**
		 * Renders a select as the input of the field.
		 */
		renderSelect: function() {
			var that    = this,
				current = this.model.getValue(),
				$input;

			this.$el.empty();

			// Create a basic select and add options to it
			$input = $( '<select></select>' );

			_.each( that.model.getOptions(), function( label, key ) {
				if( 'object' != typeof label ) {
					// Normal option
					var $option = $( '<option />' )
						.attr( 'value', key )
						.text( label )
						.appendTo( $input );

					if( key == current ) $option.prop( 'selected', 'selected' );
				} else {
					// Groups
					var $group = $( '<optgroup />' )
						.attr( 'label', key )
						.appendTo( $input );

					_.each( label, function( label, key ){
						var $option = $( '<option />' )
							.attr( 'value', key )
							.text( label )
							.appendTo( $group );

						if( key == current ) $option.prop( 'selected', 'selected' );
					});
				}
			});

			// Append the element to the dom
			this.$el.append( $input );

			// Add select2 if needed.
			if( this.model.get( 'use_select2' ) ) {
				$input.select2();
			}
		},

		/**
		 * Handles changes of the select field.
		 */
		selectChanged: function( e ) {
			this.model.setValue( e.target.value );
		},

		/**
		 * Renders radio buttons as the input of the field.
		 */
		renderRadios: function() {
			var that    = this,
				$list   = $( '<ul />' ),
				current = this.model.getValue(),
				name;

			// Generate a name for the inputs
			name = 'uf-radio-' + ( selectField.lastListName++ );

			// Add options
			_.each( this.model.getOptions(), function( option, value ) {
				var $label, $input;

				$input = $( '<input type="radio" />' ).attr({
					value: value,
					name:  name
				});
				if( value == current ) $input.prop( 'checked', true );

				$label = $( '<label />' ).html( option ).prepend( $input );

				$list.append( $( '<li />' ).append( $label ) );
			});

			// Use the right layout
			$list
				.addClass( 'uf-radio' )
				.addClass( 'uf-radio-orientation-' + this.model.get( 'orientation' ) );

			// Add the list to the dom
			this.$el.append( $list );
		},

		/**
		 * Whenever a radio gets changed, save its value.
		 */
		radioChanged: function( e ) {
			if( e.target.checked ) {
				this.model.setValue( e.target.value );
			}
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			if( 'radio' == this.model.get( 'input_type' ) ) {
				this.$el.find( 'input:eq(0)' ).focus();
			} else {
				this.$el.find( 'select' ).focus();
			}
		}
	});

})( jQuery );
