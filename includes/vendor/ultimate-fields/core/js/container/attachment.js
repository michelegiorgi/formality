(function( $ ){

	/**
	 * This file handles the Media container of Ultimate Fields.
	 */
	var container  = UltimateFields.Container,
		attachment = container.Attachment = {},
		controller;

	/**
	 * This is the model that works with attachments.
	 *
	 * The additional functionality in this model is related to the fact that no data is loaded/saved directly
	 * by Ultimate Fields. Instead, the data must be loaded from within attachments and saved back to them.
	 */
	attachment.Model = container.Base.Model.extend({
		/**
		 * Initializes the model and atts a timeout for saving data.
		 */
		initialize: function() {
			container.Base.Model.prototype.initialize.apply( this, arguments );
			this.saveTimeout = null;
			this.set( 'isValid', true );
		},

		/**
		 * When a new datastore is set, we will listen to it's changes and save the data within the attachment.
		 */
		setDatastore: function( datastore ) {
			var that = this;

			// Call the base method
			container.Base.Model.prototype.setDatastore.apply( this, arguments );

			// Listen to changes in the datastore and save them after a timeoutp
			datastore.on( 'change', function( e ) {
				clearTimeout( that.saveTimeout );
				that.saveTimeout = setTimeout(function() {
					that.sendValuesToAttachment( e );
				}, 500);
			});
		},

		/**
		 * Sends the values of the current datastore to the attachment it belongs to.
		 */
		sendValuesToAttachment: function( e ) {
			var that = this,
				data = JSON.stringify( this.datastore ),
				valid;

			// Validate first
			valid = this.isValid();

			this.set( 'isValid', valid );

			if( ! valid ) {
				// If the model is not valid, don't even send the data to the attachment
				_.each( e.changed, function( value, key ) {
					var field = that.get('fields').findWhere({ name: key });

					if( field ) {
						field.validate();
					}
				});
			}

			// Put the data in the attachment
			this.get( 'attachment' ).set( 'uf_data_' + this.get( 'id' ), data );
			this.get( 'attachment' ).save();
		},

		/**
		 * Whenever an attachment is loaded, this loads the values of the container from that attachment.
		 */
		loadValuesFromAttachment: function() {
			var data = {}, raw;

			// Get the data and parse if needed
			raw = this.get( 'attachment' ).get( 'uf_data_' + this.get( 'id' ) );
			if( 'string' == typeof raw ) {
				raw = $.parseJSON( raw );
			}

			// Save in the datastore
			this.setDatastore( new UltimateFields.Datastore( raw ) );

			// Trigger the change to let the view update itself.
			this.trigger( 'attachmentChanged' );
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
		}
	});

	/**
	 * Displays fields in the attachment sidebar.
	 */
	attachment.View = container.Base.View.extend({
		/**
		 * Initialize the view by adding a listener for changes in the attachment model.
		 */
		initialize: function() {
			container.Base.View.prototype.initialize.apply( this, arguments );
			this.model.on( 'attachmentChanged', _.bind( this.render, this ) );
		},

		/**
		 * Standard container render
		 */
		render: function() {
			var that  = this,
				tmpl  = UltimateFields.template( 'attachment' );

			this.$el.html( tmpl( this.model.toJSON() ) );

			this.addFields( false, {
				wrap: UltimateFields.Field.AttachmentWrap
			});

			if( '$tabs' in this ) {
				this.$tabs.addClass( 'uf-media-tabs' );
			}

			if( this.requiresExpandedSidebar() ) {
				this.$el.addClass( 'uf-media-wide' );
				this.$el.append( UltimateFields.template( 'attachment-warning' )() );
				this.$el.find( '.uf-attachment-expand' ).on( 'click', function() {
					attachment.modalModel.set( 'expanded', true );
				})
			}
		},

		/**
		 * Indicates whether the container supports inline tabs.
		 */
		allowsInlineTabs() {
			return false;
		},

		/**
		 * Checks whether a container requires the sidebar to be expanded.
		 */
		requiresExpandedSidebar: function() {
			var that   = this,
				simple = true;

			var simpleTypes = [
				'Tab',
				'Checkbox',
				'Text',
				'Textarea'
			];

			this.model.get( 'fields' ).each( function( model ) {
				if( ! simple ) {
					return;
				}

				if( -1 === simpleTypes.indexOf( model.get( 'type' ) ) ) {
					simple = false;
				}
			});

			return ! simple;
		}
	});

	/**
	 * A controller that handles multiple containers, associated with the same attachment.
	 */
	attachment.Controller = container.Controller.extend({
		/**
		 * Initializes the controller for a new attachment.
		 */
		initialize: function( args ) {
			var that = this, tmpl;

			this.containers = [];
			this.$el        = args.$el;
			this.attachment = args.attachment;

			// Add the backbone
			tmpl = UltimateFields.template( 'controller-attachment' );
			this.$el.html( tmpl );

			// Start all containers within the controller
			_.each( this.getAll(), function( container ) {
				that.initializeContainer( container );
			});

			UltimateFields.ContainerLayout.DOMUpdated();
		},

		/**
		 * Initializes a container within the attachment.
		 */
		initializeContainer: function( settings ) {
			var $root, container, created, type, include, exclude;

			// Check what types are supported
			include = [];
			exclude = [];

			_.each( settings.locations, function( location ) {
				if( location.file_types ) {
					include = include.concat( location.file_types.visible );
					exclude = exclude.concat( location.file_types.hidden );
				}
			});

			type = this.attachment.get( 'type' ) + '/' + this.attachment.get( 'subtype' );
			if( -1 != exclude.indexOf( type ) ) {
				return;
			}
			if( include.length && -1 == include.indexOf( type ) ) {
				return;
			}

			// Add the element to the DOM
			$root = $( '<div class="uf-media-fields" />' );
			this.$el.append( $root );

			// Set the type for the initializer
			container = {
				type:     'Attachment',
				settings: settings
			}

			// Initialize the container with the appropriate datastore
			created = UltimateFields.initializeContainer( $root, container, this.attachment.get( 'uf_data_' + settings.id ) );
			created.model.set( 'attachment', this.attachment );
			created.model.loadValuesFromAttachment();

			// Save the container
			this.containers.push( created.view );

			// Listen to validation changes
			created.model.on( 'change:isValid', _.bind( this.toggleValidation, this ) );
		},

		/**
		 * Gets all containers that are to be used.
		 */
		getAll: function() {
			attachment.Controller.cacheContainers();
			return _.clone( attachment.Controller.cachedContainers );
		},

		/**
		 * Checks if all containers are avalid and displays the validation message if not.
		 * If everything is alright, the attachment is changed.
		 */
		toggleValidation: function() {
			var valid = true;

			_.each( this.containers, function( view ) {
				if( ! view.model.get( 'isValid' ) ) {
					valid = false;
				}
			});

			if( ! valid ) {
				this.$el.find( '.uf-media-validation-message' ).show();
			} else {
				this.$el.find( '.uf-media-validation-message' ).hide();
			}
		}
	}, {
		/**
		 * Caches all existing attachment containers.
		 */
		cacheContainers: function() {
			var that = this;

			if( 'cachedContainers' in this ) {
				return;
			}

			that.cachedContainers = [];

			$( '.uf-attachment-container-settings' ).each(function() {
				that.cachedContainers.push( $.parseJSON( $( this ).html() ) );
			});
		}
	});

	/**
	 * Renders all containers for an attachment.
	 */
	attachment.addContainersToAttachment = function( that, $appendTo ) {
		var $root = $( '<div class="uf-media-wrapper" />' );
		$appendTo.append( $root );// Make sure to move the element

		new attachment.Controller({
			$el: $root,
			attachment: that.model
		});
	}

	/**
	 * This model will contain the state of the media modal
	 */
	attachment.ModalModel = Backbone.Model.extend({
		defaults: {
			expanded: false
		},

		sync: function() {}
	});

	/**
	 * A button, which will expand/collapse the media popup.
	 */
	attachment.ExpandButton = Backbone.View.extend({
		tagName: 'a',
		className: 'uf-media-toggle',

		events: {
			'click': 'click'
		},

		initialize: function() {
			this.model.on( 'change', _.bind( this.update, this ) );
		},

		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'attachment-expand' );

			this.$el.attr( 'href', '#' );

			this.$el.html( tmpl({
				text: 'Expand'
			}));
		},

		click: function( e ) {
			e.preventDefault();
			this.model.set( 'expanded', ! this.model.get( 'expanded') );
		},

		update: function() {
			if( this.model.get( 'expanded' ) ) {
		        this.$el.find( 'strong' ).text( 'Collapse' );
		        this.$el.find( 'span' ).addClass( 'dashicons-arrow-right' );
		    } else {
		        this.$el.find( 'strong' ).text( 'Expand' );
		        this.$el.find( 'span' ).removeClass( 'dashicons-arrow-right' );
		    }
		}
	});

	/**
	 * Will handle the body as the view of the modal.
	 */
	attachment.FrameView = Backbone.View.extend({
		initialize: function() {
			var that = this;

			this.model.on( 'change', function() {
				that.$el[
					that.model.get( 'expanded' )
						? 'addClass'
						: 'removeClass'
				]( 'media-frame-uf-wide' );

				UltimateFields.ContainerLayout.DOMUpdated( true );
			});
		}
	});

	/**
	 * When UF is initializing, hijack the rendering methods of media popups.
	 */
	$( document ).on( 'uf-init', function() {

		if( ( 'undefined' == typeof wp ) || ! ( 'media' in wp ) )
			return;

		var twoColumnView,
			originalAttachmentRenderer,
			originalRouterRenderer,
			attachmentReady = false,
			modal, toggle, frame;

		modal  = new attachment.ModalModel();
		frame  = new attachment.FrameView({ model: modal, el: document.body });

		// Save a global handle to the modal
		attachment.modalModel = modal;

		/**
		 * Extends the two-column view with details.
		 */
		twoColumnView = wp.media.view.Attachment.Details.TwoColumn;
		if( twoColumnView ) wp.media.view.Attachment.Details.TwoColumn = twoColumnView.extend({
			render: function() {
				var that = this;

				// Normal initialisation
				twoColumnView.prototype.render.apply( this, arguments );

				// Add the custom container
				attachment.addContainersToAttachment( this, this.$el.find( '.attachment-info .settings' ) );
			}
		});

		/**
		 * Replaces the render function of the media popup on the media page.
		 */
		originalAttachmentRenderer = wp.media.view.Attachment.prototype.render;
		if( originalAttachmentRenderer ) wp.media.view.Attachment.prototype.render = function() {
			var that = this;

			originalAttachmentRenderer.apply( this, arguments );

			if(
				! ( that instanceof wp.media.view.Attachment.Details )
				|| ( 'TwoColumn' in wp.media.view.Attachment.Details )
			) {
				return;
			}

			if( attachmentReady ) {
				attachment.addContainersToAttachment( that, that.$el );
			} else {
				this.on( 'ready' , function() {
					attachment.addContainersToAttachment( that, that.$el );
					attachmentReady = true;
				});
			}
		}

		/**
		 * Change the toolbar in order to add an expand button.
		 */
		originalRouterRenderer = wp.media.view.Router.prototype.render;
		if( originalRouterRenderer ) wp.media.view.Router.prototype.render = function() {
			originalRouterRenderer.apply( this, arguments );

			var toggle = new attachment.ExpandButton({ model: modal });
			this.$el.append( toggle.$el );
			toggle.render();
		}
	});

})( jQuery );
