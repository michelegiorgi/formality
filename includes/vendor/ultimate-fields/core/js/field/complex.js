(function( $ ){

	var uf       = window.UltimateFields,
		field    = window.UltimateFields.Field,
		complex  = {};

	// Export the handle
	field.Complex = complex;

	// Add some extra functionality for repeater models
	complex.Model  = field.Model.extend({
		/**
		 * Overwrite the datastore method in order to avoid working
		 * with values before there is a datastore to save them in.
		 */
		setDatastore: function( datastore ) {
			var that = this, model;

			// Do the normal initialization
			field.Model.prototype.setDatastore.call( this, datastore );

			// Create the local datastore
			this.values = new UltimateFields.Datastore( this.getValue() );
			this.values.parent = this.datastore;
			this.values.on( 'change', function() {
				that.setValue( that.values.toJSON() );
			});

			// Generate the model
			if( this.get( 'group' ) ) {
				model = new UltimateFields.Container.Group.Model( this.get( 'group' ) );
				model.setDatastore( this.values );

				// Save a handle
				this.set( 'groupModel', model );
			} else {
				this.set( 'groupModel', false );
			}
		},

		/**
		 * Validates the value of the field.
		 */
		validate: function() {
			var errors, message;

			// Check for a model
			if( ! this.get( 'groupModel' ) ) {
				return;
			}

			if( ! ( errors = this.get( 'groupModel' ).validate() ) ) {
				return;
			}

			// Do normal validation
			var message = UltimateFields.L10N.localize( 'repeater-incorrect-value' ).replace( '%s', this.get( 'label' ) );
			this.set( 'invalid', message );
			return message;
		},

		/**
		 * Returns an SEO-analyzable value of the field.
		 */
		getSEOValue: function() {
			var values = [],
				group = this.get( 'groupModel' );

			if( ! group ) {
				return '';
			}

			group.get( 'fields' ).each(function( field ) {
				var value = field.getSEOValue();

				if( value ) {
					values.push( value );
				}
			});

			return values.join( ' ' );
		}
	});

	// Define the view for the complex
	complex.View = field.View.extend({
		initialize: function() {
			var that = this;

			// Do the standard initialization
			field.View.prototype.initialize.apply( this, arguments );

			if( ! this.model.get( 'groupModel' ) ) {
				return;
			}

			// Listen for replacements
			this.model.datastore.on( 'value-replaced', function( name ) {
				if( name != that.model.get( 'name' ) ) {
					return;
				}

				that.model.get( 'groupModel' ).datastore.set( that.model.getValue() );
				that.render();
				$( window ).trigger( 'resize' );
			})
		},

		render: function() {
			var that = this, model, datastore, view;

			// Cleanup first
			this.$el.empty();

			// Create a group model
			model = this.model.get( 'groupModel' );

			if( ! model ) {
				var $p = $( '<p />' );
				$p.addClass( 'uf-complex-group-missing' );
				$p.text( UltimateFields.L10N.localize( 'complex-no-group' ) );
				this.$el.append( $p );
				return;
			}

			// Create a view for the group and get the fields
			view = new UltimateFields.Container.Group.ComplexView({
				model: model
			});

			view.$el.appendTo( this.$el );
			view.render();

			// Allow external influence over the group
			UltimateFields.applyFilters( 'complex_group_created', {
				complexView: this,
				model:       model,
				view:        view,
				datastore:   model.datastore
			});
		}
	});

})( jQuery );
