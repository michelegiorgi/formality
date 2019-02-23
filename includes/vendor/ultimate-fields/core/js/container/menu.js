(function( $ ){

	/**
	 * This file handles the Menu container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		menu      = container.Menu = {},
		controller;

	// Holds the current DOM object for locations
	menu.currentDOMObject = false;

	/**
	 * Standard models for menu items.
	 */
	menu.Model = container.Base.Model.extend({
		/**
		 * Performs normal validation, but saves the errors.
		 */
		validate: function() {
			var state = container.Base.Model.prototype.validate.apply( this );

			this.set( 'validation_state', state );

			return state;
		},

		/**
		 * Initializes all locations when needed.
		 *
		 * In most containers this works in the view, but here it's in the model since the view
		 * is only initialized for visible items.
		 */
		initializeLocations: function( locationClass ) {
			 var that = this, locations = new UltimateFields.Locations;

			 // Convert to the right class
 			_.each( this.get( 'locations' ), function( location ) {
 				locations.add( new locationClass( location ) );
 			});

 			// Save a handle
 			this.set( 'locations', locations );

 			// Listen for changes
 			locations.on( 'change:visible', function() {
				that.set( 'visible', locations.isVisible() );
 			});

			this.set( 'visible', locations.isVisible() );
		}
	});

	/**
	 * Basic view operations for the menu
	 */
	menu.View = container.Base.View.extend({
		className: 'uf-menu-container',

		/**
		 * Initialize the view and location rules.
		 */
		initialize: function() {
			var that = this;

 			this.model.on( 'change:visible', function() {
				that[ that.model.get( 'visible' ) ? 'show' : 'hide' ]();
			});

			if( ! this.model.get( 'visible' ) ) {
				this.$el.hide();
			}
		},

		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'menu' ),
				args;

			// Add the basic HTML
			args = _.extend( {}, this.model.toJSON(), {
				item:  this.model.get( 'itemId' )
			});

			this.$el.html( tmpl( args ) );

 			// Add normal fields and initialize the hidden field
 			this.addFields( null, {
				wrap: UltimateFields.Field.MenuWrap
			});

			this.$fields.addClass( 'uf-menu-fields' );

			// Modify the tabs
			if( this.$tabs ) {
				this.$tabs.addClass( 'uf-menu-tab-wrapper' );
			}

 			this.initializeHiddenField();
		},

		/**
		 * Shows the container based on location rules.
		 */
 		show: function() {
 			this.$el.slideDown();
			setTimeout( UltimateFields.ContainerLayout.DOMUpdated, 20);
 		},

 		/**
 		 * Hides the container based on location rules.
 		 */
 		hide: function() {
 			this.$el.slideUp();
 		},

		/**
		 * Indicates whether the container supports inline tabs.
		 */
		allowsInlineTabs() {
			return false;
		}
	});

	/**
	 * Extends the normal menu view, but instead of displaying fields, displays a button for a popup.
	 */
	menu.ButtonView = menu.View.extend({
		render: function() {
			var that = this, button;

			button = new UltimateFields.Button({
				type:     'secondary',
				icon:     'dashicons-external',
				text:     this.model.get( 'title' ),
				callback: function() {
					that.openPopup();
				}
			});

			button.$el.appendTo( this.$el );
			button.render();

			this.initializeHiddenField();
		},

		/**
		 * Opens an editor popup.
		 */
		openPopup: function() {
			var that = this, view;

			view = new menu.fullScreenView({
				model: this.model
			});

			UltimateFields.Overlay.show({
				view: view,
				title: this.model.get( 'title' ),
				buttons: view.getButtons()
			});
		}
	});

	/**
	 * A view for full-screen editing.
	 */
	menu.fullScreenView = menu.View.extend({
		render: function() {
			var that  = this;

			// Add a simple fields wrapper
			this.$el.append( $( '<div class="uf-fields" />' ) );

 			// Add normal fields and initialize the hidden field
 			this.addFields( null, {
				tabs: false
			});
		},

		/**
		 * Returns the buttons for the popup footer.
		 */
		getButtons: function() {
			var that = this, buttons = [];

			buttons.push({
				type:     'secondary',
				cssClass: 'uf-button-delete-popup',
				text:     UltimateFields.L10N.localize( 'close-menu-item' ),
				icon:     'dashicons-yes',
				callback: _.bind( this.closePopup, this )
			});

			return buttons;
		},

		/**
		 * When the popup is being closed, toggle validation states.
		 */
		closePopup: function() {
			this.model.get( 'fields' ).each(function( field ) {
				field.validate();
			});

			return true;
		}
	});

	/**
	 * Handles the definition of menu item locations.
	 */
	 UltimateFields.Location.Menu_Item = UltimateFields.Location.extend({
 		defaults: {
 			visible: true,
 			levels: { visible: [], hidden: [] }
 		},

 		/**
 		 * Starts listening for all needed items/rules.
 		 */
 		listen: function() {
 			var that = this;

 			this.checked = new Backbone.Model();

 			if( this.get( 'levels' ) && ! this.empty( this.get( 'levels' ) ) ) {
 				this.listenForLevels();
 			}
		},

		/**
		 * Starts listening for levels.
		 */
		listenForLevels: function() {
			var that = this, levels = this.get( 'levels' );

			this.domObject = menu.currentDOMObject;

			// Format levels first
			levels.visible = levels.visible.map( parseInt );
			levels.hidden  = levels.hidden.map( parseInt );

			// Check the initial level
			this.checkLevel();

			// Listen for sorting
			$( '#menu-to-edit' ).on( 'sortstop', function( e, ui ){
				// Split the timeline in order to let WP handle CSS classes first
				setTimeout(function() {
					that.checkLevel();
				}, 1);
			} );
		},

		/**
		 * Checks the level of the menu item when needed.
		 */
		checkLevel: function() {
			var level = 1;

			// Determine the level
			_.each( this.domObject.get( 0 ).classList, function( cssClass ) {
				if( 0 !== cssClass.indexOf( 'menu-item-depth-' ) )
					return;

				level = parseInt( cssClass.replace( 'menu-item-depth-', '' ) ) + 1;
			});

			// Save the value
			this.checked.set( 'levels', this.checkSingleValue( level, this.get( 'levels' ) ) );
		}
	});

	/**
	 * Controls multiple containers on the page, particularly menu containers.
	 */
	menu.Controller = container.Controller.extend({
		initialize: function( args ) {
			this.models = [];

			// Save the model
			menu.Controller.addExisting( this );
		},

 		/**
 		 * Handles new containers.
 		 */
 		addModel: function( model ) {
 			this.models.push( model );
 		},

 		/**
 		 * Attempts validating all available forms.
 		 */
 		validate: function() {
			var that     = this,
				problems = [];

			_.each( that.models, function( model ){
				_.each( model.validate(), function( problem ) {
					problems.push( problem );
				})
			});

			if( problems.length ) {
				return false;
			}

			return true;
 		},

		/**
		 * Displays a general error message.
		 */
		showErrorMessage: function( problems ) {
			menu.Controller.showErrorMessage();
		},

		/**
		 * Prevents the model from listening.
		 */
		detach: function() {
			menu.Controller.removeExisting( this );
		}
	}, {
		existing: [],

		/**
		 * Adds an existing controller and binds the form.
		 */
		addExisting: function( controller ) {
			this.existing.push( controller );
		},

		/**
		 * Indicates that a controller is no longer active.
		 */
		removeExisting: function( controller ) {
			var index;

			if( -1 != ( index = this.existing.indexOf( controller ) ) ) {
				this.existing.splice( index, 1 );
			}
		},

		/**
		 * Starts listening to the form.
		 */
		listen: function() {
			var that = this;

			$( '#update-nav-menu' ).submit( _.bind( this.submit, this ) );
		},

		/**
		 * Handles form submission.
		 */
		submit: function( e ) {
			// Initialize newly created items if neccessary
			$( '.menu-item:not(.uf-menu-item)' ).each(function() {
				new menu.Item({
					el: this
				});
			});

			// Check the validation status
			if( ! this.validate() ) {
				e.preventDefault();
				this.showErrorMessage();
			}
		},

		/**
		 * Performs a general validation.
		 */
		validate: function() {
			var valid = true;

			_.each( this.existing, function( controller ) {
				if( ! controller.validate() ) {
					valid = false;
				}
			});

			return valid;
		},

		/**
		 * Indicates that there is a problem with validation.
		 */
		showErrorMessage: function() {
			var $after = $( '.drag-instructions' ), tmpl, $div;

			if( $after.siblings( '.uf-error' ).length ) {
				return; // Message is already displayed
			}

			tmpl = UltimateFields.template( 'menu-error' );
			$div = $( tmpl( {
				title: UltimateFields.L10N.localize( 'menu-issues' )
			}));

			// Add the message
			$after.after( $div );

			// Scroll the body to the top
			$( 'html,body' ).animate({
				scrollTop: 0
			});
		}
	});

	/**
	 * Handles individual menu items.
	 */
	menu.Item = Backbone.View.extend({
		events: {
			'mouseup .item-edit': 'maybeInitialize',
			'click .item-delete': 'delete'
		},

		initialize: function( args ) {
			var that = this;

			this.viewInitialized = false;

			// Generate the models for each container
			this.containers = [];

			// Create a controller
			this.controller = new menu.Controller();

			// Parse all containers
			this.$el.find( '.uf-menu-item-data' ).each(function() {
				that.initializeContainer( this );
			});

			// Indicate success
			this.$el.addClass( 'uf-menu-item' );
		},

		/**
		 * Initializes a particular container.
		 */
		initializeContainer: function( container ) {
			var that = this,
				$el = $( container ),
				data, settings, datastore, model;

			// Get settings for the container
			settings = menu.Item.getContainerSettings( $el.data( 'container' ) );
			settings[ 'itemId' ] = $el.data( 'item-id' );

			// Create the model and datastore
			data = $.parseJSON( $el.html() );
			datastore = new UltimateFields.Datastore( data );

			model = new menu.Model( settings );
			model.setDatastore( datastore );

			// Setup the location based on the current menu item, disable transitions in the middle
			menu.currentDOMObject = this.$el.closest( '.menu-item' );
			model.initializeLocations( UltimateFields.Location.Menu_Item );
			menu.currentDOMObject = false;

			// Add the model to the controller
			this.controller.addModel( model );

			// Save the combo
			this.containers.push({
				placeholder: $el,
				data:        data,
				model:       model
			});

			// Listen for changes
			model.on( 'change:validation_state', function() {
				that.toggleValidationState();
			})
		},

		/**
		 * Initializes the containers within the item.
		 */
		maybeInitialize: function() {
			var that = this;

			if( this.viewInitialized ) {
				return;
			}

			_.each( this.containers, function( combo ) {
				var viewClass = combo.model.get( 'show_in_popup' )
					? menu.ButtonView
					: menu.View;

				var view = new viewClass({
					model: combo.model
				});

				// Save a handle
				combo.view = view;

				// Replace and render
				combo.placeholder.replaceWith( view.$el );
				view.render();

				// Force grid resizing
				setTimeout( UltimateFields.ContainerLayout.DOMUpdated, 20 );
			});

			this.viewInitialized = true;
		},

		/**
		 * Toggles the validation state of the item
		 */
		toggleValidationState: function() {
			var hasErrors = false;

			_.each( this.containers, function( combo ) {
				if( combo.model.get( 'validation_state' ) && combo.model.get( 'visible' ) ) {
					hasErrors = true;
				}
			});

			this.$el.closest( '.menu-item' )[ hasErrors ? 'addClass' : 'removeClass' ]( 'uf-menu-item-error' );
		},

		/**
		 * Delete the menu item
		 */
		delete: function( e ) {
			this.controller.detach();
		}
	}, {
		settingsCache: {},

		/**
		 * Returns the basic settings for a container.
		 */
		getContainerSettings: function( id ) {
			if( ! ( id in menu.Item.settingsCache ) ) {
				menu.Item.settingsCache[ id ] = $.parseJSON( $( '#uf-menu-settings-' + id ).html() );
			}

			return _.clone( menu.Item.settingsCache[ id ] );
		}
	});

	/**
	 * Listen for item expansions and render containers inside.
	 */
	$( document ).on( 'uf-init', function() {
		menu.Controller.listen();

		// Process existing items
		$( '.menu-item' ).each(function() {
			new menu.Item({
				el: this
			});
		});

		// Whenever an item is about to be expanded, check if there is an item
		$( document ).on( 'mouseup', '.menu-item:not(.uf-menu-item) .item-edit', function() {
			var item = new menu.Item({
				el: $( this ).closest( '.menu-item' )
			});

			// Now that the box is about to be shown, initialize the view
			item.maybeInitialize();
		});
	});

})( jQuery );
