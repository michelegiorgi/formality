(function( $ ){

	/**
	 * This file handles the user meta container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		comment   = container.Comment = {},
		controller;

	/**
	 * Comments use a basic model, which has no additional functionality.
	 */
 	comment.Model = container.Base.Model.extend();

 	/**
 	 * The view for the comment container.
 	 */
 	comment.View = UltimateFields.Container.Base.View.extend({
		/**
 		 * Initializes the view.
 		 */
 		initialize: function() {
 			var that = this;

 			this.initializeLocations( UltimateFields.Location.Comment );

			// Connect to the controller
			controller.addContainer( this );
 		},

 		/**
 		 * Renders the visible part of the container.
 		 */
 		render: function() {
 			var that = this,
 				tmpl = UltimateFields.template( 'comment' );

 			// Add the basic layout
 			this.$el.html( tmpl( this.model.toJSON() ) );

			// Make the container seamless if needed
			this.seamless();

 			// Add normal fields and initialize the hidden field
 			this.addFields();
 			this.initializeHiddenField();
 		},

 		/**
 		 * Shows the meta box of the container and the responsible checkbox.
 		 */
 		show: function() {
 			this.$el.closest( '.postbox' ).show();
 			$( '#adv-settings label[for="' + this.model.get( 'id' ) + '-hide"]' ).show();
 		},

 		/**
 		 * Hides the meta box of the container and the responsible checkbox.
 		 */
 		hide: function() {
 			this.$el.closest( '.postbox' ).hide();
 			$( '#adv-settings label[for="' + this.model.get( 'id' ) + '-hide"]' ).hide();
 		}
 	});

	/**
 	 * This handles the visibility of the group based on rules and locations.
 	 */
 	UltimateFields.Location.Comment = UltimateFields.Location.extend({
		defaults: {
			visible: true,
			stati: { visible: [], hidden: [] }
		},

		/**
		 * Starts listening for all needed items/rules.
		 */
		listen: function() {
			var that = this;

			this.checked = new Backbone.Model();

			if( this.get( 'stati' ) && ! this.empty( this.get( 'stati' ) ) ) {
				this.listenForStati();
			}
		},

		/**
		 * Listens for status changes, if stati are present.
		 */
		listenForStati: function() {
			var that    = this,
				stati   = this.get( 'stati' ),
				$radios = $( '#comment-status-radio input' ),
				check;

			// Check if there is a selector at all
			if( ! $radios.length ) {
				return;
			}

			check = function() {
				var active;

				switch( $radios.filter( ':checked' ).val() ) {
					case '1':
						active = 'approved';
						break;
					case '0':
						active = 'pending';
						break;
					case 'spam':
						active = 'spam';
						break;
				}

				that.checked.set( 'stati', that.checkSingleValue( active, stati ) );
			}

			// Listen for changes
			$radios.change( _.bind( check, this ) );
			check();
		}
	});

 	/**
 	 * This controller will handle, validate and reset all groups.
 	 */
 	comment.Controller = container.Controller.extend({
 		/**
 		 * Binds the controller to the actual form.
 		 */
 		bindToForm: function() {
 			var that = this;

 			$( '#post' ).submit(function( e ) {
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
			var $div = this.generateErrorMessage( problems ), $after;

			// Locate the after div
			$after = $( '#comment-link-box' ).length
				? $( '#comment-link-box' )
				: $( 'h1:eq(0)' );

			// Remove old messages
			$after.siblings( '.uf-error' ).remove();

			// Add the message
			$after.after( $div );

			// Scroll the body to the top
			$( 'html,body' ).animate({
				scrollTop: 0
			});
		}
 	});

 	// Create a single controller that would be used with all forms
 	controller = new comment.Controller();

 	// Connect the controller to the form
 	$( document ).on( 'uf-init', function() {
 		controller.bindToForm();
 	});

})( jQuery );
