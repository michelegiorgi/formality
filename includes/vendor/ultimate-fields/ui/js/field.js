(function( $ ){

	var ui        = window.UltimateFields.UI,
		field     = ui.Field = {},
		cache     = {},
		lastIndex = 0;

	/**
	 * Extends the default datastore to add some defaults.
	 */
	field.Datastore = UltimateFields.Datastore.extend({
		defaults: {
			type: 'Text'
		}
	});

	/**
	 * This is the model for an UI field.
	 *
	 * Although a field is being wrapped, this is a container.
	 */
	field.Model = UltimateFields.Container.Base.Model.extend({
		defaults: function() {
			return _.extend( {}, UltimateFields.Container.Base.Model.prototype.defaults, {
				_new: true
			});
		},

		/**
		 * Initializes the container model and adds additional listeners.
		 */
		initialize: function( args ) {
			var that = this, nameField;

			args = args || {};

			this.set({
				tabs:      {},
				rawFields: args.fields || this.get( 'fields' ) || [],
				fields:    new UltimateFields.Field.Collection()
			});
		},

		/**
		 * Whenever needed (mainly when opening the popup), this will generate the
		 * internal container fields for the field that is being edited.
		 */
		generateInternalFields: function() {
			var that = this;

			// Prevent the fields from being created multiple times
			if( this.fieldsGenerated )
				return;
			this.fieldsGenerated = true;

			// Create a collection witht he fields first
			this.set( 'fields', new UltimateFields.Field.Collection( this.get( 'rawFields') ) );

			this.get( 'fields' ).each( function( field ) {
				field.set( 'description_position', that.get( 'description_position' ) );
			});

			// Hook into the validation of the name field to prevent duplicates
			nameField = this.get( 'fields' ).findWhere({ name: 'name' });
			this.addNameValidation( nameField );

			// Add helpers for each field type
			_.each( this.get( 'fields' ).findWhere({ name: 'type' }).getOptions(), function( types ) {
				_.each( types, function( label, key ) {
					if( ( key in field.Helper ) && 'function' == typeof field.Helper[ key ].bindToEditor ) {
						field.Helper[ key ].bindToEditor( that );
					}
				});
			});
		},

		/**
		 * Adds validation handlers for the name field in order to ensure uniqueness.
		 */
		addNameValidation: function( nameField ) {
			var that = this;

			nameField.set( 'default_message', nameField.get( 'validation_message' ) );

			nameField.validateValue = function( value ) {
				var used = [];

				if( 'get' in ui.Context.getCurrent().get( 'fields' ) ) {
					ui.Context.getCurrent().get( 'fields' ).each(function( field ) {
						if( field === that )
							return;

						used.push( field.getValue( 'name' ) );
					});
				}

				if( -1 != used.indexOf( value ) ) {
					this.set( 'validation_message', 'The ID is already in use!' );
					return false;
				} else {
					this.set( 'validation_message', this.get( 'default_message' ) );
					return UltimateFields.Field.Model.prototype.validateValue.call( this, value );
				}
			}
		},

		/**
		 * Generates a model for the preview.
		 */
		generateFieldModel: function() {
			var model, type = this.datastore.get( 'type' ), default_key, helper, datastore, helperArgs;

			if( ( type in UltimateFields.Field ) && ( 'Model' in UltimateFields.Field[ type ] ) ) {
				model = new UltimateFields.Field[ type ].Model();
			} else {
				model = new UltimateFields.Field.Model();
			}

			model.set({
				label:           this.datastore.get( 'label' ),
				name:            this.datastore.get( 'name' ),
				type:            this.datastore.get( 'type' ),
				field_width:     this.datastore.get( 'field_width' ),
				description:     this.datastore.get( 'description' ),
				hide_label:      this.datastore.get( 'hide_label' ),
				wrapper_id:      this.datastore.get( 'wrapper_id' ),
				wrapper_style:   this.datastore.get( 'wrapper_style' ),
				css_class:       this.datastore.get( 'wrapper_css_class' )
			});

			default_key = 'default_value_' + type.toLowerCase();
			if( this.datastore.get( default_key ) ) {
				model.set( 'default_value', this.datastore.get( default_key ) );
			}

			// Add required
			if( this.datastore.get( 'required' ) ) {
				model.set( 'required', true );
			}

			helperArgs = {
				model:     model,
				data:      this.datastore,
				datastore: new UltimateFields.Datastore()
			};

			helper = this.getHelper();
			helper.setupPreview( helperArgs );
			model.helper = helper;

			// Allow external modifications to be done
			$( document ).trigger( 'uf-parse-' + this.datastore.get( 'type' ) + '-settings', helperArgs );

			// Create a dummy datastore
			if( helperArgs.datastore ) {
				model.setDatastore( helperArgs.datastore );
			}

			return model;
		},

		/**
		 * When setting a datastore up, make sure to indicate a change.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			UltimateFields.Container.Base.Model.prototype.setDatastore.call( this, datastore );

			datastore.on( 'change', function() {
				that.trigger( 'change' );
			});

			datastore.set( 'field_id', this.get( 'id' ) );
		},

		/**
		 * Backs up the state of the field before editing.
		 */
		backupState: function() {
			var temp = this.datastore.clone();
			temp.parent = this.datastore.parent;
			this.realDatastore = this.datastore;
			this.setDatastore( temp );
		},

		/**
		 * Saves the state of the field after editing.
		 */
		saveState: function() {
			var newData = this.datastore.toJSON();

			if( '__tab' in newData )
				delete newData.__tab;

			this.realDatastore.set( newData );
			delete this.datastore;
			this.setDatastore( this.realDatastore );
			this.trigger( 'stateSaved' );
		},

		/**
		 * Restores the state of the field after unsuccessfull edit.
		 */
		restoreState: function() {
			this.setDatastore( this.realDatastore );
		},

		/**
		 * Opens a new popup that edits the model.
		 */
		edit: function() {
			var that = this, view, creating, context, buttons = [];

			// Make sure that all internal fields are available
			this.generateInternalFields();

			// Before opening the view, push the context
			if( this.get( 'context' ) ) {
				UltimateFields.UI.Context.addLevel( this.get( 'context' ) );
			}

			context = UltimateFields.UI.Context.get();
			context[ context.length - 1 ].set( 'field', this );

			// Restore the state of the field
			this.backupState();

			view = new ui.Field.EditView({
				model: this
			});

			creating = this.get( '_new' );

			if( creating ) {
				buttons.push({
					type: 'primary',
					icon: 'dashicons dashicons-plus',
					text: UltimateFields.L10N.localize( 'add-field' ),
					callback: function( overlay ) {
						return that.validateOverlay( overlay );
					}
				});

				buttons.push({
					icon:     'dashicons dashicons-no',
					text:     UltimateFields.L10N.localize( 'cancel' ),
					callback: function( overlay ) {
						// Remove the current field from the context
						ui.Context.popLevel();
						that.restoreState();

						return true;
					}
				});
			} else {
				buttons.push({
					type: 'primary',
					icon: 'dashicons dashicons-category',
					text: UltimateFields.L10N.localize( 'field-save' ),
					callback: function( overlay ) {
						return that.validateOverlay( overlay );
					}
				});

				buttons.push({
					icon: 'dashicons dashicons-no',
					text: UltimateFields.L10N.localize( 'delete-field' ),
					callback: function() {
						if( ! confirm( UltimateFields.L10N.localize( 'confirm-field-deletion' ) ) )
							return;

						that.destroy();

						// Remove the current field from the context
						ui.Context.popLevel();

						return true;
					}
				});
			}

			UltimateFields.Overlay.show({
				title: UltimateFields.L10N.localize( creating ? 'new-field' : 'edit-field' ),
				icon: creating ? 'dashicons dashicons-plus' : 'dashicons dashicons-edit',
				buttons: buttons,
				view: view
			});
		},

		/**
		 * Validates data for the overlay.
		 */
		validateOverlay: function( overlay ) {
			var that = this, errors;

			// Perform the real validation
			errors = this.validate();

			// If there are errors, show a message in the overlay
			if( ! errors || 0 === errors.length ) {
				// Mark the model as not new
				that.set( '_new', false );

				// Indicate that the model has been saved
				that.trigger( 'saved' );

				// Pop the context level
				ui.Context.popLevel();

				// Restore the database
				that.saveState();

				return true;
			} else {
				var $body = $( '<div />' ), $ul = $( '<ul />' );

				_.each( errors, function( error ) {
					$( '<li />' )
						.appendTo( $ul )
						.html( error );
				});

				$ul.appendTo( $body );

				$( '<p />' )
					.text( UltimateFields.L10N.localize( 'error-corrections' ) )
					.appendTo( $body );

				// Show a message in the overlay
				overlay.alert({
					title: UltimateFields.L10N.localize( 'container-issues-title' ),
					body:  $body.children()
				});

				return false;
			}
		},

		/**
		 * Sets a context for the field.
		 */
		setContext: function( context ) {
			this.set( 'context', context );
		},

		/**
		 * Generates a helper that will generate previews, prepare fields and etc.
		 */
		getHelper: function() {
			var that = this,
				helper, type, args;

			type = this.getValue( 'type' );
			args = {
				model: this
			};

			if( type in field.Helper ) {
				return new field.Helper[ type ]( args );
			} else {
				return new field.Helper( args );
			}
		}
	}, {
		/**
		 * Retrieves the settings for a fields' container.
		 */
		_createModel: function() {
			var model, settings;

			// Fetch the needed settings
			if( ! ( 'fieldSettings' in cache ) ) {
				var json = $( '.uf-field-settings' ).html();
				cache.fieldSettings = $.parseJSON( json );
			}

			settings = _.clone( cache.fieldSettings );

			// Create the model
			model = new field.Model( settings );

			// Set a random/consecutive ID
			model.set( 'id', lastIndex++ );

			return model;
		},

		/**
		 * Handles an existing field.
		 */
		factory: function( data ) {
			var model = this._createModel();

			// Indicate that this is an existing field
			model.set( '_new', false );

			// Add a new datastore
			if( data instanceof UltimateFields.Datastore ) {
				model.setDatastore( data );
			} else {
				model.setDatastore( new field.Datastore( data || {} ) );
			}

			return model;
		},

		/**
		 * Creates the model for a field, either existing or new.
		 */
		create: function( options ) {
			var model = this._createModel();

			// Create an empty datastore
			model.setDatastore( new field.Datastore() );
			if( options ) {
				model.set( 'context', options.context );
			}

			model.edit();

			return model;
		}
	});

	/**
	 * Handles a collection of fields.
	 */
	field.Collection = Backbone.Collection.extend({
		model: field.Model,

		comparator: function( datastore ) {
			return datastore.get( '__index' );
		}
	});

	/**
	 * This is the editor, popup view for the field.
	 */
	field.EditView = UltimateFields.Container.Base.View.extend({
		tagName:   'form',
		className: 'uf-fields uf-boxed-fields uf-fields-label-250 uf-editor-fields',

		/**
		 * Add form attributes when initializing.
		 */
		initialize: function() {
			UltimateFields.Container.Base.View.prototype.initialize.apply( this, arguments );

			this.$el.attr({
				action: window.location.href,
				method: 'post'
			});
		},

		/**
		 * Renders the fields for the editor.
		 */
		render: function() {
			var that = this, nameField, labelField;

			this.model.datastore.set( 'field_id', this.model.get( 'id' ) );

			this.addFields( this.$el, {
				tabs: false
			});

			// Listen to changes in the label and apply them to the name when possible
			labelField = this.model.get( 'fields' ).findWhere({ name: 'label' });
			nameField = this.model.get( 'fields' ).findWhere({ name: 'name' });

			labelField.on( 'text-changed', function() {
				var newName = labelField.getValue();

				if( nameField.getValue() )
					return;

				newName = newName
					.trim()
					.toLowerCase()
					.replace( /[\s\-]/g, '_' );

				nameField.setValue( newName );
				nameField.trigger( 'external-value' );
			});

			// Setup the conditional logic
			this.model.get( 'fields' ).findWhere({ name: 'conditional_logic'}).set({
				field_id: this.model.get( 'id' )
			});

			// Focus the first field
			this.$el.find( '.uf-field:eq(0) input' ).focus();

			// Allow the context to be modified
			UltimateFields.applyFilters( 'field_editor_rendered', {
				view:  this,
				model: this.model
			});

			// Add a hidden button to the form to allow submitting with the enter key.
			this.$el.append(
				$( '<button type="submit" />' )
					.addClass( 'uf-hidden-submit' )
					.attr( 'tabindex', -1 )
			);
		},

		/**
		 * Removes the view.
		 */
		remove: function() {
			// Remove the views' context.
			ui.Context.popLevel();

			this.$el.remove();
		},

		/**
		 * Generates the wrapper for tabs.
		 */
		getTabsWrapper: function() {
			var $div = $( '<div class="uf-tab-wrapper uf-fields-label-250" />' );

			$div.append( this.getTabs() );
			$div.addClass( 'uf-tabs-layout-rows' );

			// Save internally
			this.$popupTabs = $div;

			return $div;
		},

		/**
		 * Attaches generic actions (like submission) to an overlay.
		 */
		attachToOverlay: function( overlay ) {
			var that = this;

			this.$el.on( 'submit', function( e ) {
				e.preventDefault();

				if( that.model.validateOverlay( overlay ) ) {
					overlay.removeScreen();
				}
			});
		}
	});

	/**
	 * This view will show a preview of the field.
	 */
	field.Preview = Backbone.View.extend({
		className: 'uf-field-preview',

		events: {
			'click': 'handleClick'
		},

		initialize: function( args ) {
			var that = this;

			this.$el.addClass( 'uf-field-preview-' + this.model.datastore.get( 'type' ).toLowerCase() );

			this.model.on( 'stateSaved', function() {
				that.renderFieldWhenReady();
			});

			this.model.on( 'destroy', function() {
				that.remove();
			});

			this.model.on( 'uf-save-sort', function() {
				that.model.set( '__index', that.$el.index(), {
					silent: true
				});
			});

			// Add the attributes to the helper
			_.each( this.model.datastore.get( 'html_attributes' ) || {}, function( value, key ){
				if( 'class' == key ) {
					that.$el.addClass( value );
				} else {
					that.$el.attr( key, value );
				}
			});
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'ui-field-preview' );

			// Add basics
			this.$el.html( tmpl( this.model.toJSON() ) );

			// Finally, render the field
			this.renderFieldWhenReady();
		},

		/**
		 * Renders the field when it's ready.
		 *
		 * If the field helper indicates that some data must be loaded through AJAX,
		 * the preview will simply display a spinner until the helper indicates full steup.
		 */
		renderFieldWhenReady: function() {
			var helper = this.model.getHelper();

			// Retrieve the model for the field
			this.fieldModel = this.model.generateFieldModel();

			if( ! this.fieldModel.helper.shouldWait( _.bind( this.renderField, this ) ) ) {
				this.renderField();
			}
		},

		/**
		 * Renders the preview of the field whenever the model is fully prepared.
		 */
		renderField: function() {
			var that = this, model, view;

			model = this.fieldModel;

			// Get the width of the model and use it
			this.$el.css({ width: model.get( 'field_width' ) + '%' });
			this.$el.data( 'width', model.get( 'field_width' ) );
			model.set( 'field_width', 100 );

			// Create a normal grid wrap, 100% width
			view = new UltimateFields.Field.GridWrap({
				model: model
			});

			this.$el.find( '.uf-field' ).remove();
			view.$el.appendTo( this.$el );
			view.render();

			if( 'function' == typeof model.helper.afterRender ) {
				model.helper.afterRender( view );
			}
		},

		/**
		 * Opens an editor for the field.
		 */
		handleClick: function( e ) {
			e.preventDefault();

			switch( $( e.target ).closest( 'a' ).data( 'action' ) ) {
				case 'clone':
					this.model.trigger( 'clone-field' );
					break;
				case 'add-before':
					this.model.trigger( 'add-before' );
					break;
				case 'get-id':
					prompt( UltimateFields.L10N.localize( 'field-id' ) + ':', this.model.datastore.get( 'name' ) );
					break;
				case 'delete':
					this.model.destroy();
					break;

				case 'edit':
				default:
					this.edit();
			}
		},

		/**
		 * Opens the editor for the view.
		 */
		edit: function() {
			this.model.edit();
		}
	});

	/**
	 * This is a helper that will be generated when a field
	 * in the interface needs a preview, conditional logic options
	 * and etc.
	 *
	 * Field models don't really have types, as the type is just a value
	 * within the model's data and the model cannot be overwritten when it changes.
	 */
	field.Helper = Backbone.View.extend({
		/**
		 * Sets the model for a preview up.
		 */
		/* abstract */ setupPreview: function( args ) {
			// Use properties from the args.data model to setup args.model.
		},

		/**
		 * If some AJAX is needed to display the preview, this will start it
		 * and return true, telling the preview to wait.
		 */
		shouldWait: function( callback ) {
			return false;
		},

		/**
		 * Returns basic comparator options.
		 */
		getComparators: function() {
			var that = this;

			return [
				{
					compare: 'NOT_NULL',
					label:   'equals true',
					operand: false
				},
				{
					compare: 'NULL',
					label:   'equals false',
					operand: false
				},
				{
					compare: '=',
					label:   'is equal to',
					operand: true
				},
				{
					compare: '!=',
					label:   'is not equal to',
					operand: true
				}
			];
		},

		/**
		 * Generates a view that will be used for the operand.
		 *
		 * This function should return a normal UltimateFields.Field.View, which
		 * already has a "proper" model associated with it.
		 */
		operand: function( currentValue ) {
			var that = this, model, datastore, view;

			// Create a blank datastore
			datastore = new UltimateFields.Datastore({
				value: currentValue
			});

			// Create a model for the field
			model = UltimateFields.Field.Collection.prototype.model({
				type:  'Text',
				name:  'value',
				label: ''
			});

			model.datastore = datastore;

			// Create the view
			view = new UltimateFields.Field.Text.View({
				model: model
			});

			return view;
		}
	});

})( jQuery );
