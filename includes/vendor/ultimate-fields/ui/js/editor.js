(function( $ ){

	var ui    = window.UltimateFields.UI,
		field = ui.Field;

	ui.Editor = Backbone.View.extend({
		/**
		 * Processes existing fields, links buttons and etc.
		 */
		initialize: function( args ) {
			var that = this;

			// Set a blank context by default
			this.context = null;

			// Handle the add button
			if( 'addButton' in args ) {
				$( args.addButton ).click(function( e ) {
					e.preventDefault();

					that.addField();
				});
			}

			// Add existing fields if any
			this.fields = new field.Collection;

			if( 'fields' in args ) _.each( args.fields, function( data ) {
				var m = field.Model.factory( data );
				that.fields.add( m );
			});

			// Handle the updated event
			if( 'updated' in args ) {
				this.fields.on( 'sort destroy add change', function( e ) {
					args.updated( that.export() );
				});
			}

			// Export at least once
			this.export();
		},

		/**
		 * Exports all fields.
		 */
		export: function() {
			var fields = [];

			this.fields.each(function( field ) {
				fields.push( field.datastore.toJSON() );
			});

			return fields;
		},

		/**
		 * Render the initial view.
		 */
		render: function() {
			var that = this;

			// Add existing fields
			if( this.fields.length ) {
				this.fields.each(function( field ) {
					that.addPreview( field );
				});

				this.$el.find( '.uf-fields-loading' ).hide();
			}

			// Listen for field changes
			this.fields.on( 'all', _.bind( this.toggleClasses, this ) );
			this.toggleClasses();

			// Make the fields sortable
			this.sortable();
		},

		/**
		 * Toggles classes based on the current state.
		 */
		toggleClasses: function() {
			if( this.fields.length ) {
				this.$el.find( '.uf-fields-loading' ).hide();
				this.$el.addClass( 'uf-fields-editor-has-fields' );
			} else {
				this.$el.find( '.uf-fields-loading' ).show();
				this.$el.removeClass( 'uf-fields-editor-has-fields' );
			}
		},

		/**
		 * Adds the preview of an individual field.
		 */
		addPreview: function( model, args ) {
			var view;

			args = args || {};

			view = new field.Preview({
				model: model
			});

			if( args.after ) {
				args.after.$el.after( view.$el );
			} else if( args.before ) {
				args.before.$el.before( view.$el );
			} else {
				view.$el.appendTo( this.$el );
			}
			view.$el.data( 'width', model.get( 'field_width') );
			view.render();

			// Listen to clones
			model.on( 'clone-field', _.bind( function() {
				this.cloneField( model, view );
			}, this ) );

			model.on( 'add-before', _.bind( function() {
				this.addBefore( model, view );
			}, this ) );
		},

		/**
		 * Opens the popup for creating a new field.
		 */
		addField: function() {
			var that  = this,
				field;

			field = ui.Field.Model.create({
				context: this.context
			});

			field.on( 'saved', function() {
				that.fields.add( field );
				that.addPreview( field );
				field.off( 'saved' );
			});
		},

		/**
		 * Makes fields sortable.
		 */
		sortable: function() {
			var that = this;

			this.$el.sortable({
				items:  '.uf-field-preview',
				tolerance: 'pointer',

				// Ensures the size of the placeholder is the same as the field
				start: function( e, ui ) {
					ui.placeholder.css({
						width: parseInt( ui.helper.data( 'width' ) ) + '%',
						height: ui.helper.outerHeight()
					});
				},

				// When sorting has ended, save the sort
				stop: function() {
					that.saveSort();
				}
			});
		},

		/**
		 * Saves the sorting of fields based on the DOM order of their views.
		 */
		saveSort: function() {
			this.fields.each(function( field ) {
				// Let the field know to save its sort
				field.trigger( 'uf-save-sort' );
			});

			// Sort the collection
			this.fields.sort();
		},

		/**
		 * Sets the context of the current editor.
		 */
		setContext: function( context ) {
			var that = this;

			this.context = context;

			// Spread through fields
			this.fields.each(function( field ) {
				field.setContext( context );
			});
		},

		/**
		 * Clones a field and opens the editor for it.
		 */
		cloneField: function( originalModel, originalView ) {
			var datastore, model;

			// Clone the datastore for the new model
			datastore = originalModel.datastore.clone();

			// Use a new name for the datastore
			datastore.set( 'name', datastore.get( 'name' ) + '_copy' );

			model = ui.Field.Model.factory( datastore );

			// Setup the new model
			model.set( '_new', true );

			// Open the editor for the field
			model.edit();

			// Listen for a save (the field will only be added then)
			model.on( 'saved', _.bind( function() {
				this.fields.add( model );
				this.addPreview( model, {
					after: originalView
				});
				model.off( 'saved' );

				// Save the sorting of all fields
				this.saveSort();
			}, this ));
		},

		/**
		 * Adds a new field before a specific another field.
		 */
		addBefore: function( originalModel, originalView ) {
			var model = ui.Field.Model.create({
				context: false
			});

			// Listen for a save (the field will only be added then)
			model.on( 'saved', _.bind( function() {
				this.fields.add( model );
				this.addPreview( model, {
					before: originalView
				});
				model.off( 'saved' );

				// Save the sorting of all fields
				this.saveSort();
			}, this ));
		}
	});

	/**
	 * Initialize the primary box.
	 */
	$( document ).on( 'uf-pre-init uf-ui-init-editor', function() {
		$( '.uf-fields-box-wrapper:not(.uf-fields-box-wrapper-initialized)' ).each(function() {
			var $wrapper = $( this ),
				$input   = $wrapper.find( '.uf-group-fields' ),
				fields = $.parseJSON( $input.val() ),
				editor, context;

			editor = new ui.Editor({
				el:        $wrapper.find( '.uf-fields-editor' ),
				addButton: $wrapper.find( '.uf-add-field-button' ),
				fields:    fields,
				updated:   function( data ) {
					context.fields = data;
					$input.val( JSON.stringify( data ) );
				}
			});

			// This is a temporary/fake datastore that allows parent field access
			context = new ui.ContextLevel({
				label: 'Top-level fields',
				fields: editor.fields
			});
			ui.Context.addLevel( context );

			editor.render();

			$( this ).addClass( 'uf-fields-box-wrapper-initialized' );
		});
	});

})( jQuery );
