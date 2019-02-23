(function( $ ){

	var uf               = window.UltimateFields,
		field            = uf.Field,
		selectField      = field.Select,
		multiselectField = field.Multiselect = {};

	multiselectField.lastListName = 0;

	multiselectField.Model = selectField.Model.extend({
		/**
		 * Overwrite the datastore method in order to allow setting a
		 * default value as soon as there is a datastore to set it to.
		 */
		setDatastore: function( datastore ) {
			var that = this, set = false;

			// Skip the select initialization
			field.Model.prototype.setDatastore.call( this, datastore );
		}
	});

	multiselectField.View = selectField.View.extend({
		events: {
			'change select':               'selectChanged',
			'change input[type=checkbox]': 'checkboxChanged'
		},

		/**
		 * Renders the input of the field.
		 */
		render: function() {
			this.$el.empty();

			if( _.isEmpty( this.model.getOptions() ) ) {
				this.$el.text( UltimateFields.L10N.localize( 'select-no-options' ) );
			} else {
				if( 'checkbox' == this.model.get( 'input_type' ) ) {
					this.renderCheckboxes();
				} else {
					this.renderSelect();
				}
			}

		},

		/**
		 * Renders a multiselect as the input of the field.
		 */
		renderSelect: function() {
			var that    = this,
				current = this.model.getValue() || [],
				$input;

			// Create a basic select and add options to it
			$input = $( '<select multiple="multiple"></select>' );

			_.each( this.model.getOptions(), function( option, key ) {
				var $option = $( '<option value="' + key + '">' + option + '</option>' );

				if( ( 'indexOf' in current ) && -1 != current.indexOf( key ) ) {
					$option.prop( 'selected', 'selected' );
				}

				$input.append( $option );
			});

			// Append the element to the dom
			this.$el.append( $input );

			// Add select2
			$input.select2({
				width: '100%'
			});
		},

		/**
		 * Handles changes of the select field.
		 */
		selectChanged: function( e ) {
			this.model.setValue( this.$el.find( 'select' ).val() );
		},

		/**
		 * Renders radio buttons as the input of the field.
		 */
		renderCheckboxes: function() {
			var that    = this,
				$list   = $( '<ul />' ),
				current = this.model.getValue() || [],
				name;

			// Generate a name for the inputs
			name = 'uf-checkboxes-' + ( selectField.lastListName++ );

			// Add options
			_.each( this.model.getOptions(), function( option, value ) {
				var $label, $input;

				$input = $( '<input type="checkbox" />' ).attr({
					value: value,
					name:  name
				});
				if( -1 != current.indexOf( value ) ) $input.prop( 'checked', true );

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
		checkboxChanged: function( e ) {
			var value = [];

			this.$el.find( 'input' ).each(function() {
				if( this.checked ) {
					value.push( this.value );
				}
			});

			this.model.setValue( value );
		},

		/**
		 * Focuses the input within the field.
		 */
		focus: function() {
			if( 'checkbox' == this.model.get( 'input_type' ) ) {
				this.$el.find( 'input:eq(0)' ).focus();
			} else {
				this.$el.find( 'select' ).focus();
			}
		}
	});

})( jQuery );
