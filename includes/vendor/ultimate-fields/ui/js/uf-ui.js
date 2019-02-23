(function( $ ){

	var ui = window.UltimateFields.UI = {}, editorField, logicField;

	/**
	 * Handles a level within the context of the editor.
	 */
	ui.ContextLevel = Backbone.Model.extend({
		defaults: {
			name: 'Top-level fields'
		}
	});

	/**
	 * This is a global context controller, which holds the
	 * current context of what's being edited, e.g. top-level
	 * fields, secondary level and so-on.
	 */
	ui.Context = {
		levels: [],

		/**
		 * Adds a new level.
		 */
		addLevel: function( context ) {
			this.levels.push( context );
		},

		/**
		 * Removes the last open level.
		 */
		popLevel: function() {
			if( this.levels.length > 1 ) {
				this.levels.pop();
			}
		},

		/**
		 * Returns all available levels.
		 */
		get: function() {
			return this.levels;
		},

		/**
		 * Returns the current context if any.
		 */
		getCurrent: function() {
			return this.levels.length
				? this.levels[ this.levels.length - 1 ]
				: false;
		}
	}

	// This field will handle sub-fields
	editorField = UltimateFields.Field.Fields = {};

	editorField.Model = UltimateFields.Field.Model.extend({
		setUIContext: function( context ) {
			this.set( 'uiContext', context );
		}
	});

	editorField.View = UltimateFields.Field.View.extend({
		input: function() {
			var that = this,
				tmpl = UltimateFields.template( 'fields-field' ),
				$input;

			$input = $( '<div class="uf-fields-field" />' );
			$input.html( tmpl({

			}));

			this.editor = new ui.Editor({
				el: $input.find( '.uf-fields-editor' ),
				fields: this.model.getValue() || [],
				addButton: $input.find( '.uf-add-field-button' ),
				updated:   function( data ) {
					that.model.setValue( data );
				}
			});

			this.editor.render();

			// Make sure that there is an easily available handle to the fields
			this.model.set( 'fields', that.editor.fields );

			// Listen for changes in the context and set them to the editor
			this.model.on( 'change:uiContext', function() {
				that.editor.setContext( that.model.get( 'uiContext' ) );
			});

			// Generate a global event to let extensions setup the context
			UltimateFields.applyFilters( 'ui.fields_field_rendered', this );

			return $input;
		}
	});

	/**
	 * When a group is created, add it to the context of the fields field within it.
	 */
	UltimateFields.addFilter( 'repeater_group_created', function( group ) {
		var fieldsField, context;

		if( 'group' != group.model.get( 'id' ) )
			return;

		// Find the field
		group.model.get( 'fields' ).each(function( field ) {
			if( field instanceof editorField.Model ) {
				fieldsField = field;
			}
		});

		// Create the context
		context = new ui.ContextLevel({
			label:  group.model.getValue( 'title' ),
			fields: fieldsField.get( 'fields' )
		});

		// Set the initial context
		fieldsField.setUIContext( context );

		// When the title/name of the group changes, use it
		group.datastore.on( 'change:title', function() {
			context.set( 'label', group.datastore.get( 'title' ) )	;
		});
	});

	UltimateFields.addFilter( 'field_editor_rendered', function( args ) {
		args.model.get( 'fields' ).each(function( field ) {
			if( 'complex_fields' != field.get( 'name' ) )
				return;

			var context = new ui.ContextLevel({
				label:  args.model.getValue( 'label' ),
				fields: field.get( 'fields' )
			});

			field.setUIContext( context );
		});
	});

	/**
	 * This field allows the selection of other fields.
	 */
	// $( document ).on( 'uf-extend', function() {
		var selectorField, selectorsField;

		selectorField = UltimateFields.Field.Field_Selector = {
			Model: UltimateFields.Field.Select.Model.extend({
				/**
				 * Listens for changes in the context.
				 */
				initialize: function() {
					var that = this, levels, level, fields;

					// Super
					UltimateFields.Field.Select.Model.prototype.initialize.apply( this );

					// Get the current level's fields
					levels = UltimateFields.UI.Context.get();
					level  = levels[ levels.length - 1 ];
					fields = level.get( 'fields' );

					// Listen for changes
					fields.on( 'add change remove', function() {
						that.trigger( 'options-changed' );
					});
				},

				getOptions: function() {
					var levels, level, types, options = {};

					levels = UltimateFields.UI.Context.get();
					level  = levels[ levels.length - 1 ];

					types = this.get( 'types' );

					level.get( 'fields' ).each(function( field ) {
						if( types.length && -1 == types.indexOf( field.datastore.get( 'type' ) ) ) {
							return;
						}

						options[ field.datastore.get( 'name' ) ] = '%s (%s)'
							.replace( '%s', field.datastore.get( 'label' ) )
							.replace( '%s', field.datastore.get( 'name' ) );
					});

					return options;
				}
			}),

			View: UltimateFields.Field.Select.View.extend()
		};

		selectorsField = UltimateFields.Field.Fields_Selector = {
			Model: UltimateFields.Field.Multiselect.Model.extend({
				initialize: selectorField.Model.prototype.initialize,
				getOptions: selectorField.Model.prototype.getOptions
			}),

			View: UltimateFields.Field.Multiselect.View.extend()
		};

		/**
		 * Extend the post type controller to check for a title.
		 */
		UltimateFields.Container.Post_Type.Controller.prototype.validate = function() {
			var that     = this,
				problems = [];

			if( 0 === $( "#title" ).val().length ) {
				problems.push( UltimateFields.L10N.localize( 'invalid-container-title' ) );
				$( '#title' ).addClass( 'uf-invalid-title' );
			} else {
				$( '#title' ).removeClass( 'uf-invalid-title' );
			}

			_.each( that.containers, function( view ){
				_.each( view.model.validate(), function( problem ) {
					problems.push( problem );
				})
			});

			if( problems.length ) {
				that.showErrorMessage( problems );
				return false;
			}

			return true;
		}
	// });

	/**
	 * Loads the data about a container.
	 *
	 * Used when loading repeater or complex fields with external containers.
	 */
	ui.containerDataCache = {};
	ui.loadContainerData = function( containerIDs, callback ) {
		var queue = [], ready;

		_.each( containerIDs, function( id ) {
			if( id in ui.containerDataCache ) {
				return;
			} else if( $( 'script#uf-ui-container-' + id ).length ) {
				ui.containerDataCache[ id ] = $.parseJSON( $( 'script#uf-ui-container-' + id ).html() );
				return;
			}

			queue.push( id );
		});

		ready = function() {
			var containers = {};

			_.each( containerIDs, function( id ) {
				containers[ id ] = ui.containerDataCache[ id ];
			});

			callback( containers );
		}

		if( ! queue.length ) {
			ready();
			return;
		}

		$.ajax({
			type: 'post',
			url:  window.location.href,
			data: {
				uf_action: 'ui_get_container',
				containers: queue,
				nonce:      UltimateFields.L10N.localize( 'ui-container-nonce' )
			},
			dataType: 'json',
			success: function( json ) {
				if( json.error ) {
					alert( json.error );
				} else {
					_.each( json.containers, function( container ) {
						ui.containerDataCache[ container.id ] = container;
					});

					ready();
				}
			}
		});
	}


})( jQuery );
