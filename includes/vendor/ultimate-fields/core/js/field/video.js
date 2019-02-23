(function( $ ){

	var field      = UltimateFields.Field,
		fileField  = field.File,
		videoField = field.Video = {};

	/**
	 * Basic model for files.
	 */
	videoField.Model = fileField.Model.extend({
		defaults: $.extend( {}, fileField.Model.prototype.defaults, {
			multiple: true
		}),

		/**
		 * Whenever the datastore is changed, set an internal object for data.
		 */
		setDatastore: function( datastore ) {
			var value, atts;

			// Call the parent
			field.Model.prototype.setDatastore.apply( this, arguments );

			// Load a value
			value = datastore.get( this.get( 'name' ) );
			if( ( ! value ) && this.get( 'default_value' ) ) {
				value = this.get( 'default_value' );
			}

			// Setup initial values
			if( value ) {
				atts = {
					videos: value.videos || [],
					poster: value.poster || false
				}
			} else {
				atts = {
					videos: [],
					poster: false
				}
			}

			this.set( atts, {
				silent: true
			});

			// Set callbacks
			this.on( 'change:videos', _.bind( this.saveValue, this ) );
			this.on( 'change:poster', _.bind( this.saveValue, this ) );

			// Load files
			this.cachePreloadedFiles( datastore );

			// Listen for the currently selected file
			this.subscribeToCurrentFile();
		},

		/**
		 * Exports the video IDs and poster to the datastore.
		 */
		saveValue: function() {
			var value = {
				videos: this.get( 'videos' ),
				poster: this.get( 'poster' )
			};

			this.datastore.set( this.get( 'name' ), value );
		},

		/**
		 * Whenever the normal setValue method is called, assume videos only.
		 */
		setValue: function( ids ) {
			// When using a default value, ignore it.
			if( ( 'object' == typeof ids ) && ( 'videos' in ids ) ) {
				return;
			}

			this.set( 'videos', ids );
			_.each( ids, _.bind( this.subscribeToCurrentFile, this ) );
		},

		/**
		 * Returns the basic value of the field (video IDs)
		 */
		getValue: function() {
			return this.get( 'videos' );
		},

		/**
		 * Generates an array with all IDs, which are used within the field.
		 */
		getUsedIDs: function() {
			var current = ( this.get( 'videos' ) || [] ).concat( [] );

			if( this.get( 'poster' ) ) {
				current.push( this.get( 'poster' ) );
			}

			// Filter valid IDs only
			current = current.filter(function( element ) {
				return !! element;
			});

			return current;
		},

		/**
		 * When a file is destroyed, this will normalize the internal value.
		 */
		fileDestroyed: function( id ) {
			if( -1 !== this.get( 'videos' ).indexOf( id ) ) {
				this.set( 'videos', this.get( 'videos' ).filter(function( chosen ) {
					return chosen != id;
				}));
			}

			if( id == this.get( 'poster' ) ) {
				this.set( 'poster', false );
			}
		},

		/**
		 * Changes the poster of the video.
		 */
		setPoster: function( id ) {
			this.set( 'poster', id );

			if( id ) {
				// Subscribe
				this.subscribeToCurrentFile();
			}
		}
	});

	/**
	 * Handles the input of the audio field.
	 */
	videoField.View = fileField.View.extend({
		/**
		 * If there are files, renders a button, otherwise a preview.
		 */
		updateView: function() {
			var value = this.model.getValue();

			// Ensure old previews are gone
			this.$el.empty();

			// Check for empty arrays
			if( this.model.get( 'multiple' ) && ( ! value || ! value.length ) ) {
				value = false;
			}

			// Render the appropriate thing
			if( value ) {
				this.renderPreview();
			} else {
				if( this.model.get( 'basic' ) ) {
					this.renderUploader();
				} else {
					this.renderButton();
				}
			}
		},

		/**
		 * Renders the inline preview of the field.
		 */
		renderPreview: function() {
			var that = this,
				tmpl = UltimateFields.template( 'video' ),
				buttons = [], button;

			// Add the basic template
			this.$el.html( tmpl({
			}));

			// Add the basic preview
			this.loadPreview();

			// Add an poster button
			if( this.model.get( 'basic' ) ) {
				buttons.push( this.getUploaderButton() );
			} else {
				buttons.push({
					text:     UltimateFields.L10N.localize( this.model.get( 'poster' ) ? 'video-change-poster' : 'video-add-poster' ),
					icon:     'dashicons dashicons-format-image',
					type:     'secondary',
					cssClass: 'uf-video-button-left',
					callback: function() {
						that.openPosterPopup();
					}
				});
			}

			// If there is a poster, remove the poster
			if( this.model.get( 'poster' ) ) {
				buttons.push({
					text:     UltimateFields.L10N.localize( 'video-remove-poster' ),
					icon:     'dashicons dashicons-trash',
					type:     'secondary',
					cssClass: 'uf-video-button-left',
					callback: function() {
						that.model.setPoster( false );
						that.updateView();
					}
				});
			}

			// Add a clear button
			buttons.push({
				text:     UltimateFields.L10N.localize( 'clear' ),
				icon:     'dashicons dashicons-no',
				type:     'secondary',
				cssClass: 'uf-video-button-right',
				callback: function() {
					that.model.setValue([]);
					that.updateView();
				}
			});

			// Add a format changer
			if( ! this.model.get( 'basic' ) ) {
				buttons.push({
					text:     UltimateFields.L10N.localize( 'video-select-files' ),
					icon:     'dashicons dashicons-edit',
					type:     'primary',
					cssClass: 'uf-video-button-right',
					callback: function() {
						that.openPopup();
					}
				});
			}

			// Append and render buttons
			_.each( buttons, function( button ) {
				button = button instanceof UltimateFields.Button ? button : new UltimateFields.Button( button );
				button.$el.appendTo( that.$el.find( '.uf-video-footer' ) );
				button.render();
			});
		},

		/**
		 * Generates the actual video element preview.
		 */
		generatePreview: function( data ) {
			var that = this,
				$preview,
				$previewDiv,
				poster,
				width = 0,
				height = 0;

			$preview    = $( '<video controls ="controls" />' );
			$previewDiv = that.$el.find( '.uf-video-preview' );
			$previewWrapper = that.$el.find( '.uf-video-preview-wrapper' );

			// Extract the poster
			if( this.model.get( 'poster' ) ) {
				poster = fileField.Cache.get( this.model.get( 'poster' ) );
				$preview.attr( 'poster', poster.get( 'url' ) );
			}

			// Add video formats
			_.each( data, function( file ) {
				file = 'function' == typeof file.get
					? file
					: new Backbone.Model( file );

				var src = file.get( 'url' );

				if( file.get( 'id' ) != that.model.get( 'poster' ) ) {
					width  = file.get( 'width' );
					height = file.get( 'height' );
				}

				if( ! src ) {
					return;
				}

				$preview
					.append( $( '<source />' ).attr( 'src', src ) );
			});

			$previewWrapper.css({
				paddingBottom: ( ( height / width ) * 100 ) + '%'
			})

			$previewDiv.show().append( $preview );
		},

		/**
		 * Opens a popup for choosing a poster.
		 */
		openPosterPopup: function() {
			var that = this,
				frame;

			// Create and setup the popup
			frame = wp.media({
				title:    UltimateFields.L10N.localize( 'video-select-poster' ),
				multiple: false,
				button: { text: UltimateFields.L10N.localize( 'file-save' ) },
				library: { type: 'image' }
			});

			// Handle selection changes
			frame.state( 'library' ).on( 'select', function(){
				that.posterSelected( this.get( 'selection' ) );
			});

			// Load the right file when opening the frame.
			frame.on( 'open', function() {
				that.changePosterSelection( frame );
			});

			// Open the popup
			frame.open();
		},

		/**
		 * Changes the selection once the poster popup is open.
		 *
		 * @param <wp.media> frame The frame that is used.
		 */
		changePosterSelection: function( frame ) {
			var that = this,
				value = this.model.get( 'poster' ),
				selection;

			if( ! value ) {
				return;
			}

			selection  = frame.state().get( 'selection' ),
			attachment = wp.media.attachment( value );
			attachment.fetch();
			selection.add( attachment ? [ attachment ] : [] );
		},

		/**
		 * Handles file selection.
		 */
		posterSelected: function( selection ) {
			var att = selection.first().get( 'id' );

			// Fill the cache
			fileField.Cache.add( att );

			// Save the value
			this.model.setPoster( att );

			// Change the preview
			this.updateView();
		},

		/**
		 * Generates a preview for the current files.
		 */
		loadPreview: function() {
			var that     = this,
				file_ids = _.clone( this.model.getValue() );


			if( this.model.get( 'poster' ) ) {
				file_ids.push( this.model.get( 'poster' ) );
			}

			this.loadFiles( file_ids, function( files ) {
				that.generatePreview( files );
			});
		},

		/**
		 * Generates an uploader button.
		 */
		getUploaderButton: function() {
			var text = UltimateFields.L10N.localize( this.model.get( 'poster' ) ? 'video-change-poster' : 'video-add-poster' );

			return new UltimateFields.UploaderButton({
				text:             text,
				icon:             'dashicons dashicons-format-image',
				type:             'secondary',
				cssClass:         'uf-video-button-left',
				uploaderSettings: this.model.generateUploaderSettings(),
				callback:         _.bind( function( fileIds ) {
					this.model.setPoster( fileIds[ 0 ] );
					this.updateView();
				}, this )
			});
		},

		/**
		 * Adjust the view of the input to a certain width.
		 */
		adjustToWidth: function( width ) {
			width = width || this.$el.width();

			this.$el[ width > 500 ? 'removeClass' : 'addClass' ]( 'uf-video-small' );
		}
	});

})( jQuery );
