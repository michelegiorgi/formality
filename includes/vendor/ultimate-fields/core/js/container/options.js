(function( $ ){

	/**
	 * This file handles the Options container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		options   = container.Options = {},
		controller;

	/**
	 * Options work almost the same way as post meta.
	 */
	options.Model = container.Base.Model.extend({
		/**
		 * Saves the initial state of the container in order to be able to check for changes later.
		 */
		saveInitialState: function() {
			this.initialState = JSON.stringify( this.datastore.export() );
		},

		/**
		 * Checks if the values within the container have changed.
		 */
		hasChanged: function() {
			return JSON.stringify( this.datastore.export() ) !== this.initialState;
		}
	});

	/**
	 * Returns the appropriate templates and controllers
	 */
	options.View = container.Base.View.extend({
 		/**
 		 * Initializes the view.
 		 */
 		initialize: function() {
 			var that = this;

			// Connect to the controller
			controller.addContainer( this );
 		},

 		/**
 		 * Renders the visible part of the container.
 		 */
 		render: function() {
 			var that = this,
 				tmpl = UltimateFields.template( 'options' );

 			// Add the basic layout
 			this.$el.html( tmpl( this.model.toJSON() ) );

			// If the container is seamless, remove the metabox class
			this.seamless();

			this.model.datastore.on( 'change:__tab', function() {
				window.location.hash = '#/tab/' + that.model.datastore.get( '__tab' );
			});

			var h = window.location.hash, r = /^\#\/tab\//;
			if( h.match( r ) ) {
				this.model.datastore.set( '__tab', h.replace( r, '' ) );
			}

 			// Add normal fields and initialize the hidden field
 			this.addFields();
 			this.initializeHiddenField();
 		}
	});

	/**
	 * This controller will handle and validateall groups.
	 */
	options.Controller = container.Controller.extend({
		/**
		 * Binds the controller to the actual form.
		 */
		bindToForm: function() {
			var that = this;

			this.preventUnload = true;

			$( '#poststuff' ).submit(function( e ) {
				that.preventUnload = false;

				if( ! that.validate() ) {
					e.preventDefault();
				} else {
					$( '.uf-actions-box .spinner' ).addClass( 'is-active' );
				}

				setTimeout(function() {
					that.preventUnload = true;
				}, 10);
			});

			// When unloading the window, prevent leaving if there have been changes
			$( window ).on( 'beforeunload', _.bind( this.onbeforeunload, this ) );

			// Let each container save their initial state
			_.each( this.containers, function( container ) {
				container.model.saveInitialState();
			});
		},

		/**
		 * Before unloading the window, check for changes.
		 */
		onbeforeunload: function() {
			var changed = false;

			if( ! this.preventUnload ) {
				return;
			}

			_.each( this.containers, function( container ) {
				if( container.model.hasChanged() ) {
					changed = true;
				}
			});

			if( changed ) {
				return false;
			}
		},

		/**
		 * Lists problems with validation.
		 *
		 * @param <Array.String> problems String representation of the problems.
		 */
		showErrorMessage: function( problems ) {
			var $div = this.generateErrorMessage( problems );

			// Remove old messages
			$( 'h1:eq(0)' ).siblings( '.uf-error, .notice-success' ).remove();

			// Add the message
			$( 'h1:eq(0)' ).after( $div );

			// Scroll the body to the top
			$( 'html,body' ).animate({
				scrollTop: 0
			});
		}
	});

	// Create a single controller that would be used with all forms
	controller = new options.Controller();

	// Connect the controller to the form
	$( document ).on( 'uf-init', function() {
		controller.bindToForm();
	});

})( jQuery );
