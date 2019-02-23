(function( $ ){

	var field     = UltimateFields.Field,
		fileField = field.File = { cache: {} };

	/**
	 * Manages the cache of the file field.
	 */
	fileField.Cache = {
		/**
		 * The items of the cache.
		 */
		items: new ( Backbone.Collection.extend({
			sync: function() {
				return false;
			}
		})),

		/**
		 * This model will be used if wp.media is not available.
		 */
		Model: Backbone.Model.extend({
			sync: function() {
				return false
			}
		}),

		/**
		 * Adds an attachment to the cache.
		 */
		add: function( item ) {
			if( item instanceof Backbone.Model ) {
				fileField.Cache.items.add( item );
			} else if( 'object' == typeof item ) {
				if( wp && wp.media ) {
					fileField.Cache.items.add( wp.media.model.Attachment.get( item.id, item ) );
				} else {
					fileField.Cache.items.add( new fileField.Cache.Model( item ) );
				}
			} else if( wp && wp.media ) {
				fileField.Cache.items.add( wp.media.model.Attachment.get( item ) );
			}
		},

		/**
		 * Retrieves an item from the cache, returning a backbone model.
		 *
		 * Instead of directly returning the item, this will
		 */
		get: function( id ) {
			return this.items.get( id );
		}
	}

	/**
	 * Basic model for files.
	 */
	fileField.Model = field.Model.extend({
		defaults: $.extend({
			multiple: false,
			preview_size: 'thumbnail'
		}, field.Model.prototype.defaults ),

		/**
		 * Upon initialization, create an array with files the plugin is listening to.
		 */
		initialize: function() {
			var that = this;

			// super
			field.Model.prototype.initialize.apply( this, arguments );

			// holds all listeners to avoid multiple callbacks
			this.listeningTo = [];

			// Throttles a change to avoid flooding the datastore
			this.throttledChangeTrigger = _.throttle(function() {
				that.datastore.trigger( 'change' );
			}, 1000);
		},

		/**
		 * When a datastore is being checked, look for some file cache.
		 */
		setDatastore: function( datastore ) {
			// Call the parent
			field.Model.prototype.setDatastore.apply( this, arguments );

			// Load files
			this.cachePreloadedFiles( datastore );

			// Listen for the currently selected file
			this.subscribeToCurrentFile();
		},

		/**
		 * Caches all preloaded files from a datastore.
		 */
		cachePreloadedFiles: function( datastore ) {
			var prepared, current;

			// Check for values to preload
			if( prepared = datastore.get( this.get( 'name' ) + '_prepared' ) ) {
				_.each( prepared, function( data, id ) {
					fileField.Cache.add( data );
				});
			}
		},

		/**
		 * Returns context for the customizer.
		 */
		getCustomizerContext: function() {
			var context = false, id = this.getValue();

			if( id ) {
				context = fileField.Model.getCachedFile( id );
			}

			return context;
		},

		/**
		 * Generates arguments for a custom plupload uploader.
		 */
		generateUploaderSettings: function() {
			var args = {
				multiple: this.get( 'multiple' ),
				settings: {}
			};

			// Add arguments for the handler
			_.extend( args.settings, {
				url:              this.get( 'uploader_url' ),
				file_data_name:   'uf_file',
				send_file_name:   false,
				multipart_params: {
					_wpnonce:      this.get( 'nonce' ),
					uf_action:     'file_upload_' + this.get( 'name' ),
					uf_force_ajax: true
				}
			});

			return args;
		},

		/**
		 * Generates an array with all IDs, which are used within the field.
		 */
		getUsedIDs: function() {
			var that    = this,
				current = this.getValue();

			// Switch to array mode
			if( 'object' !== typeof current ) {
				current = [ current ];
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
			var value = this.getUsedIDs().filter(function( item ) {
				return item != id;
			});

			this.setValue( this.get( 'multiple' ) ? value : value.shift() );
		},

		/**
		 * Handles file changes.
		 */
		fileChanged: function( eventName, event ) {
			var that = this,
				id   = ( eventName.get ? eventName.get( 'id' ) : event.get( 'id' ) ),
				ids;

			// Load the ids
			ids = this.getUsedIDs();

			if( ( ! id ) || -1 == ids.indexOf( id ) ) {
				return;
			}

			if( eventName === 'destroy' || event.destroyed ) {
				that.fileDestroyed( id );
				this.trigger( 'update-views' );
			} else {
				// Trigger a change
				this.throttledChangeTrigger();
			}
		},

		/**
		 * Subscribes the model to changes in the current value.
		 */
		subscribeToCurrentFile: function() {
			var that    = this,
				current = this.getUsedIDs();

			// Listen to each file
			_.each( current, function( id ) {
				var cached = fileField.Cache.get( id );

				if( cached && -1 == that.listeningTo.indexOf( id ) ) {
					cached.on(
						'change:url change:width change:height change:alt destroy',
						_.bind( that.fileChanged, that )
					);
				}
			});
		},

		/**
		 * When a value is being set, subscribe to the IDs from that value.
		 */
		setValue: function( value, args ) {
			field.Model.prototype.setValue.call( this, value, args );

			if( 'object' !== typeof value ) {
				value = [ value ];
			}

			value = value.filter(function( item ) {
				return !! item;
			});

			_.each( value, _.bind( this.subscribeToCurrentFile, this ) );
		}
	}, {
		getCachedFile: function( id ) {
			return fileField.Cache.get( id );
		}
	});

	/**
	 * Handles the input of the file field.
	 */
	fileField.View = field.View.extend({
		/**
		 * Adds some basic listeners.
		 */
		initialize: function() {
			field.View.prototype.initialize.apply( this, arguments );
			this.model.on( 'update-views', _.bind( this.updateView, this ) );
		},

		/**
		 * Renders the content of the field.
		 */
		render: function() {
			this.updateView();
		},

		/**
		 * If there is a file, renders a button, otherwise a preview.
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
		 * Renders a simple button for choosing files.
		 */
		renderButton: function() {
			var that = this, button;

			button = new UltimateFields.Button({
				type:     'primary',
				icon:     'dashicons-admin-media',
				text:     UltimateFields.L10N.localize( 'file-select' ),
				callback: function() {
					that.openPopup();
				}
			});

			button.$el.appendTo( this.$el );
			button.render();
		},

		/**
		 * Renders an uploader for new files.
		 */
		renderUploader: function() {
			var uploader = new fileField.UploaderView({
				model: this.model
			});

			uploader.$el.appendTo( this.$el );
			uploader.render();

			// Whenever a file has been uploaded and selected, display it
			uploader.on( 'fileSelected', _.bind( this.updateView, this ) );
		},

		/**
		 * Opens the popup for selecting a file.
		 */
		openPopup: function() {
			var that = this,
				args, frame, type;

			// Arguments for the media popup
			args = {
				title:    UltimateFields.L10N.localize( 'file-select' ),
				multiple: this.model.get( 'multiple' ),
				button: {
					text: UltimateFields.L10N.localize( 'file-save' )
				},
			};

			// Set the needed file type.
			if( type = that.getFileType() ) args.library = {
				type: that.getFileType()
			};

			// Create and setup the popup
			frame = wp.media( args );

			// Handle selection changes
			frame.state( 'library' ).on( 'select', function(){
				that.fileSelected( this.get( 'selection' ) );
			});

			// Load the right file when opening the frame.
			frame.on( 'open', function() {
				that.changeSelection( frame );
			});

			// Open the popup
			frame.modal.open();

			var overlay = UltimateFields.Overlay.show({
				view: frame.modal,
				title: 'Select file',
				buttons: [],
				media: true
			});

			frame.modal.on( 'close', function() {
				overlay.removeScreen();
			})
		},

		/**
		 * This is the file type that will be passed to the media popup.
		 *
		 * To extend the field for specific formats, change this value.
		 * Can be all/image/video/audio.
		 *
		 * @return <string>.
		 */
		getFileType: function() {
			var type = this.model.get( 'file_type' );

			if( ! type || 'all' == type ) {
				return false;
			}

			// Split types
			type = type.split( ',' );

			return type;
		},

		/**
		 * Changes the selection once the popup is open.
		 *
		 * @param <wp.media> frame The frame that is used.
		 */
		changeSelection: function( frame ) {
			var that = this, value;

			// Check if there is something to select
			if( ! ( value = that.model.getValue() ) || ( 'object' == typeof value ) && ! value.length ) {
				return;
			}

			// Make sure there is an array
			if( ! this.model.get( 'multiple' ) && 'object' != typeof value ) {
				value = [ value ];
			}

			// Select
			_.each(value, function( id ) {
				var selection, attachment;

				selection  = frame.state().get( 'selection' ),
				attachment = wp.media.attachment( id );
				attachment.fetch();
				selection.add( attachment ? [ attachment ] : [] );
			});
		},

		/**
		 * Handles file selection.
		 */
		fileSelected: function( selection ) {
			if( this.model.get( 'multiple' ) ) {
				var ids = [];

				selection.each(function( attachment ) {
					ids.push( attachment.get( 'id' ) );
					fileField.Cache.add( attachment );
				});

				this.model.setValue( ids );
			} else {
				var attachment = selection.first();

				// Cache the value
				fileField.Cache.add( attachment );

				// Save the value
				this.model.setValue( attachment.get( 'id' ) );
			}

			// Change the preview
			this.updateView();
		},

		/**
		 * Renders a preview for the file.
		 */
		renderPreview: function() {
			var that = this,
				tmpl = UltimateFields.template( 'file' ),
				button, $buttons;

			// Add the basic template
			this.$el.html( tmpl({
				or: UltimateFields.L10N.localize( 'file-or' )
			}));

			// Add the basic preview
			this.loadPreview();

			// Button time!
			$buttons = this.$el.find( '.uf-file-buttons' );

			// Add an edit button if the media modal is available
			if( ! this.model.get( 'basic' ) ) {
				button = new UltimateFields.Button({
					text:     '',
					title:     UltimateFields.L10N.localize( 'file-edit' ),
					icon:     'dashicons dashicons-edit',
					type:     'primary',
					callback: function() {
						that.openPopup();
					}
				});

				button.$el.appendTo( $buttons );
				button.render();
			}

			// Add a remove button
			button = new UltimateFields.Button({
				text:     UltimateFields.L10N.localize( 'file-remove' ),
				icon:     'dashicons dashicons-no',
				type:     'secondary',
				callback: function() {
					that.model.setValue( false );
					that.updateView();
				}
			});

			button.$el.appendTo( $buttons );
			button.render();
		},

		/**
		 * Prepares data for attachment(s), while checking the cache for existing data.
		 */
		loadFiles: function( file_ids, callback ) {
			var that = this, prepared, missing, needed = 0;

			// Check if there are cached items and which ones
			prepared = [];
			missing  = [];

			_.each( file_ids, function( id, index ){
				if( fileField.Cache.get( id ) ) {
					prepared.push( fileField.Cache.get( id ) );
				} else {
					prepared.push( false );
					missing.push( id );
					needed++;
				}
			});

			// If nothing is needed, just proceed
			if( 0 == needed ) {
				callback( prepared );
				return;
			}

			// Request the needed files
			$.ajax({
				url: window.location.href,
				type: 'post',
				data: {
					uf_action: 'file_preview_' + this.model.get( 'name' ),
					file_ids: missing,
					nonce:    this.model.get( 'nonce' )
				},
				success: function( result ) {
					var data;

					if( result ) {
						data = $.parseJSON( result );

						_.each( prepared, function( value, index ) {
							var model;

							if( false === value ) {
								model = data.shift();
								prepared[ index ] = model;
								fileField.Cache.add( model );
							}
						});

						callback( prepared );
					}
				}
			});
		},

		/**
		 * Generates a preview for the current file.
		 */
		loadPreview: function() {
			var that  = this, value, file_ids;

			value    = this.model.getValue();
			file_ids = this.model.get( 'multiple' ) ? value : [ value ];

			this.loadFiles( file_ids, function( files ) {
				var good = 0;

				_.each( files, function( file ) {
					if( file instanceof Backbone.Model ) {
						file = file.toJSON();
					}

					if( file && ! ( 'missing' in file ) ) {
						fileField.Cache.add( file );
						good++;
					}
				});

				if( good ) {
					that.generatePreview( files );
				} else {
					that.model.setValue( false );
					that.updateView();
				}
			});
		},

		/**
		 * Genrates the preview for the field based on a file's data.
		 */
		generatePreview: function( data ) {
			var that = this,
				$preview, icon;

			// Use the first element
			data = data[ 0 ];

			if( ! ( data instanceof Backbone.Model ) ) {
				data = new wp.media.model.Attachment( data );
			}

			// Locate the elmenent
			$preview = that.$el.find( '.uf-file-preview' );
			if( 'image' == data.get( 'type' ) && data.get( 'sizes' ) ) {
				var thumb = data.get( 'sizes' )[ this.model.get( 'preview_size' ) ];
				if( ! thumb ) {
					thumb = data.get( 'sizes' ).full;
				}
				icon = '<img src="' + thumb.url + '" alt="" width="' + thumb.width + '" height="' + thumb.height + '" />';

				$preview
					.html( icon )
					.find( 'img' )
					.addClass( 'thumb' );
			} else {
				icon = '<img src="' + data.get( 'icon' ) + '" alt="" />';

				$preview
					.html( icon )
					.find( 'img' )
						.addClass( 'icon' )
						.after( $( '<em />' ).text( data.get( 'url' ).split( '/' ).pop() ) );
			}
		},

		/**
		 * Focuses on whatever is visible.
		 */
		focus: function() {
			this.$el.find( '.uf-button' ).eq( 0 ).focus();
		}
	});

})( jQuery );
