(function( $ ){

	var UltimateFields = window.UltimateFields,
		shortcode      = UltimateFields.shortcode = {};

	/**
	 * If 'wp' is not defined or there is no media popup, then there
	 * is no editor and the functionality of this file is not only useless,
	 * but might actually break the page, therefore we're bailing out.
	 */
	 if( ( 'undefined' == typeof wp ) || ! ( 'media' in wp ) ) {
 		return;
 	}

	/**
	 * Contains the static shortcode functionality.
	 */
	_.extend( shortcode, {
		// Save the original frame for posts & pages
		originalFrame: wp.media.view.MediaFrame.Post,

		// This will cache the data about existing shortcodes
		shortcodes: {},

		// Create a new state for the menu item in the media popup
		State: wp.media.controller.State.extend({}),

		initialize: function() {
			var found = false;

			// Initialize the functionality of each individual shortcode
			$( '.uf-shortcode-container' ).each(function() {
				var container = $.parseJSON( this.innerHTML );

				// Cache and attach
				shortcode.shortcodes[ container.tag ] = container;
				shortcode.attachToTinyMCE( container.tag );

				found = true;
			});

			// If there are shortcodes to work with, replace the media modal frame
			if( found ) {
				wp.media.view.MediaFrame.Post = shortcode.MediaFrame;
			}
		},

		/**
		 * Registers a shortcode with WordPress as a view within the
		 * tinyMCE editor.
		 */
		attachToTinyMCE: function( id ) {
			var container = this.shortcodes[ id ];

			if( ! 'mce' in wp ) {
				return;
			}

			wp.mce.views.register( id, {
				initialize: function() {
					shortcode.initializeExisting( this, container );
				},

				edit: function( text, update ) {
					shortcode.edit( this, container, update );
				}
			});
		},

		/**
		 * Generates an container model for a shortcode.
		 */
		generateModelFromMceView: function( mceView, container ) {
			var datastore, model;

			// Combine attributes and content
			datastore = new UltimateFields.Datastore( _.extend(
				_.clone( mceView.shortcode.attrs.named ),
				$.parseJSON( mceView.shortcode.content || '{}' )
			));

			// Create the model
			model = new UltimateFields.Container.Shortcode.Model( container );
			model.setDatastore( datastore );

			return model;
		},

		/**
		 * Initializes an existing shortcode.
		 */
		initializeExisting: function( mceView, container ) {
			var view = new UltimateFields.Container.Shortcode.PreviewView({
				model: this.generateModelFromMceView( mceView, container )
			});

			// Get the HTML of the view and use it for the shortcode
			mceView.content = view.getHTML();
		},

		/**
		 * Opens a shortcode for editing.
		 */
		edit: function( mceView, container, updateCallback ) {
			var that = this, model, view;

			// Generate a model for the container
			model = this.generateModelFromMceView( mceView, container );

			// Create the editor view
			view = new UltimateFields.Container.Shortcode.EditorView({
				model:   model,
				mceView: mceView,
				updater: updateCallback
			});

			// Show the view as an overlay
			UltimateFields.Overlay.show({
				model:   model,
				view:    view,
				title:   model.get( 'title' ),
				buttons: view.getButtons()
			});
		}
	});

	/**
	 * Overwrites the media modal for posts in order to add the possibility
	 * to select a new shortcode from the sidebar and create it in the content
	 * area when ready.
	 */
	shortcode.MediaFrame = shortcode.originalFrame.extend({
		/**
		 * Initialize the frame and prepare some local data.
		 */
		initialize: function() {
			var frame = this;

			// Change the priority
			frame.priority = 123;

			// super
			shortcode.originalFrame.prototype.initialize.apply( this, arguments );

			// Create a new controller for each shortcode
			_.each( shortcode.shortcodes, function( container ){
				frame.addShortcode( container );
			});

			// Add a separator before shortcodes
			this.on( 'menu:render:default', this.addSeparator );
		},

		/**
		 * Adds a new container/shortcode as a state of the frame.
		 */
		addShortcode: function( container ) {
			var frame = this, controller, instance = {};

			// Save the frame within the instance
			instance.frame = this;
			instance.container = container;

			// Create a controller for the new state
			controller = new shortcode.State({
				id:       container.id,
				search:   false,
				router:   false,
				toolbar:  'uf-' + container.id + '-toolbar',
				menu:     'default',
				tabs:     [],
				title:    container.title,
				priority: this.priority++,
				content:  container.id + '-content'
			});

			frame.states.add([ controller ]);

			// Render the content and toolbar for the particular shortcode
			this.on(
				'content:render:' + container.id + '-content',
				_.bind( frame.renderContent, this, container, instance )
			);

			this.on(
				'toolbar:create:uf-' + container.id + '-toolbar',
				_.bind( frame.renderToolbar, this, container, instance )
			);
		},

		/**
		 * Resets an instance to make it blank for the next use.
		 */
		flushInstance: function( instance, container ) {
			// Create a basic model
			instance.model = new UltimateFields.Container.Shortcode.Model( container );

			// Add a blank datastore
			instance.datastore = new UltimateFields.Datastore();
			instance.model.setDatastore( instance.datastore );

			// Generate the view
			instance.view = new UltimateFields.Container.Shortcode.CreateView({
				model: instance.model
			});
		},

		/**
		 * Render the content for a particular container.
		 */
		renderContent: function( container, instance ) {
			var frame = this;

			this.flushInstance( instance, container );
			frame.content.set( instance.view );

			// Let the view control the insert button
			instance.view.controlButton( instance.button );
		},

		/**
		 * Renders the toolbar for a shortcode.
		 */
		renderToolbar: function( container, instance, toolbar ) {
			var frame = this, insertButton;

			insertButton = {
				text:     'Insert into content',
				style:    'primary',
				priority: 80,
				requires: false,
				click:    _.bind( frame.insertButtonClicked, frame, instance )
			};

			toolbar.view = new wp.media.view.Toolbar({
				controller: frame,
				items: {
					insert: insertButton
				}
			});

			// Save a handle to the button
			instance.button = toolbar.view.get( 'insert' ).model;
		},

		/**
		 * Handles a click on the toolbar button.
		 */
		insertButtonClicked: function( instance, e ) {
			var view;

			if( ! instance.view.buttonClicked( e ) ) {
				e.preventDefault();
				return;
			}

			// Reset the content and replace the view
			view = instance.view;
			this.flushInstance( instance, instance.container );
			view.$el.replaceWith( instance.view.$el );
			instance.view.render();

			// Close the popup
			this.close();
		},

		/**
		 * Adds a separator before the shortcodes in the sidebar.
		 */
		addSeparator: function( menu ) {
			menu.set({
				'uf-shortcodes-separator': new wp.media.View({
					className: 'separator',
					priority:   122
				})
			});
		}
	});

	// Trigger the shortcode initialisation process on document.ready
	$( shortcode.initialize );

})( jQuery );
