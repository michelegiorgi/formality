(function( $ ){

	var field     = UltimateFields.Field,
		fileField = field.File;

	/**
	 * Displays a big uploader within a field.
	 */
	fileField.UploaderView = Backbone.View.extend({
		render: function() {
			var that = this, args, uploader;

			// Create an uploader view
			args = this.model.generateUploaderSettings();
			uploader = new UltimateFields.Uploader( args );

			// Listen for uploaded files
			uploader.model.on( 'change:selected', function(){
				that.model.setValue( uploader.model.get( 'selected' ) );
				that.trigger( 'fileSelected' );
			});

			// Render the uploader
			uploader.$el.appendTo( this.$el );
			uploader.render();
		}
	});

	/**
	 * Displays a single button that works as an uploader.
	 */
	UltimateFields.UploaderButton = UltimateFields.Button.extend({
		tagName: 'div',
		className: 'uf-button uf-uploader-button',
		events: {},

		render: function() {
			var that = this, $trigger, icon, args, uploder;

			// Add generic classes
			this.$el.addClass( 'button-' + this.model.get( 'type' ) );
			this.$el.addClass( this.model.get( 'cssClass' ) );

			// Add an actual link
			$trigger = $( '<a href="#" class="uf-button-a" />' )
				.text( this.model.get( 'text' ) );
			$trigger.appendTo( this.$el );

			// Add an icon
			if( icon = this.model.get( 'icon' ) ) {
				if( icon.match( /^dashicons-[^\s]+$/ ) ) {
					icon = 'dashicons ' + icon;
				}

				$trigger.prepend(  '<span class="uf-button-icon ' + icon + '"></span>');
			}

			// Create an uploader view
			args = this.model.get( 'uploaderSettings' );
			args.$trigger = $trigger;
			args.$drag    = this.$el;
			uploader = new UltimateFields.Uploader( args );

			// // Listen for uploaded files
			uploader.model.on( 'change:selected', function(){
				that.model.get( 'callback' )( uploader.model.get( 'selected' ) );
			});

			// Render the uploader
			uploader.$el.appendTo( this.$el );
			uploader.$el.addClass( 'uf-uploader-incognito' );
			uploader.render();

			// Listen for loading
			uploader.on( 'loadingStarted', function() {
				that.$el.addClass( 'uf-button-loading' );
			});
			uploader.on( 'loadingEnded', function() {
				that.$el.removeClass( 'uf-button-loading' );
			});
		}
	});

	/**
	 * Handles an uploader for custom files.
	 */
	UltimateFields.Uploader = Backbone.View.extend({
		className: 'uf-uploader',

		/**
		 * Initialize the view and create a hidden model.
		 */
		initialize: function( args ) {
			var args, defaults;

			// Prepare defaults for the model
			defaults = {
				multiple: false,
				selected: []
			}

			// Save arguments within a model
			this.model = new Backbone.Model( _.extend( {}, defaults, args ) );
		},

		/**
		 * Render the uploader.
		 */
		render: function() {
			var that = this,
				tmpl = UltimateFields.template( 'file-uploader' ),
				$drag, $trigger, settings, uploader;

			// Render the element and get the uploader
			if( this.model.get( '$drag' ) ) {
				$drag    = this.model.get( '$drag' );
				$trigger = this.model.get( '$trigger' );
			} else {
				this.$el.html( tmpl( this.model.toJSON() ) );
				$drag    = this.$el.find( '.uf-uploader-drop' );
				$trigger = this.$el.find( 'a' );
			}

			// Generate settings
			settings = _.extend( this.generatePluploadSettings(), {
				browse_button:   $trigger.get( 0 ),
				container:       $drag.get( 0 ),
				drop_element:    $drag.get( 0 )
			});

			// Create the uploader, add listeners and init
			uploader = new plupload.Uploader( settings );
			this.addUploaderListeners( uploader );
			uploader.init();

			// Add styles
			$drag.on({
				dragenter: function() {
					that.$el.addClass( 'uf-uploader-drop-active' );
				},
				dragleave: function() {
					that.$el.removeClass( 'uf-uploader-drop-active' );
				}
			});
		},

		/**
		 * Generates settings for plupload.
		 */
		generatePluploadSettings: function() {
			return _.extend(
				{},
				_wpPluploadSettings.defaults,
				this.model.get( 'settings' ),
				{
					url: window.location.href,
					multi_selection: this.model.get( 'multiple' )
				}
			)
		},

		/**
		 * Adds event listeners to an uploader.
		 */
		addUploaderListeners: function( uploader ) {
			_.each({
				FilesAdded:     _.bind( this.filesAdded, this ),
				UploadProgress: _.bind( this.uploadProgress, this ),
				Error:          _.bind( this.uploadError, this ),
				FileUploaded:   _.bind( this.fileUploaded, this ),
				UploadComplete: _.bind( this.uploadComplete, this ),
			}, function( handler, event ) {
				uploader.bind( event, handler );
			});
		},

		/**
		 * When files are added to the uploader, this will start uploading them.
		 */
		filesAdded: function( up, files ) {
			// Save the amount of total files (used for progress)
			this.totalFiles  = files.length;
			this.currentFile = 1;
			this.files       = [];

			// Auto-start the upload
			up.start();

			// Some events
			this.trigger( 'loadingStarted' );
		},

		/**
		 * Handles upload progress indication.
		 */
		uploadProgress: function( up, file ) {
			var total, perFile;

			// Calculate the percentage
			perFile = Math.ceil( 100 / this.totalFiles );
			total   = ( this.currentFile - 1 ) * perFile;
			total  += Math.ceil( file.percent / this.totalFiles );

			// Change the indicator
			this.$el.find( '.uf-uploader-progressbar' ).css({
				width: total + '%'
			});
		},

		/**
		 * Handlers errors on upload.
		 */
		uploadError: function( up, err ) {
			console.log("Error #" + err.code + ": " + err.message);
		},

		/**
		 * When a file is uploaded, save its response.
		 */
		fileUploaded: function( up, files, result  ) {
			var json = $.parseJSON( result.response );
			this.currentFile++;

			if( ( 'success' in json ) && ! json.success ) {
				alert( json.message );
				return;
			}

			// Save the file for later
			this.files.push( json );
		},

		/**
		 * When everything is complete, save it in the cache and model.
		 */
		uploadComplete: function() {
			var that         = this,
				$progressbar = this.$el.find( '.uf-uploader-progressbar' ),
				ids          = [];

			// Cleanup and remove the progress bar
			$progressbar.addClass( 'uf-uploader-progressbar-hidden' );
			setTimeout(function() {
				$progressbar
					.hide()
					.css({ width: 0 })
					.removeClass( 'uf-uploader-progressbar-hidden' );
			}, 500 );

			// Check for errors
			if( 1 == this.files.length && this.files[ 0 ].error ) {
				alert( this.files[ 0 ].error );
				return;
			}

			// Process files
			_.each( this.files, function( data ) {
				var file = data.data;

				// Save the attachment in the cache of the file field
				fileField.Cache.add( file );

				// Add the ID to the value
				ids.push( file.id );
			});

			// Save the value
			this.model.set( 'selected', this.model.get( 'multiple' ) ? ids : ids[ 0 ] )

			// External events
			this.trigger( 'loadingEnded' );
		}
	});

})( jQuery );
