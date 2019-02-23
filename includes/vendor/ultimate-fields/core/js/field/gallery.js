(function( $ ){

	var field        = UltimateFields.Field,
		fileField    = field.File,
		galleryField = field.Gallery = {};

	/**
	 * Basic model for files.
	 */
	galleryField.Model = fileField.Model.extend({
		defaults: $.extend( {}, fileField.Model.prototype.defaults, {
			multiple: 'add'
		}),

		/**
		 * Returns an SEO-crawlable value for the field.
		 */
		getSEOValue: function() {
			var output = [],
				ids    = this.getValue(),
				cache;

			if( ! ids || ! ids.length ) {
				return false;
			}

			_.each( ids, function( id ) {
				obj = fileField.Cache.get( id );

				output.push(
					'<img src="%s" height="%s" width="%s" alt="%s" />'
						.replace( '%s', obj.get( 'url' ) )
						.replace( '%s', obj.get( 'width' ) )
						.replace( '%s', obj.get( 'height' ) )
						.replace( '%s', obj.get( 'alt' ).replace( /['"]/g, "&#39;" ) )
				);
			});

			return output.join( ' ' );
		}
	});

	/**
	 * Handles attachments within the gallery.
	 */
	galleryField.ImageModel = Backbone.Model.extend({
		/**
		 * Prevents automatic syncronisations of attachments with the backend.
		 */
		sync: function() {
			return false; // No automatic syncing
		}
	});

	/**
	 * Handles a collection of local images.
	 */
	galleryField.ImagesCollection = Backbone.Collection.extend({
		model: galleryField.ImageModel
	});

	/**
	 * Handles the input of the audio field.
	 */
	galleryField.View = fileField.View.extend({
		events: {
			'click .uf-video-sort': 'sort'
		},

		/**
		 * Renders a preview when there is a selection.
		 */
		renderPreview: function() {
			var that = this,
				tmpl = UltimateFields.template( 'gallery' ),
				buttons = [];

			// Add the basic template
			this.$el.html( tmpl( {} ) );

			// Load images
			that.loadPreview();

			// Remove button
			buttons.push({
				text: UltimateFields.L10N.localize( 'gallery-remove' ),
				icon: 'dashicons-no',
				callback: function() {
					that.model.setValue( [] );
					that.updateView();
				}
			});

			// Update button
			if( this.model.get( 'basic' ) ) {
				buttons.push( this.getAddButton() );
			} else {
				buttons.push({
					text: UltimateFields.L10N.localize( 'gallery-select' ),
					icon: 'dashicons-admin-media',
					type: 'primary',
					callback: function() {
						that.openPopup();
					}
				});
			}

			// Render buttons
			_.each( buttons, function( button ) {
				button = ( button instanceof UltimateFields.Button ) ? button : new UltimateFields.Button( button );
				button.$el.prependTo( that.$el.find( '.uf-gallery-footer' ) );
				button.render();
			});

			// Adjust the size of the gallery when the gallery is visible
			$( window ).on( 'resize', _.bind( this.responsive, this ) );
			$( document ).on( 'uf-grid-resize', _.bind( this.responsive, this ) );

			UltimateFields.ContainerLayout.DOMUpdated();
		},

		/**
		 * Sorts images when the button is clicked.
		 */
		sort: function( e ) {
			var that = this,
				$select = this.$el.find( '.uf-gallery-order select' ),
				order;

			e.preventDefault();

			// Get the order and sort
			order = $select.val();
			this.changeOrder( order );

			// Reset the select
			$select.val( '' );
		},

		/**
		 * Changes the order of the attachments that are added to the field.
		 *
		 * @param <string> order The order that should be followed.
		 */
		changeOrder: function( order ) {
			var that      = this,
				sort      = '',
				direction = '',
				atts      = this.model.get( 'attachments' );

			// Determine the order.
			if( order == 'random' ) {
				sort = 'random';
			} else if( order == 'default' ) {
				sort      = 'menuOrder';
				direction = 'asc';
			} else if( order == 'default-reversed' ) {
				sort      = 'menuOrder';
				direction = 'desc';
			} else {
				order     = order.split( '-' );
				sort      = order[ 0 ];
				direction = order[ 1 ];
			}

			// Sort items
			if( 'random' == sort ) {
				atts.reset( atts.shuffle(), {
					silent:true
				});
			} else {
				atts.comparator = function( a, b ) {
					var valueA = a.get( sort ),
						valueB = b.get( sort );

					if( direction == 'desc' ) {
						return valueB > valueA ? -1 : 1;
					} else {
						return valueA > valueB ? -1 : 1;
					}
				}
				atts.sort();
			}

			// "Sort" the DOM elements
			that.model.setValue( atts.map(function( att ) {
				return att.get( 'id' );
			}));

			that.loadPreview();
		},

		/**
		 * Generates the preview once all files have been loaded.
		 */
		generatePreview: function( data ) {
			var that = this;

			// Empty the existing list first
			this.$el.find( '.uf-gallery-images' ).empty();

			// Convert the raw data to attachments
			var attachments = new galleryField.ImagesCollection( data );
			this.model.set( 'attachments', attachments );

			// Render the gallery
			this.renderGallery();

			// When an attachment is modified, change the value
			attachments.on( 'all', function() {
				that.model.setValue( attachments.map(function( attachment ) {
					return attachment.get( 'id' );
				}));

				// At some point there might be no images, so it's nice to re-render
				if( ! that.model.getValue().length ) {
					that.updateView();
				}
			});
		},

		/**
		 * Renders the images after all of them are loaded.
		 */
		renderGallery: function() {
			var that = this;

			// Add images
			this.model.get( 'attachments' ).each(function( attachment ) {
				that.renderAttachment( attachment );
			});

			// Hide the loading message
			this.$el.find( '.uf-gallery' ).addClass( 'uf-gallery-loaded' );

			// Make images sortable
			this.$el.find( '.uf-gallery-images' ).sortable({
				selector: '.uf-gallery-image',
				tolerance: 'pointer',
				stop: function() {
					var ids = [];

					that.$el.find( '.uf-gallery-image' ).each(function() {
						ids.push( $( this ).data( 'attachment' ).get( 'id' ) );
					});

					that.model.setValue( ids );
				}
			});
		},

		/**
		 * Changes the columns of the gallery.
		 */
		responsive: function() {
			var $gallery = this.$el.find( '.uf-gallery-images' ),
				prefix   = 'uf-gallery-columns-',
				className;


			if( ! $gallery.length ) {
				return;
			}

			className = prefix + Math.ceil( $gallery.width() / 160 );

			if( ! $gallery.is( '.' + className ) ) {
				// Remove old classes
				_.each( $gallery.get( 0 ).classList, function( existing ) {
					if( 0 === existing.indexOf( prefix ) ) {
						$gallery.removeClass( existing );
					}
				});

				$gallery.addClass( className );
			}
		},

		/**
		 * Adds an attachment to the visible gallery.
		 *
		 * @param <wp.media.model.Attachment> attachment THe attachment to be displayed.
		 */
		renderAttachment: function( attachment ) {
			var that  = this,
				sizes = attachment.get ? attachment.get( 'sizes' ) : attachment.sizes,
				img   = sizes.thumbnail ? sizes.thumbnail : sizes.full,
				$div  = $( '<div class="uf-gallery-image" />' ),
				$img  = $( '<img />' ),
				$rem  = $( '<a class="button-secondary" />' );

			$div
				.appendTo( that.$el.find( '.uf-gallery-images' ) )
				.data( 'attachment', attachment );

			// Prepare the image
			$img
				.attr( 'width', img.width )
				.attr( 'height', img.height )
				.attr( 'src', img.url )
				.appendTo( $div );

			$img.wrap( '<div class="uf-gallery-image-inner" />' );

			// Add a delete button
			$rem
				.append( '<span class="dashicons dashicons-no"></span>' )
				.appendTo( $div )
				.click(function( e ) {
					e.preventDefault();
					attachment.destroy();
					$div.fadeOut(function() {
						$div.remove();
					});
				});
		},

		/**
		 * Creates a button that is used for adding images to the gallery.
		 */
		getAddButton: function() {
			var text = UltimateFields.L10N.localize( this.model.get( 'poster' ) ? 'video-change-poster' : 'video-add-poster' );

			return new UltimateFields.UploaderButton({
				text:             UltimateFields.L10N.localize( 'gallery-select' ),
				icon:             'dashicons-admin-media',
				type:             'primary',
				uploaderSettings: this.model.generateUploaderSettings(),
				callback:         _.bind( function( fileIds ) {
					this.model.get( 'attachments' ).add( _.map( fileIds , function( id ) {
						return fileField.Cache.get( id );
					}));

					this.updateView();
				}, this )
			});
		}
	});

})( jQuery );
