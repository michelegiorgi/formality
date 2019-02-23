(function( $ ){

	var uf          = window.UltimateFields,
		field       = uf.Field,
		selectField = field.Image_Select = {};

	selectField.lastListName = 0;

	selectField.Model = field.Model.extend({
		/**
		 * Overwrite the datastore method in order to allow setting a
		 * default value as soon as there is a datastore to set it to.
		 */
		setDatastore: function( datastore ) {
			var that = this, set = false;

			// Do the normal initialization
			field.Model.prototype.setDatastore.call( this, datastore );

			// Locate the first option and use it if any
			if( ! this.getValue() ) {
				_.each( this.get( 'options' ), function( option, key ) {
					if( ! set ) {
						that.setValue( key );
						set = true;
					}
				});
			}
		}
	});

	selectField.View = field.View.extend({
		events: {
			'change input': 'changed'
		},

		/**
		 * Renders the input of the field.
		 */
		render: function() {
			var that    = this,
				current = this.model.getValue(),
				tmpl    = UltimateFields.template( 'image-select' ), args, current;

			args =  {
				options: this.model.get( 'options' ),
				inputId: 'image-select-' + ( selectField.lastListName++ )
			};

			this.$el
				.addClass( 'uf-image-select' )
				.html( tmpl( args ) );

			// Activate the right element
			current = this.model.getValue();
			this.$el.find( 'input' ).each(function() {
				if( this.value == current ) {
					$( this ).prop( 'checked', 'checked' ).closest( 'label' ).addClass( 'uf-selected' );
				}
			});
		},

		/**
		 * Whenever a radio gets changed, save its value.
		 */
		changed: function( e ) {
			if( e.target.checked ) {
				this.model.setValue( e.target.value );

				$( e.target )
					.closest( 'label' )
					.addClass( 'uf-selected' )
					.siblings()
					.removeClass( 'uf-selected' );
			}
		}
	});

})( jQuery );
