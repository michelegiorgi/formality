(function( $ ){

	/**
	 * This file handles the front-end container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		frontEnd  = container.Front_End = {};

	/**
	 * Create an additional model for front-end containers.
	 *
	 * At a later stage, this model might be used for AJAX communication with the back-end.
	 */
	frontEnd.Model = container.Base.Model.extend({});

	/**
	 * The front-end container uses a basic view.
	 */
	frontEnd.View = UltimateFields.Container.Base.View.extend({
		className: 'uf-frontend-container',

		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'front-end' );

			this.$el.html( tmpl({
				id:   this.model.get( 'id' ),
				form: this.model.get( 'form' )
			}));

			this.addFields( '.uf-form-fields' );

			// Save values when changed and initially
			this.populateInput();
			this.model.datastore.on( 'all', function() {
				that.populateInput();
			});
		},

		/**
		 * Populates the hidden input, which contains all values for the container.
		 */
		populateInput: function() {
			var data = this.model.datastore;
			this.$el.find( '.uf-container-data' ).val( JSON.stringify( data ) );
		}
	});

	/**
	 * This controller will manage a whole form in the front-end, along with it's containers.
	 */
	frontEnd.Controller = container.Controller.extend({
		/**
		 * Initializes a form.
		 */
		initialize: function( $form ) {
			var that = this;

			// Save a handle for each container
			this.$el = $( $form );
			this.containers = [];

			// Initialize all containers within the form
			this.$el.find( '.uf-form-container' ).each(function() {
				that.initializeContainer( $( this ) );
			});

			// Listen for submission
			this.$el.on( 'submit', _.bind( this.submit, this ) );
		},

		/**
		 * Initializes a new container within the form.
		 */
		initializeContainer: function( $el ) {
			var data = JSON.parse( $el.find( 'script' ).html() ),
				container;

			// Initialize the container
			container = UltimateFields.initializeContainer( $el, data );

			// Save as a container within the controller
			this.addContainer( container.view );
		},

		/**
		 * Handles the submission of the form.
		 */
		submit: function( e ) {
			// Remove pre-existing validation messages
			this.$el.find( '.uf-error' ).remove();

			if( ! this.validate() ) {
				e.preventDefault();
			}
		},

		/**
		 * Shows an error message based on particular problems.
		 */
		showErrorMessage: function( problems ) {
			var offset;

			this.$el.prepend( this.generateErrorMessage( problems ) );

			// Generate a proper offset with a small gap, minding the admin-bar
			offset = this.$el.offset().top;
			if( $( 'body' ).is( '.admin-bar' ) ) {
				offset -= $( '#wpadminbar' ).height();
			}
			offset -= 20;

			$( 'html,body' ).animate({
				scrollTop: offset
			});
		}
	});

	$(function() {
		$( document ).trigger( 'uf-extend' );
		$( document ).trigger( 'uf-pre-init' );

		$( '.uf-form' ).each(function() {
			new frontEnd.Controller( this );
		});

		$( document ).trigger( 'uf-init' );
	});

})( jQuery );
