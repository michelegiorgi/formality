(function( $ ){

	/**
	 * This file handles the user meta container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		user      = container.User = {},
		controller;

	/**
	 * A simple model for the user.
	 */
	user.Model = container.Base.Model.extend();

	/**
	 * The view for the user screen.
	 */
	user.View = UltimateFields.Container.Base.View.extend({
		/**
 		 * Initializes the view.
 		 */
 		initialize: function() {
 			var that = this;

			this.initializeLocations( UltimateFields.Location.User );

			// Connect to the controller
			controller.addContainer( this );
 		},

 		/**
 		 * Renders the visible part of the container.
 		 */
 		render: function() {
 			var that = this,
 				tmpl = UltimateFields.template( 'user' );

 			// Add the basic layout
 			this.$el.html( tmpl( this.model.toJSON() ) );

 			// Add normal fields and initialize the hidden field
 			this.addFields();
			if( 'boxed' == this.model.get( 'style' ) ) {
				this.$fields.addClass( 'uf-boxed-fields' );
			}
 			this.initializeHiddenField();

			// Adjust for the registration form
			if( this.model.get( 'registration_form' ) ) {
				this.$el.addClass( 'uf-register' );
				this.$el.children( 'h2' ).remove();
			}

			if( -1 != [ 'auto', 'seamless' ].indexOf( this.model.get( 'style' ) ) ) {
				this.$el.find( '.uf-fields' ).eq( 0 ).addClass( 'uf-fields-seamless' );
			}
 		}
	});

	/**
 	 * This handles the visibility of the group based on rules and locations.
 	 */
 	UltimateFields.Location.User = UltimateFields.Location.extend({
		defaults: {
			visible: true,
			roles: { visible: [], hidden: [] }
		},

		/**
		 * Starts listening for all needed items/rules.
		 */
		listen: function() {
			var that = this;

			this.checked = new Backbone.Model();

			if( this.get( 'roles' ) && ! this.empty( this.get( 'roles' ) ) ) {
				this.listenForRoles();
			}
		},

		/**
		 * Listens for template changes, if roles are present.
		 */
		listenForRoles: function() {
			var that    = this,
				roles   = this.get( 'roles' ),
				$select = $( 'select#role' ),
				check;

			// Check if there is a selector at all
			if( ! $select.length ) {
				return;
			}

			check = function() {
				that.checked.set( 'roles', that.checkSingleValue( $select.val(), roles ) );
			}

			// Listen for changes
			$select.change( _.bind( check, this ) );
			check();
		}
	});

	/**
	 * This controller will handle, validate and reset all groups.
	 */
	user.Controller = container.Controller.extend({
		/**
		 * Binds the controller to the actual form.
		 */
		bindToForm: function() {
			var that = this;

			$( '#your-profile' ).submit(function( e ) {
				if( ! that.validate() ) {
					e.preventDefault();
				};
			});
		},

		/**
		 * Lists problems with validation.
		 *
		 * @param <Array.String> problems String representation of the problems.
		 */
		showErrorMessage: function( problems ) {
			var $div = this.generateErrorMessage( problems );

			// Remove old messages
			$( 'h1:eq(0)' ).siblings( '.uf-error' ).remove();

			// Add the message
			$( 'h1:eq(0)' ).after( $div );

			// Scroll the body to the top
			$( 'html,body' ).animate({
				scrollTop: 0
			});
		}
	});

	// Create a single controller that would be used with all forms
	controller = new user.Controller();

	// Connect the controller to the form
	$( document ).on( 'uf-init', function() {
		controller.bindToForm();
	});

})( jQuery );
