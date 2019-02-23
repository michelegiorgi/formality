(function( $, api ){

	var container    = UltimateFields.Container.Base,
		customizer   = UltimateFields.Container.Customizer = {},
		controlClass = 'uf_customizer_control';

	// Cache initialized containers
	customizer.initializedContainers = {};

	/**
	 * Extends customizer controls in order to be able to render the container properly.
	 */
	wp.customize.controlConstructor[ controlClass ] = wp.customize.Control.extend({
		/**
		 * When the control is ready, render the container inside.
		 */
		ready: function() {
			var control = this, json, container;

			json      = this.container.find( 'script[type="text/json"]' ).html();
			container = JSON.parse( json );

			// Generate a model for the container
			this.datastore = new UltimateFields.Datastore( container.data );
			this.model = new UltimateFields.Container.Customizer.Model( container.settings );
			this.model.setDatastore( this.datastore );

			// Create settings
			if( container.item ) {
				this.model.setObject( container.item );
			}

			// Render the view of the container
			this.view = view = new UltimateFields.Container.Customizer.View({
				el:    this.container.find( '.uf-customizer-container' ),
				model: this.model
			});

			this.view.render();

			// Save a handle
			customizer.initializedContainers[ this.model.get( 'id' ) ] = this.model;
		}
	});

	/**
	 * The model of the customizer handles the communication between the preview and the previewer.
	 */
	customizer.Model = container.Model.extend({
		/**
		 * When the datastore of the model is set, listen for changes and send them to the API.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			// Super
			container.Model.prototype.setDatastore.call( this, datastore );

			// Listen for changes
			datastore.on( 'change', function( e ) {
				if( customizer.changingPage ) {
					return;
				}

				that.datastoreChanged( e );
			});

			// Save a handle to the container
			customizer.initializedContainers[ this.get( 'id' ) ] = this;
		},

		/**
		 * Handles changes in the datastore.
		 */
		datastoreChanged: function( e ) {
			var that = this;

			if( that.isValid() ) {
				// Send each changed value to the customizer
				_.each( e.changed, function( value, key ) {
					that.send( key, value );
				});
			} else {
				// If the model is not valid, don't even send the data to the customizer
				_.each( e.changed, function( value, key ) {
					var field = that.get('fields').findWhere({ name: key });

					if( field ) {
						field.validate();
					}
				});

				return false;
			}
		},

		/**
		 * Performs a blind validation, in order not to send invalid values to the back-end.
		 */
		isValid: function() {
			var errors = [],
				tabs   = this.get( 'tabs' );

			this.get( 'fields' ).each( function( field ) {
				var state;

				// If the fields' tab is invisible, the field is invisible too
				if( field.get( 'tab' ) && ! tabs[ field.get( 'tab' ) ] ) {
					return;
				}

				// Silently get the validation state
				state = field.validate( true );

				// If there are errors save them
				if( 'undefined' != typeof state ) {
					errors.push( state );
				}
			});

			// Return the errors
			return 0 == errors.length;
		},

		/**
		 * Returns the setting name for a field based on the curent object.
		 */
		getFieldSettingName: function( field ) {
			if( ! field ) {
				return false;
			}

			var settingName  = field.get( 'name' ), setting;

			if( 'options' != this.get( 'object' ) ) {
				settingName += '[' + this.get( 'object' ) + ']';
			}

			return settingName;
		},

		/**
		 * Registers a setting for an individual field.
		 */
		registerFieldSetting: function( field ) {
			var settingName = this.getFieldSettingName( field );

			// Make sure the setting is not registered yet
			if( settingName in customizer.Model.createdSettings ) {
				return; // Setting already exists
			}

			setting = new api.Setting( settingName, field.getValue(), {
				previewer: api.previewer,
				transport: this.getFieldTransport( field ),
				_dirty:    true
			});

			api.add( setting.id, setting );

			// Cache the setting
			customizer.Model.createdSettings[ settingName ] = setting;
		},

		/**
		 * Returns the transport for a setting.
		 */
		getFieldTransport: function( field ) {
			return -1 == this.get( 'dynamic_fields' ).indexOf( field.get( 'name' ) )
				? 'refresh'
				: 'postMessage';
		},

		/**
		 * Checks if settings exist for fields within the container, associated with an object.
		 */
		setObject: function( object ){
			var that = this;

			this.set( 'object', object );

			// Create custom settings for each field
			this.get( 'fields' ).each( function( field ) {
				that.registerFieldSetting( field, object );
			});
		},

		/**
		 * Allows the values of the container to be rewritten when the displayed object changes.
		 */
		importDataForObject: function( object, data ) {
			var that = this;

			this.set( 'object', object );

			this.setObject( object );

			// Save the data as the current datastore
			this.datastore.set( data, {
				silent: true
			});

			// Reset field validation states
			this.get( 'fields' ).each(function( field ) {
				field.set( 'invalid', false );
				field.setDatastore( that.datastore );
			});

			// Let views refresh with the new data
			this.trigger( 'dataImported' );
		},

		/**
		 * Sends a value to the customizer.
		 */
		send: function( field, value ) {
			var key, fieldObject, context;

			// Locate the field and get additional data
			fieldObject = this.get( 'fields' ).findWhere({ name: field });

			if( ! fieldObject ) {
				return;
			}

			key         = this.getFieldSettingName( fieldObject );
			context     = fieldObject.getCustomizerContext();

			wp.customize( key, _.bind( function( controlValue ) {
	            controlValue.set( value );

				api.previewer.send( 'uf.context', {
					setting: key,
					value:   value,
					context: context
				});
	        }, this ) );
		}
	}, {
		createdSettings: {}
	});

	customizer.View = container.View.extend({
		/**
		 * Initializes the view in the customizer.
		 */
		initialize: function() {
			var that = this;

			container.View.prototype.initialize.apply( this, arguments );

			// Re-renders the container when the model got its data re-imported
			this.model.on( 'dataImported', _.bind( this.render, this ) );

			// Listen for changes in the field
			this.model.get( 'fields' ).on( 'change:invalid', function() {
				if( that.model.isValid() ) {
					that.hideValidationMessage();
				} else {
					that.showValidationMessage();
				}
			});
		},

		/**
		 * Renders the view.
		 */
		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'customizer' );

			this.$el.html( tmpl( this.model.toJSON() ) );

			this.addFields( null, {
				wrap: UltimateFields.Field.GridWrap
			});

			this.$fields.addClass( 'uf-customizer-fields' );
		},

		/**
		 * Shows a message regarding validation to the user.
		 */
		showValidationMessage: function() {
			this.$el.find( '.uf-customizer-validation-message' ).show();
		},

		/**
		 * Hides the validation message.
		 */
		hideValidationMessage: function() {
			this.$el.find( '.uf-customizer-validation-message' ).hide();
		},

		/**
		 * Indicates whether the container supports inline tabs.
		 */
		allowsInlineTabs() {
			return false;
		}
	});

	/**
	 * This static method will handle newly loaded values from pages.
	 *
	 * This is needed because the customizer wirks primarily with options (or theme mods) and
	 * there doesn't seem to be a proper, universal and clean way of sending data back to the
	 * customizer whenever the displayed page changes.
	 */
	customizer.loadValuesForScreen = function( json ) {
		var data = $.parseJSON( json );

		_.each( data.values, function( values, containerId ){
			var container = customizer.initializedContainers[ containerId ];

			if( data.object == container.get( 'object' ) )
				return; // Values must be displayed already

			customizer.changingPage = true;
			container.importDataForObject( data.object, values );
			customizer.changingPage = false;
		});
	}
})( jQuery, wp.customize );
