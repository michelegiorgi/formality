(function( $ ){

	/**
	 * This file handles the widget container of Ultimate Fields.
	 */
	var container = UltimateFields.Container,
		widget    = container.Widget = {};

	/**
	 * Widgets have basic containers.
	 */
 	widget.Model = container.Base.Model.extend();

 	/**
 	 * This is the view for widgets.
 	 */
 	widget.View = UltimateFields.Container.Base.View.extend({
 		render: function() {
 			var that   = this,
 				tmpl   = UltimateFields.template( 'widget' );

 			this.$el.html( tmpl({
				id:          this.model.get( 'id' ),
				title:       this.model.get( 'title' ),
				description: this.model.get( 'description' ),
				inputName:   this.model.get( 'field_name' )
 			}));

 			this.addFields( null, {
 				wrap: UltimateFields.Field.GridWrap
 			});

			this.$fields.addClass( 'uf-fields-layout-grid' );

 			this.initializeHiddenField();
 		},

		/**
		 * Indicates whether the container supports inline tabs.
		 */
		allowsInlineTabs() {
			return false;
		}
 	});

 	/**
 	 * This controller will handle, validate and reset all groups.
 	 */
 	widget.Controller = container.Controller.extend({
 		/**
 		 * Initializes a widget.
 		 */
 		initialize: function( $widget ) {
			var that = this;

			this.$el = $widget;
			this.containers = [];

			// Start the containers within the widget
			this.$el.find( '.uf-widget' ).each(function() {
				that.initializeContainer( $( this ) );
			});

			// Bind validation
			if( ! $( 'body' ).is( '.wp-customizer' ) ) {
				$widget.find( '.widget-control-save' ).on( 'click.uf-widget', function( e ){
					return that.validate( e );
				});
			}

			// Save a handle
			this.$el.data( 'uf-controller', this );
		},

		/**
		 * Initializes a particular container.
		 */
		initializeContainer: function( $el ) {
			var data = $.parseJSON( $el.find( 'script' ).html() ),
				container, datastore, model, view;

			// Locate the container settings
			container = widget.Controller.getContainerSettings( $el.data( 'type' ) );
			container.field_name = $el.data( 'input-name' );

			// Create the datastore+model pair
			datastore = new UltimateFields.Datastore( data );
			model = new UltimateFields.Container.Widget.Model( container );
			model.setDatastore( datastore );

			// Render the view
			view = new UltimateFields.Container.Widget.View({
				el: $el,
				model: model
			});

			// Save as a container within the controller
			this.addContainer( view );

			view.render();
		},

		/**
		 * Lists problems with validation.
		 *
		 * @param <Array.String> problems String representation of the problems.
		 */
		showErrorMessage: function( problems ) {
			var $div = this.generateErrorMessage( problems );

			// Remove old messages
			this.$el.find( '.widget-content' ).find( '.uf-error' ).remove();
			this.$el.find( '.widget-content' ).prepend( $div );
		},

		/**
		 * Performs validation of a widget.
		 *
		 * @param <Event> e The event that is triggered when applying the widget.
		 */
		validate: function( e ) {
			var valid = container.Controller.prototype.validate.apply( this );

			if( ! valid ) {
				e.preventDefault();
				e.stopPropagation();
			}

			return valid;
		},

		/**
		 * Handles the validation of a specific widget after PHP processes it.
		 *
		 * @param <Strong> token The token that is associated with the new message.
		 */
		toggleValidation: function( token ) {
			var that = this;

			// Remove boring validation
			this.$el.find( '.widget-error' ).remove();

			// Remove old validation message
			this.$el.find( '.uf-widget-error' ).each(function() {
				var $message = $( this );

				if( $message.data( 'token' ) != token ) {
					$message.remove();
				}
			});

			// Remove the loading state
			this.$el.closest( '.customize-control-widget_form' )
				.removeClass( 'widget-form-disabled' )
				.removeClass( 'previewer-loading' );

			// Force the contaienrs to highlight errors
			_.each( this.containers, function( container ) {
				container.model.validate();
			});
		}
 	}, {
 		/**
 		 * Will cache container information.
 		 */
 		containers: {},

 		/**
 		 * Adds all listeners.
 		 */
 		start: function() {
 			$( '.widget:not(#available-widgets .widget)' ).each(function() {
 				widget.Controller.initializeWidget( $( this ) );
 			});

 			$( document ).on( 'widget-added widget-updated', function( e, $widget ) {
 				widget.Controller.initializeWidget( $widget );
 			});
 		},

 		/**
 		 * Initializes a single widget.
 		 */
 		initializeWidget: function( $widget ) {
 			if( 0 === $widget.find( '.uf-widget' ).length )
 				return;

 			new widget.Controller( $widget );
 		},

 		/**
 		 * Returns the settings for a container based on an ID.
 		 */
 		getContainerSettings: function( id ) {
 			var that = this;

 			if( ! ( id in this.containers ) ) {
 				var $el = $( '#uf-widget-settings-' + id );
 				this.containers[ id ] = $.parseJSON( $el.html() );
 			}

 			return _.clone( this.containers[ id ] );
 		},

		/**
		 * Handles the validation of a specific widget after PHP processes it.
		 *
		 * @param <Strong> token The token that is associated with the new message.
		 */
		handleValidation: function( token ) {
			var $widget;

			$( '.uf-widget-error' ).each(function() {
				if( token == $( this ).data( 'token' ) ) {
					$widget = $( this ).closest( '.widget' );
				}
			});

			$widget.data( 'uf-controller' ).toggleValidation( token );
		}
 	});

 	/**
 	 * This will initialize all widgets.
 	 */
 	$( document ).on( 'uf-init', widget.Controller.start );

})( jQuery );
