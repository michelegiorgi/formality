(function( $ ){

	UltimateFields.L10N.init();

	/**
	 * This file handles the shortcode container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		shortcode = container.Shortcode = {};

	shortcode.Model = container.Base.Model.extend({
		defaults: function() {
			return _.extend( {}, container.Base.Model.prototype.defaults, {
				insertable:      true,
				savingAttempted: false
			});
		},

		setDatastore: function( datastore ) {
			container.Base.Model.prototype.setDatastore.call( this, datastore );

			// Handle changes in the datastore as validation for the button
			datastore.on( 'change', _.bind( this.maybeValidate, this ) );
		},

		/**
		 * Attempts a validation, but only performs it when the save button
		 * has already been clicked and validation state is active.
		 */
		maybeValidate: function() {
			if( this.get( 'savingAttempted' ) ) {
				this.validate();
			}
		},

		/**
		 * Validates the fields in the shortcode.
		 */
		validate: function() {
			var errors = [],
				tabs   = this.get( 'tabs' );

			this.get( 'fields' ).each( function( field ) {
				var state;

				// If the fields' tab is invisible, the field is invisible too
				if( field.get( 'tab' ) && ! tabs[ field.get( 'tab' ) ] ) {
					return;
				}

				state = field.validate();

				if( 'undefined' != typeof state ) {
					errors.push( state );
				}
			});

			this.set( 'insertable', errors.length === 0 );
		},

		/**
		 * Generates a shrotcode based on the wp.shortcode class.
		 */
		generateShortcode: function() {
			var that       = this,
				attributes = {},
				content    = {},
				ignored    = [ '__type', '__tab', '__index' ],
				shortcode;

			_.each( this.datastore.toJSON(), function( value, key ) {
				if( -1 != ignored.indexOf( key ) ) {
					return;
				}

				if( 'object' == typeof value ) {
					content[ key ] = value;
				} else {
					if( ( value + '' ).match( /[\n"]/ ) ) {
						content[ key ] = value;
					} else {
						attributes[ key ] = value;
					}
				}
			});

			content = JSON.stringify( content );
			if( '{}' == content ) content = false;

			shortcode = new wp.shortcode({
				tag:     this.get( 'tag' ),
				attrs:   attributes,
				content: content,
				type:    false !== content ? '' : ''
			});

			return shortcode.string();
		}
	});

	/**
	 * This is a simple view that allows the creation of a shortcode.
	 */
	shortcode.CreateView = container.Base.View.extend({
		className: 'uf-shortcode-media-ui uf-shortcode-editor-view',

		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'shortcode-editor' ),
				$fields;

			this.$el.html( tmpl({
				id         : this.model.get( 'id' ),
				description: this.model.get( 'description' )
			}));

			$fields = this.$el.find( '.uf-shortcode-fields' );
			$fields.addClass( 'uf-fields-label-200' );
			$fields.addClass( 'uf-boxed-fields' );

			this.addFields( $fields );

			UltimateFields.ContainerLayout.DOMUpdated( true );
		},

		/**
		 * Recieves a button that is to be controlled by the view/validation state.
		 */
		controlButton: function( button ) {
			var that = this;

			this.model.on( 'change:insertable', function() {
				button.set( 'disabled', ! that.model.get( 'insertable' ) );
			});
		},

		/**
		 * Handles clicks of the "insert to content" button.
		 */
		buttonClicked: function( e ) {
			var that = this;

			// Indicate that saving has been attempted, to allow validation
			this.model.set( 'savingAttempted', true );

			// Try to validate the fields
			this.model.validate();

			// If there is something invalid, don't proceed
			if( ! this.model.get( 'insertable' ) ) {
				return false;
			}

			// Send the shortcode to the editor
			console.log( this.model.generateShortcode() )
			send_to_editor( this.model.generateShortcode() )

			return true;
		}
	});

	/**
	 * Handles the shortcode editor within a popup.
	 */
	shortcode.EditorView = container.Base.View.extend({
		className: 'uf-shortcode-editor-view',

		/**
		 * Handles the initialization of the view and saves extra parameters.
		 */
		initialize: function( args ) {
			// Call the super
			container.Base.View.prototype.initialize.apply( this );

			// Save the updater method and the mceView
			this.updater = args.updater;
			this.mceView = args.mceView;
		},

		/**
		 * Renders the content of the editor.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'shortcode-editor' );

			// Add a basic layout
			this.$el.html( tmpl({
				id:    this.model.get( 'id' ),
				description: this.model.get( 'description' )
			}));

			// Add the fields, without tabs
			this.addFields( this.$el.find( '.uf-shortcode-fields' ), {
				tabs: false
			});

			// Toggle the state of the button based on validation
			this.model.on( 'change:insertable', function() {
				that.saveButton.model.set( 'disabled', ! that.model.get( 'insertable' ) );
			});
		},

		/**
		 * Toggles the state of the save button.
		 */
		toggleSaveButton: function() {
			var insertable = this.model.get( 'insertable' );

			this.saveButton.model.set( 'disabled', ! insertable );
		},

		/**
		 * Returns the buttons for the popup.
		 */
		getButtons: function() {
			var that = this, saveButton;

			this.saveButton = new UltimateFields.Button({
				type:     'primary',
				cssClass: 'uf-button-save-popup',
				text:     'Save ' + this.model.get( 'title' ),
				icon:     'dashicons-category',
				callback: _.bind( this.sendToEditor, this  )
			});

			return [ this.saveButton ];
		},

		/**
		 * Saves the shortcode/sends it to the editor.
		 */
		sendToEditor: function( args ) {
			// Indicate that saving has been attempted, to allow validation
			this.model.set( 'savingAttempted', true );

			// Try to validate the fields
			this.model.validate();

			// If there is something invalid, don't proceed
			if( ! this.model.get( 'insertable' ) ) {
				return false;
			}

			// Send the shortcode to the editor
			this.updater( this.model.generateShortcode() );

			return true;
		},

		/**
		 * Generates the wrapper for tabs.
		 */
		getTabsWrapper: function() {
			var $div = $( '<div class="uf-tab-wrapper" />' );

			if( 'grid' != this.model.get( 'layout' ) ) {
				$div.addClass( 'uf-fields-label-200' );
				$div.addClass( 'uf-tabs-layout-rows' );
			}

			$div.append( this.getTabs() );

			return $div;
		}
	});

	/**
	 * This view will be used to show a preview of the shortcode within the editor.
	 */
	shortcode.PreviewView = container.Base.View.extend({
		/**
		 * Based on the shortcode's template generates the HTML for the preview.
		 */
		getHTML: function() {
			var html = this.model.get( 'template' ),
				tmpl = _.template( html ),
				data;

			this.model.get( 'fields' ).each(function( field ) {
				field.useDefaultValueIfNeeded();
			});

			data = this.model.datastore.toJSON();
			data.__type  = data.__shortcode = this.model.get( 'tag' );
			data.__title = this.model.get( 'title' );

			try {
				return tmpl( data );
			} catch( e ) {
				return e.message;
			}
		}
	});

})( jQuery );
